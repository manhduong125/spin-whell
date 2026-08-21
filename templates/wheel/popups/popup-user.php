<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<!-- Modal Danh sách vòng quay của User -->
<div class="modal fade" id="modalUserWheels" tabindex="-1" aria-modal="true" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                    Danh sách vòng quay của tôi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">Quản lý và chuyển đổi nhanh giữa các vòng quay bạn đã tạo:</span>
                    <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1"
                        id="btn-create-new-user-wheel">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Tạo vòng quay mới
                    </button>
                </div>
                <div id="user-wheels-container">
                    <div class="text-center py-4 text-muted" id="user-wheels-loading">
                        <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                        <div>Đang tải danh sách vòng quay...</div>
                    </div>
                    <div class="table-responsive d-none" id="user-wheels-table-wrap">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên vòng quay</th>
                                    <th class="text-center">Số giải</th>
                                    <th class="text-center">Ngày tạo</th>
                                    <th class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="user-wheels-list-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>