(function ($) {
    "use strict";

    const vmCallAPI = () => {
        const app = document.querySelector("#vm-external-orders");
        const apiConfig = window.vmExternalAPI;

        if (!app || !apiConfig) {
            return;
        }

        // ---------------------------------------------------------------------
        // DOM
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
        let isDeleting = false;

        const selectedOrderIds = new Set();
        const orderCache = new Map();

        let modalConfirmCallback = null;
        let modalIsProcessing = false;

        // ---------------------------------------------------------------------
        // Confirmation Modal
        // ---------------------------------------------------------------------

        function injectConfirmModal() {
            // Modal được thêm vào document.body nên phải kiểm tra trên document.
            if (document.querySelector("[data-order-confirm-modal]")) {
                return;
            }

            const modal = document.createElement("div");

            modal.dataset.orderConfirmModal = "";
            modal.className = "vm-order-modal";
            modal.hidden = true;

            modal.innerHTML = `
                <div
                    class="vm-order-modal__backdrop"
                    data-modal-close
                ></div>

                <section
                    class="vm-order-modal__dialog"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="vm-order-modal-title"
                >
                    <header class="vm-order-modal__header">
                        <div>
                            <h2 id="vm-order-modal-title">
                                Confirm deletion
                            </h2>

                            <p data-modal-description></p>
                        </div>

                        <button
                            type="button"
                            data-modal-close
                            aria-label="Close dialog"
                        >&times;</button>
                    </header>

                    <div class="vm-order-modal__body">
                        <div class="vm-order-modal__summary">
                            Orders to delete:
                            <strong data-modal-count>0</strong>
                        </div>

                        <div class="vm-order-modal__table-wrap">
                            <table class="vm-order-modal__table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Table</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Created at</th>
                                    </tr>
                                </thead>

                                <tbody data-modal-orders></tbody>
                            </table>
                        </div>

                        <p class="vm-order-modal__warning">
                            This action permanently deletes the listed orders
                            and cannot be undone.
                        </p>

                        <p
                            class="vm-order-modal__error"
                            data-modal-error
                            hidden
                        ></p>
                    </div>

                    <footer class="vm-order-modal__footer">
                        <button type="button" data-modal-close>
                            Cancel
                        </button>

                        <button
                            type="button"
                            data-modal-confirm
                            class="vm-order-modal__delete"
                        >
                            Confirm deletion
                        </button>
                    </footer>
                </section>
            `;

            document.body.appendChild(modal);

            modal.addEventListener("click", async (event) => {
                const closeButton = event.target.closest("[data-modal-close]");

                if (closeButton) {
                    if (!modalIsProcessing) {
                        closeConfirmModal();
                    }

                    return;
                }

                const confirmButton = event.target.closest(
                    "[data-modal-confirm]"
                );

                if (
                    !confirmButton ||
                    modalIsProcessing ||
                    !modalConfirmCallback
                ) {
                    return;
                }

                modalIsProcessing = true;

                confirmButton.disabled = true;
                confirmButton.textContent = "Processing...";

                try {
                    await modalConfirmCallback();
                } catch (error) {
                    const errorElement = modal.querySelector(
                        "[data-modal-error]"
                    );

                    errorElement.textContent =
                        error.message || "An unexpected error occurred.";

                    errorElement.hidden = false;
                } finally {
                    modalIsProcessing = false;

                    // Luôn reset nút, kể cả modal đã được đóng thành công.
                    confirmButton.disabled = false;
                    confirmButton.textContent = "Confirm deletion";
                }
            });

            document.addEventListener("keydown", (event) => {
                if (
                    event.key === "Escape" &&
                    !modal.hidden &&
                    !modalIsProcessing
                ) {
                    closeConfirmModal();
                }
            });
        }

        function openConfirmModal(title, description, orders, onConfirm) {
            const modal = document.querySelector(
                "[data-order-confirm-modal]"
            );

            if (!modal) {
                throw new Error(
                    "Confirmation modal has not been initialized."
                );
            }

            modal.querySelector("#vm-order-modal-title").textContent = title;

            modal.querySelector("[data-modal-description]").textContent =
                description;

            modal.querySelector("[data-modal-count]").textContent =
                String(orders.length);

            const errorElement = modal.querySelector("[data-modal-error]");
            errorElement.textContent = "";
            errorElement.hidden = true;

            const ordersContainer = modal.querySelector(
                "[data-modal-orders]"
            );

            ordersContainer.innerHTML = orders.map((order) => {
                const id = Number(order.id);
                const tableName = order.table?.name || "-";

                const createdAt = order.created_at
                    ? new Date(order.created_at).toLocaleString()
                    : "-";

                return `
                    <tr>
                        <td>#${id}</td>
                        <td>${escapeHtml(tableName)}</td>
                        <td>${escapeHtml(order.status || "-")}</td>
                        <td>${money(order.total_price)}</td>
                        <td>${escapeHtml(createdAt)}</td>
                    </tr>
                `;
            }).join("");

            modalConfirmCallback = onConfirm;

            modal.hidden = false;
            document.body.classList.add("vm-order-modal-open");

            modal.querySelector('[aria-label="Close dialog"]')?.focus();
        }

        function closeConfirmModal(force = false) {
            const modal = document.querySelector(
                "[data-order-confirm-modal]"
            );

            if (!modal || (modalIsProcessing && !force)) {
                return;
            }

            modal.hidden = true;
            modalConfirmCallback = null;

            document.body.classList.remove("vm-order-modal-open");
        }

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

        // ---------------------------------------------------------------------
        // UI
        // ---------------------------------------------------------------------

        function showLoading(show) {
            loading.hidden = !show;

            callApiButton.disabled = show || isDeleting;

            if (searchOrderButton) {
                searchOrderButton.disabled = show || isDeleting;
            }

            if (clearOrderButton) {
                clearOrderButton.disabled = show || isDeleting;
            }

            updateDeleteControls();
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
        // Date Filter
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

            if (dayField) {
                dayField.hidden = type !== "day";
            }

            if (weekField) {
                weekField.hidden = type !== "week";
            }

            if (monthField) {
                monthField.hidden = type !== "month";
            }

            if (yearField) {
                yearField.hidden = type !== "year";
            }
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

            // Day
            if (type === "day") {
                if (!dateInput.value) {
                    throw new Error("Please select a date.");
                }

                return {
                    date_from: dateInput.value,
                    date_to: dateInput.value,
                };
            }

            // Week: ISO week, Monday to Sunday
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

                // ISO week 1 is the week containing January 4.
                const jan4 = new Date(year, 0, 4);
                const monday = new Date(jan4);
                const dayOfWeek = jan4.getDay() || 7;

                monday.setDate(
                    jan4.getDate() - dayOfWeek + 1 + (week - 1) * 7
                );

                const sunday = new Date(monday);
                sunday.setDate(monday.getDate() + 6);

                // Validate the selected ISO week-year.
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

            // Month
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

            // Year
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

        function buildRequestData(page, range = null, perPage = 20) {
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
                throw new Error(
                    `Invalid AJAX response (${response.status}).`
                );
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
            if (isLoading || isDeleting) {
                return;
            }

            const orderId = orderIdInput
                ? orderIdInput.value.trim()
                : "";

            let range = null;

            // Skip date validation when searching by Order ID.
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
        // Rendering
        // ---------------------------------------------------------------------

        function renderOrders(apiResponse) {
            const orders = apiResponse?.data || [];
            const meta = apiResponse?.meta || {};

            totalOrders.textContent = Number(
                meta.total ?? orders.length
            ).toLocaleString();

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

                const checked = selectedOrderIds.has(orderId)
                    ? "checked"
                    : "";

                return `
                    <tr>
                        <td>
                            <input
                                type="checkbox"
                                data-order-checkbox
                                value="${orderId}"
                                aria-label="Select order ${orderId}"
                                ${checked}
                                ${isDeleting ? "disabled" : ""}
                            >
                        </td>

                        <td>#${orderId}</td>

                        <td>${escapeHtml(tableName)}</td>

                        <td>${escapeHtml(order.status || "-")}</td>

                        <td>${itemsCount}</td>

                        <td>${money(order.total_price)}</td>

                        <td>${escapeHtml(createdAt)}</td>
                    </tr>
                `;
            }).join("");

            renderPagination(meta);
            updateDeleteControls();
        }

        function renderEmpty() {
            const orderId = orderIdInput
                ? orderIdInput.value.trim()
                : "";

            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="vm-api-empty">
                        ${orderId
                    ? "No order found with this Order ID."
                    : "No orders found for this period."
                }
                    </td>
                </tr>
            `;

            pagination.innerHTML = "";
            updateDeleteControls();
        }

        function renderError(errorMessage) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="vm-api-empty">
                        ${escapeHtml(errorMessage)}
                    </td>
                </tr>
            `;

            pagination.innerHTML = "";
            summary.hidden = true;
            updateDeleteControls();
        }

        function renderPagination(meta) {
            const current = Number(meta.current_page || 1);
            const last = Number(meta.last_page || 1);

            if (last <= 1) {
                pagination.innerHTML = "";
                return;
            }

            let html = "";

            // Previous
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

            // Page numbers
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

            // Next
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
        // Selection Toolbar
        // ---------------------------------------------------------------------

        function injectDeleteControls() {
            if (!app.querySelector("[data-delete-toolbar]")) {
                const toolbar = document.createElement("div");

                toolbar.dataset.deleteToolbar = "";
                toolbar.className = "vm-api-delete-toolbar";

                toolbar.innerHTML = `
                    <label>
                        <input type="checkbox" data-select-all>
                        Select all on this page
                    </label>

                    <span>
                        Selected:
                        <strong data-selected-count>0</strong>
                    </span>

                    <button
                        type="button"
                        data-delete-selected
                        disabled
                    >
                        Delete selected
                    </button>

                    <button
                        type="button"
                        data-delete-random
                    >
                        Delete random 30%
                    </button>
                `;

                ordersTable.parentNode.insertBefore(toolbar, ordersTable);
            }

            const headerRow = ordersTable.querySelector("thead tr");

            if (
                headerRow &&
                !headerRow.querySelector("[data-select-column]")
            ) {
                const header = document.createElement("th");

                header.dataset.selectColumn = "";
                header.textContent = "Select";

                headerRow.insertBefore(
                    header,
                    headerRow.firstElementChild
                );
            }
        }

        function getPageCheckboxes() {
            return Array.from(
                tbody.querySelectorAll("[data-order-checkbox]")
            );
        }

        function updateDeleteControls() {
            const selectedCount = app.querySelector(
                "[data-selected-count]"
            );

            const deleteSelectedButton = app.querySelector(
                "[data-delete-selected]"
            );

            const deleteRandomButton = app.querySelector(
                "[data-delete-random]"
            );

            const selectAll = app.querySelector("[data-select-all]");
            const pageCheckboxes = getPageCheckboxes();

            const checkedCount = pageCheckboxes.filter(
                (checkbox) => checkbox.checked
            ).length;

            if (selectedCount) {
                selectedCount.textContent = String(
                    selectedOrderIds.size
                );
            }

            if (deleteSelectedButton) {
                deleteSelectedButton.disabled =
                    isLoading ||
                    isDeleting ||
                    selectedOrderIds.size === 0;
            }

            if (deleteRandomButton) {
                deleteRandomButton.disabled = isLoading || isDeleting;
            }

            if (selectAll) {
                selectAll.checked =
                    pageCheckboxes.length > 0 &&
                    checkedCount === pageCheckboxes.length;

                selectAll.indeterminate =
                    checkedCount > 0 &&
                    checkedCount < pageCheckboxes.length;

                selectAll.disabled =
                    isLoading ||
                    isDeleting ||
                    pageCheckboxes.length === 0;
            }

            pageCheckboxes.forEach((checkbox) => {
                checkbox.disabled = isLoading || isDeleting;
            });
        }

        // ---------------------------------------------------------------------
        // API: Delete Orders
        // ---------------------------------------------------------------------

        async function requestDelete(action, ids = []) {
            const formData = new FormData();

            const orderId = orderIdInput
                ? orderIdInput.value.trim()
                : "";

            formData.append("action", action);
            formData.append("nonce", apiConfig.nonce);

            // Keep the same filter priority as the list.
            if (orderId) {
                formData.append("order_id", orderId);
            } else {
                const range = getDateRange();

                formData.append("date_from", range.date_from);
                formData.append("date_to", range.date_to);
            }

            if (action === "vm_external_delete_orders") {
                ids.forEach((id) => {
                    formData.append("ids[]", String(id));
                });
            }

            if (action === "vm_external_delete_random_orders") {
                formData.append("random_percent", "30");
            }

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
                throw new Error(
                    `Invalid AJAX response (${response.status}).`
                );
            }

            if (!response.ok || !ajaxResponse.success) {
                throw new Error(
                    ajaxResponse?.data?.message ||
                    `Delete request failed with status ${response.status}.`
                );
            }

            return ajaxResponse.data;
        }

        async function deleteOrdersByIds(ids) {
            // Laravel endpoint giới hạn tối đa 100 ID mỗi request.
            const chunkSize = 100;
            let deletedCount = 0;

            for (let i = 0; i < ids.length; i += chunkSize) {
                const chunk = ids.slice(i, i + chunkSize);

                const result = await requestDelete(
                    "vm_external_delete_orders",
                    chunk
                );

                deletedCount += Number(
                    result?.deleted_count ?? chunk.length
                );
            }

            return deletedCount;
        }

        // ---------------------------------------------------------------------
        // Delete Selected Orders
        // ---------------------------------------------------------------------

        async function deleteSelectedOrders() {
            if (
                isLoading ||
                isDeleting ||
                selectedOrderIds.size === 0
            ) {
                return;
            }

            const ids = Array.from(selectedOrderIds);

            const orders = ids.map((id) => {
                return orderCache.get(id) || {
                    id,
                    status: "-",
                    total_price: 0,
                    created_at: "",
                    table: { name: "-" },
                };
            });

            openConfirmModal(
                "Delete selected orders",
                "Review the orders below before confirming deletion.",
                orders,
                async () => {
                    isDeleting = true;
                    showLoading(true);
                    clearMessage();

                    try {
                        const deletedCount = await deleteOrdersByIds(ids);

                        selectedOrderIds.clear();

                        // Cho phép đóng modal sau khi xóa thành công.
                        closeConfirmModal(true);

                        isDeleting = false;

                        await loadOrders(1);

                        showMessage(
                            `Successfully deleted ${deletedCount} order(s).`,
                            "success"
                        );
                    } catch (error) {
                        showMessage(
                            error.message || "Unable to delete orders."
                        );

                        throw error;
                    } finally {
                        isDeleting = false;
                        showLoading(false);
                        updateDeleteControls();
                    }
                }
            );
        }

        // ---------------------------------------------------------------------
        // Delete Random 30% of Matching Orders
        // ---------------------------------------------------------------------

        async function deleteRandomOrders() {
            if (isLoading || isDeleting) {
                return;
            }

            let range = null;

            const orderId = orderIdInput
                ? orderIdInput.value.trim()
                : "";

            try {
                if (!orderId) {
                    range = getDateRange();
                }
            } catch (error) {
                showMessage(error.message);
                return;
            }

            isLoading = true;
            showLoading(true);
            clearMessage();

            try {
                // Fetch all matching pages before selecting random orders.
                const firstPageResponse = await requestOrders(
                    buildRequestData(1, range, 100)
                );

                const meta = firstPageResponse?.meta || {};
                const allOrders = [
                    ...(firstPageResponse?.data || []),
                ];

                const lastPage = Number(meta.last_page || 1);

                for (let page = 2; page <= lastPage; page++) {
                    const response = await requestOrders(
                        buildRequestData(page, range, 100)
                    );

                    allOrders.push(...(response?.data || []));
                }

                if (allOrders.length === 0) {
                    showMessage("No matching orders found.", "info");
                    return;
                }

                // Fisher-Yates shuffle.
                for (let i = allOrders.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1));

                    [allOrders[i], allOrders[j]] = [
                        allOrders[j],
                        allOrders[i],
                    ];
                }

                // Approximately 30%, at least one if orders exist.
                const deleteCount = Math.max(
                    1,
                    Math.round(allOrders.length * 0.3)
                );

                const ordersToDelete = allOrders.slice(0, deleteCount);

                const idsToDelete = ordersToDelete.map(
                    (order) => Number(order.id)
                );

                // Stop loading before showing the modal.
                isLoading = false;
                showLoading(false);

                const filterDescription = orderId
                    ? `Order ID #${orderId}`
                    : `${range.date_from} to ${range.date_to}`;

                openConfirmModal(
                    "Delete random 30% of orders",
                    `Filter: ${filterDescription}. Review the exact orders selected for deletion.`,
                    ordersToDelete,
                    async () => {
                        isDeleting = true;
                        showLoading(true);
                        clearMessage();

                        try {
                            const deletedCount = await deleteOrdersByIds(
                                idsToDelete
                            );

                            selectedOrderIds.clear();

                            // Cho phép đóng modal sau khi xóa thành công.
                            closeConfirmModal(true);

                            isDeleting = false;

                            await loadOrders(1);

                            showMessage(
                                `Successfully deleted ${deletedCount} order(s).`,
                                "success"
                            );
                        } catch (error) {
                            showMessage(
                                error.message || "Unable to delete orders."
                            );

                            throw error;
                        } finally {
                            isDeleting = false;
                            showLoading(false);
                            updateDeleteControls();
                        }
                    }
                );
            } catch (error) {
                console.error("Random order preview error:", error);

                showMessage(
                    error.message || "Unable to preview orders."
                );
            } finally {
                isLoading = false;
                showLoading(false);
            }
        }

        // ---------------------------------------------------------------------
        // Events
        // ---------------------------------------------------------------------

        function bindEvents() {
            timeType.addEventListener("change", () => {
                updateFilterFields();
                selectedOrderIds.clear();
                loadOrders(1);
            });

            callApiButton.addEventListener("click", (event) => {
                event.preventDefault();
                selectedOrderIds.clear();
                loadOrders(1);
            });

            // Search Order ID.
            if (searchOrderButton && orderIdInput) {
                searchOrderButton.addEventListener("click", (event) => {
                    event.preventDefault();
                    selectedOrderIds.clear();
                    loadOrders(1);
                });
            }

            // Press Enter in Order ID input.
            if (orderIdInput) {
                orderIdInput.addEventListener("keydown", (event) => {
                    if (event.key !== "Enter") {
                        return;
                    }

                    event.preventDefault();
                    selectedOrderIds.clear();
                    loadOrders(1);
                });
            }

            // Clear Order ID.
            if (clearOrderButton && orderIdInput) {
                clearOrderButton.addEventListener("click", (event) => {
                    event.preventDefault();

                    orderIdInput.value = "";
                    selectedOrderIds.clear();

                    clearMessage();
                    loadOrders(1);
                });
            }

            // Select or deselect an individual order.
            tbody.addEventListener("change", (event) => {
                const checkbox = event.target.closest(
                    "[data-order-checkbox]"
                );

                if (!checkbox) {
                    return;
                }

                const id = Number(checkbox.value);

                if (!Number.isInteger(id) || id <= 0) {
                    return;
                }

                if (checkbox.checked) {
                    selectedOrderIds.add(id);
                } else {
                    selectedOrderIds.delete(id);
                }

                updateDeleteControls();
            });

            // Select all orders on the current page.
            app.addEventListener("change", (event) => {
                const selectAll = event.target.closest("[data-select-all]");

                if (!selectAll) {
                    return;
                }

                getPageCheckboxes().forEach((checkbox) => {
                    const id = Number(checkbox.value);

                    checkbox.checked = selectAll.checked;

                    if (selectAll.checked) {
                        selectedOrderIds.add(id);
                    } else {
                        selectedOrderIds.delete(id);
                    }
                });

                updateDeleteControls();
            });

            // Delete buttons.
            app.addEventListener("click", (event) => {
                if (event.target.closest("[data-delete-selected]")) {
                    event.preventDefault();
                    deleteSelectedOrders();
                    return;
                }

                if (event.target.closest("[data-delete-random]")) {
                    event.preventDefault();
                    deleteRandomOrders();
                }
            });

            // Pagination.
            pagination.addEventListener("click", (event) => {
                const button = event.target.closest("[data-page]");

                if (!button || isLoading || isDeleting) {
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

        injectDeleteControls();
        injectConfirmModal();
        setDefaultDates();
        updateFilterFields();
        bindEvents();
        updateDeleteControls();
    };

    $(document).ready(function () {
        vmCallAPI();
    });

})(jQuery);