<?php if (! defined('ABSPATH')) {
    exit;
} ?>
<!-- Popup kết quả quay -->
<div class="modal" id="modal-result" tabindex="-1" style="display: none;" aria-modal="true" aria-hidden="true"
    role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="d-flex align-items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-clipboard me-1"
                            style="width:28px;height:28px;vertical-align:middle;">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        </svg>
                        <span
                            id="modal-result-popup-label"><?php esc_html_e('Bạn đã quay vào ô', 'wp-spin-wheel'); ?></span>
                    </span>
                </h5>
                <button type="button" class="btn-close" id="modal-result-close" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4 text-center">
                <div class="tick-btn active mb-3">
                    <svg viewBox="0 0 64 64" width="64" height="64">
                        <circle class="circ" cx="32" cy="32" r="28" style="stroke:#4f9a29;"></circle>
                        <path class="chk" d="M20 33 L28 41 L44 24" fill="none" stroke-linecap="round"
                            stroke-linejoin="round" style="stroke:#4f9a29;"></path>
                    </svg>
                </div>
                <div class="fs-2 fw-bold" id="modal-result-title" style="color:#4f9a29;word-break:break-word;"></div>
                <div class="mt-2 text-muted" id="modal-result-desc"></div>
            </div>
            <div class="modal-footer justify-content-center gap-2">
                <button type="button" class="btn btn-secondary" id="modal-result-close-btn">
                    <?php esc_html_e('Đóng lại', 'wp-spin-wheel'); ?>
                </button>
                <button type="button" class="btn btn-outline-danger" id="btn-remove-result-item" style="display:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        style="vertical-align:middle;margin-right:3px;">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6l-1 14H6L5 6"></path>
                        <path d="M10 11v6"></path>
                        <path d="M14 11v6"></path>
                        <path d="M9 6V4h6v2"></path>
                    </svg>
                    <?php esc_html_e('Xóa ô này', 'wp-spin-wheel'); ?>
                </button>
            </div>
        </div>
    </div>
</div>