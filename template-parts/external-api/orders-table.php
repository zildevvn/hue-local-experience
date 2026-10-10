<?php
/**
 * External API - Orders Table
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="vm-api-table-wrapper">

    <table class="vm-api-table">

        <thead>
            <tr>
                <th data-select-column>
                    Select
                </th>
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
                <td colspan="7" class="vm-api-empty">
                    Select a period and click
                    <strong>Call API</strong>.
                </td>
            </tr>
        </tbody>

    </table>

</div>