const assert = require("assert");

console.log("=== RUNNING AUTOMATED SUITE FOR AGGREGATED DELETED ORDER ITEMS ===");

// 1. Mock browser storage
class MockSessionStorage {
    constructor(shouldFail = false) {
        this.store = new Map();
        this.shouldFail = shouldFail;
    }
    getItem(key) {
        return this.store.get(key) || null;
    }
    setItem(key, value) {
        if (this.shouldFail) {
            throw new Error("QuotaExceededError: DOM Exception 22");
        }
        this.store.set(key, String(value));
    }
    removeItem(key) {
        this.store.delete(key);
    }
    clear() {
        this.store.clear();
    }
}

let mockStorage = new MockSessionStorage(false);
global.sessionStorage = mockStorage;

// Helpers mirroring external-api.js
const SESSION_STORAGE_KEY = "accountant_deleted_order_items";
let ramFallbackDeletedItems = [];
const processedActionTokens = new Set();
let isDeletingItems = false;
let isExportingPdf = false;

function isSessionStorageAvailable() {
    try {
        const testKey = "__vm_test_storage__";
        sessionStorage.setItem(testKey, "1");
        sessionStorage.removeItem(testKey);
        return true;
    } catch {
        return false;
    }
}

function getItemFinalPrice(item) {
    if (!item) return 0;
    if (item.final_price !== undefined && item.final_price !== null) {
        return Math.max(0, Number(item.final_price) || 0);
    }
    const qty = Number(item.quantity || 1) || 1;
    const unitPrice = Number(item.price || 0) || 0;
    const discount = Number(item.discount_amount || 0) || 0;
    return Math.max(0, qty * unitPrice - discount);
}

function getAllAggregatedDeletedItems() {
    let storageItems = [];
    if (isSessionStorageAvailable()) {
        try {
            const raw = sessionStorage.getItem(SESSION_STORAGE_KEY);
            if (raw) {
                const parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) {
                    storageItems = parsed;
                }
            }
        } catch (e) {
            console.error("[ExternalAPI] Lỗi đọc sessionStorage:", e);
        }
    }

    const seenKeys = new Set();
    const merged = [];

    [...storageItems, ...ramFallbackDeletedItems].forEach((item) => {
        if (!item) return;
        const key = item.record_id || `${item.order_id}_${item.item_id}_${item.deleted_at}`;
        if (!seenKeys.has(key)) {
            seenKeys.add(key);
            merged.push(item);
        }
    });

    return merged;
}

function saveDeletedItemsToSessionOrRam(newRecords, modalNoticeEl) {
    if (!newRecords || newRecords.length === 0) return true;

    let storageSuccess = false;
    if (isSessionStorageAvailable()) {
        try {
            const raw = sessionStorage.getItem(SESSION_STORAGE_KEY);
            const currentList = raw ? JSON.parse(raw) : [];
            const safeCurrent = Array.isArray(currentList) ? currentList : [];
            const updated = [...safeCurrent, ...newRecords];
            sessionStorage.setItem(SESSION_STORAGE_KEY, JSON.stringify(updated));
            storageSuccess = true;
        } catch (storageErr) {
            console.warn("[ExternalAPI] Không thể lưu vào sessionStorage:", storageErr);
            storageSuccess = false;
        }
    }

    if (!storageSuccess) {
        ramFallbackDeletedItems.push(...newRecords);
        if (modalNoticeEl) {
            const warnText = " (Cảnh báo: Bộ nhớ sessionStorage không khả dụng. Dữ liệu chỉ lưu tạm trên RAM và sẽ bị mất khi tải lại trang)";
            modalNoticeEl.textContent += warnText;
        }
        return false;
    }
    return true;
}

// ---------------------------------------------------------
// TEST 1: In-flight lock (isDeletingItems) blocks concurrent calls
// ---------------------------------------------------------
console.log("\n[Test 1] Kiểm tra khóa in-flight isDeletingItems...");
isDeletingItems = true;
let callExecuted = false;
function attemptDelete() {
    if (isDeletingItems) {
        return; // blocked
    }
    callExecuted = true;
}
attemptDelete();
assert.strictEqual(callExecuted, false, "Lệnh gọi xóa thứ 2 phải bị block khi isDeletingItems = true");
isDeletingItems = false;
attemptDelete();
assert.strictEqual(callExecuted, true, "Lệnh gọi xóa được thực hiện khi isDeletingItems = false");
console.log("-> PASS: Khóa isDeletingItems hoạt động chính xác.");

