<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<!-- Sửa tiêu đề và mô tả -->
<div class="modal" id="modal-edit" tabindex="-1" style="display: none;" aria-modal="true" aria-hidden="true"
    role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" id="modal-dialog">
        <div class="modal-content" id="modal-content">
            <div class="modal-header" id="modal-header">
                <h5 class="modal-title" id="modal-title"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-edit"
                        style="width: 30px; height: 30px; vertical-align: middle;">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg> Sửa tiêu đề và mô tả</h5>
                <button type="button" id="modal-edit-close" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div class="mb-2"><label for="editTitle" class="form-label">Tiêu đề</label><input type="text"
                        class="form-control" value="<?php echo esc_attr($default_title); ?>" data-maxlength="60"
                        id="editTitle" placeholder="Tiêu đề vòng quay"></div>
                <div class="mb-3"><label for="editDesc" class="form-label">Mô tả</label><textarea class="form-control"
                        id="editDesc" placeholder="Mô tả vòng quay" data-maxlength="160"
                        rows="3"><?php echo esc_textarea($default_description); ?></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="modal-edit-cancel">Đóng</button>
                <button type="button" id="saveTitleDesc" class="btn btn-primary">Lưu lại</button>
            </div>
        </div>
    </div>
</div>
