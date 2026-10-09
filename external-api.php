<?php
/**
 * Template Name: External API
 */

get_header();
?>

<main id="primary" class="site-main">

    <div id="vm-external-orders" class="vm-external-orders">

        <div class="vm-external-orders__filters">

            <div class="vm-filter-field">
                <label for="vm-time-type">
                    Time Period
                </label>

                <select id="vm-time-type" data-time-type>
                    <option value="day">Day</option>
                    <option value="week">Week</option>
                    <option value="month">Month</option>
                    <option value="year">Year</option>
                </select>
            </div>

            <div class="vm-filter-field" data-filter-day>
                <label for="vm-filter-date">
                    Date
                </label>

                <input type="date" id="vm-filter-date" data-date>
            </div>

            <div class="vm-filter-field" data-filter-week hidden>
                <label for="vm-filter-week">
                    Week
                </label>

                <input type="week" id="vm-filter-week" data-week>
            </div>

            <div class="vm-filter-field" data-filter-month hidden>
                <label for="vm-filter-month">
                    Month
                </label>

                <input type="month" id="vm-filter-month" data-month>
            </div>

            <div class="vm-filter-field" data-filter-year hidden>
                <label for="vm-filter-year">
                    Year
                </label>

                <input type="number" id="vm-filter-year" data-year min="2000" max="2100">
            </div>

            <div class="vm-filter-actions">
                <button type="button" data-call-api>
                    Call API
                </button>
            </div>

        </div>

        <!-- Search Order ID -->
        <div class="vm-order-search">

            <div class="vm-filter-field">
                <label for="vm-order-id-search">
                    Search Order ID
                </label>

                <input type="number" id="vm-order-id-search" data-order-id min="1" inputmode="numeric"
                    autocomplete="off" placeholder="Enter Order ID...">
            </div>

            <div class="vm-filter-actions">

                <button type="button" data-search-order>
                    Search
                </button>

                <button type="button" data-clear-order>
                    Clear
                </button>

            </div>

        </div>

        <div class="vm-api-message" data-message hidden></div>

        <div class="vm-api-loading" data-loading hidden>
            Loading orders...
        </div>

        <div class="vm-api-summary" data-summary hidden>
            <strong data-total-orders>0</strong>
            orders found.
        </div>

        <div class="vm-api-table-wrapper">

            <table class="vm-api-table">

                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Table</th>
                        <th>Status</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Created</th>
                    </tr>
                </thead>

                <tbody data-orders-body>
                    <tr>
                        <td colspan="6" class="vm-api-empty">
                            Select a period and click
                            <strong>Call API</strong>.
                        </td>
                    </tr>
                </tbody>

            </table>

        </div>

        <div class="vm-api-pagination" data-pagination></div>

    </div>

</main>

<?php get_footer(); ?>