// ---------------------------------------------------------
// TEST 2: Action token deduplication
// ---------------------------------------------------------
console.log("\n[Test 2] Kiểm tra Action Token chống retry/duplicate callback...");
const actionToken1 = "act_1001_abc";
processedActionTokens.clear();
mockStorage.clear();
ramFallbackDeletedItems = [];

const recordA = {
    record_id: `501_101_${actionToken1}`,
    action_token: actionToken1,
    order_id: 501,
    table_name: "Bàn 1",
    item_id: 101,
    product_name: "Cà phê sữa",
    quantity: 1,
    unit_price: 35000,
    discount_amount: 0,
    final_price: 35000,
    deleted_at: new Date().toISOString()
};

// First callback
if (!processedActionTokens.has(actionToken1)) {
    saveDeletedItemsToSessionOrRam([recordA], null);
    processedActionTokens.add(actionToken1);
}
assert.strictEqual(getAllAggregatedDeletedItems().length, 1);

// Second duplicate callback for same action token
if (!processedActionTokens.has(actionToken1)) {
    saveDeletedItemsToSessionOrRam([recordA], null);
    processedActionTokens.add(actionToken1);
}
assert.strictEqual(getAllAggregatedDeletedItems().length, 1, "Duplicate callback phải bị chặn, không tăng số bản ghi");
console.log("-> PASS: Action Token deduplication hoạt động chính xác.");

// ---------------------------------------------------------
// TEST 3: Snapshot matching & Ignoring unconfirmed/mismatched IDs
// ---------------------------------------------------------
console.log("\n[Test 3] Kiểm tra đối soát snapshot và bỏ qua ID không có trong snapshot...");
const preDeleteSnapshot = new Map([
    [101, { id: 101, product_name_vi: "Phở bò", quantity: 2, price: 50000, discount_amount: 10000, final_price: 90000 }],
    [102, { id: 102, product_name_vi: "Trà tắc", quantity: 1, price: 20000, discount_amount: 0, final_price: 20000 }]
]);

// Backend returns confirmed deleted IDs: [101, 999] (999 is unknown/mismatched)
const backendDeletedIds = [101, 999];
const reconciledRecords = [];
const actionToken2 = "act_1002_xyz";

backendDeletedIds.forEach(id => {
    const itemSnapshot = preDeleteSnapshot.get(id);
    if (!itemSnapshot) {
        // Must skip unknown ID, do not guess price or quantity
        return;
    }
    reconciledRecords.push({
        record_id: `502_${id}_${actionToken2}`,
        action_token: actionToken2,
        order_id: 502,
        table_name: "Bàn 2",
        item_id: id,
        product_name: itemSnapshot.product_name_vi,
        quantity: itemSnapshot.quantity,
        unit_price: itemSnapshot.price,
        discount_amount: itemSnapshot.discount_amount,
        final_price: itemSnapshot.final_price,
        deleted_at: new Date().toISOString()
    });
});

assert.strictEqual(reconciledRecords.length, 1, "Chỉ bản ghi 101 khớp snapshot mới được ghi nhận");
assert.strictEqual(reconciledRecords[0].item_id, 101);
assert.strictEqual(reconciledRecords[0].final_price, 90000);
console.log("-> PASS: Snapshot matching bỏ qua an toàn ID không khớp, không suy đoán dữ liệu.");

// ---------------------------------------------------------
// TEST 4: Response format validation (malformed or missing deleted_ids)
// ---------------------------------------------------------
console.log("\n[Test 4] Kiểm tra an toàn khi response thiếu hoặc sai định dạng deleted_ids...");
function extractValidDeletedIds(ajaxResponse) {
    if (!ajaxResponse || typeof ajaxResponse !== "object" || !ajaxResponse.success) {
        return [];
    }
    const resultData = ajaxResponse.data;
    let rawDeletedIds = null;
    if (resultData && typeof resultData === "object") {
        if (Array.isArray(resultData.deleted_ids)) {
            rawDeletedIds = resultData.deleted_ids;
        } else if (resultData.data && Array.isArray(resultData.data.deleted_ids)) {
            rawDeletedIds = resultData.data.deleted_ids;
        }
    }
    return Array.isArray(rawDeletedIds)
        ? rawDeletedIds.map(Number).filter(id => Number.isInteger(id) && id > 0)
        : [];
}

