<?php if (! defined('ABSPATH')) {
    exit;
}
$login_url = wp_login_url( home_url( $_SERVER['REQUEST_URI'] ?? '/' ) );
$register_url = wp_registration_url();
?>
<div class="modal" id="modalNoneUser" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" id="modal-dialog">
        <div class="modal-content" id="modal-content">
            <div class="modal-header" id="modal-header">
                <h5 class="modal-title" id="modal-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-in" style="vertical-align:middle;margin-right:4px;">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <polyline points="10 17 15 12 10 7"></polyline>
                        <line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg> Yêu cầu đăng nhập
                </h5>
                <button type="button" id="modal-close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4 text-center" id="modal-body">
                <p class="alert alert-warning mb-4">Để thực hiện tính năng này, bạn cần phải đăng nhập tài khoản:</p>
                <div class="mb-3">
                    <a href="<?php echo esc_url( $login_url ); ?>" class="btn btn-primary w-50 py-2 fw-bold">Đăng nhập</a>
                </div>
                <p class="text-secondary mb-0">hoặc <a class="text-primary text-decoration-none fw-semibold" href="<?php echo esc_url( $register_url ); ?>">Đăng ký tài khoản</a></p>
            </div>
            <div class="modal-footer" id="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Bỏ qua</button>
            </div>
        </div>
    </div>
</div>