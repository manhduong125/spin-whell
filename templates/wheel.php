<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$settings = WP_Spin_Wheel_Helper::get_wheel_settings( $post_id );
$prizes = WP_Spin_Wheel_Prize::get_prizes( $post_id );
$nonce = wp_create_nonce( 'spin_wheel_nonce' );
$entries_count = count( $prizes );
$current_title = ! empty( $settings['title'] ) ? $settings['title'] : __( 'Vòng quay may mắn', 'wp-spin-wheel' );
$description = ! empty( $settings['description'] ) ? $settings['description'] : __( 'Nhấn vào nút quay để bắt đầu.', 'wp-spin-wheel' );
?>
<div class="container-fluid noads wp-spin-wheel-wrapper" id="wheel-wrapper" data-wheel-id="<?php echo esc_attr( $post_id ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" data-wheel-settings="<?php echo esc_attr( wp_json_encode( $settings ) ); ?>" data-wheel-prizes="<?php echo esc_attr( wp_json_encode( $prizes ) ); ?>">
    <div class="row" id="row-wheel">
        <div class="col-xl-3 toggle-show-hide" id="wheel-left">
            <div class="vqmm-entry mb-4">
                <a href="javascript:void(0);" class="btn-edit toggle-show-hide" id="edit-content" aria-label="Chỉnh sửa tiêu đề và mô tả">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                </a>
                <span id="edit-mode-txt"></span>
                <div id="vqmm-content" class="text-center text-xl-start vqmm-content">
                    <div class="wp-block-heading has-x-large-font-size toggle-show-hide" id="vqmm-title"><?php echo esc_html( $current_title ); ?></div>
                    <p class="toggle-show-hide" id="vqmm-desc"><?php echo nl2br( esc_html( $description ) ); ?></p>
                </div>
            </div>
        </div>

        <div class="col-xl-6 text-center mb-3" id="wheel-center">
            <div id="wheel-container">
                <div id="wheelOfFortune">
                    <canvas id="wheel" width="700" height="700"></canvas>
                    <div id="spin"><?php esc_html_e( 'Quay', 'wp-spin-wheel' ); ?></div>
                    <div id="instruction">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 16v-4"></path><path d="M12 8h.01"></path></svg>
                    </div>
                </div>
            </div>
            <div class="my-3 d-flex justify-content-center align-content-center d-xl-none btn-actions" style="z-index: 4;" id="btn-actions">
                <div class="pt-2" title="Gọn gàng">
                    <span class="form-check form-switch d-inline-block mx-auto">
                        <input class="form-check-input" type="checkbox" value="0" id="toggleInfo">
                        <label class="form-check-label" for="toggleInfo"><span class="d-none">Gọn gàng</span></label>
                    </span>
                </div>
                <button class="btn btn-sm border-0" id="btn-embed-wheel" title="Tạo link chia sẻ và mã nhúng"><?php esc_html_e( 'Chia sẻ', 'wp-spin-wheel' ); ?></button>
                <button class="btn btn-sm border-0" id="btn-settings-wheel" title="Cài đặt"><?php esc_html_e( 'Cài đặt', 'wp-spin-wheel' ); ?></button>
                <button class="btn btn-sm border-0 d-none d-md-inline-blockx" id="btn-download" title="Tải ảnh về máy"><?php esc_html_e( 'Tải ảnh', 'wp-spin-wheel' ); ?></button>
            </div>
        </div>

        <div class="col-xl-3 toggle-show-hide" id="wheel-right">
            <div class="wrc mb-3 bg-light" id="wrc">
                <nav>
                    <div class="nav nav-tabs" id="main-tab" role="tablist">
                        <button class="nav-link w-50 active" id="tab-entries" type="button" role="tab" aria-selected="true"><?php esc_html_e( 'Mục', 'wp-spin-wheel' ); ?> <span class="badge bg-primary text-light rounded-pill" id="entries_count"><?php echo esc_html( $entries_count ); ?></span></button>
                        <button class="nav-link w-50" id="tab-result" type="button" role="tab" aria-selected="false"><?php esc_html_e( 'Kết quả', 'wp-spin-wheel' ); ?> <span class="badge bg-primary text-light rounded-pill" id="result_count">0</span></button>
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane py-3 fade show active" id="tab-content-entries" role="tabpanel">
                        <div class="d-flex flex-wrap w-100 justify-content-start align-items-center mb-3" id="top-tool">
                            <button type="button" class="btn btn-outline-secondary btn-sm me-1" id="btn-shuffle-wheel"><?php esc_html_e( 'Trộn', 'wp-spin-wheel' ); ?></button>
                            <button type="button" class="btn btn-outline-secondary btn-sm me-1 mb-1" id="btn-sort-wheel-az">⇣ AZ</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm me-1 mb-1 d-none" id="btn-sort-wheel-za">⇣ ZA</button>
                            <label class="btn btn-outline-secondary btn-sm btn-upanh me-1 mb-1" for="inputImage" title="Upload image file">
                                <input type="file" class="d-none" id="inputImage" name="file" accept=".jpg,.jpeg,.png,.gif" multiple="">
                                <?php esc_html_e( 'Thêm ảnh', 'wp-spin-wheel' ); ?>
                            </label>
                            <button type="button" class="btn btn-outline-danger btn-sm me-1 mb-1" id="btn-clear-entry"><?php esc_html_e( 'Xoá', 'wp-spin-wheel' ); ?></button>
                            <div class="form-check ms-1 me-0">
                                <label class="form-check-label" for="is_advance"><?php esc_html_e( 'Nâng cao', 'wp-spin-wheel' ); ?></label>
                                <input class="form-check-input" type="checkbox" id="is_advance" value="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <div id="sector_list" contenteditable="false" spellcheck="false" class="form-control section-list rounded-0" placeholder="Hãy thêm các 'mục' tham gia quay, mỗi 'mục' trên một dòng." style="max-height: unset; height: 486px; overflow:auto;">
                                <?php if ( $entries_count > 0 ) : ?>
                                    <?php foreach ( $prizes as $prize ) : ?>
                                        <div><?php echo esc_html( $prize['title'] ); ?></div>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <div><?php esc_html_e( 'Chưa có mục nào.', 'wp-spin-wheel' ); ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3" id="hide_ratio_option"></div>
                            <div class="d-flex flex-column advance-editor d-none" id="advance-editor"></div>
                        </div>
                        <div id="upload-excel-section">
                            <input type="hidden" id="_wpnonce" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'spin_wheel_upload' ) ); ?>">
                            <div id="upload_excel_process" class="d-none">
                                <div class="spinner-border text-primary" role="status"><span class="visually-hidden"><?php esc_html_e( 'Loading…', 'wp-spin-wheel' ); ?></span></div>
                                <?php esc_html_e( 'Vui lòng đợi…', 'wp-spin-wheel' ); ?>
                            </div>
                            <input type="file" id="upload_excel" name="upload_excel" data-maxsize="10" class="d-none" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                        </div>
                        <div class="text-start" id="bottom-tool">
                            <label class="btn btn-sm btn-secondary" for="upload_excel"><?php esc_html_e( 'Nhập Excel', 'wp-spin-wheel' ); ?></label>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-download-excel"><?php esc_html_e( 'Mẫu Excel', 'wp-spin-wheel' ); ?></button>
                        </div>
                    </div>
                    <div class="tab-pane py-3 fade" id="tab-content-result" role="tabpanel">
                        <div class="d-flex justify-content-start mb-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm me-2" id="btn-sort-result"><?php esc_html_e( 'Sắp xếp', 'wp-spin-wheel' ); ?></button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-clear-result"><?php esc_html_e( 'Xóa kết quả', 'wp-spin-wheel' ); ?></button>
                        </div>
                        <div id="wheel_result" class="form-control rounded-0 section-list" readonly="" placeholder="Kết quả quay" style="min-height:260px; overflow:auto;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
