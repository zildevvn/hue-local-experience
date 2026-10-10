<?php
/**
 * External API - Confirm Modal
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="vm-order-modal" data-order-confirm-modal hidden>
    <div class="vm-order-modal__backdrop" data-modal-close></div>

    <section class="vm-order-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="vm-order-modal-title"
        aria-describedby="vm-order-modal-description">
        <header class="vm-order-modal__header">
            <div>
                <h2 id="vm-order-modal-title">
                    Confirm deletion
                </h2>

                <p id="vm-order-modal-description" data-modal-description></p>
            </div>

            <button type="button" data-modal-close aria-label="Close dialog">&times;</button>
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

            <p class="vm-order-modal__error" data-modal-error role="alert" hidden></p>
        </div>

        <footer class="vm-order-modal__footer">
            <button type="button" data-modal-close>
                Cancel
            </button>

            <button type="button" data-modal-confirm class="vm-order-modal__delete">
                Confirm deletion
            </button>
        </footer>
    </section>
</div>