import { jsPDF } from "jspdf";
import { applyPlugin } from "jspdf-autotable";
import { ROBOTO_REGULAR_BASE64, ROBOTO_BOLD_BASE64 } from "../utils/pdf-font";

applyPlugin(jsPDF);

(function ($) {
    "use strict";

    const vmCallAPI = () => {
        const app = document.querySelector("#vm-external-orders");
        const apiConfig = window.vmExternalAPI;

        if (!app || !apiConfig) {
            return;
        }

        // ---------------------------------------------------------------------
        // DOM Elements
        // ---------------------------------------------------------------------

        const timeType = app.querySelector("[data-time-type]");

        const dayField = app.querySelector("[data-filter-day]");
        const weekField = app.querySelector("[data-filter-week]");
        const monthField = app.querySelector("[data-filter-month]");
        const yearField = app.querySelector("[data-filter-year]");

        const dateInput = app.querySelector("[data-date]");
        const weekInput = app.querySelector("[data-week]");
        const monthInput = app.querySelector("[data-month]");
        const yearInput = app.querySelector("[data-year]");

        const orderIdInput = app.querySelector("[data-order-id]");
        const searchOrderButton = app.querySelector("[data-search-order]");
        const clearOrderButton = app.querySelector("[data-clear-order]");

        const callApiButton = app.querySelector("[data-call-api]");
        const tbody = app.querySelector("[data-orders-body]");
        const pagination = app.querySelector("[data-pagination]");
        const loading = app.querySelector("[data-loading]");
        const message = app.querySelector("[data-message]");
        const summary = app.querySelector("[data-summary]");
        const totalOrders = app.querySelector("[data-total-orders]");
        const totalOrdersLabelEl = app.querySelector("[data-total-orders-label]");
        const totalOrdersAmountEl = app.querySelector("[data-total-orders-amount]");
        const totalCashLabelEl = app.querySelector("[data-total-cash-label]");
        const totalCashAmountEl = app.querySelector("[data-total-cash-amount]");
        const totalDeletedAmountEl = app.querySelector("[data-total-deleted-amount]");
        const deletedItemsRateEl = app.querySelector("[data-deleted-items-rate]");

        if (
            !timeType ||
            !dateInput ||
            !weekInput ||
            !monthInput ||
            !yearInput ||
            !callApiButton ||
            !tbody ||
            !pagination ||
            !loading ||
            !message ||
            !summary ||
            !totalOrders
        ) {
            return;
        }

        const ordersTable = tbody.closest("table");
        if (!ordersTable) {
            return;
        }

        let isLoading = false;
        const orderCache = new Map();

        let activeItemsFetchId = 0;
        let currentItemsOrderId = null;
        let currentOrderItems = [];
        const selectedItemIds = new Set();
        let isDeletingItems = false;
        let currentOrdersTotalAmount = 0;
        let currentCashTotalAmount = 0;
        const LOCAL_STORAGE_KEY = "accountant_deleted_order_items";
        let ramFallbackDeletedItems = [];
        let ramFallbackCashAmount = null;
        let ramFallbackDateKey = "";
        const processedActionTokens = new Set();
        let midnightTimerId = null;

        // ---------------------------------------------------------------------
        // Utils
        // ---------------------------------------------------------------------

        const pad = (value) => String(value).padStart(2, "0");

        function escapeHtml(value) {
            const div = document.createElement("div");
            div.textContent = value ?? "";
            return div.innerHTML;
        }

        function money(value) {
            return Number(value || 0).toLocaleString("en-US");
        }

        function formatVnd(value) {
            return `${money(value)} ₫`;
        }

        function getOrderCashAmount(order) {
            if (!order) return null;
            const method = String(order.payment_method || "").toLowerCase().trim();

            if (order.cash_amount !== undefined && order.cash_amount !== null) {
                return Number(order.cash_amount);
            }

            // Backward compatibility fallback for API responses without cash_amount
            if (method === "cash") {
                return Number(order.total_price || 0);
            }

            // For split (or bank/card), do NOT infer cash from total_price
            return null;
        }

        function parseApiDate(dateStr) {
            if (!dateStr || typeof dateStr !== "string") return null;
            const trimmed = dateStr.trim();
            if (!trimmed) return null;

            let normalized = trimmed;
            if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}(\.\d+)?$/.test(normalized)) {
                normalized = normalized.replace(" ", "T") + "Z";
            }

            const d = new Date(normalized);
            return isNaN(d.getTime()) ? null : d;
        }

        function getOrderPaidTime(order) {
            if (!order) return null;

            // 1. Explicit paid_at / payment_time fields if backend provides them
            if (order.paid_at) return order.paid_at;
            if (order.payment_time) return order.payment_time;

            // 2. Actual payment transaction time from payments array
            if (Array.isArray(order.payments) && order.payments.length > 0) {
                let latestTimeMs = -1;
                let latestDateStr = null;

                for (const payment of order.payments) {
                    const timeStr = payment?.created_at || payment?.updated_at;
                    if (timeStr) {
                        const parsed = parseApiDate(timeStr);
                        if (parsed) {
                            const ms = parsed.getTime();
                            if (ms > latestTimeMs) {
                                latestTimeMs = ms;
                                latestDateStr = timeStr;
                            }
                        }
                    }
                }

                if (latestDateStr) {
                    return latestDateStr;
                }
            }

            // 3. Fallback: completed_at (when order was marked completed)
            if (order.completed_at) {
                return order.completed_at;
            }

            return null;
        }

        function isPaidBeforeOrAt16VN(dateInput) {
            if (!dateInput) return false;
            const date = dateInput instanceof Date ? dateInput : parseApiDate(dateInput);
            if (!date || isNaN(date.getTime())) return false;

            try {
                const parts = new Intl.DateTimeFormat("en-GB", {
                    timeZone: "Asia/Ho_Chi_Minh",
                    hour12: false,
                    hour: "numeric",
                    minute: "numeric",
                    second: "numeric",
                }).formatToParts(date);

                let hour = 0;
                let minute = 0;
                let second = 0;

                for (const p of parts) {
                    if (p.type === "hour") hour = parseInt(p.value, 10);
                    if (p.type === "minute") minute = parseInt(p.value, 10);
                    if (p.type === "second") second = parseInt(p.value, 10);
                }

                if (hour === 24) hour = 0;

                // Paid between 00:00:00 and 16:00:00 inclusive in Asia/Ho_Chi_Minh
                if (hour < 16) return true;
                if (hour === 16 && minute === 0 && second === 0) return true;
                return false;
            } catch {
                // Fallback: UTC+7
                const vnDate = new Date(date.getTime() + 7 * 60 * 60 * 1000);
                const h = vnDate.getUTCHours();
                const m = vnDate.getUTCMinutes();
                const s = vnDate.getUTCSeconds();

                if (h < 16) return true;
                if (h === 16 && m === 0 && s === 0) return true;
                return false;
            }
        }

        function isOrderPaidBeforeOrAt16(order) {
            const paidTime = getOrderPaidTime(order);
            if (!paidTime) return false;
            return isPaidBeforeOrAt16VN(paidTime);
        }

        // ---------------------------------------------------------------------
        // UI Helpers
        // ---------------------------------------------------------------------

        function showLoading(show) {
            loading.hidden = !show;
            callApiButton.disabled = show;

            if (searchOrderButton) {
                searchOrderButton.disabled = show;
            }

            if (clearOrderButton) {
                clearOrderButton.disabled = show;
            }

            if (show) {
                if (totalOrdersAmountEl) {
                    totalOrdersAmountEl.textContent = "Đang tính...";
                }
                if (totalCashAmountEl) {
                    totalCashAmountEl.textContent = "Đang tính...";
                }
            }
        }

        function showMessage(text, type = "error") {
            message.textContent = text;
            message.className = `vm-api-message vm-api-message--${type}`;
            message.hidden = false;
        }

        function clearMessage() {
            message.hidden = true;
            message.textContent = "";
        }

        // ---------------------------------------------------------------------
        // Order Items Modal
        // ---------------------------------------------------------------------

        function getOrderItemsModal() {
            return document.querySelector("[data-order-items-modal]");
        }

        function canUserDeleteOrderItems() {
            const modal = getOrderItemsModal();
            if (modal && modal.dataset.canDelete !== undefined) {
                return modal.dataset.canDelete === "true";
            }
            return Boolean(apiConfig?.canDelete);
        }

        function bindOrderItemsModalEvents() {
            const modal = getOrderItemsModal();
            if (!modal) {
                return;
            }

            if (modal.dataset.eventsBound === "true") {
                return;
            }
            modal.dataset.eventsBound = "true";

            modal.addEventListener("click", async (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }

                if (target.closest("[data-items-modal-close]")) {
                    if (!isDeletingItems) {
                        closeOrderItemsModal();
                    }
                    return;
                }

                if (target.closest("[data-items-modal-delete]")) {
                    deleteSelectedOrderItems();
                    return;
                }



                // Row click delegation to toggle order item selection
                const itemRow = target.closest("tr[data-item-row]");
                if (itemRow) {
                    // If click originated directly on the checkbox, let the native click & change event handle it
                    if (target.closest("[data-item-checkbox]")) {
                        return;
                    }

                    // If click originated on any interactive element (buttons, links, inputs), do not toggle
                    if (target.closest("button, a, input, select, textarea, label")) {
                        return;
                    }

                    const checkbox = itemRow.querySelector("[data-item-checkbox]");
                    if (checkbox && !checkbox.disabled) {
                        checkbox.checked = !checkbox.checked;
                        checkbox.dispatchEvent(new Event("change", { bubbles: true }));
                    }
                }
            });

            modal.addEventListener("change", (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }

                const selectAll = target.closest("[data-items-select-all]");
                if (selectAll) {
                    const checkboxes = modal.querySelectorAll("[data-item-checkbox]");
                    checkboxes.forEach((cb) => {
                        const id = Number(cb.value);
                        cb.checked = selectAll.checked;
                        if (selectAll.checked) {
                            selectedItemIds.add(id);
                        } else {
                            selectedItemIds.delete(id);
                        }
                    });

                    updateOrderItemsControls();
                    return;
                }

                const itemCheckbox = target.closest("[data-item-checkbox]");
                if (itemCheckbox) {
                    const id = Number(itemCheckbox.value);
                    if (itemCheckbox.checked) {
                        selectedItemIds.add(id);
                    } else {
                        selectedItemIds.delete(id);
                    }

                    updateOrderItemsControls();
                }
            });

            document.addEventListener("keydown", (event) => {
                if (event.key === "Escape" && !modal.hidden && !isDeletingItems) {
                    closeOrderItemsModal();
                }
            });
        }

        async function openOrderItemsModal(orderId) {
            const modal = getOrderItemsModal();
            if (!modal) {
                return;
            }

            const cachedOrder = orderCache.get(orderId);
            const method = String(cachedOrder?.payment_method || "").toLowerCase().trim();
            if (method === "split") {
                return; // Guard: Split orders do not support modal view/delete
            }

            const thisFetchId = ++activeItemsFetchId;
            currentItemsOrderId = orderId;
            currentOrderItems = [];
            selectedItemIds.clear();
            updateDeletedAmountPreview();

            const titleEl = modal.querySelector("[data-items-modal-title]");
            const subtitleEl = modal.querySelector("[data-items-modal-subtitle]");
            const totalCountEl = modal.querySelector("[data-items-modal-total]");
            const totalPriceEl = modal.querySelector("[data-items-modal-total-price]");
            const selectedCountEl = modal.querySelector("[data-items-modal-selected]");
            const deleteBtn = modal.querySelector("[data-items-modal-delete]");
            const deleteCountEl = modal.querySelector("[data-items-modal-delete-count]");
            const selectAllCheckbox = modal.querySelector("[data-items-select-all]");
            const loadingEl = modal.querySelector("[data-items-modal-loading]");
            const errorEl = modal.querySelector("[data-items-modal-error]");
            const noticeEl = modal.querySelector("[data-items-modal-notice]");
            const emptyEl = modal.querySelector("[data-items-modal-empty]");
            const tableWrapEl = modal.querySelector("[data-items-modal-table-wrap]");
            const tbodyEl = modal.querySelector("[data-items-modal-tbody]");

            if (titleEl) {
                titleEl.textContent = `Chi tiết món ăn - Đơn hàng #${orderId}`;
            }

            const tableName = cachedOrder?.table?.name || "-";
            const orderCreated = cachedOrder?.created_at
                ? new Date(cachedOrder.created_at).toLocaleString()
                : "-";
            const initialTotal = cachedOrder?.total_price || 0;

            const isSplit = method === "split";
            const cashAmount = getOrderCashAmount(cachedOrder);
            let paymentMethodInfo = "";
            if (isSplit) {
                const cashText = cashAmount !== null ? `${money(cashAmount)} ₫` : "—";
                paymentMethodInfo = ` | PT: Split (Tiền mặt: ${cashText})`;
            }

            if (subtitleEl) {
                subtitleEl.textContent = `${tableName}${paymentMethodInfo} | Thời gian: ${orderCreated}`;
            }

            if (totalCountEl) totalCountEl.textContent = "-";
            if (totalPriceEl) totalPriceEl.textContent = money(initialTotal);
            if (selectedCountEl) selectedCountEl.textContent = "0";
            if (deleteCountEl) deleteCountEl.textContent = "0";
            if (deleteBtn) {
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = 'Xóa món đã chọn (<span data-items-modal-delete-count>0</span>)';
            }
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.disabled = true;
            }

            if (errorEl) { errorEl.hidden = true; errorEl.textContent = ""; }
            if (noticeEl) { noticeEl.hidden = true; noticeEl.textContent = ""; }
            if (emptyEl) emptyEl.hidden = true;
            if (tableWrapEl) tableWrapEl.hidden = true;
            if (tbodyEl) tbodyEl.replaceChildren();
            if (loadingEl) loadingEl.hidden = false;

            modal.hidden = false;
            document.body.classList.add("vm-order-modal-open");

            modal.querySelector('.vm-order-modal__header [data-items-modal-close], [aria-label="Đóng cửa sổ"], [aria-label="Close dialog"]')?.focus();

            const formData = new FormData();
            formData.append("action", "vm_external_get_order_items");
            formData.append("nonce", apiConfig.nonce);
            formData.append("order_id", String(orderId));

            try {
                const response = await fetch(apiConfig.ajaxUrl, {
                    method: "POST",
                    credentials: "same-origin",
                    body: formData,
                });

                const rawResponse = await response.text();
                let ajaxResponse;

                try {
                    ajaxResponse = JSON.parse(rawResponse);
                } catch {
                    throw new Error(`Invalid AJAX response (${response.status}).`);
                }

                if (thisFetchId !== activeItemsFetchId || modal.hidden) {
                    return;
                }

                if (!response.ok || !ajaxResponse.success) {
                    throw new Error(
                        ajaxResponse?.data?.message ||
                        `Request failed with status ${response.status}.`
                    );
                }

                const items = ajaxResponse.data?.data || [];
                currentOrderItems = items;

                renderOrderItemsList(items);
            } catch (error) {
                if (thisFetchId !== activeItemsFetchId || modal.hidden) {
                    return;
                }

                console.error("Order items load error:", error);
                if (errorEl) {
                    errorEl.textContent = error.message || "Không thể tải danh sách món ăn. Vui lòng thử lại.";
                    errorEl.hidden = false;
                }
            } finally {
                if (thisFetchId === activeItemsFetchId && !modal.hidden) {
                    if (loadingEl) loadingEl.hidden = true;
                }
            }
        }

        function renderOrderItemsList(items) {
            const modal = getOrderItemsModal();
            if (!modal) return;

            const canDelete = canUserDeleteOrderItems();
            const totalCountEl = modal.querySelector("[data-items-modal-total]");
            const totalPriceEl = modal.querySelector("[data-items-modal-total-price]");
            const emptyEl = modal.querySelector("[data-items-modal-empty]");
            const tableWrapEl = modal.querySelector("[data-items-modal-table-wrap]");
            const tbodyEl = modal.querySelector("[data-items-modal-tbody]");
            const selectAllCheckbox = modal.querySelector("[data-items-select-all]");

            if (totalCountEl) totalCountEl.textContent = String(items.length);

            const itemsSum = items.reduce((sum, it) => sum + Number(it.final_price || 0), 0);
            const cachedOrder = orderCache.get(currentItemsOrderId);
            const displayTotal = cachedOrder?.total_price !== undefined
                ? cachedOrder.total_price
                : itemsSum;

            if (totalPriceEl) totalPriceEl.textContent = money(displayTotal);

            if (items.length === 0) {
                if (emptyEl) emptyEl.hidden = false;
                if (tableWrapEl) tableWrapEl.hidden = true;
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.disabled = true;
                }
                updateOrderItemsControls();
                return;
            }

            if (emptyEl) emptyEl.hidden = true;
            if (tableWrapEl) tableWrapEl.hidden = false;
            if (selectAllCheckbox) selectAllCheckbox.disabled = false;

            tbodyEl.innerHTML = items.map((item) => {
                const itemId = Number(item.id);
                const displayName = item.product_name_vi || item.name || `Món #${itemId}`;
                const rawType = (item.type || "").toLowerCase();
                const typeBadgeClass = rawType === "food"
                    ? "vm-badge--food"
                    : rawType === "drink"
                        ? "vm-badge--drink"
                        : rawType === "packaged_drink"
                            ? "vm-badge--packaged_drink"
                            : "vm-badge--default";

                const rawStatus = (item.status || "").toLowerCase();
                const statusBadgeClass = rawStatus === "served"
                    ? "vm-badge--served"
                    : rawStatus === "pending"
                        ? "vm-badge--pending"
                        : "vm-badge--default";

                const discountAmount = Number(item.discount_amount || 0);
                const discountText = discountAmount > 0
                    ? `-${money(discountAmount)}`
                    : "-";

                const finalPrice = item.final_price !== undefined
                    ? item.final_price
                    : (Number(item.quantity || 1) * Number(item.price || 0));

                const isChecked = selectedItemIds.has(itemId) ? "checked" : "";

                const checkboxCell = canDelete
                    ? `
                        <td style="text-align: center;">
                            <input
                                type="checkbox"
                                data-item-checkbox
                                value="${itemId}"
                                aria-label="Select item ${itemId}"
                                ${isChecked}
                                ${isDeletingItems ? "disabled" : ""}
                            >
                        </td>
                    `
                    : "";

                return `
                    <tr data-item-row="${itemId}">
                        ${checkboxCell}
                        <td style="text-align: center; font-weight: 600; color: #64748b; font-family: ui-monospace, SFMono-Regular, monospace;">
                            #${itemId}
                        </td>
                        <td>
                            <div class="vm-item-name">${escapeHtml(displayName)}</div>
                        </td>
                        <td>
                            <span class="vm-badge ${typeBadgeClass}">${escapeHtml(item.type || "-")}</span>
                        </td>
                        <td style="text-align: center; font-weight: 600;">
                            ${Number(item.quantity || 1)}
                        </td>
                        <td style="text-align: right;">
                            ${money(item.price)}
                        </td>
                        <td style="text-align: right; color: ${discountAmount > 0 ? "#dc2626" : "inherit"};">
                            ${escapeHtml(discountText)}
                        </td>
                        <td style="text-align: right; font-weight: 700; color: #0f172a;">
                            ${money(finalPrice)}
                        </td>
                        <td>
                            <div class="vm-item-note">${escapeHtml(item.note || "-")}</div>
                        </td>
                    </tr>
                `;
            }).join("");

            updateOrderItemsControls();
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

        function getSelectedItemsAmount() {
            let amount = 0;
            selectedItemIds.forEach((id) => {
                const found = currentOrderItems.find((it) => Number(it.id) === id);
                if (found) {
                    amount += getItemFinalPrice(found);
                }
            });
            return amount;
        }

        function getAggregatedDeletedSummary() {
            const allItems = getAllAggregatedDeletedItems();
            let totalAmount = 0;
            let hasUnknownPrice = false;

            allItems.forEach((item) => {
                if (item && item.final_price !== null && item.final_price !== undefined && !isNaN(Number(item.final_price))) {
                    totalAmount += Number(item.final_price);
                } else {
                    hasUnknownPrice = true;
                }
            });

            return {
                items: allItems,
                count: allItems.length,
                totalAmount,
                hasUnknownPrice
            };
        }

        function calculateDeletedRate(deletedAmount, cashAmount, includeBase = true) {
            if (deletedAmount === null || deletedAmount === undefined || isNaN(Number(deletedAmount))) {
                return "—";
            }
            if (cashAmount === null || cashAmount === undefined || isNaN(Number(cashAmount))) {
                return "—";
            }

            const deleted = Number(deletedAmount);
            const cash = Number(cashAmount);

            if (deleted < 0 || cash < 0) {
                return "—";
            }

            const denominator = cash + deleted;
            if (denominator <= 0) {
                return "—";
            }

            const rate = (deleted / denominator) * 100;
            if (!isFinite(rate) || isNaN(rate)) {
                return "—";
            }

            const formattedRate = rate.toLocaleString("vi-VN", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            if (!includeBase) {
                return `${formattedRate}%`;
            }
            const formattedBase = `${denominator.toLocaleString("vi-VN")} ₫`;

            return `${formattedRate}% (${formattedBase})`;
        }

        function getStoredLatestCashAmount() {
            const todayVnKey = checkAndPurgeExpiredDailyCycle();
            if (isLocalStorageAvailable()) {
                try {
                    const raw = localStorage.getItem(LOCAL_STORAGE_KEY);
                    if (raw) {
                        const parsed = JSON.parse(raw);
                        if (parsed && typeof parsed === "object" && parsed.date === todayVnKey && parsed.latest_cash_amount !== undefined && parsed.latest_cash_amount !== null && !isNaN(Number(parsed.latest_cash_amount))) {
                            return Number(parsed.latest_cash_amount);
                        }
                    }
                } catch (e) {
                    // error
                }
            }
            if (ramFallbackDateKey === todayVnKey && ramFallbackCashAmount !== null && !isNaN(Number(ramFallbackCashAmount))) {
                return Number(ramFallbackCashAmount);
            }
            return null;
        }

        function saveLatestCashAmountToStorage(cashAmount) {
            if (cashAmount === null || cashAmount === undefined || isNaN(Number(cashAmount))) return;
            const numCash = Math.max(0, Number(cashAmount));
            const todayVnKey = checkAndPurgeExpiredDailyCycle();

            if (isLocalStorageAvailable()) {
                try {
                    const raw = localStorage.getItem(LOCAL_STORAGE_KEY);
                    let currentItems = [];
                    if (raw) {
                        const parsed = JSON.parse(raw);
                        if (parsed && typeof parsed === "object" && parsed.date === todayVnKey && Array.isArray(parsed.items)) {
                            currentItems = parsed.items;
                        }
                    }
                    const payload = {
                        date: todayVnKey,
                        items: currentItems,
                        latest_cash_amount: numCash
                    };
                    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(payload));
                } catch (e) {
                    console.warn("[ExternalAPI] Không thể cập nhật latest_cash_amount vào localStorage:", e);
                }
            }

            ramFallbackCashAmount = numCash;
            ramFallbackDateKey = todayVnKey;
        }

        function updateAllOrdersDeletedAmountDisplay(summary = null) {
            const activeSummary = summary || getAggregatedDeletedSummary();
            const deletedFormatted = formatVnd(activeSummary.totalAmount);
            const storedCash = getStoredLatestCashAmount();

            if (totalDeletedAmountEl) {
                if (storedCash !== null && !isNaN(storedCash) && storedCash >= 0) {
                    const denominator = storedCash + activeSummary.totalAmount;
                    totalDeletedAmountEl.textContent = `${deletedFormatted} / ${formatVnd(denominator)}`;
                } else {
                    totalDeletedAmountEl.textContent = `${deletedFormatted} / —`;
                }

                if (activeSummary.hasUnknownPrice) {
                    totalDeletedAmountEl.title = "Lưu ý: Có món chưa đủ thông tin giá thành tiền trong tổng này.";
                } else {
                    totalDeletedAmountEl.removeAttribute("title");
                }
            }

            const modal = getOrderItemsModal();
            if (modal) {
                const modalDeletedEl = modal.querySelector("[data-items-modal-deleted-amount]");
                if (modalDeletedEl) {
                    modalDeletedEl.textContent = deletedFormatted;
                    if (activeSummary.hasUnknownPrice) {
                        modalDeletedEl.title = "Lưu ý: Có món chưa đủ thông tin giá thành tiền trong tổng này.";
                    } else {
                        modalDeletedEl.removeAttribute("title");
                    }
                }
            }

            const actualRateFormatted = (storedCash !== null && !isNaN(storedCash))
                ? calculateDeletedRate(activeSummary.totalAmount, storedCash, false)
                : "—";

            if (deletedItemsRateEl) {
                deletedItemsRateEl.textContent = actualRateFormatted;
            }

            updateModalDeletedRateDisplay(activeSummary.totalAmount, storedCash);
        }

        function updateModalDeletedRateDisplay(historicalDeletedAmount = null, storedCash = null) {
            const modal = getOrderItemsModal();
            if (!modal) return;

            const modalRateEl = modal.querySelector("[data-items-modal-deleted-rate]");
            const modalRateLabelEl = modal.querySelector("[data-items-modal-deleted-rate-label]");
            if (!modalRateEl) return;

            const currentStoredCash = (storedCash !== null && !isNaN(storedCash))
                ? storedCash
                : getStoredLatestCashAmount();

            const histDeleted = (historicalDeletedAmount !== null && !isNaN(Number(historicalDeletedAmount)))
                ? Number(historicalDeletedAmount)
                : getAggregatedDeletedSummary().totalAmount;

            const selectedAmount = getSelectedItemsAmount();
            const isProjected = selectedAmount > 0 && selectedItemIds.size > 0;

            const statRateContainer = modalRateEl.closest(".vm-order-items-modal__stat--rate");
            if (statRateContainer) {
                statRateContainer.classList.toggle("is-projected", isProjected);
            }

            if (modalRateLabelEl) {
                modalRateLabelEl.textContent = isProjected
                    ? "Tỷ lệ tiền món đã xóa (dự kiến):"
                    : "Tỷ lệ tiền món đã xóa:";
            }

            if (currentStoredCash === null || isNaN(currentStoredCash)) {
                modalRateEl.textContent = "—";
                return;
            }

            if (!isProjected) {
                modalRateEl.textContent = calculateDeletedRate(histDeleted, currentStoredCash);
                return;
            }

            const currentOrder = currentItemsOrderId ? orderCache.get(Number(currentItemsOrderId)) : null;
            const method = String(currentOrder?.payment_method || "").toLowerCase().trim();
            const isCashOrder = method === "cash";

            const projectedDeletedAmount = histDeleted + selectedAmount;
            const projectedCashReceived = isCashOrder
                ? Math.max(0, currentStoredCash - selectedAmount)
                : currentStoredCash;

            modalRateEl.textContent = calculateDeletedRate(projectedDeletedAmount, projectedCashReceived);
        }

        function getVietnamDateKey(date = new Date()) {
            try {
                const formatter = new Intl.DateTimeFormat("en-CA", {
                    timeZone: "Asia/Ho_Chi_Minh",
                    year: "numeric",
                    month: "2-digit",
                    day: "2-digit"
                });
                return formatter.format(date);
            } catch {
                const vnDate = new Date(date.getTime() + (7 * 60 + date.getTimezoneOffset()) * 60000);
                const y = vnDate.getFullYear();
                const m = String(vnDate.getMonth() + 1).padStart(2, "0");
                const d = String(vnDate.getDate()).padStart(2, "0");
                return `${y}-${m}-${d}`;
            }
        }

        function getMsUntilNextVnMidnight() {
            const now = Date.now();
            const vnDate = new Date(now + 7 * 3600 * 1000);
            const nextMidnightVn = new Date(Date.UTC(
                vnDate.getUTCFullYear(),
                vnDate.getUTCMonth(),
                vnDate.getUTCDate() + 1,
                0, 0, 0, 0
            ));
            const nextMidnightUtc = nextMidnightVn.getTime() - 7 * 3600 * 1000;
            return Math.max(1000, nextMidnightUtc - now);
        }

        function isLocalStorageAvailable() {
            try {
                const testKey = "__vm_test_storage__";
                localStorage.setItem(testKey, "1");
                localStorage.removeItem(testKey);
                return true;
            } catch {
                return false;
            }
        }

        function checkAndPurgeExpiredDailyCycle() {
            const todayVnKey = getVietnamDateKey();
            let hasPurged = false;

            if (ramFallbackDateKey && ramFallbackDateKey !== todayVnKey) {
                ramFallbackDeletedItems = [];
                ramFallbackCashAmount = null;
                ramFallbackDateKey = todayVnKey;
                hasPurged = true;
            } else if (!ramFallbackDateKey) {
                ramFallbackDateKey = todayVnKey;
            }

            if (isLocalStorageAvailable()) {
                try {
                    const raw = localStorage.getItem(LOCAL_STORAGE_KEY);
                    if (raw) {
                        const parsed = JSON.parse(raw);
                        if (parsed && typeof parsed === "object" && !Array.isArray(parsed)) {
                            if (parsed.date !== todayVnKey) {
                                const resetPayload = {
                                    date: todayVnKey,
                                    items: [],
                                    latest_cash_amount: null
                                };
                                localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(resetPayload));
                                hasPurged = true;
                            }
                        } else if (Array.isArray(parsed)) {
                            localStorage.removeItem(LOCAL_STORAGE_KEY);
                            hasPurged = true;
                        }
                    }
                } catch (e) {
                    console.error("[ExternalAPI] Lỗi khi kiểm tra chu kỳ ngày localStorage:", e);
                }
            }

            if (hasPurged) {
                currentCashTotalAmount = 0;
                if (totalCashAmountEl) {
                    totalCashAmountEl.textContent = "—";
                }
                syncDeletedPdfButtonState();
            }

            return todayVnKey;
        }

        function scheduleMidnightCycleCheck() {
            if (midnightTimerId) {
                clearTimeout(midnightTimerId);
            }
            const msUntilMidnight = getMsUntilNextVnMidnight() + 1000;
            midnightTimerId = setTimeout(() => {
                checkAndPurgeExpiredDailyCycle();
                scheduleMidnightCycleCheck();
            }, msUntilMidnight);
        }

        function getAllAggregatedDeletedItems() {
            const todayVnKey = checkAndPurgeExpiredDailyCycle();
            let storageItems = [];
            if (isLocalStorageAvailable()) {
                try {
                    const raw = localStorage.getItem(LOCAL_STORAGE_KEY);
                    if (raw) {
                        const parsed = JSON.parse(raw);
                        if (parsed && typeof parsed === "object" && parsed.date === todayVnKey && Array.isArray(parsed.items)) {
                            storageItems = parsed.items;
                        }
                    }
                } catch (e) {
                    console.error("[ExternalAPI] Lỗi đọc localStorage:", e);
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

        function saveDeletedItemsToStorageOrRam(newRecords, modalNoticeEl) {
            if (!newRecords || newRecords.length === 0) return true;

            const todayVnKey = checkAndPurgeExpiredDailyCycle();
            let storageSuccess = false;

            if (isLocalStorageAvailable()) {
                try {
                    const raw = localStorage.getItem(LOCAL_STORAGE_KEY);
                    let currentItems = [];
                    let storedCash = null;
                    if (raw) {
                        const parsed = JSON.parse(raw);
                        if (parsed && typeof parsed === "object" && parsed.date === todayVnKey) {
                            if (Array.isArray(parsed.items)) {
                                currentItems = parsed.items;
                            }
                            if (parsed.latest_cash_amount !== undefined && parsed.latest_cash_amount !== null && !isNaN(Number(parsed.latest_cash_amount))) {
                                storedCash = Number(parsed.latest_cash_amount);
                            }
                        }
                    }
                    const updatedItems = [...currentItems, ...newRecords];
                    const payload = {
                        date: todayVnKey,
                        items: updatedItems,
                        latest_cash_amount: storedCash
                    };
                    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(payload));
                    storageSuccess = true;
                } catch (storageErr) {
                    console.warn("[ExternalAPI] Không thể lưu vào localStorage (quota hoặc bị tắt):", storageErr);
                    storageSuccess = false;
                }
            }

            if (!storageSuccess) {
                ramFallbackDateKey = todayVnKey;
                ramFallbackDeletedItems.push(...newRecords);
                if (modalNoticeEl) {
                    const warnText = " (Cảnh báo: Bộ nhớ localStorage không khả dụng. Dữ liệu chỉ lưu tạm trên RAM và sẽ bị mất khi tải lại trang)";
                    modalNoticeEl.textContent += warnText;
                }
                return false;
            }
            return true;
        }

        function syncDeletedPdfButtonState() {
            const exportBtn = document.querySelector("[data-export-deleted-pdf]");
            const countBadge = document.querySelector("[data-export-deleted-count]");

            const summary = getAggregatedDeletedSummary();
            const totalCount = summary.count;

            if (countBadge) {
                countBadge.textContent = String(totalCount);
            }

            if (exportBtn) {
                if (totalCount === 0) {
                    exportBtn.disabled = true;
                    exportBtn.title = "Chưa có món nào bị xóa trong ngày hôm nay";
                } else {
                    exportBtn.disabled = isExportingPdf;
                    exportBtn.title = `Tải PDF tổng hợp ${totalCount} món đã xóa trong ngày hôm nay (Giờ VN)`;
                }
            }

            updateAllOrdersDeletedAmountDisplay(summary);
        }

        function updateSelectedItemsAmountDisplay() {
            const modal = getOrderItemsModal();
            if (!modal) return;
            const orderDeletedEl = modal.querySelector("[data-items-modal-order-deleted-amount]");
            if (orderDeletedEl) {
                orderDeletedEl.textContent = formatVnd(getSelectedItemsAmount());
            }
        }

        function updateDeletedAmountPreview() {
            updateAllOrdersDeletedAmountDisplay();
            updateSelectedItemsAmountDisplay();
        }

        function updateOrderDeletedAmountDisplay() {
            updateSelectedItemsAmountDisplay();
        }

        function updateOrderItemsControls() {
            const modal = getOrderItemsModal();
            if (!modal) return;

            const selectedCountEl = modal.querySelector("[data-items-modal-selected]");
            const deleteCountEl = modal.querySelector("[data-items-modal-delete-count]");
            const deleteBtn = modal.querySelector("[data-items-modal-delete]");
            const selectAllCheckbox = modal.querySelector("[data-items-select-all]");
            const checkboxes = Array.from(modal.querySelectorAll("[data-item-checkbox]"));

            const checkedCount = checkboxes.filter(cb => cb.checked).length;
            const totalCount = checkboxes.length;

            if (selectedCountEl) selectedCountEl.textContent = String(selectedItemIds.size);
            if (deleteCountEl) deleteCountEl.textContent = String(selectedItemIds.size);

            if (deleteBtn) {
                deleteBtn.disabled = selectedItemIds.size === 0 || isDeletingItems;
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.checked = totalCount > 0 && checkedCount === totalCount;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCount;
                selectAllCheckbox.disabled = totalCount === 0 || isDeletingItems;
            }

            checkboxes.forEach((cb) => {
                cb.disabled = isDeletingItems;
            });

            updateDeletedAmountPreview();
        }

        async function deleteSelectedOrderItems() {
            checkAndPurgeExpiredDailyCycle();
            const modal = getOrderItemsModal();
            if (!modal || !canUserDeleteOrderItems() || isDeletingItems || selectedItemIds.size === 0 || !currentItemsOrderId) {
                return;
            }

            const countToDelete = selectedItemIds.size;
            const confirmed = window.confirm(
                `Bạn có chắc chắn muốn xóa ${countToDelete} món đã chọn khỏi đơn hàng #${currentItemsOrderId}?\nThao tác này sẽ xóa vĩnh viễn món ăn và tính lại tổng tiền đơn hàng.`
            );

            if (!confirmed) {
                return;
            }

            const errorEl = modal.querySelector("[data-items-modal-error]");
            const noticeEl = modal.querySelector("[data-items-modal-notice]");
            const deleteBtn = modal.querySelector("[data-items-modal-delete]");

            if (errorEl) { errorEl.hidden = true; errorEl.textContent = ""; }
            if (noticeEl) { noticeEl.hidden = true; noticeEl.textContent = ""; }

            isDeletingItems = true;
            const actionToken = `act_${Date.now()}_${Math.random().toString(36).substring(2, 9)}`;

            updateOrderItemsControls();
            if (deleteBtn) deleteBtn.textContent = "Đang xóa...";

            const targetOrderId = currentItemsOrderId;
            const itemIdsToDelete = Array.from(selectedItemIds);
            const cachedOrder = orderCache.get(targetOrderId);

            // Snapshot selected items before delete request
            const selectedSnapshot = new Map();
            currentOrderItems.forEach(item => {
                const numId = Number(item.id);
                if (selectedItemIds.has(numId)) {
                    selectedSnapshot.set(numId, { ...item });
                }
            });

            const formData = new FormData();
            formData.append("action", "vm_external_delete_order_items");
            formData.append("nonce", apiConfig.nonce);
            formData.append("order_id", String(targetOrderId));
            itemIdsToDelete.forEach((id) => {
                formData.append("item_ids[]", String(id));
            });

            try {
                const response = await fetch(apiConfig.ajaxUrl, {
                    method: "POST",
                    credentials: "same-origin",
                    body: formData,
                });

                const rawResponse = await response.text();
                let ajaxResponse;

                try {
                    ajaxResponse = JSON.parse(rawResponse);
                } catch {
                    throw new Error(`Invalid AJAX response (${response.status}).`);
                }

                if (currentItemsOrderId !== targetOrderId || modal.hidden) {
                    return;
                }

                if (!response.ok || !ajaxResponse || typeof ajaxResponse !== "object" || !ajaxResponse.success) {
                    const serverMsg = ajaxResponse?.data?.message || `Xóa món thất bại (${response.status}).`;
                    const notFound = ajaxResponse?.data?.data?.not_found_ids;
                    if (Array.isArray(notFound) && notFound.length > 0) {
                        throw new Error(`${serverMsg} (Không tìm thấy món ID: #${notFound.join(", #")})`);
                    }
                    throw new Error(serverMsg);
                }

                // Verify exact structure of deleted_ids from WordPress AJAX / Laravel
                const resultData = ajaxResponse.data;
                let rawDeletedIds = null;
                if (resultData && typeof resultData === "object") {
                    if (Array.isArray(resultData.deleted_ids)) {
                        rawDeletedIds = resultData.deleted_ids;
                    } else if (resultData.data && Array.isArray(resultData.data.deleted_ids)) {
                        rawDeletedIds = resultData.data.deleted_ids;
                    }
                }

                const deletedIds = Array.isArray(rawDeletedIds)
                    ? rawDeletedIds.map(Number).filter(id => Number.isInteger(id) && id > 0)
                    : [];
                const deletedSet = new Set(deletedIds);
                const notFoundIds = Array.isArray(resultData?.not_found_ids)
                    ? resultData.not_found_ids.map(Number)
                    : [];

                // --- 1. CORE DELETION FLOW (UI, row removal, dashboard) ---
                deletedIds.forEach(id => {
                    selectedItemIds.delete(id);
                    const row = modal.querySelector(`tr[data-item-row="${id}"]`);
                    if (row) row.remove();
                });

                updateSelectedItemsAmountDisplay();

                currentOrderItems = currentOrderItems.filter(item => !deletedSet.has(Number(item.id)));

                const orderSummary = resultData?.order_summary || {};
                const remainingItemsCount = orderSummary.remaining_items_count !== undefined
                    ? Number(orderSummary.remaining_items_count)
                    : currentOrderItems.length;

                const newTotalPrice = orderSummary.total_price !== undefined
                    ? Number(orderSummary.total_price)
                    : currentOrderItems.reduce((sum, it) => sum + Number(it.final_price || 0), 0);

                const totalCountEl = modal.querySelector("[data-items-modal-total]");
                const totalPriceEl = modal.querySelector("[data-items-modal-total-price]");
                const emptyEl = modal.querySelector("[data-items-modal-empty]");
                const tableWrapEl = modal.querySelector("[data-items-modal-table-wrap]");

                if (totalCountEl) totalCountEl.textContent = String(remainingItemsCount);
                if (totalPriceEl) totalPriceEl.textContent = money(newTotalPrice);

                if (currentOrderItems.length === 0) {
                    if (emptyEl) emptyEl.hidden = false;
                    if (tableWrapEl) tableWrapEl.hidden = true;
                }

                if (noticeEl) {
                    let msg = `Đã xóa thành công ${resultData?.deleted_count || deletedIds.length} món.`;
                    if (notFoundIds.length > 0) {
                        msg += ` Lưu ý: Không tìm thấy món ID #${notFoundIds.join(", #")} trong đơn hàng này.`;
                    }
                    noticeEl.textContent = msg;
                    noticeEl.hidden = false;
                }

                const dashboardRow = tbody.querySelector(`tr[data-order-row="${targetOrderId}"]`);
                if (dashboardRow) {
                    const countCell = dashboardRow.querySelector("[data-order-items-count]");
                    const priceCell = dashboardRow.querySelector("[data-order-total-price]");
                    if (countCell) countCell.textContent = String(remainingItemsCount);
                    if (priceCell) priceCell.textContent = money(newTotalPrice);
                }

                if (cachedOrder) {
                    const oldOrderPrice = Number(cachedOrder.total_price || 0);
                    const priceDiff = oldOrderPrice - newTotalPrice;
                    if (priceDiff > 0) {
                        currentOrdersTotalAmount = Math.max(0, currentOrdersTotalAmount - priceDiff);
                        if (totalOrdersAmountEl) {
                            totalOrdersAmountEl.textContent = formatVnd(currentOrdersTotalAmount);
                        }

                        const orderMethod = String(cachedOrder.payment_method || "").toLowerCase().trim();
                        if (orderMethod === "cash") {
                            currentCashTotalAmount = Math.max(0, currentCashTotalAmount - priceDiff);
                            if (totalCashAmountEl) {
                                totalCashAmountEl.textContent = formatVnd(currentCashTotalAmount);
                            }

                            const storedCash = getStoredLatestCashAmount();
                            if (storedCash !== null && !isNaN(storedCash)) {
                                const newStoredCash = Math.max(0, storedCash - priceDiff);
                                saveLatestCashAmountToStorage(newStoredCash);
                            }
                        }
                    }

                    cachedOrder.total_price = newTotalPrice;
                    const orderMethod = String(cachedOrder.payment_method || "").toLowerCase().trim();
                    if (orderMethod === "cash") {
                        if (cachedOrder.cash_amount !== undefined && cachedOrder.cash_amount !== null) {
                            cachedOrder.cash_amount = newTotalPrice;
                        }
                    }
                    if (orderSummary.subtotal !== undefined) cachedOrder.subtotal = orderSummary.subtotal;
                    if (orderSummary.discount_amount !== undefined) cachedOrder.discount_amount = orderSummary.discount_amount;
                    if (Array.isArray(cachedOrder.items)) {
                        cachedOrder.items = cachedOrder.items.filter(item => !deletedSet.has(Number(item.id)));
                    }
                }

                // --- 2. SEPARATED REPORTING & STORAGE FLOW ---
                try {
                    if (!processedActionTokens.has(actionToken) && deletedIds.length > 0) {
                        const newRecords = [];
                        deletedIds.forEach(id => {
                            const itemSnapshot = selectedSnapshot.get(id);
                            if (!itemSnapshot) {
                                console.warn(`[ExternalAPI] Món ID #${id} được backend xác nhận xóa nhưng không có trong snapshot trước khi xóa. Bỏ qua để tránh suy đoán số liệu.`);
                                return;
                            }

                            const rawQty = itemSnapshot.quantity;
                            const rawPrice = itemSnapshot.price;
                            const rawDiscount = itemSnapshot.discount_amount;

                            let finalPrice = null;
                            if (itemSnapshot.final_price !== undefined && itemSnapshot.final_price !== null && !isNaN(Number(itemSnapshot.final_price))) {
                                finalPrice = Number(itemSnapshot.final_price);
                            } else if (rawQty !== undefined && rawQty !== null && !isNaN(Number(rawQty)) && rawPrice !== undefined && rawPrice !== null && !isNaN(Number(rawPrice))) {
                                finalPrice = getItemFinalPrice(itemSnapshot);
                            }

                            newRecords.push({
                                record_id: `${targetOrderId}_${id}_${actionToken}`,
                                action_token: actionToken,
                                order_id: targetOrderId,
                                table_name: cachedOrder?.table?.name || "—",
                                item_id: id,
                                product_name: itemSnapshot.product_name_vi || itemSnapshot.product_name || "—",
                                quantity: (rawQty !== undefined && rawQty !== null && !isNaN(Number(rawQty))) ? Number(rawQty) : null,
                                unit_price: (rawPrice !== undefined && rawPrice !== null && !isNaN(Number(rawPrice))) ? Number(rawPrice) : null,
                                discount_amount: (rawDiscount !== undefined && rawDiscount !== null && !isNaN(Number(rawDiscount))) ? Number(rawDiscount) : 0,
                                final_price: finalPrice,
                                deleted_at: new Date().toISOString()
                            });
                        });

                        if (newRecords.length > 0) {
                            saveDeletedItemsToStorageOrRam(newRecords, noticeEl);
                            processedActionTokens.add(actionToken);
                        }
                    }
                } catch (reportErr) {
                    console.error("[ExternalAPI] Lỗi khi ghi nhận dữ liệu xóa vào báo cáo:", reportErr);
                }

                syncDeletedPdfButtonState();
            } catch (error) {
                console.error("Delete order items error:", error);
                if (errorEl) {
                    errorEl.textContent = error.message || "Không thể xóa món ăn.";
                    errorEl.hidden = false;
                }
            } finally {
                isDeletingItems = false;
                if (deleteBtn) {
                    deleteBtn.innerHTML = `Xóa món đã chọn (<span data-items-modal-delete-count>${selectedItemIds.size}</span>)`;
                }
                updateOrderItemsControls();
            }
        }

        function closeOrderItemsModal(force = false) {
            const modal = getOrderItemsModal();
            if (!modal || (isDeletingItems && !force)) {
                return;
            }

            modal.hidden = true;
            activeItemsFetchId++;
            currentItemsOrderId = null;
            selectedItemIds.clear();
            currentOrderItems = [];
            updateDeletedAmountPreview();

            document.body.classList.remove("vm-order-modal-open");
        }

        // ---------------------------------------------------------------------
        // Aggregated Deleted Order Items PDF Export
        // ---------------------------------------------------------------------

        let isExportingPdf = false;

        async function exportAllDeletedItemsToPdf() {
            if (isExportingPdf) {
                return;
            }

            const todayVnKey = checkAndPurgeExpiredDailyCycle();
            const allItems = getAllAggregatedDeletedItems();
            if (allItems.length === 0) {
                return;
            }

            const exportBtn = document.querySelector("[data-export-deleted-pdf]");
            const originalBtnHtml = exportBtn ? exportBtn.innerHTML : "";

            isExportingPdf = true;
            if (exportBtn) {
                exportBtn.disabled = true;
                exportBtn.innerHTML = `
                    <div class="vm-api-spinner" style="width: 14px; height: 14px; border-width: 2px;"></div>
                    <span>Đang tạo PDF...</span>
                `;
            }

            try {
                // Yield to allow UI spinner to render
                await new Promise((resolve) => setTimeout(resolve, 60));

                const doc = new jsPDF({
                    orientation: "landscape",
                    unit: "mm",
                    format: "a4"
                });

                doc.addFileToVFS("Roboto-Regular.ttf", ROBOTO_REGULAR_BASE64);
                doc.addFileToVFS("Roboto-Bold.ttf", ROBOTO_BOLD_BASE64);
                doc.addFont("Roboto-Regular.ttf", "Roboto", "normal");
                doc.addFont("Roboto-Bold.ttf", "Roboto", "bold");
                doc.setFont("Roboto", "normal");

                const pageWidth = doc.internal.pageSize.getWidth();
                let currentY = 16;

                // Title
                doc.setFont("Roboto", "bold");
                doc.setFontSize(16);
                doc.setTextColor(30, 41, 59);
                doc.text("BÁO CÁO TỔNG HỢP CÁC MÓN ĐÃ XÓA", 14, currentY);

                currentY += 7;

                // Subtitle / printed time
                doc.setFont("Roboto", "normal");
                doc.setFontSize(9);
                doc.setTextColor(100, 116, 139);
                const printedAt = new Date().toLocaleString("vi-VN", { timeZone: "Asia/Ho_Chi_Minh" });
                doc.text(`Chu kỳ ngày: ${todayVnKey} (Giờ VN) | Thời điểm xuất file: ${printedAt}`, 14, currentY);

                currentY += 6;

                // Table columns
                const tableHeaders = [
                    [
                        "STT",
                        "Order ID",
                        "Bàn",
                        "Tên món",
                        "SL",
                        "Đơn giá",
                        "Giảm giá",
                        "Thành tiền",
                        "Thời điểm xóa"
                    ]
                ];

                let hasUnknownPrice = false;
                let calculatedTotalAmount = 0;
                let totalQuantity = 0;

                const tableBody = allItems.map((item, index) => {
                    const stt = String(index + 1);
                    const orderIdStr = `#${item.order_id || "-"}`;
                    const tableName = item.table_name || "—";
                    const productName = item.product_name || "—";
                    const qtyStr = item.quantity !== null && item.quantity !== undefined
                        ? String(item.quantity)
                        : "—";

                    if (item.quantity !== null && item.quantity !== undefined) {
                        totalQuantity += Number(item.quantity) || 0;
                    }

                    const unitPriceStr = item.unit_price !== null && item.unit_price !== undefined
                        ? `${money(item.unit_price)} ₫`
                        : "—";

                    const discountStr = item.discount_amount
                        ? `${money(item.discount_amount)} ₫`
                        : "0 ₫";

                    let finalPriceStr = "—";
                    if (item.final_price !== null && item.final_price !== undefined) {
                        finalPriceStr = `${money(item.final_price)} ₫`;
                        calculatedTotalAmount += Number(item.final_price) || 0;
                    } else {
                        hasUnknownPrice = true;
                    }

                    let deletedAtStr = "—";
                    if (item.deleted_at) {
                        const d = new Date(item.deleted_at);
                        if (!isNaN(d.getTime())) {
                            deletedAtStr = d.toLocaleString("vi-VN");
                        }
                    }

                    return [
                        stt,
                        orderIdStr,
                        tableName,
                        productName,
                        qtyStr,
                        unitPriceStr,
                        discountStr,
                        finalPriceStr,
                        deletedAtStr
                    ];
                });

                doc.autoTable({
                    head: tableHeaders,
                    body: tableBody,
                    startY: currentY,
                    theme: "grid",
                    styles: {
                        font: "Roboto",
                        fontSize: 8.5,
                        textColor: [30, 41, 59],
                        lineColor: [226, 232, 240],
                        lineWidth: 0.2,
                        cellPadding: 2.5
                    },
                    headStyles: {
                        fillColor: [241, 245, 249],
                        textColor: [15, 23, 42],
                        fontStyle: "bold",
                        lineWidth: 0.2,
                        lineColor: [203, 213, 225]
                    },
                    columnStyles: {
                        0: { halign: "center", cellWidth: 12 },
                        1: { halign: "center", cellWidth: 20 },
                        2: { halign: "left", cellWidth: 26 },
                        3: { halign: "left", cellWidth: "auto" },
                        4: { halign: "center", cellWidth: 14 },
                        5: { halign: "right", cellWidth: 28 },
                        6: { halign: "right", cellWidth: 24 },
                        7: { halign: "right", cellWidth: 30 },
                        8: { halign: "center", cellWidth: 38 }
                    },
                    alternateRowStyles: {
                        fillColor: [248, 250, 252]
                    },
                    margin: { left: 14, right: 14, top: 14, bottom: 18 },
                    didDrawPage: (data) => {
                        const str = `Trang ${data.pageNumber} / ${doc.internal.getNumberOfPages()}`;
                        doc.setFontSize(8);
                        doc.setFont("Roboto", "normal");
                        doc.setTextColor(148, 163, 184);
                        doc.text(
                            str,
                            pageWidth - 14,
                            doc.internal.pageSize.getHeight() - 8,
                            { align: "right" }
                        );
                    }
                });

                let finalY = doc.lastAutoTable.finalY + 8;
                const pageHeight = doc.internal.pageSize.getHeight();
                const summaryHeight = hasUnknownPrice ? 36 : 30;

                if (finalY + summaryHeight > pageHeight - 16) {
                    doc.addPage();
                    finalY = 16;
                }

                const summaryWidth = 110;
                const summaryX = pageWidth - 14 - summaryWidth;

                doc.setFillColor(248, 250, 252);
                doc.setDrawColor(203, 213, 225);
                doc.setLineWidth(0.3);
                doc.roundedRect(summaryX, finalY, summaryWidth, summaryHeight, 2, 2, "FD");

                const uniqueOrderCount = new Set(allItems.map((it) => it.order_id)).size;

                doc.setFontSize(8.5);
                doc.setFont("Roboto", "normal");
                doc.setTextColor(71, 85, 105);
                doc.text("Tổng số đơn liên quan:", summaryX + 6, finalY + 6);
                doc.text("Tổng số dòng món đã xóa:", summaryX + 6, finalY + 12);
                doc.text("Tổng số lượng món đã xóa:", summaryX + 6, finalY + 18);

                doc.setFont("Roboto", "bold");
                doc.setTextColor(185, 28, 28);
                doc.text("TỔNG TIỀN MÓN ĐÃ XÓA:", summaryX + 6, finalY + 24);
                doc.text(`${money(calculatedTotalAmount)} ₫`, summaryX + summaryWidth - 6, finalY + 24, { align: "right" });

                if (hasUnknownPrice) {
                    doc.setFont("Roboto", "normal");
                    doc.setFontSize(7.5);
                    doc.setTextColor(148, 163, 184);
                    doc.text("* Một số món không đủ dữ liệu giá nên không cộng vào tổng tiền", summaryX + 6, finalY + 31);
                }

                const now = new Date();
                const padStr = (n) => String(n).padStart(2, "0");
                const datePart = `${todayVnKey}_${padStr(now.getHours())}${padStr(now.getMinutes())}`;
                const fileName = `tong-hop-mon-da-xoa-${datePart}.pdf`;

                doc.save(fileName);
            } catch (err) {
                console.error("[ExternalAPI] Xuất PDF tổng hợp thất bại:", err);
                alert(`Tạo PDF tổng hợp thất bại: ${err.message || "Lỗi không xác định"}. Vui lòng thử lại.`);
            } finally {
                isExportingPdf = false;
                if (exportBtn) {
                    exportBtn.innerHTML = originalBtnHtml;
                }
                syncDeletedPdfButtonState();
            }
        }


        // ---------------------------------------------------------------------
        // Date Filter Helpers
        // ---------------------------------------------------------------------

        function setDefaultDates() {
            const now = new Date();

            dateInput.value =
                `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;

            monthInput.value =
                `${now.getFullYear()}-${pad(now.getMonth() + 1)}`;

            yearInput.value = now.getFullYear();

            const temp = new Date(now);
            const day = temp.getDay() || 7;

            temp.setDate(temp.getDate() + 4 - day);

            const yearStart = new Date(temp.getFullYear(), 0, 1);

            const weekNumber = Math.ceil(
                (((temp - yearStart) / 86400000) + 1) / 7
            );

            weekInput.value =
                `${temp.getFullYear()}-W${pad(weekNumber)}`;
        }

        function updateFilterFields() {
            const type = timeType.value;

            if (dayField) dayField.hidden = type !== "day";
            if (weekField) weekField.hidden = type !== "week";
            if (monthField) monthField.hidden = type !== "month";
            if (yearField) yearField.hidden = type !== "year";
        }

        function getDateRange() {
            const type = timeType.value;

            const formatDate = (date) => {
                return [
                    date.getFullYear(),
                    pad(date.getMonth() + 1),
                    pad(date.getDate()),
                ].join("-");
            };

            if (type === "day") {
                if (!dateInput.value) {
                    throw new Error("Please select a date.");
                }

                return {
                    date_from: dateInput.value,
                    date_to: dateInput.value,
                };
            }

            if (type === "week") {
                if (!weekInput.value) {
                    throw new Error("Please select a week.");
                }

                const match = weekInput.value.match(/^(\d{4})-W(\d{2})$/);
                if (!match) {
                    throw new Error("Please select a valid week.");
                }

                const year = Number(match[1]);
                const week = Number(match[2]);

                if (week < 1 || week > 53) {
                    throw new Error("Please select a valid week.");
                }

                const jan4 = new Date(year, 0, 4);
                const monday = new Date(jan4);
                const dayOfWeek = jan4.getDay() || 7;

                monday.setDate(
                    jan4.getDate() - dayOfWeek + 1 + (week - 1) * 7
                );

                const sunday = new Date(monday);
                sunday.setDate(monday.getDate() + 6);

                const thursday = new Date(monday);
                thursday.setDate(monday.getDate() + 3);

                if (thursday.getFullYear() !== year) {
                    throw new Error("The selected week is invalid.");
                }

                return {
                    date_from: formatDate(monday),
                    date_to: formatDate(sunday),
                };
            }

            if (type === "month") {
                if (!monthInput.value) {
                    throw new Error("Please select a month.");
                }

                const match = monthInput.value.match(/^(\d{4})-(\d{2})$/);
                if (!match) {
                    throw new Error("Please select a valid month.");
                }

                const year = Number(match[1]);
                const month = Number(match[2]);

                if (month < 1 || month > 12) {
                    throw new Error("Please select a valid month.");
                }

                const lastDay = new Date(year, month, 0).getDate();

                return {
                    date_from: `${year}-${pad(month)}-01`,
                    date_to: `${year}-${pad(month)}-${pad(lastDay)}`,
                };
            }

            if (type === "year") {
                const value = yearInput.value.trim();
                const year = Number(value);

                if (
                    !/^\d{4}$/.test(value) ||
                    year < 2000 ||
                    year > 2100
                ) {
                    throw new Error("Please enter a valid year.");
                }

                return {
                    date_from: `${year}-01-01`,
                    date_to: `${year}-12-31`,
                };
            }

            throw new Error("Invalid time period.");
        }

        // ---------------------------------------------------------------------
        // API: Load Orders
        // ---------------------------------------------------------------------

        function buildRequestData(page, range = null, perPage = 100) {
            const formData = new FormData();

            formData.append("action", "vm_external_get_orders");
            formData.append("nonce", apiConfig.nonce);
            formData.append("page", String(page));
            formData.append("per_page", String(perPage));

            const orderId = orderIdInput
                ? orderIdInput.value.trim()
                : "";

            if (orderId) {
                formData.append("order_id", orderId);
            } else if (range) {
                formData.append("date_from", range.date_from);
                formData.append("date_to", range.date_to);
            }

            return formData;
        }

        async function requestOrders(formData) {
            const response = await fetch(apiConfig.ajaxUrl, {
                method: "POST",
                credentials: "same-origin",
                body: formData,
            });

            const rawResponse = await response.text();
            let ajaxResponse;

            try {
                ajaxResponse = JSON.parse(rawResponse);
            } catch {
                throw new Error(`Invalid AJAX response (${response.status}).`);
            }

            if (!response.ok || !ajaxResponse.success) {
                throw new Error(
                    ajaxResponse?.data?.message ||
                    `Request failed with status ${response.status}.`
                );
            }

            return ajaxResponse.data;
        }

        async function loadOrders(page = 1) {
            checkAndPurgeExpiredDailyCycle();
            if (isLoading) {
                return;
            }

            const orderId = orderIdInput
                ? orderIdInput.value.trim()
                : "";

            let range = null;

            if (!orderId) {
                try {
                    range = getDateRange();
                } catch (error) {
                    showMessage(error.message);
                    return;
                }
            }

            isLoading = true;
            showLoading(true);
            clearMessage();

            const formData = buildRequestData(page, range);
            const todayVnKey = getVietnamDateKey();
            const isTodayScope = !orderId && Boolean(range && range.date_from === todayVnKey && range.date_to === todayVnKey);

            try {
                const apiResponse = await requestOrders(formData);
                renderOrders(apiResponse, isTodayScope);
            } catch (error) {
                console.error("External API error:", error);
                renderError(error.message);
                showMessage(error.message);
            } finally {
                isLoading = false;
                showLoading(false);
            }
        }

        // ---------------------------------------------------------------------
        // Orders Rendering
        // ---------------------------------------------------------------------

        function renderOrders(apiResponse, isTodayScope = false) {
            const orders = apiResponse?.data || [];
            const meta = apiResponse?.meta || {};

            totalOrders.textContent = Number(
                meta.total ?? orders.length
            ).toLocaleString();

            currentOrdersTotalAmount = orders.reduce((sum, order) => {
                return sum + Number(order.total_price || 0);
            }, 0);

            currentCashTotalAmount = orders.reduce((sum, order) => {
                const cash = getOrderCashAmount(order);
                return sum + Number(cash || 0);
            }, 0);

            const currentPage = Number(meta.current_page || 1);
            const lastPage = Number(meta.last_page || 1);
            const isMultiPage = lastPage > 1;
            const pageSuffix = isMultiPage ? ` (Trang ${currentPage}/${lastPage})` : "";

            if (totalOrdersLabelEl) {
                totalOrdersLabelEl.textContent = `Tổng giá trị hóa đơn${pageSuffix}:`;
            }
            if (totalCashLabelEl) {
                totalCashLabelEl.textContent = `Tiền mặt thực thu${pageSuffix}:`;
            }

            if (totalOrdersAmountEl) {
                totalOrdersAmountEl.textContent = formatVnd(currentOrdersTotalAmount);
            }

            if (totalCashAmountEl) {
                totalCashAmountEl.textContent = formatVnd(currentCashTotalAmount);
            }

            const isSingleOrderSearch = Boolean(orderIdInput && orderIdInput.value.trim());
            const isCompleteDailyScope = !isSingleOrderSearch && !isMultiPage && isTodayScope;

            if (isCompleteDailyScope) {
                saveLatestCashAmountToStorage(currentCashTotalAmount);
            }
            updateAllOrdersDeletedAmountDisplay();

            summary.hidden = false;

            if (!orders.length) {
                renderEmpty();
                return;
            }

            orders.forEach((order) => {
                orderCache.set(Number(order.id), order);
            });

            tbody.innerHTML = orders.map((order) => {
                const orderId = Number(order.id);
                const tableName = order.table?.name || "-";
                const itemsCount = Array.isArray(order.items)
                    ? order.items.length
                    : 0;

                const createdAt = order.created_at
                    ? new Date(order.created_at).toLocaleString()
                    : "-";

                const method = String(order.payment_method || "").toLowerCase().trim();
                const isSplit = method === "split";
                const cashAmount = getOrderCashAmount(order);
                const requiresVat = order.requires_vat === true;

                const splitInfoHtml = isSplit
                    ? `
                        <div class="vm-order-split-info">
                            <span class="vm-order-cash-val">Tiền mặt: <strong>${cashAmount !== null ? money(cashAmount) + ' ₫' : '—'}</strong></span>
                            <span class="vm-badge vm-badge--split">Split</span>
                        </div>
                    `
                    : "";

                const vatBadgeHtml = requiresVat
                    ? `
                        <div class="vm-order-vat-info">
                            <span class="vm-badge vm-badge--vat" title="Đơn hàng yêu cầu xuất hóa đơn VAT">VAT</span>
                        </div>
                    `
                    : "";

                const rowClasses = [];
                if (isSplit) {
                    rowClasses.push("vm-api-table__row--split");
                }
                if (isOrderPaidBeforeOrAt16(order)) {
                    rowClasses.push("vm-api-table__row--paid-before-16");
                }
                if (requiresVat) {
                    rowClasses.push("vm-api-table__row--requires-vat");
                }
                const rowClassAttr = rowClasses.length > 0 ? ` class="${rowClasses.join(" ")}"` : "";

                let orderIdCellContent = "";
                if (requiresVat) {
                    orderIdCellContent = `
                        <span
                            class="vm-order-id-label vm-order-id-label--vat"
                            title="Đơn VAT (chỉ xem thông tin, không mở modal)"
                        >
                            #${orderId}
                        </span>
                    `;
                } else if (isSplit) {
                    orderIdCellContent = `
                        <span
                            class="vm-order-id-label vm-order-id-label--split"
                            title="Đơn Split (chỉ xem thông tin, không mở modal)"
                        >
                            #${orderId}
                        </span>
                    `;
                } else {
                    orderIdCellContent = `
                        <button
                            type="button"
                            class="vm-order-link"
                            data-view-items="${orderId}"
                            title="Xem chi tiết món ăn"
                        >
                            #${orderId}
                        </button>
                    `;
                }

                return `
                    <tr data-order-row="${orderId}" data-payment-method="${escapeHtml(method)}" data-requires-vat="${requiresVat ? "true" : "false"}"${rowClassAttr}>
                        <td class="d-flex align-items-center gap-2">
                            ${orderIdCellContent}
                            ${vatBadgeHtml}
                        </td>
                        <td>${escapeHtml(tableName)}</td>
                        <td>${escapeHtml(order.status || "-")}</td>
                        <td data-order-items-count>${itemsCount}</td>
                        <td>
                            <div data-order-total-price>${money(order.total_price)}</div>
                            ${splitInfoHtml}
                        </td>
                        <td>${escapeHtml(createdAt)}</td>
                    </tr>
                `;
            }).join("");

            renderPagination(meta);
        }

        function renderEmpty() {
            const orderId = orderIdInput
                ? orderIdInput.value.trim()
                : "";

            currentOrdersTotalAmount = 0;
            currentCashTotalAmount = 0;
            if (totalOrdersLabelEl) {
                totalOrdersLabelEl.textContent = "Tổng giá trị hóa đơn:";
            }
            if (totalCashLabelEl) {
                totalCashLabelEl.textContent = "Tiền mặt thực thu:";
            }
            if (totalOrdersAmountEl) {
                totalOrdersAmountEl.textContent = "0 ₫";
            }
            if (totalCashAmountEl) {
                totalCashAmountEl.textContent = "0 ₫";
            }
            updateAllOrdersDeletedAmountDisplay();

            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="vm-api-empty">
                        ${orderId
                    ? "No order found with this Order ID."
                    : "No orders found for this period."
                }
                    </td>
                </tr>
            `;

            pagination.innerHTML = "";
        }

        function renderError(errorMessage) {
            totalOrders.textContent = "0";
            currentOrdersTotalAmount = 0;
            currentCashTotalAmount = 0;
            if (totalOrdersLabelEl) {
                totalOrdersLabelEl.textContent = "Tổng giá trị hóa đơn:";
            }
            if (totalCashLabelEl) {
                totalCashLabelEl.textContent = "Tiền mặt thực thu:";
            }
            if (totalOrdersAmountEl) {
                totalOrdersAmountEl.textContent = "—";
            }
            if (totalCashAmountEl) {
                totalCashAmountEl.textContent = "—";
            }
            updateAllOrdersDeletedAmountDisplay();

            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="vm-api-empty">
                        ${escapeHtml(errorMessage)}
                    </td>
                </tr>
            `;

            pagination.innerHTML = "";
            summary.hidden = true;
        }

        function renderPagination(meta) {
            const current = Number(meta.current_page || 1);
            const last = Number(meta.last_page || 1);

            if (last <= 1) {
                pagination.innerHTML = "";
                return;
            }

            let html = "";

            if (current > 1) {
                html += `
                    <button
                        type="button"
                        data-page="${current - 1}"
                    >
                        Previous
                    </button>
                `;
            }

            for (let i = 1; i <= last; i++) {
                if (
                    i === 1 ||
                    i === last ||
                    Math.abs(i - current) <= 2
                ) {
                    html += `
                        <button
                            type="button"
                            class="${i === current ? "is-active" : ""}"
                            data-page="${i}"
                        >
                            ${i}
                        </button>
                    `;
                }
            }

            if (current < last) {
                html += `
                    <button
                        type="button"
                        data-page="${current + 1}"
                    >
                        Next
                    </button>
                `;
            }

            pagination.innerHTML = html;
        }

        // ---------------------------------------------------------------------
        // Event Binding
        // ---------------------------------------------------------------------

        function bindEvents() {
            timeType.addEventListener("change", () => {
                updateFilterFields();
                loadOrders(1);
            });

            callApiButton.addEventListener("click", (event) => {
                event.preventDefault();
                loadOrders(1);
            });

            if (searchOrderButton && orderIdInput) {
                searchOrderButton.addEventListener("click", (event) => {
                    event.preventDefault();
                    loadOrders(1);
                });
            }

            if (orderIdInput) {
                orderIdInput.addEventListener("keydown", (event) => {
                    if (event.key !== "Enter") {
                        return;
                    }

                    event.preventDefault();
                    loadOrders(1);
                });
            }

            if (clearOrderButton && orderIdInput) {
                clearOrderButton.addEventListener("click", (event) => {
                    event.preventDefault();
                    orderIdInput.value = "";
                    clearMessage();
                    loadOrders(1);
                });
            }

            tbody.addEventListener("click", (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }

                // Explicitly block any modal action when clicking anywhere on a split row or a VAT row
                const row = target.closest("tr[data-order-row]");
                if (row) {
                    const rowMethod = String(row.dataset.paymentMethod || "").toLowerCase().trim();
                    if (rowMethod === "split") {
                        return;
                    }
                    if (row.dataset.requiresVat === "true") {
                        return;
                    }
                }

                const viewBtn = target.closest("[data-view-items]");
                if (viewBtn) {
                    event.preventDefault();
                    const orderId = Number(viewBtn.dataset.viewItems);
                    if (Number.isInteger(orderId) && orderId > 0) {
                        const cachedOrder = orderCache.get(orderId);
                        const method = String(cachedOrder?.payment_method || "").toLowerCase().trim();
                        if (method === "split") {
                            return; // Double-guard against split orders
                        }
                        if (cachedOrder?.requires_vat === true) {
                            return; // Double-guard against VAT orders
                        }
                        openOrderItemsModal(orderId);
                    }
                }
            });

            pagination.addEventListener("click", (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }

                const button = target.closest("[data-page]");
                if (!button || isLoading) {
                    return;
                }

                const page = Number(button.dataset.page);
                if (!Number.isInteger(page) || page < 1) {
                    return;
                }

                loadOrders(page);
            });

            document.addEventListener("click", (event) => {
                const target = event.target;
                if (!(target instanceof Element)) {
                    return;
                }

                if (target.closest("[data-export-deleted-pdf]")) {
                    event.preventDefault();
                    exportAllDeletedItemsToPdf();
                }
            });
        }

        // ---------------------------------------------------------------------
        // Init
        // ---------------------------------------------------------------------

        checkAndPurgeExpiredDailyCycle();
        const storedCash = getStoredLatestCashAmount();
        if (storedCash !== null && storedCash > 0) {
            currentCashTotalAmount = storedCash;
            if (totalCashAmountEl) {
                totalCashAmountEl.textContent = formatVnd(currentCashTotalAmount);
            }
            summary.hidden = false;
        }
        bindOrderItemsModalEvents();
        setDefaultDates();
        updateFilterFields();
        bindEvents();
        syncDeletedPdfButtonState();
        scheduleMidnightCycleCheck();

        document.addEventListener("visibilitychange", () => {
            if (document.visibilityState === "visible") {
                checkAndPurgeExpiredDailyCycle();
            }
        });

        setInterval(() => {
            checkAndPurgeExpiredDailyCycle();
        }, 60000);
    };

    $(document).ready(function () {
        vmCallAPI();
    });

})(jQuery);