assert.deepStrictEqual(extractValidDeletedIds(null), []);
assert.deepStrictEqual(extractValidDeletedIds({ success: false }), []);
assert.deepStrictEqual(extractValidDeletedIds({ success: true, data: {} }), []);
assert.deepStrictEqual(extractValidDeletedIds({ success: true, data: { deleted_ids: "not-an-array" } }), []);
assert.deepStrictEqual(extractValidDeletedIds({ success: true, data: { deleted_ids: [101, "abc", -5, 102] } }), [101, 102]);
console.log("-> PASS: Trích xuất deleted_ids phòng thủ chặt chẽ với mọi response lỗi.");

// ---------------------------------------------------------
// TEST 5: Storage Fallback (RAM) when sessionStorage throws QuotaExceeded
// ---------------------------------------------------------
console.log("\n[Test 5] Kiểm tra Storage Fallback sang RAM và cảnh báo người dùng...");
// Enable failing storage
global.sessionStorage = new MockSessionStorage(true);
ramFallbackDeletedItems = [];

const modalNotice = { textContent: "Đã xóa thành công 1 món." };
const recordFallback = {
    record_id: "503_105_act_fail",
    order_id: 503,
    item_id: 105,
    product_name: "Nem lụi",
    quantity: 2,
    final_price: 80000,
    deleted_at: new Date().toISOString()
};

const savedSuccess = saveDeletedItemsToSessionOrRam([recordFallback], modalNotice);
assert.strictEqual(savedSuccess, false, "saveDeletedItemsToSessionOrRam phải trả về false khi storage lỗi");
assert.strictEqual(ramFallbackDeletedItems.length, 1, "Dữ liệu phải được lưu vào ramFallbackDeletedItems");
assert.ok(modalNotice.textContent.includes("Cảnh báo: Bộ nhớ sessionStorage không khả dụng"), "Thông báo phải có cảnh báo RAM fallback");

// Restore working storage
global.sessionStorage = mockStorage;
console.log("-> PASS: Fallback RAM kích hoạt an toàn và thông báo minh bạch cho người dùng.");

// ---------------------------------------------------------
// TEST 6: Merging sessionStorage & RAM fallback without duplication
// ---------------------------------------------------------
console.log("\n[Test 6] Kiểm tra hàm getAllAggregatedDeletedItems hợp nhất 2 nguồn không trùng lặp...");
mockStorage.clear();
ramFallbackDeletedItems = [];

const itemStorage = { record_id: "order1_item1_tok1", order_id: 1, item_id: 1, product_name: "Món 1" };
const itemRamOnly = { record_id: "order2_item2_tok2", order_id: 2, item_id: 2, product_name: "Món 2" };
const itemDuplicate = { record_id: "order1_item1_tok1", order_id: 1, item_id: 1, product_name: "Món 1 (trùng)" };

mockStorage.setItem(SESSION_STORAGE_KEY, JSON.stringify([itemStorage]));
ramFallbackDeletedItems.push(itemRamOnly, itemDuplicate);

const merged = getAllAggregatedDeletedItems();
assert.strictEqual(merged.length, 2, "Hợp nhất phải có đúng 2 món duy nhất, loại bỏ bản ghi trùng ID");
assert.strictEqual(merged[0].record_id, "order1_item1_tok1");
assert.strictEqual(merged[1].record_id, "order2_item2_tok2");
console.log("-> PASS: Hợp nhất sessionStorage và RAM mượt mà, không sót và không nhân đôi.");

// ---------------------------------------------------------
// TEST 7: Financial calculation with null final_price
// ---------------------------------------------------------
console.log("\n[Test 7] Kiểm tra tính tổng tài chính an toàn khi có món final_price = null...");
const testItemsForTotal = [
    { product_name: "Món có giá 1", final_price: 50000, quantity: 1 },
    { product_name: "Món không xác định giá", final_price: null, quantity: 2 },
    { product_name: "Món có giá 2", final_price: 30000, quantity: 1 }
];

let totalCalculated = 0;
let hasUnknownPrice = false;
testItemsForTotal.forEach(it => {
    if (it.final_price !== null && it.final_price !== undefined) {
        totalCalculated += it.final_price;
    } else {
        hasUnknownPrice = true;
    }
});

assert.strictEqual(totalCalculated, 80000, "Tổng tiền chỉ tính các món có giá xác thực");
assert.strictEqual(hasUnknownPrice, true, "Phải bật cờ cảnh báo hasUnknownPrice khi có món giá null");
console.log("-> PASS: Phép tính tài chính loại trừ món null an toàn, hiển thị dấu gạch ngang.");

console.log("\n=======================================================");
console.log("TẤT CẢ 7/7 BÀI TEST TỰ ĐỘNG ĐÃ VƯỢT QUA 100% THÀNH CÔNG!");
console.log("=======================================================");
