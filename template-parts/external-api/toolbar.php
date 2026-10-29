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

<div class="vm-export-deleted-toolbar">
    <button type="button" data-export-deleted-pdf class="vm-btn-export-deleted" disabled title="Chưa có món nào bị xóa trong phiên này">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
        <span>Tải PDF tổng hợp món đã xóa (<span data-export-deleted-count>0</span>)</span>
    </button>
</div>