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
        let sessionTotalDeletedAmount = 0;
        const sessionDeletedItemIds = new Set();

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
                subtitleEl.textContent = `Bàn: ${tableName}${paymentMethodInfo} | Thời gian: ${orderCreated}`;
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

            modal.querySelector('[aria-label="Close dialog"]')?.focus();

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

        function updateDeletedAmountPreview() {
            let selectedItemsAmount = 0;
            selectedItemIds.forEach((id) => {
                if (!sessionDeletedItemIds.has(id)) {
                    const found = currentOrderItems.find((it) => Number(it.id) === id);
                    if (found) {
                        selectedItemsAmount += getItemFinalPrice(found);
                    }
                }
            });

            const preview = sessionTotalDeletedAmount + selectedItemsAmount;
            const formatted = formatVnd(preview);

            if (totalDeletedAmountEl) {
                totalDeletedAmountEl.textContent = formatted;
            }

            const modal = getOrderItemsModal();
            if (modal) {
                const modalDeletedEl = modal.querySelector("[data-items-modal-deleted-amount]");
                if (modalDeletedEl) {
                    modalDeletedEl.textContent = formatted;
                }
            }
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
            updateOrderItemsControls();
            if (deleteBtn) deleteBtn.textContent = "Đang xóa...";

            const targetOrderId = currentItemsOrderId;
            const itemIdsToDelete = Array.from(selectedItemIds);

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

                if (!response.ok || !ajaxResponse.success) {
                    const serverMsg = ajaxResponse?.data?.message || `Xóa món thất bại (${response.status}).`;
                    const notFound = ajaxResponse?.data?.data?.not_found_ids;
                    if (Array.isArray(notFound) && notFound.length > 0) {
                        throw new Error(`${serverMsg} (Không tìm thấy món ID: #${notFound.join(", #")})`);
                    }
                    throw new Error(serverMsg);
                }

                const resultData = ajaxResponse.data || {};
                const deletedIds = Array.isArray(resultData.deleted_ids)
                    ? resultData.deleted_ids.map(Number)
                    : [];
                const deletedSet = new Set(deletedIds);
                const notFoundIds = Array.isArray(resultData.not_found_ids)
                    ? resultData.not_found_ids.map(Number)
                    : [];

                let newlyDeletedAmount = 0;
                deletedIds.forEach(id => {
                    if (!sessionDeletedItemIds.has(id)) {
                        sessionDeletedItemIds.add(id);
                        const foundItem = currentOrderItems.find(it => Number(it.id) === id);
                        if (foundItem) {
                            newlyDeletedAmount += getItemFinalPrice(foundItem);
                        }
                    }
                    selectedItemIds.delete(id);
                    const row = modal.querySelector(`tr[data-item-row="${id}"]`);
                    if (row) row.remove();
                });

                sessionTotalDeletedAmount += newlyDeletedAmount;
                updateDeletedAmountPreview();

                currentOrderItems = currentOrderItems.filter(item => !deletedSet.has(Number(item.id)));

                const orderSummary = resultData.order_summary || {};
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
                    let msg = `Đã xóa thành công ${resultData.deleted_count || deletedIds.length} món.`;
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

                const cached = orderCache.get(targetOrderId);
                if (cached) {
                    const oldOrderPrice = Number(cached.total_price || 0);
                    const priceDiff = oldOrderPrice - newTotalPrice;
                    if (priceDiff > 0) {
                        currentOrdersTotalAmount = Math.max(0, currentOrdersTotalAmount - priceDiff);
                        if (totalOrdersAmountEl) {
                            totalOrdersAmountEl.textContent = formatVnd(currentOrdersTotalAmount);
                        }

                        const orderMethod = String(cached.payment_method || "").toLowerCase().trim();
                        // For cash orders, single cash payment record is synchronized with newTotalPrice on backend
                        if (orderMethod === "cash") {
                            currentCashTotalAmount = Math.max(0, currentCashTotalAmount - priceDiff);
                            if (totalCashAmountEl) {
                                totalCashAmountEl.textContent = formatVnd(currentCashTotalAmount);
                            }
                        }
                        // For split orders: do not alter cash amount at frontend
                    }

                    cached.total_price = newTotalPrice;
                    if (orderSummary.subtotal !== undefined) cached.subtotal = orderSummary.subtotal;
                    if (orderSummary.discount_amount !== undefined) cached.discount_amount = orderSummary.discount_amount;
                    if (Array.isArray(cached.items)) {
                        cached.items = cached.items.filter(item => !deletedSet.has(Number(item.id)));
                    }
                }
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

            try {
                const apiResponse = await requestOrders(formData);
                renderOrders(apiResponse);
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

        function renderOrders(apiResponse) {
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

            if (totalDeletedAmountEl) {
                totalDeletedAmountEl.textContent = formatVnd(sessionTotalDeletedAmount);
            }

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
            if (totalDeletedAmountEl) {
                totalDeletedAmountEl.textContent = formatVnd(sessionTotalDeletedAmount);
            }

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
            if (totalDeletedAmountEl) {
                totalDeletedAmountEl.textContent = formatVnd(sessionTotalDeletedAmount);
            }

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
        }

        // ---------------------------------------------------------------------
        // Init
        // ---------------------------------------------------------------------

        bindOrderItemsModalEvents();
        setDefaultDates();
        updateFilterFields();
        bindEvents();
    };

    $(document).ready(function () {
        vmCallAPI();
    });

})(jQuery);