<?php if (! defined('ABSPATH')) {
    exit;
} ?>

<div class="modal show" id="modalLogin" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" id="modal-dialog">
        <div class="modal-content" id="modal-content">
            <div class="modal-header" id="modal-header">
                <h5 class="modal-title" id="modal-title"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-log-in" style="width:24px;heigh:24px;">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <polyline points="10 17 15 12 10 7"></polyline>
                        <line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg> Đăng nhập</h5> <button type="button" id="modal-close" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3" id="modal-body">
                <p class="alert alert-warning">Để thực hiện tính năng này, bạn cần phải đăng nhập:</p>
                <p class="text-center"><button type="button"
                        onclick="location.href='https://vongquaymayman.co/wp-login.php?redirect_to=https://vongquaymayman.co/'"
                        class="btn btn-primary w-50">Đăng nhập</button> </p>
                <p class="text-center text-secondary">hoặc <a class="text-primary text-decoration-none"
                        href="https://vongquaymayman.co/wp-login.php?action=register">Đăng ký</a></p>
            </div>
            <div class="modal-footer" id="modal-footer"><button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Bỏ qua</button></div>
        </div>
    </div>
</div>