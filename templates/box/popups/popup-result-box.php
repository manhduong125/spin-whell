<?php if (! defined('ABSPATH')) {
    exit;
} ?>

<!-- 1. POPUP TRÚNG THƯỞNG KHI MỞ TỪNG HỘP QUÀ -->
<div class="modal fade" id="modalBoxResult" tabindex="-1" aria-labelledby="modalBoxResultLabel" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
            <div class="modal-header border-bottom-0 pb-0 justify-content-between">
                <h5 class="modal-title d-flex align-items-center gap-2" id="modalBoxResultLabel">
                    <span class="fs-4">🎁</span>
                    <span id="modal-box-result-label" class="fw-bold text-danger"><?php esc_html_e('Hộp quà có', 'wp-spin-wheel'); ?></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4 text-center">
                <div class="mb-3 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); box-shadow: 0 8px 20px rgba(220, 53, 69, 0.2);">
                    <span style="font-size: 40px; animation: pulse-gift 1s infinite alternate;">🎉</span>
                </div>

                <div class="fs-1 fw-bold text-danger mb-2 px-3" id="modal-box-result-title" style="word-break: break-word; text-shadow: 0 2px 8px rgba(220,53,69,0.15);">
                    100k
                </div>

                <div class="badge bg-secondary-subtle text-secondary-emphasis px-3 py-2 rounded-pill fs-6 fw-semibold" id="modal-box-result-turns">
                    <?php esc_html_e('Bạn còn 2 lượt mở', 'wp-spin-wheel'); ?>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 justify-content-center gap-2 pb-4">
                <button type="button" class="btn btn-primary px-4 py-2 rounded-pill fw-bold" id="btn-box-continue" data-bs-dismiss="modal">
                    <?php esc_html_e('Mở tiếp', 'wp-spin-wheel'); ?> ➔
                </button>
                <button type="button" class="btn btn-outline-danger px-4 py-2 rounded-pill fw-bold" id="btn-box-view-all">
                    <?php esc_html_e('Xem tất cả kết quả & Nhận quà', 'wp-spin-wheel'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 2. POPUP BẢNG TỔNG KẾT KẾT QUẢ CHI TIẾT -->
<div class="modal fade" id="modalKetqua" tabindex="-1" aria-labelledby="modalKetquaLabel" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg border-0 rounded-4">
            <div class="modal-header bg-danger text-white rounded-top-4">
                <h5 class="modal-title d-flex align-items-center gap-2 text-white" id="modalKetquaLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-gift"><polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path></svg>
                    <span><?php esc_html_e('KẾT QUẢ MỞ HỘP QUÀ', 'wp-spin-wheel'); ?></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
                    <span class="fs-5">👉</span>
                    <div><?php esc_html_e('Dưới đây là danh sách các món quà bạn đã mở trúng. Hãy chọn món quà bạn thích nhất và ấn nút', 'wp-spin-wheel'); ?> <strong>"<?php esc_html_e('Nhận quà', 'wp-spin-wheel'); ?>"</strong>.</div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle text-center mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 90px;"><?php esc_html_e('Lần mở', 'wp-spin-wheel'); ?></th>
                                <th class="text-start"><?php esc_html_e('Phần thưởng', 'wp-spin-wheel'); ?></th>
                                <th style="width: 140px;"><?php esc_html_e('Thao tác', 'wp-spin-wheel'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="sw-box-results-tbody">
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4"><?php esc_html_e('Chưa có lượt mở hộp quà nào.', 'wp-spin-wheel'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top-0 justify-content-between px-4 pb-4">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" id="btn-modal-reload-box"><?php esc_html_e('🔄 Chơi lại lượt mới', 'wp-spin-wheel'); ?></button>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal"><?php esc_html_e('Đóng lại', 'wp-spin-wheel'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- 3. POPUP XÁC NHẬN NHẬN QUÀ CHI TIẾT -->
<div class="modal fade" id="modalBoxClaim" tabindex="-1" aria-labelledby="modalBoxClaimLabel" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4">
            <div class="modal-header border-bottom-0 pb-0 justify-content-between">
                <h5 class="modal-title fw-bold text-success d-flex align-items-center gap-2" id="modalBoxClaimLabel">
                    <span>🎉</span> <?php esc_html_e('NHẬN QUÀ THÀNH CÔNG', 'wp-spin-wheel'); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4 text-center">
                <p class="text-muted mb-2"><?php esc_html_e('Chúc mừng bạn đã chọn nhận phần quà:', 'wp-spin-wheel'); ?></p>
                <div class="fs-2 fw-bold text-danger mb-3" id="modal-box-claim-gift-name">100k</div>

                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="small text-muted mb-1"><?php esc_html_e('Mã nhận thưởng của bạn:', 'wp-spin-wheel'); ?></div>
                    <code class="fs-4 fw-bold text-primary" id="modal-box-claim-code">HQ-889922</code>
                </div>

                <p class="small text-muted mb-0"><?php esc_html_e('Vui lòng chụp lại màn hình hoặc lưu mã này để liên hệ ban tổ chức nhận quà.', 'wp-spin-wheel'); ?></p>
            </div>
            <div class="modal-footer border-top-0 pt-0 justify-content-center pb-4">
                <button type="button" class="btn btn-success px-4 py-2 rounded-pill fw-bold" data-bs-dismiss="modal"><?php esc_html_e('Tuyệt vời, tôi đã hiểu!', 'wp-spin-wheel'); ?></button>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes pulse-gift {
    0% { transform: scale(0.92); }
    100% { transform: scale(1.1); }
}
</style>
