<?php if (! defined('ABSPATH')) {
    exit;
} ?>

<div class="modal" id="modalKetqua" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lgx modal-dialog-centered" id="modal-dialog">
        <div class="modal-content" id="modal-content">
            <div class="modal-header" id="modal-header">
                <h5 class="modal-title" id="modal-title"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-gift" style="width: 24px;height: 24px;">
                        <polyline points="20 12 20 22 4 22 4 12"></polyline>
                        <rect x="2" y="7" width="20" height="5"></rect>
                        <line x1="12" y1="22" x2="12" y2="7"></line>
                        <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                        <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                    </svg> KẾT QUẢ CHI TIẾT</h5>
                <button type="button" class="btn-close" id="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-body">
                <p class="alert alert-warning">Hãy chọn một món quà mà bạn muốn và ấn nút nhận quà.</p>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Lần mở</th>
                            <th>Quà tặng</th>
                            <th style="width: 120px;" class="text-center">Nhận quà</th>
                        </tr>
                    </thead>
                    <tbody id="sw-box-results-tbody">
                        <tr>
                            <td colspan="3" class="text-center text-muted">Chưa có lượt mở hộp quà nào.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer" id="modal-footer"><button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Đóng lại</button></div>
        </div>
    </div>
</div>