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

        let isLoading = false;

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
            callApiButton.disabled = show;
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
                (
                    ((temp - yearStart) / 86400000) + 1
                ) / 7
            );

            weekInput.value =
                `${temp.getFullYear()}-W${pad(weekNumber)}`;
        }

        function updateFilterFields() {
            const type = timeType.value;

            dayField.hidden = type !== "day";
            weekField.hidden = type !== "week";
            monthField.hidden = type !== "month";
            yearField.hidden = type !== "year";
        }

        function getDateRange() {
            const type = timeType.value;

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

            // Week
            if (type === "week") {
                if (!weekInput.value) {
                    throw new Error("Please select a week.");
                }

                const [year, week] =
                    weekInput.value.split("-W").map(Number);

                const simple =
                    new Date(year, 0, 1 + (week - 1) * 7);

                const day = simple.getDay();

                const monday = new Date(simple);

                monday.setDate(
                    simple.getDate() -
                    (day === 0 ? 6 : day - 1)
                );

                const sunday = new Date(monday);

                sunday.setDate(
                    monday.getDate() + 6
                );

                return {
                    date_from:
                        `${monday.getFullYear()}-${pad(monday.getMonth() + 1)}-${pad(monday.getDate())}`,

                    date_to:
                        `${sunday.getFullYear()}-${pad(sunday.getMonth() + 1)}-${pad(sunday.getDate())}`,
                };
            }

            // Month
            if (type === "month") {
                if (!monthInput.value) {
                    throw new Error("Please select a month.");
                }

                const [year, month] =
                    monthInput.value.split("-").map(Number);

                const lastDay =
                    new Date(year, month, 0);

                return {
                    date_from:
                        `${year}-${pad(month)}-01`,

                    date_to:
                        `${year}-${pad(month)}-${pad(lastDay.getDate())}`,
                };
            }

            // Year
            if (type === "year") {
                const year = Number(yearInput.value);

                if (year < 2000 || year > 2100) {
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
        // API
        // ---------------------------------------------------------------------

        function buildRequestData(page, range) {
            const formData = new FormData();

            formData.append(
                "action",
                "vm_external_get_orders"
            );

            formData.append(
                "nonce",
                apiConfig.nonce
            );

            formData.append(
                "page",
                page
            );

            formData.append(
                "per_page",
                20
            );

            formData.append(
                "date_from",
                range.date_from
            );

            formData.append(
                "date_to",
                range.date_to
            );

            return formData;
        }

        async function requestOrders(formData) {
            const response = await fetch(
                apiConfig.ajaxUrl,
                {
                    method: "POST",
                    body: formData,
                }
            );

            const rawResponse = await response.text();

            console.log(
                "WordPress AJAX status:",
                response.status
            );

            console.log(
                "WordPress AJAX response:",
                rawResponse
            );

            let ajaxResponse;

            try {
                ajaxResponse = JSON.parse(rawResponse);
            } catch (error) {
                throw new Error(
                    `Invalid AJAX response (${response.status}).`
                );
            }

            if (
                !response.ok ||
                !ajaxResponse.success
            ) {
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

            let range;

            try {
                range = getDateRange();
            } catch (error) {
                showMessage(error.message);
                return;
            }

            isLoading = true;

            showLoading(true);
            clearMessage();

            const formData =
                buildRequestData(page, range);

            console.log(
                "External API request:",
                {
                    date_from: range.date_from,
                    date_to: range.date_to,
                    page: page,
                }
            );

            try {
                const apiResponse =
                    await requestOrders(formData);

                renderOrders(apiResponse);

            } catch (error) {
                console.error(
                    "External API error:",
                    error
                );

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
            const orders =
                apiResponse?.data || [];

            const meta =
                apiResponse?.meta || {};

            totalOrders.textContent =
                Number(
                    meta.total || orders.length
                ).toLocaleString();

            summary.hidden = false;

            if (!orders.length) {
                renderEmpty();

                return;
            }

            tbody.innerHTML = orders
                .map((order) => {
                    const tableName =
                        order.table?.name || "-";

                    const itemsCount =
                        Array.isArray(order.items)
                            ? order.items.length
                            : 0;

                    const createdAt =
                        order.created_at
                            ? new Date(
                                order.created_at
                            ).toLocaleString()
                            : "-";

                    return `
                        <tr>
                            <td>#${Number(order.id)}</td>

                            <td>
                                ${escapeHtml(tableName)}
                            </td>

                            <td>
                                ${escapeHtml(
                        order.status || "-"
                    )}
                            </td>

                            <td>
                                ${itemsCount}
                            </td>

                            <td>
                                ${money(order.total_price)}
                            </td>

                            <td>
                                ${escapeHtml(createdAt)}
                            </td>
                        </tr>
                    `;
                })
                .join("");

            renderPagination(meta);
        }

        function renderEmpty() {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="vm-api-empty">
                        No orders found for this period.
                    </td>
                </tr>
            `;

            pagination.innerHTML = "";
        }

        function renderError(errorMessage) {
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
            const current =
                Number(meta.current_page || 1);

            const last =
                Number(meta.last_page || 1);

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
        // Events
        // ---------------------------------------------------------------------

        function bindEvents() {
            timeType.addEventListener(
                "change",
                updateFilterFields
            );

            callApiButton.addEventListener(
                "click",
                () => loadOrders(1)
            );

            pagination.addEventListener(
                "click",
                (event) => {
                    const button =
                        event.target.closest("[data-page]");

                    if (!button || isLoading) {
                        return;
                    }

                    const page =
                        Number(button.dataset.page);

                    if (!Number.isInteger(page) || page < 1) {
                        return;
                    }

                    loadOrders(page);
                }
            );
        }

        // ---------------------------------------------------------------------
        // Init
        // ---------------------------------------------------------------------

        setDefaultDates();
        updateFilterFields();
        bindEvents();
    };

    $(document).ready(function () {
        vmCallAPI();
    });

})(jQuery);