<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$default_settings = array(
    'background' => array(
        'type'  => 'color',
        'value' => '#f8fafc',
    ),
    'wheel' => array(
        'size'         => 520,
        'border'       => 12,
        'border_color' => '#ffffff',
        'shadow'       => true,
    ),
    'button' => array(
        'text'       => __( 'Quay ngay', 'wp-spin-wheel' ),
        'color'      => '#3b82f6',
        'text_color' => '#ffffff',
        'radius'     => 50,
    ),
    'pointer' => array(
        'image' => '',
        'size'  => 90,
    ),
    'font' => array(
        'family' => 'Poppins',
        'size'   => 18,
    ),
    'animation' => array(
        'duration' => 6,
        'confetti' => true,
    ),
);

$default_prizes = array(
    array( 'title' => __( 'Tuấn', 'wp-spin-wheel' ), 'color' => '#ef4444' ),
    array( 'title' => __( 'Linh', 'wp-spin-wheel' ), 'color' => '#f59e0b' ),
    array( 'title' => __( 'Trang', 'wp-spin-wheel' ), 'color' => '#10b981' ),
    array( 'title' => __( 'Nga', 'wp-spin-wheel' ), 'color' => '#3b82f6' ),
    array( 'title' => __( 'Thiện', 'wp-spin-wheel' ), 'color' => '#8b5cf6' ),
    array( 'title' => __( 'Kiên', 'wp-spin-wheel' ), 'color' => '#ec4899' ),
    array( 'title' => __( 'Hùng', 'wp-spin-wheel' ), 'color' => '#14b8a6' ),
    array( 'title' => __( 'Nam', 'wp-spin-wheel' ), 'color' => '#f97316' ),
);
$default_title = __( '', 'wp-spin-wheel' );
$default_description = __( '', 'wp-spin-wheel' );
?>
<div class="container-fluid noads wp-spin-wheel-wrapper" id="wheel-wrapper" data-wheel-id="0"
    data-wheel-settings="<?php echo esc_attr( wp_json_encode( $default_settings ) ); ?>"
    data-wheel-prizes="<?php echo esc_attr( wp_json_encode( $default_prizes ) ); ?>">
    <div class="row" id="row-wheel">

        <div class="col-xl-3 toggle-show-hide" id="wheel-left">
            <div class="vqmm-entry mb-4"> <a href="javascript:void(0);" data-bs-toggle="tooltip"
                    data-bs-title="Chỉnh sửa tiêu đề và mô tả" class="btn-edit toggle-show-hide" id="edit-content"
                    aria-label="Chỉnh sửa tiêu đề và mô tả" data-bs-original-title="Chỉnh sửa tiêu đề và mô tả"><svg
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-edit">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg></a> <span id="edit-mode-txt"></span>
                <div id="vqmm-content" class="text-center text-xl-start vqmm-content">
                    <div class="wp-block-heading has-x-large-font-size toggle-show-hide" id="vqmm-title"><?php echo esc_html( $default_title ); ?></div>
                    <p class="toggle-show-hide" id="vqmm-desc"><?php echo esc_html( $default_description ); ?></p>
                </div>
            </div>
        </div>

        <div class="col-xl-6 text-center mb-3" id="wheel-center">
            <div id="wheel-container">
                <div id="wheelOfFortune">
                    <canvas id="wheel" width="700" height="700"></canvas>
                    <div id="spin"><?php esc_html_e( 'Quay', 'wp-spin-wheel' ); ?></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3" id="wheel-right">
            <div class="wrc mb-3 bg-light" id="wrc">
                <nav>
                    <div class="nav nav-tabs" id="main-tab" role="tablist">
                        <button class="nav-link w-50 active" id="tab-entries" data-bs-toggle="tab"
                            data-bs-target="#tab-content-entries" type="button" role="tab"
                            aria-controls="tab-content-entries"
                            aria-selected="true"><?php esc_html_e( 'Mục', 'wp-spin-wheel' ); ?> <span
                                class="badge bg-primary text-light rounded-pill"
                                id="entries_count"><?php echo esc_html( count( $default_prizes ) ); ?></span></button>
                        <button class="nav-link w-50" id="tab-result" data-bs-toggle="tab"
                            data-bs-target="#tab-content-result" type="button" role="tab"
                            aria-controls="tab-content-result"
                            aria-selected="false"><?php esc_html_e( 'Kết quả', 'wp-spin-wheel' ); ?> <span
                                class="badge bg-primary text-light rounded-pill" id="result_count">0</span></button>
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane py-3 fade show active" id="tab-content-entries" role="tabpanel"
                        aria-labelledby="tab-entries">
                        <div class="d-flex flex-wrap w-100 justify-content-start align-items-center mb-3" id="top-tool">
                            <button type="button" class="btn btn-outline-secondary btn-sm me-1" id="btn-shuffle-wheel">
                                <img decoding="async"
                                    src="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/icons/shuffle.png"
                                    class="custom-icon" width="20" height="20" alt="shuffle" loading="lazy">
                                Trộn
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm me-1 mb-1"
                                id="btn-sort-wheel-az">⇣ AZ</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm me-1 mb-1 d-none"
                                id="btn-sort-wheel-za">⇣ ZA</button>
                            <button type="button" class="btn btn-outline-danger btn-sm me-1 mb-1" id="btn-clear-entry">
                                <span data-feather="x" style="width:20px;height:20px;"></span>
                                Xoá
                            </button>
                            <div class="form-check ms-1 me-0" style="display: none;">
                                <label class="form-check-label" for="is_advance">Nâng cao</label>
                                <input class="form-check-input" type="checkbox" role="checkbox" id="is_advance"
                                    value="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="input-group input-group-sm mb-2">
                                <input type="text" id="new_prize_title" class="form-control"
                                    placeholder="<?php echo esc_attr__( 'Nhập mục mới', 'wp-spin-wheel' ); ?>">
                                <input type="color" id="new_prize_color" class="form-control form-control-color"
                                    value="#10b981"
                                    title="<?php echo esc_attr__( 'Màu phần thưởng', 'wp-spin-wheel' ); ?>">
                                <button class="btn btn-primary" type="button"
                                    id="btn-add-prize"><?php esc_html_e( 'Thêm', 'wp-spin-wheel' ); ?></button>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                id="btn-restore-defaults"><?php esc_html_e( 'Khôi phục mặc định', 'wp-spin-wheel' ); ?></button>
                        </div>
                        <div class="mb-3">
                            <div id="sector_list" class="form-control section-list rounded-0" readonly
                                placeholder="<?php esc_attr_e( 'Danh sách phần thưởng', 'wp-spin-wheel' ); ?>">
                                <?php foreach ( $default_prizes as $prize ) : ?>
                                <div><?php echo esc_html( $prize['title'] ); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane py-3 fade" id="tab-content-result" role="tabpanel"
                        aria-labelledby="tab-result">
                        <div class="d-flex justify-content-start mb-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm me-2"
                                id="btn-sort-result"><?php esc_html_e( 'Sắp xếp', 'wp-spin-wheel' ); ?></button>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                id="btn-clear-result"><?php esc_html_e( 'Xóa kết quả', 'wp-spin-wheel' ); ?></button>
                        </div>
                        <div id="wheel_result" class="form-control rounded-0 section-list" readonly
                            placeholder="<?php esc_attr_e( 'Kết quả quay', 'wp-spin-wheel' ); ?>"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="myModal" tabindex="-1" style="display: none;" aria-modal="true" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" id="modal-dialog">
        <div class="modal-content" id="modal-content">
            <div class="modal-header" id="modal-header">
                <h5 class="modal-title" id="modal-title"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-edit"
                        style="width: 30px; height: 30px; vertical-align: middle;">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg> Sửa tiêu đề và mô tả</h5> <button type="button" id="modal-close" class="btn-close"
                    data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3" id="modal-body">
                <div class="mb-2"><label for="editTitle" class="form-label">Tiêu đề</label><input type="text"
                        class="form-control" value="<?php echo esc_attr( $default_title ); ?>" data-maxlength="60" id="editTitle"
                        placeholder="Tiêu đề vòng quay"></div>
                <div class="mb-3"><label for="editDesc" class="form-label">Mô tả</label><textarea class="form-control"
                        id="editDesc" placeholder="Mô tả vòng quay" data-maxlength="160" rows="3"><?php echo esc_textarea( $default_description ); ?></textarea></div>
            </div>
            <div class="modal-footer" id="modal-footer"><button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Đóng</button><button type="button" id="saveTitleDesc"
                    onclick="saveTitleDesc()" class="btn btn-primary" data-bs-dismiss="modal">Lưu lại</button></div>
        </div>
    </div>
</div>