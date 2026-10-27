<?php
/**
 * External API - Toolbar
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="vm-order-search">

    <div class="vm-filter-field">
        <label for="vm-order-id-search">
            Search Order ID
        </label>

        <input type="number" id="vm-order-id-search" data-order-id min="1" inputmode="numeric" autocomplete="off"
            placeholder="Enter Order ID...">
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
    <div class="vm-api-summary__item">
        <span>Đơn hàng:</span>
        <strong data-total-orders>0</strong>
    </div>

    <!-- <div class="vm-api-summary__item">
        <span data-total-orders-label>Tổng giá trị hóa đơn:</span>
        <strong data-total-orders-amount>0 ₫</strong>
    </div> -->

    <div class="vm-api-summary__item vm-api-summary__item--cash">
        <span data-total-cash-label>Tiền mặt thực thu:</span>
        <strong data-total-cash-amount>0 ₫</strong>
    </div>

    <div class="vm-api-summary__item vm-api-summary__item--deleted">
        <span>Tổng tiền món đã xóa:</span>
        <strong data-total-deleted-amount>0 ₫</strong>
    </div>
</div>