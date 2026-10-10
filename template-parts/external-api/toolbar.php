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
    <strong data-total-orders>0</strong>
    orders found.
</div>

<!-- Delete Toolbar -->
<div class="vm-api-delete-toolbar" data-delete-toolbar>
    <label>
        <input type="checkbox" data-select-all>
        Select all on this page
    </label>

    <span>
        Selected:
        <strong data-selected-count>0</strong>
    </span>

    <button type="button" data-delete-selected disabled>
        Delete selected
    </button>

    <button type="button" data-delete-random>
        Delete random 30%
    </button>
</div>