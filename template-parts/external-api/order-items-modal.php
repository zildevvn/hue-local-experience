<?php
/**
 * External API - Order Items Details Modal
 */

if (!defined('ABSPATH')) {
    exit;
}

$can_delete = current_user_can('manage_options');
?>

<div class="vm-order-modal vm-order-items-modal" data-order-items-modal
    data-can-delete="<?php echo $can_delete ? 'true' : 'false'; ?>" hidden>
    <div class="vm-order-modal__backdrop" data-items-modal-close></div>

    <section class="vm-order-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="vm-order-items-modal-title"
        aria-describedby="vm-order-items-modal-description">
        <header class="vm-order-modal__header">
            <div>
                <h2 id="vm-order-items-modal-title" data-items-modal-title>
                    Chi tiết món ăn
                </h2>

                <p id="vm-order-items-modal-description" data-items-modal-subtitle></p>
            </div>

            <button type="button" class="vm-order-modal__close-btn" data-items-modal-close aria-label="Đóng cửa sổ" title="Đóng cửa sổ">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </header>

        <div class="vm-order-modal__body">
            <div class="vm-order-modal__summary vm-order-items-modal__summary">
                <div class="vm-order-items-modal__stat">
                    <span>Tổng số món:</span>
                    <strong data-items-modal-total>0</strong>
                </div>

                <?php if ($can_delete): ?>
                    <div class="vm-order-items-modal__stat" data-items-stat-selected>
                        <span>Đang chọn:</span>
                        <strong data-items-modal-selected>0</strong>
                    </div>
                <?php endif; ?>

                <div class="vm-order-items-modal__stat">
                    <span>Tổng tiền đơn:</span>
                    <strong data-items-modal-total-price>0</strong>
                </div>

                <div class="vm-order-items-modal__stat vm-order-items-modal__stat--deleted">
                    <span>Tổng tiền món đã xóa (tất cả order):</span>
                    <strong data-items-modal-deleted-amount>0 ₫</strong>
                </div>

                <div class="vm-order-items-modal__stat vm-order-items-modal__stat--rate">
                    <span data-items-modal-deleted-rate-label>Tỷ lệ tiền món đã xóa:</span>
                    <strong data-items-modal-deleted-rate>—</strong>
                </div>

                <div class="vm-order-items-modal__stat vm-order-items-modal__stat--order-deleted">
                    <span>Số tiền xóa của bàn đang chọn:</span>
                    <strong data-items-modal-order-deleted-amount>0 ₫</strong>
                </div>
            </div>

            <div class="vm-items-modal__state" data-items-modal-loading hidden>
                <div class="vm-api-spinner"></div>
                <span>Đang tải danh sách món ăn...</span>
            </div>

            <p class="vm-order-modal__notice" data-items-modal-notice role="status" hidden></p>
            <p class="vm-order-modal__error" data-items-modal-error role="alert" hidden></p>

            <div class="vm-items-modal__empty" data-items-modal-empty hidden>
                Đơn hàng này hiện không có món nào.
            </div>

            <div class="vm-order-modal__table-wrap" data-items-modal-table-wrap>
                <table class="vm-order-modal__table vm-items-table">
                    <thead>
                        <tr>
                            <?php if ($can_delete): ?>
                                <th style="width: 44px; text-align: center;" data-items-select-col>
                                    <input type="checkbox" data-items-select-all aria-label="Chọn tất cả món">
                                </th>
                            <?php endif; ?>
                            <th style="width: 70px; text-align: center;">Item ID</th>
                            <th>Món ăn</th>
                            <th>Loại</th>
                            <th style="text-align: center;">SL</th>
                            <th style="text-align: right;">Đơn giá</th>
                            <th style="text-align: right;">Giảm giá</th>
                            <th style="text-align: right;">Thành tiền</th>
                            <th>Ghi chú</th>

                        </tr>
                    </thead>

                    <tbody data-items-modal-tbody></tbody>
                </table>
            </div>
        </div>

        <footer class="vm-order-modal__footer">
            <button type="button" data-items-modal-close>
                Đóng
            </button>

            <?php if ($can_delete): ?>
                <button type="button" data-items-modal-delete class="vm-order-modal__delete" disabled>
                    Xóa món đã chọn (<span data-items-modal-delete-count>0</span>)
                </button>
            <?php endif; ?>
        </footer>
    </section>
</div>