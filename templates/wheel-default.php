<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Lấy option nhạc từ global settings và danh sách audio items
$_global_opts     = WP_Spin_Wheel_Helper::get_global_settings();
$_start_sound     = $_global_opts['start_sound'] ?? '';
$_start_sound_file = $_global_opts['start_sound_file'] ?? '';
$_end_sound       = $_global_opts['end_sound'] ?? '';
$_end_sound_file  = $_global_opts['end_sound_file'] ?? '';
$_audios_start    = WP_Spin_Wheel_Helper::get_setting_items( 'audios_start' );
$_audios_end      = WP_Spin_Wheel_Helper::get_setting_items( 'audios_end' );

$default_settings = array(
    'background' => array(
        'type'  => 'color',
        'value' => '',
    ),
    'wheel' => array(
        'size'         => 600,
        'border'       => 12,
        'border_color' => '#ffffff',
        'shadow'       => true,
    ),
    'button' => array(
        'text'       => 'Quay',
        'color'      => '#000',
        'text_color' => '',
        'radius'     => 100,
    ),
    'animation' => array(
        'duration' => 6,
        'confetti' => false,
    ),
    'audio' => array(
        'start_sound'      => $_start_sound,
        'start_sound_file' => $_start_sound_file,
        'end_sound'        => $_end_sound,
        'end_sound_file'   => $_end_sound_file,
    ),
);

$default_prizes = array();
$default_title = '';
$default_description = '';
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
                    <div class="spin-heading toggle-show-hide" id="vqmm-title"><?php echo esc_html( $default_title ); ?></div>
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
            <div class="mt-2">
                <button type="button" class="btn btn-outline-secondary btn-sm"
                    data-bs-toggle="modal" data-bs-target="#modalSettings"
                    aria-label="Cài đặt vòng quay">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" style="vertical-align:middle;">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    Cài đặt
                </button>
            </div>
        </div>

        <div class="col-xl-3" id="wheel-right">
            <div class="wrc bg-light" id="wrc">
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
                            <button type="button" class="btn btn-outline-secondary btn-sm me-1"
                                id="btn-sort-wheel-az">⇣ AZ</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm me-1 d-none"
                                id="btn-sort-wheel-za">⇣ ZA</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                id="btn-restore-defaults"><?php esc_html_e( 'Khôi phục', 'wp-spin-wheel' ); ?></button>
                            <button type="button" class="btn btn-outline-danger btn-sm me-1" id="btn-clear-entry">
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

<div class="modal" id="modal-edit" tabindex="-1" style="display: none;" aria-modal="true" aria-hidden="true" role="dialog">
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
                        class="form-control" value="<?php echo esc_attr( $default_title ); ?>" data-maxlength="60" id="editTitle"
                        placeholder="Tiêu đề vòng quay"></div>
                <div class="mb-3"><label for="editDesc" class="form-label">Mô tả</label><textarea class="form-control"
                        id="editDesc" placeholder="Mô tả vòng quay" data-maxlength="160" rows="3"><?php echo esc_textarea( $default_description ); ?></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="modal-edit-cancel">Đóng</button>
                <button type="button" id="saveTitleDesc" class="btn btn-primary">Lưu lại</button>
            </div>
        </div>
    </div>
</div>

<!-- Popup kết quả quay -->
<div class="modal" id="modal-result" tabindex="-1" style="display: none;" aria-modal="true" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="d-flex align-items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-clipboard me-1" style="width:28px;height:28px;vertical-align:middle;">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        </svg>
                        <span id="modal-result-popup-label"><?php esc_html_e( 'Bạn đã quay vào ô', 'wp-spin-wheel' ); ?></span>
                    </span>
                </h5>
                <button type="button" class="btn-close" id="modal-result-close" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4 text-center">
                <div class="tick-btn active mb-3">
                    <svg viewBox="0 0 64 64" width="64" height="64">
                        <circle class="circ" cx="32" cy="32" r="28" style="stroke:#4f9a29;"></circle>
                        <path class="chk" d="M20 33 L28 41 L44 24" fill="none" stroke-linecap="round" stroke-linejoin="round" style="stroke:#4f9a29;"></path>
                    </svg>
                </div>
                <div class="fs-1 fw-bold" id="modal-result-title" style="color:#4f9a29;word-break:break-word;"></div>
                <div class="mt-2 text-muted" id="modal-result-desc"></div>
            </div>
            <div class="modal-footer justify-content-center gap-2">
                <button type="button" class="btn btn-secondary" id="modal-result-close-btn">
                    <?php esc_html_e( 'Đóng lại', 'wp-spin-wheel' ); ?>
                </button>
                <button type="button" class="btn btn-outline-danger" id="btn-remove-result-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round"
                        style="vertical-align:middle;margin-right:3px;">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6l-1 14H6L5 6"></path>
                        <path d="M10 11v6"></path>
                        <path d="M14 11v6"></path>
                        <path d="M9 6V4h6v2"></path>
                    </svg>
                    <?php esc_html_e( 'Xóa ô này', 'wp-spin-wheel' ); ?>
                </button>
            </div>
        </div>
    </div>
</div>


<!-- Option cho theme -->
<div class="modal" id="modalSettings" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><span data-feather="settings"></span> Cài đặt</h5> <button
                type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body py-3">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation"> <button class="nav-link active"
                        id="gen-setting-tab" data-bs-toggle="tab" data-bs-target="#gen-setting-tab-pane"
                        type="button" role="tab" aria-controls="gen-setting-tab-pane"
                        aria-selected="true">Chung</button> </li>
                <li class="nav-item" role="presentation"> <button class="nav-link" id="appearance-tab"
                        data-bs-toggle="tab" data-bs-target="#appearance-tab-pane" type="button"
                        role="tab" aria-controls="appearance-tab-pane" aria-selected="false">Giao
                        diện</button> </li>
                <li class="nav-item" role="presentation"> <button class="nav-link" id="media-tab"
                        data-bs-toggle="tab" data-bs-target="#media-tab-pane" type="button" role="tab"
                        aria-controls="media-tab-pane" aria-selected="false">Thư viện</button> </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade pt-3 show active" id="gen-setting-tab-pane" role="tabpanel"
                    aria-labelledby="gen-setting-tab" tabindex="0">
                    <!-- audio player ẩn dùng chung cho preview -->
                    <audio id="sw-audio-preview" preload="none" style="display:none;"></audio>

                    <!-- ♪ Nhạc bắt đầu -->
                    <div class="mb-2">
                        <div class="input-group mb-1">
                            <span class="input-group-text">♪ Bắt đầu</span>
                            <select class="form-select" id="start_sound">
                                <!-- dữ liệu nhạc bắt đầu -->
                                <option value="0" <?php selected( $_start_sound, '0' ); selected( $_start_sound, '' ); ?>>Tắt tiếng</option>
                                <?php if ( ! empty( $_audios_start ) ) : ?>
                                <option value="random" <?php selected( $_start_sound, 'random' ); ?>>Ngẫu nhiên</option>
                                <?php endif; ?>
                                <?php if ( ! empty( $_audios_start ) ) : ?>
                                <optgroup label="── Thư viện nhạc ──">
                                    <?php foreach ( $_audios_start as $_as ) :
                                        $_as_id  = esc_attr( $_as['id'] ?? '' );
                                        $_as_url = esc_attr( $_as['config']['file'] ?? '' );
                                        $_as_lbl = esc_html( $_as['name'] ?? '' );
                                    ?>
                                    <option value="<?php echo $_as_id; ?>" data-url="<?php echo $_as_url; ?>" <?php selected( $_start_sound, $_as_id ); ?>><?php echo $_as_lbl; ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php endif; ?>
                            </select>
                            <button type="button" class="btn btn-outline-secondary sw-btn-preview"
                                data-target="start_sound"
                                data-audios="<?php echo esc_attr( wp_json_encode( array_values( array_map( function( $a ) { return [ 'id' => $a['id'] ?? '', 'url' => $a['config']['file'] ?? '' ]; }, $_audios_start ) ) ) ); ?>"
                                title="Nghe thử">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-play"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                            </button>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">
                                <a class="text-decoration-none" target="_blank" href="/huong-dan-lay-file_id-tren-nhactik-com/">♪ nhactik.com</a>
                            </span>
                            <input type="text" class="form-control" id="start_sound_file"
                                placeholder="File ID" value="<?php echo esc_attr( $_start_sound_file ); ?>">
                            <button type="button" class="btn btn-outline-secondary" id="btn-start-sound-play-file" title="Nghe thử nhactik">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-play"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                            </button>
                        </div>
                    </div>

                    <!-- ♪ Nhạc kết thúc -->
                    <div class="mb-2">
                        <div class="input-group mb-1">
                            <span class="input-group-text">♪ Kết thúc</span>
                            <select class="form-select" id="end_sound">
                                <!-- Dữ liệu nhạc kết thúc -->
                                <option value="0" <?php selected( $_end_sound, '0' ); selected( $_end_sound, '' ); ?>>Tắt tiếng</option>
                                <?php if ( ! empty( $_audios_end ) ) : ?>
                                <option value="random" <?php selected( $_end_sound, 'random' ); ?>>Ngẫu nhiên</option>
                                <?php endif; ?>
                                <option value="read" <?php selected( $_end_sound, 'read' ); ?>>Đọc kết quả</option>
                                <option value="slot_end" <?php selected( $_end_sound, 'slot_end' ); ?>>Slot end</option>
                                <?php if ( ! empty( $_audios_end ) ) : ?>
                                <optgroup label="── Thư viện nhạc ──">
                                    <?php foreach ( $_audios_end as $_ae ) :
                                        $_ae_id  = esc_attr( $_ae['id'] ?? '' );
                                        $_ae_url = esc_attr( $_ae['config']['file'] ?? '' );
                                        $_ae_lbl = esc_html( $_ae['name'] ?? '' );
                                    ?>
                                    <option value="<?php echo $_ae_id; ?>" data-url="<?php echo $_ae_url; ?>" <?php selected( $_end_sound, $_ae_id ); ?>><?php echo $_ae_lbl; ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php endif; ?>
                            </select>
                            <button type="button" class="btn btn-outline-secondary sw-btn-preview"
                                data-target="end_sound"
                                data-audios="<?php echo esc_attr( wp_json_encode( array_values( array_map( function( $a ) { return [ 'id' => $a['id'] ?? '', 'url' => $a['config']['file'] ?? '' ]; }, $_audios_end ) ) ) ); ?>"
                                title="Nghe thử">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-play"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                            </button>
                        </div>
                        <div class="input-group mb-3">
                            <span class="input-group-text">
                                <a class="text-decoration-none" target="_blank" href="/huong-dan-lay-file_id-tren-nhactik-com/">♪ nhactik.com</a>
                            </span>
                            <input type="text" class="form-control" id="end_sound_file"
                                value="<?php echo esc_attr( $_end_sound_file ); ?>" placeholder="File ID">
                            <button type="button" class="btn btn-outline-secondary" id="btn-end-sound-play-file" title="Nghe thử nhactik">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-play"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                            </button>
                        </div>
                    </div>
                    <div class="input-group mb-3"> <span class="input-group-text">Tốc độ quay</span>
                        <select class="form-select" id="duration">
                            <?php
                            $_dur = $default_settings['animation']['duration'];
                            $duration_options = array(
                                4  => 'Nhanh',
                                6  => 'Tiêu chuẩn',
                                8  => 'Chậm hơn',
                                12 => 'Chậm hơn nữa',
                            );
                            foreach ( $duration_options as $_sec => $_label ) :
                            ?>
                            <option value="<?php echo esc_attr( $_sec ); ?>" <?php selected( (int) $_dur, $_sec ); ?>><?php echo esc_html( $_label ); ?></option>
                            <?php endforeach; ?>
                        </select> </div>
                    <div class="form-check mb-3"> <input class="form-check-input" type="checkbox"
                            id="show_confetti" checked> <label class="form-check-label"
                            for="show_confetti"> Bắn hoa giấy khi kết thúc </label> </div>
                    <div class="form-check mb-3"> <input class="form-check-input" type="checkbox"
                            id="auto_remove"> <label class="form-check-label" for="auto_remove"> Tự động
                            xóa kết quả sau 5 giây </label> </div>
                    <div class="form-check mb-3"> <input class="form-check-input" type="checkbox"
                            id="show_popup" checked> <label class="form-check-label" for="show_popup">
                            Popup kết quả với tiêu đề: </label> </div>
                    <div class="mb-3"> <input type="text" class="form-control ms-3"
                            style="width: calc(100% - 1rem);" id="popup_label" value="Bạn đã quay vào ô"
                            placeholder="Bạn đã quay vào ô"> </div>
                            <div class="form-check ms-3 mb-3"> <input class="form-check-input" type="checkbox" id="show_remove_button" checked=""> <label class="form-check-label" for="show_remove_button"> Hiển thị nút “Xóa ô này” </label> </div>
                </div>
                <div class="tab-pane fade pt-1" id="appearance-tab-pane" role="tabpanel"
                    aria-labelledby="appearance-tab" tabindex="0">
                    <fieldset class="border border-2 px-2">
                        <legend class="float-none w-auto p-2 fs-6 fw-bold">Vòng quay <span
                                data-feather="chevron-down"></span></legend>
                        <div class="mb-3" id="custom-basic-style"> <input type="hidden" id="type"
                                value="color">
                            <div class="row justify-content-center align-items-center mb-3">
                                <div class="col-5 text-end"> <button class="btn mb-1"
                                        id="btn_color_wheel"><img decoding="async"
                                            src="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/images/color-wheel.png"
                                            width="36" alt="Color wheel"></button> <label
                                        for="btn_color_wheel">Màu cho mỗi phần</label> </div>
                                <div class="col-2 text-center">
                                    <div class="form-check form-switch p-0 d-inline-block mx-auto">
                                        <input class="form-check-input ms-0"
                                            style="background-color: #0d6efd;border-color: #0d6efd;background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'-4 -4 8 8\'%3e%3ccircle r=\'3\' fill=\'%23fff\'/%3e%3c/svg%3e');"
                                            type="checkbox" role="switch" id="switch_cover_img"> </div>
                                </div>
                                <div class="col-5 text-start"> <button class="btn mb-1"
                                        id="btn_cover_wheel"><img decoding="async"
                                            src="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/images/cover-wheel.jpg"
                                            width="36" class="rounded-pill" alt="Cover wheel"></button>
                                    <label for="btn_cover_wheel">Ảnh nền vòng quay</label> </div>
                            </div>
                            <div class="mb-3 d-none" id="form_cover_img">
                                <div class="input-group justify-content-center mb-3"> <input type="text"
                                        class="form-control" id="cover_img"
                                        value="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/images/cover-wheel.jpg"
                                        placeholder="https://example.com/cover800.jpg"> <span
                                        class="input-group-text"> <img decoding="async"
                                            src="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/images/cover-wheel.jpg"
                                            width="36" height="36" id="cover_img_display"
                                            class="rounded-pill" alt="default cover image"> </span>
                                    <button class="btn btn-secondary" id="btn-select-cover-img">Chọn
                                        <span data-feather="chevron-down"></span></button> <span
                                        class="input-group-text"> <label for="upload_cover_img"
                                            id="btn_upload_cover_img" data-bs-toggle="tooltip"
                                            title="Kích thước khuyên dùng: 800 x 800 (px)"><span
                                                data-feather="image"></span></label> </span> <input
                                        type="file" id="upload_cover_img" data-maxsize="2"
                                        class="d-none" accept="image/*"> </div>
                            </div>
                            <div id="form_theme_color">
                                <div class="dropdown mb-3" id="myDropdown"> <button type="button"
                                        class="btn btn-primary dropdown-toggle" id="btn-mydropdown"
                                        data-bs-toggle="dropdown" aria-expanded="false"
                                        data-bs-auto-close="outside"> Áp dụng một chủ đề </button>
                                    <div class="dropdown-menu">
                                        <div class="item-header border-top border-bottom pt-3 pb-2 m-2">
                                            Mặc định</div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#D6392E,#3369E8,#4F9A29,#EEB331"
                                            data-border="#FF4D00,#F6FA00" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="conic-gradient(from 90deg, rgb(223, 48, 0) 0deg, rgb(223, 48, 0) 27.692deg, rgb(254, 96, 0) 27.692deg, rgb(254, 96, 0) 55.385deg, rgb(255, 145, 37) 55.385deg, rgb(255, 145, 37) 83.077deg, rgb(251, 187, 95) 83.077deg, rgb(251, 187, 95) 110.769deg, rgb(218, 217, 154) 110.769deg, rgb(218, 217, 154) 138.462deg, rgb(169, 230, 202) 138.462deg, rgb(169, 230, 202) 166.154deg, rgb(114, 224, 232) 166.154deg, rgb(114, 224, 232) 193.846deg, rgb(62, 201, 236) 193.846deg, rgb(62, 201, 236) 221.538deg, rgb(20, 163, 214) 221.538deg, rgb(20, 163, 214) 249.231deg, rgb(0, 116, 171) 249.231deg, rgb(0, 116, 171) 276.923deg, rgb(0, 67, 115) 276.923deg, rgb(0, 67, 115) 304.615deg, rgb(18, 22, 55) 304.615deg, rgb(18, 22, 55) 332.308deg, rgb(58, 0, 5) 332.308deg, rgb(58, 0, 5) 360deg)"
                                            data-title="Mặc định" data-tcsw="#000000">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Mặc định</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#D6392E; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#3369E8; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#4F9A29; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#EEB331; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="item-header border-top border-bottom pt-3 pb-2 m-2">
                                            Mạng xã hội</div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#002296,#82008F,#C0007A,#EA0C5F,#FF5341,#FF8820"
                                            data-border="#1419AD,#FFFFFF" data-spin_label=""
                                            data-spin_img="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/instagram.jpg"
                                            data-is_stroke="no" data-bg_img="" data-bg_gradient=""
                                            data-title="Instagram 1" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Instagram 1</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#002296; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#82008F; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#C0007A; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#EA0C5F; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FF5341; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FF8820; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#82008F,#C0007A,#EA0C5F,#FF5341,#FF8820,#F6BA00"
                                            data-border="#FF4D00,#F6FA00" data-spin_label=""
                                            data-spin_img="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/instagram.jpg"
                                            data-is_stroke="no" data-bg_img="" data-bg_gradient=""
                                            data-title="Instagram 2" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Instagram 2</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#82008F; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#C0007A; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#EA0C5F; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FF5341; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FF8820; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#F6BA00; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#4285f4,#0F9D58,#F4B400,#DB4437"
                                            data-border="#FF4D00,#F6FA00" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Google" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Google</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#4285f4; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#0F9D58; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#F4B400; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#DB4437; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#1e85ff,#56a4ff,#95c5ff,#cce3ff,#eff7ff"
                                            data-border="#1e85ff,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Facebook"
                                            data-tcsw="#1e85ff">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Facebook</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#1e85ff; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#56a4ff; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#95c5ff; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#cce3ff; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#eff7ff; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#fe3e61,#ff6aa3,#ff98e0,#ffc5ff"
                                            data-border="#fe3e61,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Tinder 1" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Tinder 1</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#fe3e61; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#ff6aa3; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#ff98e0; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#ffc5ff; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#fe3e61,#d7418a,#9e509c,#635796,#38537b,#2f4858"
                                            data-border="#9e509c,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Tinder 2" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Tinder 2</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#fe3e61; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#d7418a; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#9e509c; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#635796; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#38537b; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#2f4858; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="item-header border-top border-bottom pt-3 pb-2 m-2">
                                            Học sinh</div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#FFFFFF,#A9D9E2,#FDB1D4"
                                            data-border="#A9D9E2,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Baby cute"
                                            data-tcsw="#FDB1D4">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Baby cute</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFFFFF; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#A9D9E2; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FDB1D4; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1" data-content="#E57782,#FFFFFF"
                                            data-border="#dc6571,#F2E08A" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="linear-gradient(45deg, rgb(252, 142, 197) 0%, rgb(255, 139, 206) 14.286%, rgb(255, 143, 212) 28.571%, rgb(255, 154, 215) 42.857%, rgb(255, 170, 215) 57.143%, rgb(255, 189, 211) 71.429%, rgb(255, 208, 204) 85.714%, rgb(255, 224, 195) 100%)"
                                            data-title="Eva" data-tcsw="#E57782">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Eva</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#E57782; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFFFFF; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#008DDA,#41C9E2,#ACE2E1,#F7EEDD"
                                            data-border="#008DDA,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Xanh" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Xanh</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#008DDA; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#41C9E2; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#ACE2E1; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#F7EEDD; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#007F73,#4CCD99,#FFC700,#FFF455"
                                            data-border="#007F73,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Xanh vàng" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Xanh vàng</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#007F73; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#4CCD99; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFC700; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFF455; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#FDA403,#E8751A,#898121,#E5C287"
                                            data-border="#FDA403,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Vàng" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Vàng</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FDA403; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#E8751A; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#898121; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#E5C287; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#59D5E0,#F5DD61,#FAA300,#F4538A"
                                            data-border="#59D5E0,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Xanh hồng" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Xanh hồng</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#59D5E0; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#F5DD61; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FAA300; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#F4538A; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#6420AA,#FF3EA5,#FF7ED4,#FFB5DA"
                                            data-border="#FF3EA5,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="HS.Nữ" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">HS.Nữ</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#6420AA; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FF3EA5; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FF7ED4; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFB5DA; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#191D88,#1450A3,#337CCF,#FFC436"
                                            data-border="#191D88,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="HS.Nam" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">HS.Nam</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#191D88; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#1450A3; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#337CCF; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFC436; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#008DDA,#41C9E2,#ACE2E1,#F7EEDD"
                                            data-border="#41C9E2,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Bầu trời xanh" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Bầu trời xanh</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#008DDA; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#41C9E2; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#ACE2E1; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#F7EEDD; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="item-header border-top border-bottom pt-3 pb-2 m-2">
                                            Phụ nữ</div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#F31559,#FF52A2,#FFB07F,#FFECAF"
                                            data-border="#F31559,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Đang yêu" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Đang yêu</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#F31559; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FF52A2; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFB07F; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFECAF; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#FF407D,#FFCAD4,#40679E,#1B3C73"
                                            data-border="#FF407D,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Lãng mạn" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Lãng mạn</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FF407D; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFCAD4; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#40679E; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#1B3C73; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#0802A3,#FF4B91,#FF7676,#FFCD4B"
                                            data-border="#FF4D00,#F6FA00" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Hạnh phúc" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Hạnh phúc</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#0802A3; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FF4B91; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FF7676; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFCD4B; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#E84B9C,#EC7E9E,#EC9EFE,#F1B1D6,#CBB2FE"
                                            data-border="#E84B9C,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Hồng xinh" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Hồng xinh</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#E84B9C; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#EC7E9E; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#EC9EFE; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#F1B1D6; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#CBB2FE; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#711DB0,#C21292,#EF4040,#FFA732"
                                            data-border="#FF4D00,#F6FA00" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Cá tính" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Cá tính</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#711DB0; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#C21292; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#EF4040; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFA732; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1" data-content="#E84BA0,#FEFCFF"
                                            data-border="#E84BA0,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Diana" data-tcsw="#E84BA0">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Diana</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#E84BA0; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FEFCFF; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#833189,#E4488F,#FFFFFF"
                                            data-border="#833189,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Kotex" data-tcsw="#E4488F">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Kotex</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#833189; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#E4488F; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFFFFF; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#E94578,#1248BF,#F3A533"
                                            data-border="#FF4D00,#F6FA00" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Durex" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Durex</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#E94578; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#1248BF; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#F3A533; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="item-header border-top border-bottom pt-3 pb-2 m-2">
                                            Chủ đề</div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#3E1C34,#602748,#B04622,#F7921D"
                                            data-border="#3E1C34,#FFFFFF" data-spin_label=""
                                            data-spin_img="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/halloween-2.png"
                                            data-is_stroke="no"
                                            data-bg_img="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/background/halloween.jpg"
                                            data-bg_gradient="" data-title="Halloween" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Halloween</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#3E1C34; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#602748; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#B04622; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#F7921D; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#27374D,#526D82,#9DB2BF,#DDE6ED"
                                            data-border="#27374D,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Buổi tối" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Buổi tối</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#27374D; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#526D82; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#9DB2BF; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#DDE6ED; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#FF9843,#FFDD95,#86A7FC,#3468C0"
                                            data-border="#FF9843,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Hoàng hôn" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Hoàng hôn</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FF9843; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFDD95; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#86A7FC; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#3468C0; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#e9692c,#ed9121,#ffc324,#fff000,#66b447,#8ee53f"
                                            data-border="#e9692c,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Màu trái cây" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Màu trái cây</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#e9692c; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#ed9121; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#ffc324; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#fff000; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#66b447; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#8ee53f; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#5e02e9,#3c70ef,#30d800,#e7e200,#fd8b00,#f20800"
                                            data-border="#FF4D00,#F6FA00" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Cầu vòng" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Cầu vòng</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#5e02e9; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#3c70ef; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#30d800; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#e7e200; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#fd8b00; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#f20800; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#FFF3C7,#FEC7B4,#FC819E,#F7418F"
                                            data-border="#FC819E,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Vàng ⇢ hồng" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Vàng ⇢ hồng</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFF3C7; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FEC7B4; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FC819E; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#F7418F; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#FFE6E6,#E1AFD1,#AD88C6,#7469B6"
                                            data-border="#E1AFD1,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Hồng ⇢ tím" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Hồng ⇢ tím</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFE6E6; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#E1AFD1; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#AD88C6; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#7469B6; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#59D5E0,#F5DD61,#FAA300,#F4538A"
                                            data-border="#59D5E0,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Retro" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Retro</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#59D5E0; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#F5DD61; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FAA300; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#F4538A; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#000000,#F72798,#F57D1F,#EBF400"
                                            data-border="#FF4D00,#F6FA00" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Neon" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Neon</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#000000; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#F72798; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#F57D1F; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#EBF400; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#944E63,#B47B84,#CAA6A6,#FFE7E7"
                                            data-border="#944E63,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Cafe" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Cafe</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#944E63; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#B47B84; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#CAA6A6; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFE7E7; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#F2EE9D,#7A9D54,#557A46,#8C3333"
                                            data-border="#7A9D54,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/christmas-4.png"
                                            data-is_stroke="no"
                                            data-bg_img="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/background/christmas-2.jpg"
                                            data-bg_gradient="" data-title="Giáng sinh 1" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Giáng sinh 1</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#F2EE9D; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#7A9D54; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#557A46; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#8C3333; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1" data-content="#c71917,#ffffff"
                                            data-border="#c50a07,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/christmas-5.png"
                                            data-is_stroke="no"
                                            data-bg_img="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/background/christmas.jpg"
                                            data-bg_gradient="" data-title="Giáng sinh 2"
                                            data-tcsw="#c71917">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Giáng sinh 2</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#c71917; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#ffffff; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="item-header border-top border-bottom pt-3 pb-2 m-2">
                                            Theo mùa</div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#FFC5C5,#FFEBD8,#C7DCA7,#89B9AD"
                                            data-border="#C7DCA7,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Mùa xuân" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Mùa xuân</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFC5C5; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFEBD8; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#C7DCA7; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#89B9AD; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#FF9843,#FFDD95,#86A7FC,#3468C0"
                                            data-border="#FF9843,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Mùa hạ" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Mùa hạ</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FF9843; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFDD95; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#86A7FC; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#3468C0; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#FFF67E,#BFEA7C,#9BCF53,#416D19"
                                            data-border="#9BCF53,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Mùa hạ xanh" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Mùa hạ xanh</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFF67E; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#BFEA7C; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#9BCF53; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#416D19; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#dc7c00,#ff9705,#feb20a,#ffcb00,#fedf05"
                                            data-border="#ff9705,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Mùa thu vàng" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Mùa thu vàng</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#dc7c00; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#ff9705; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#feb20a; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#ffcb00; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#fedf05; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#99A98F,#C1D0B5,#D6E8DB,#FFF8DE"
                                            data-border="#99A98F,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Mùa thu xanh" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Mùa thu xanh</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#99A98F; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#C1D0B5; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#D6E8DB; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFF8DE; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#2377a4,#50a3c6,#79c0d7,#f8f8f8,#dddfdf,#c2c2c2"
                                            data-border="#50a3c6,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Mùa đông"
                                            data-tcsw="#79c0d7">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Mùa đông</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#2377a4; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#50a3c6; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#79c0d7; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#f8f8f8; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#dddfdf; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#c2c2c2; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="item-header border-top border-bottom pt-3 pb-2 m-2">
                                            Màu cờ</div>
                                        <div class="dropdown-item ms-1" data-content="#DD241F,#FBF500"
                                            data-border="#dd241f,#f6fa00" data-spin_label=""
                                            data-spin_img="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/vnflag.jpg"
                                            data-is_stroke="no" data-bg_img="" data-bg_gradient=""
                                            data-title="Việt Nam" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Việt Nam</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#DD241F; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FBF500; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1" data-content="#1464f4,#1c2e51"
                                            data-border="#1464f4,#FFFFFF" data-spin_label=""
                                            data-spin_img="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/vinfast.jpg"
                                            data-is_stroke="no" data-bg_img="" data-bg_gradient=""
                                            data-title="Vinfast" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Vinfast</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#1464f4; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#1c2e51; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#b22234,#3c3b6e,#FFFFFF"
                                            data-border="#b22234,#ffffff" data-spin_label=""
                                            data-spin_img="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/america.jpg"
                                            data-is_stroke="no" data-bg_img="" data-bg_gradient=""
                                            data-title="Hoa Kỳ" data-tcsw="#3c3b6e">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Hoa Kỳ</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#b22234; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#3c3b6e; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FFFFFF; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1" data-content="#e40001,#ffffff"
                                            data-border="#e40001,#ffffff" data-spin_label="スピン"
                                            data-spin_img="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/japan.jpg"
                                            data-is_stroke="no" data-bg_img="" data-bg_gradient=""
                                            data-title="Nhật Bản" data-tcsw="#e40001">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Nhật Bản</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#e40001; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#ffffff; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#ce2f3b,#0149a0,#010101"
                                            data-border="#0149a0,#ffffff" data-spin_label=""
                                            data-spin_img="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/korea.jpg"
                                            data-is_stroke="no" data-bg_img="" data-bg_gradient=""
                                            data-title="Hàn Quốc" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Hàn Quốc</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#ce2f3b; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#0149a0; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#010101; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#231F20,#E93F33,#F9CD61"
                                            data-border="#FF4D00,#F6FA00" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Đức" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Đức</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#231F20; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#E93F33; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#F9CD61; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#55A752,#ffffff,#E93F33"
                                            data-border="#FF4D00,#F6FA00" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Ý" data-tcsw="#55A752">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Ý</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#55A752; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#ffffff; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#E93F33; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#E63E37,#074890,#ffffff"
                                            data-border="#FF4D00,#F6FA00" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Úc" data-tcsw="#074890">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Úc</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#E63E37; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#074890; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#ffffff; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1" data-content="#0056B8,#FCD833"
                                            data-border="#0056B8,#FFFFFF" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Ukraine" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Ukraine</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#0056B8; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#FCD833; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#E93F33,#000000,#ffffff,#4D9839"
                                            data-border="#FF4D00,#F6FA00" data-spin_label="Quay"
                                            data-spin_img="" data-is_stroke="no" data-bg_img=""
                                            data-bg_gradient="" data-title="Palestine" data-tcsw="">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Palestine</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#E93F33; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#000000; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#ffffff; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#4D9839; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1"
                                            data-content="#ffffff,#1010ff,#ff0e0e"
                                            data-border="#1010ff,#FFFFFF" data-spin_label=""
                                            data-spin_img="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/russia.jpg"
                                            data-is_stroke="no" data-bg_img="" data-bg_gradient=""
                                            data-title="Nga" data-tcsw="#1010ff">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Nga</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#ffffff; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#1010ff; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#ff0e0e; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                        <div class="dropdown-item ms-1" data-content="#2b57ba,#e7e7e7"
                                            data-border="#2b57ba,#e7e7e7" data-spin_label=""
                                            data-spin_img="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/israel.jpg"
                                            data-is_stroke="no" data-bg_img="" data-bg_gradient=""
                                            data-title="Israel" data-tcsw="#2b57ba">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title">Israel</div>
                                                <div class="item-icon"> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#2b57ba; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> <svg width="12" height="12"
                                                        style="margin-left: 5px;">
                                                        <rect width="12" height="12"
                                                            style="fill:#e7e7e7; stroke-width: 1; stroke: rgb(0, 0, 0);">
                                                        </rect>
                                                    </svg> </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mb-3"><span>Tùy chỉnh màu
                                        sắc</span> <a class="text-primary text-decoration-none"
                                        target="_blank"
                                        href="https://vongquaymayman.co/meo-chon-mau-sac-dep/">Mẹo chọn
                                        màu đẹp°</a></div>
                                <div class="row g-1 g-lg-1 mb-3">
                                    <div class="col col-lg-2">
                                        <div class="d-flex">
                                            <div class="form-check"> <input class="form-check-input"
                                                    type="checkbox" value="1" name="chkcolor"
                                                    id="chkcolor-1" checked> </div> <input type="color"
                                                class="form-control form-control-sm form-control-color m-0 p-0 border-0"
                                                value="#D6392E" id="color-1">
                                        </div>
                                    </div>
                                    <div class="col col-lg-2">
                                        <div class="d-flex">
                                            <div class="form-check"> <input class="form-check-input"
                                                    type="checkbox" value="2" name="chkcolor"
                                                    id="chkcolor-2" checked> </div> <input type="color"
                                                class="form-control form-control-sm form-control-color m-0 p-0 border-0"
                                                value="#3369E8" id="color-2">
                                        </div>
                                    </div>
                                    <div class="col col-lg-2">
                                        <div class="d-flex">
                                            <div class="form-check"> <input class="form-check-input"
                                                    type="checkbox" value="3" name="chkcolor"
                                                    id="chkcolor-3" checked> </div> <input type="color"
                                                class="form-control form-control-sm form-control-color m-0 p-0 border-0"
                                                value="#4F9A29" id="color-3">
                                        </div>
                                    </div>
                                    <div class="col col-lg-2">
                                        <div class="d-flex">
                                            <div class="form-check"> <input class="form-check-input"
                                                    type="checkbox" value="4" name="chkcolor"
                                                    id="chkcolor-4" checked> </div> <input type="color"
                                                class="form-control form-control-sm form-control-color m-0 p-0 border-0"
                                                value="#EEB331" id="color-4">
                                        </div>
                                    </div>
                                    <div class="col col-lg-2">
                                        <div class="d-flex">
                                            <div class="form-check"> <input class="form-check-input"
                                                    type="checkbox" value="5" name="chkcolor"
                                                    id="chkcolor-5"> </div> <input type="color"
                                                class="form-control form-control-sm form-control-color m-0 p-0 border-0"
                                                value="#ADB2B0" id="color-5">
                                        </div>
                                    </div>
                                    <div class="col col-lg-2">
                                        <div class="d-flex">
                                            <div class="form-check"> <input class="form-check-input"
                                                    type="checkbox" value="6" name="chkcolor"
                                                    id="chkcolor-6"> </div> <input type="color"
                                                class="form-control form-control-sm form-control-color m-0 p-0 border-0"
                                                value="#ADB2B0" id="color-6">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex mb-3"> <span class="me-2">Màu chữ khi sector
                                    trắng:</span> <input type="color"
                                    class="form-control form-control-sm form-control-color m-0 p-0 border-0"
                                    value="#000000" id="tcsw" placeholder="Text color sector white">
                            </div>
                        </div>
                        <hr>
                        <div class="form-check mb-3"> <input class="form-check-input" type="checkbox"
                                id="is_stroke"> <label class="form-check-label" for="is_stroke"> Bo viền
                                từng ô </label> </div>
                        <div class="form-check mb-3"> <input class="form-check-input" type="checkbox"
                                id="show_border" checked> <label class="form-check-label"
                                for="show_border"> Viền kim cương </label> </div>
                        <div class="row " id="custom_border_color">
                            <div class="col-6">
                                <div class="input-group mb-3"> <span class="input-group-text">Màu
                                        viền</span> <input type="color"
                                        class="form-control form-control-color" id="border_color"
                                        value="#FF4D00"> </div>
                            </div>
                            <div class="col-6">
                                <div class="input-group mb-3"> <span class="input-group-text">Màu kim
                                        cương</span> <input type="color"
                                        class="form-control form-control-color" id="diamond_color"
                                        value="#F6FA00"> </div>
                            </div>
                        </div>
                    </fieldset>
                    <fieldset class="border border-2 px-2">
                        <legend class="float-none w-auto p-2 fs-6 fw-bold">Nút quay <span
                                data-feather="chevron-down"></span></legend>
                        <div class="input-group mb-3"> <span class="input-group-text">Văn bản</span>
                            <input type="text" class="form-control" id="btn-spin-label"
                                placeholder="Quay" value="Quay"> </div>
                        <div class="input-group mb-3"> <span class="input-group-text">Nền</span> <input
                                type="url" class="form-control" id="btn-spin-img" value=""
                                placeholder="https://example.com/nut.jpg"> <button
                                class="btn btn-secondary" id="btn-select-spin-img">Chọn <span
                                    data-feather="chevron-down"></span></button> <span
                                class="input-group-text"> <label for="upload_spin_bg"
                                    id="btn_upload_spin_bg" data-bs-toggle="tooltip"
                                    title="Kích thước khuyên dùng: 250 x 250 (px)"><span
                                        data-feather="image"></span></label> </span> <input type="file"
                                id="upload_spin_bg" data-maxsize="5" class="d-none" accept="image/*">
                        </div>
                    </fieldset>
                    <fieldset class="border border-2 px-2 mb-3">
                        <legend class="float-none w-auto p-2 fs-6 fw-bold">Body <span
                                data-feather="chevron-down"></span></legend>
                        <div class="row mb-3">
                            <div class="col">
                                <div class="input-group"> <span class="input-group-text">Màu nền</span>
                                    <input type="color" class="form-control form-control-color"
                                        id="custom-bg-color" value="#ffffff"> </div>
                            </div>
                            <div class="col">
                                <div class="input-group"> <span class="input-group-text">Màu chữ</span>
                                    <input type="color" class="form-control form-control-color"
                                        id="custom-color" value="#000000"> </div>
                            </div>
                        </div>
                        <ul class="nav nav-tabs" id="deviceTab" role="tablist">
                            <li class="nav-item" role="presentation"> <button class="nav-link active"
                                    id="desktop-tab" data-bs-toggle="tab"
                                    data-bs-target="#desktop-tab-pane" type="button" role="tab"
                                    aria-controls="desktop-tab-pane" aria-selected="true"><span
                                        class="btn-desktop">Desktop</span></button> </li>
                            <li class="nav-item" role="presentation"> <button class="nav-link"
                                    id="tablet-tab" data-bs-toggle="tab"
                                    data-bs-target="#tablet-tab-pane" type="button" role="tab"
                                    aria-controls="tablet-tab-pane" aria-selected="false"><span
                                        class="btn-tablet">Tablet</span></button> </li>
                            <li class="nav-item" role="presentation"> <button class="nav-link"
                                    id="mobile-tab" data-bs-toggle="tab"
                                    data-bs-target="#mobile-tab-pane" type="button" role="tab"
                                    aria-controls="mobile-tab-pane" aria-selected="false"><span
                                        class="btn-mobile">Mobile</span></button> </li>
                        </ul>
                        <div class="tab-content pt-1" id="deviceTabContent">
                            <div class="tab-pane fade show active" id="desktop-tab-pane" role="tabpanel"
                                aria-labelledby="desktop-tab" tabindex="0">
                                <div class="input-group mb-3"> <span class="input-group-text">Nền</span>
                                    <input type="text" class="form-control" id="custom-bg-img"
                                        placeholder="https://example.com/bg.jpg" value=""> <button
                                        class="btn btn-secondary" id="btn-select-bg">Chọn<span
                                            data-feather="chevron-down"></span></button> <span
                                        class="input-group-text"> <label for="upload_bgr"
                                            id="btn_upload_bgr" data-bs-toggle="tooltip"
                                            title="Kích thước (16:9): 1920 x 1080 (px)"><span
                                                data-feather="image"></span></label> </span> <input
                                        type="file" id="upload_bgr" data-maxsize="5" class="d-none"
                                        accept="image/*"> </div>
                            </div>
                            <div class="tab-pane fade" id="tablet-tab-pane" role="tabpanel"
                                aria-labelledby="tablet-tab" tabindex="0">
                                <div class="input-group mb-3"> <span class="input-group-text">Nền</span>
                                    <input type="text" class="form-control" id="custom-bg-img-tablet"
                                        placeholder="https://example.com/bg-tablet.jpg"> <button
                                        class="btn btn-secondary" id="btn-select-bg-tablet">Chọn<span
                                            data-feather="chevron-down"></span></button> <span
                                        class="input-group-text"> <label for="upload_bgr_tablet"
                                            id="btn_upload_bgr_tablet" data-bs-toggle="tooltip"
                                            title="Kích thước (4:3): 1536 x 2048 (px)"><span
                                                data-feather="image"></span></label> </span> <input
                                        type="file" id="upload_bgr_tablet" data-maxsize="5"
                                        class="d-none" accept="image/*"> </div>
                            </div>
                            <div class="tab-pane fade" id="mobile-tab-pane" role="tabpanel"
                                aria-labelledby="mobile-tab" tabindex="0">
                                <div class="input-group mb-3"> <span class="input-group-text">Nền</span>
                                    <input type="text" class="form-control" id="custom-bg-img-mobile"
                                        placeholder="https://example.com/bg-mobile.jpg"> <button
                                        class="btn btn-secondary" id="btn-select-bg-mobile">Chọn<span
                                            data-feather="chevron-down"></span></button> <span
                                        class="input-group-text"> <label for="upload_bgr_mobile"
                                            id="btn_upload_bgr_mobile" data-bs-toggle="tooltip"
                                            title="Kích thước (9:16): 1080 x 1920 (px)"><span
                                                data-feather="image"></span></label> </span> <input
                                        type="file" id="upload_bgr_mobile" data-maxsize="5"
                                        class="d-none" accept="image/*"> </div>
                            </div>
                        </div>
                        <div class="mb-3"> <label for="bg-gradient" class="form-label"> <span
                                    class="dropdown mt-3"> <button
                                        class="btn btn-secondary btn-sm dropdown-toggle"
                                        id="btnDropdownGradient" type="button" data-bs-toggle="dropdown"
                                        aria-expanded="false"> Chọn nền Gradient </button> &#8211; <span
                                        class="text-success"><strong>Đẹp, nhẹ</strong> mà ko cần
                                        ảnh</span>
                                    <ul id="gradientList" class="dropdown-menu"
                                        style="max-height:300px; overflow:auto;"> </ul>
                                </span> </label>
                            <div class="d-flex">
                                <div id="previewBox"
                                    style="width:150px; height:150px; border:2px solid #ccc; border-radius: 6px; margin-right: 5px; transition: all 0.2s;">
                                </div> <textarea class="form-control" id="bg-gradient" rows="2"
                                    placeholder="Nhập CSS gradient (vd: conic-gradient(...))">conic-gradient(from 90deg, rgb(223, 48, 0) 0deg, rgb(223, 48, 0) 27.692deg, rgb(254, 96, 0) 27.692deg, rgb(254, 96, 0) 55.385deg, rgb(255, 145, 37) 55.385deg, rgb(255, 145, 37) 83.077deg, rgb(251, 187, 95) 83.077deg, rgb(251, 187, 95) 110.769deg, rgb(218, 217, 154) 110.769deg, rgb(218, 217, 154) 138.462deg, rgb(169, 230, 202) 138.462deg, rgb(169, 230, 202) 166.154deg, rgb(114, 224, 232) 166.154deg, rgb(114, 224, 232) 193.846deg, rgb(62, 201, 236) 193.846deg, rgb(62, 201, 236) 221.538deg, rgb(20, 163, 214) 221.538deg, rgb(20, 163, 214) 249.231deg, rgb(0, 116, 171) 249.231deg, rgb(0, 116, 171) 276.923deg, rgb(0, 67, 115) 276.923deg, rgb(0, 67, 115) 304.615deg, rgb(18, 22, 55) 304.615deg, rgb(18, 22, 55) 332.308deg, rgb(58, 0, 5) 332.308deg, rgb(58, 0, 5) 360deg)</textarea>
                            </div>
                            <div class="small text-secondary mt-2">➥ Gradient sẽ thay thế nền màu và ảnh
                                đã thiết lập ở trên</div>
                        </div>
                        <div class="form-check mb-2"> <input class="form-check-input" type="checkbox"
                                checked id="show_particle"> <label class="form-check-label"
                                for="show_particle"> Hiệu ứng đẹp <sup class="text-danger">Mới</sup>
                            </label> </div> <select class="form-select mb-2" id="particle_type"
                            aria-label="Kiểu hiệu hứng">
                            <option value="default">Mặc định</option>
                            <option value="snow">Tuyết rơi</option>
                            <option value="bubble">Bong bóng</option>
                            <option value="heart">Trái tim</option>
                        </select>
                        <div class="small text-secondary mb-3">➥ Nền phải khác màu trắng mới nhìn thấy
                        </div>
                    </fieldset>
                </div>
                <!-- option thay mà -->
                <div
   class="tab-pane py-4 fade"
   id="media-tab-pane"
   role="tabpanel"
   aria-labelledby="media-tab"
   tabindex="0"
>
   <div id="uploaded-list" class="mb-3">
      <div class="form-check form-check-inline">
         <input
            class="form-check-input"
            type="radio"
            name="filter_media"
            id="all"
            value="all"
            onclick="filter_media('all');"
            checked=""
         /><label class="form-check-label" for="all">Tất cả</label>
      </div>
      <div class="form-check form-check-inline">
         <input
            class="form-check-input"
            type="radio"
            name="filter_media"
            id="media_button"
            value="btn"
            onclick="filter_media('btn');"
         /><label class="form-check-label" for="media_button">Nút quay</label>
      </div>
      <div class="form-check form-check-inline">
         <input
            class="form-check-input"
            type="radio"
            name="filter_media"
            id="media_background"
            value="bgr"
            onclick="filter_media('bgr');"
         /><label class="form-check-label" for="media_background">Nền</label>
      </div>
      <div class="form-check form-check-inline">
         <input
            class="form-check-input"
            type="radio"
            name="filter_media"
            id="media_gradient"
            value="grd"
            onclick="filter_media('grd');"
         /><label class="form-check-label" for="media_gradient">Gradient</label>
      </div>
      <div class="input-group my-3">
         <span class="input-group-text"
            ><svg
               xmlns="http://www.w3.org/2000/svg"
               width="24"
               height="24"
               viewBox="0 0 24 24"
               fill="none"
               stroke="currentColor"
               stroke-width="2"
               stroke-linecap="round"
               stroke-linejoin="round"
               class="feather feather-search"
            >
               <circle cx="11" cy="11" r="8"></circle>
               <line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></span
         ><input
            type="text"
            class="form-control"
            id="media_kw"
            onkeyup="searhMedia();"
            placeholder="Từ khoá"
         />
      </div>
      <hr />
      <div
         class="mt-3"
         style="max-height: 350px; overflow-y: auto; overflow-x: hidden"
      >
         <table class="table table-striped" id="media">
            <thead>
               <tr>
                  <th>STT</th>
                  <th>Tên</th>
                  <th>Link / Preview</th>
                  <th>Đặt làm</th>
                  <th>Action</th>
               </tr>
            </thead>
            <tbody id="mediaBody">
               <tr style="">
                  <td><span class="badge bg-secondary">1</span></td>
                  <td class="small">30_04</td>
                  <td>
                     <a
                        href="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/30_04.jpg"
                        target="_blank"
                        ><img
                           src="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/30_04.jpg"
                           width="50"
                           heigh="50"
                           class="border border-1 rounded-1"
                     /></a>
                  </td>
                  <td data-type="btn">
                     <button
                        class="btn btn-sm btn-secondary"
                        onclick="el('btn-spin-img').value='https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/30_04.jpg';el('btn-spin-label').value='';el('btn_wheel_setting').click();"
                     >
                        Nút Quay
                     </button>
                  </td>
                  <td>∗</td>
               </tr>
               <tr style="">
                  <td><span class="badge bg-secondary">2</span></td>
                  <td class="small">02_09</td>
                  <td>
                     <a
                        href="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/02_09.jpg"
                        target="_blank"
                        ><img
                           src="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/02_09.jpg"
                           width="50"
                           heigh="50"
                           class="border border-1 rounded-1"
                     /></a>
                  </td>
                  <td data-type="btn">
                     <button
                        class="btn btn-sm btn-secondary"
                        onclick="el('btn-spin-img').value='https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/02_09.jpg';el('btn-spin-label').value='';el('btn_wheel_setting').click();"
                     >
                        Nút Quay
                     </button>
                  </td>
                  <td>∗</td>
               </tr>

               <tr style="">
                  <td><span class="badge bg-secondary">48</span></td>
                  <td class="small">trickortreat</td>
                  <td>
                     <a
                        href="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/trickortreat.png"
                        target="_blank"
                        ><img
                           src="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/trickortreat.png"
                           width="50"
                           heigh="50"
                           class="border border-1 rounded-1"
                     /></a>
                  </td>
                  <td data-type="btn">
                     <button
                        class="btn btn-sm btn-secondary"
                        onclick="el('btn-spin-img').value='https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/buttons/trickortreat.png';el('btn-spin-label').value='';el('btn_wheel_setting').click();"
                     >
                        Nút Quay
                     </button>
                  </td>
                  <td>∗</td>
               </tr>
            </tbody>
         </table>
      </div>
   </div>
</div>

                </div>
            <div class="mb-3"> <button class="btn btn-primary w-100" id="btn_wheel_setting">Lưu
                    lại</button> </div>
            <p class="text-center"><a href="javascript:void(0);" id="btn-reset-wheel"
                    class="link-secondary text-decoration-none">Reset về mặc định</a></p>
        </div>
    </div>
</div>

<script>
(function ($) {
    /* ── Audio preview cho select nhạc bắt đầu / kết thúc ── */
    var $player    = null;
    var playingBtn = null;

    function getPlayer() {
        if (!$player || !$player.length) $player = $('#sw-audio-preview');
        return $player;
    }

    // Dừng player và reset icon nút đang phát
    function stopPreview() {
        var p = getPlayer();
        if (p.length) {
            p[0].pause();
            p[0].currentTime = 0;
            p.attr('src', '');
        }
        if (playingBtn) {
            $(playingBtn).find('[data-feather]').attr('data-feather', 'play');
            if (typeof feather !== 'undefined') feather.replace();
            playingBtn = null;
        }
    }

    // Phát URL — click lần 2 vào cùng nút thì dừng
    function playUrl(url, btn) {
        if (!url) return;
        if (playingBtn === btn) { stopPreview(); return; }
        stopPreview();
        var p = getPlayer();
        p.attr('src', url);
        p[0].play().catch(function () {});
        $(btn).find('[data-feather]').attr('data-feather', 'square');
        if (typeof feather !== 'undefined') feather.replace();
        playingBtn = btn;
        p[0].onended = function () {
            $(btn).find('[data-feather]').attr('data-feather', 'play');
            if (typeof feather !== 'undefined') feather.replace();
            playingBtn = null;
        };
    }

    // Lấy URL từ option đang chọn trong select
    function getUrlFromSelect($select, audios) {
        var val = $select.val();
        if (!val || val === '0') return null;   // Tắt tiếng → không phát

        if (val === 'random') {
            // Ngẫu nhiên → pick 1 bài trong thư viện
            var list = (audios || []).filter(function (a) { return !!a.url; });
            if (!list.length) return null;
            return list[Math.floor(Math.random() * list.length)].url;
        }

        // Option thư viện nhạc có data-url
        return $select.find('option:selected').data('url') || null;
    }

    // Nút play của select (class .sw-btn-preview, data-target = id của <select>)
    $(document).on('click', '.sw-btn-preview', function () {
        var btn    = this;
        var audios = $(btn).data('audios') || [];
        var $sel   = $('#' + $(btn).data('target'));
        var url    = getUrlFromSelect($sel, audios);
        if (!url) { stopPreview(); return; }
        playUrl(url, btn);
    });

    // Nút play nhactik.com — bắt đầu
    $(document).on('click', '#btn-start-sound-play-file', function () {
        var fileId = $.trim($('#start_sound_file').val());
        if (!fileId) { stopPreview(); return; }
        playUrl('https://nhactik.com/play/' + fileId + '.mp3', this);
    });

    // Nút play nhactik.com — kết thúc
    $(document).on('click', '#btn-end-sound-play-file', function () {
        var fileId = $.trim($('#end_sound_file').val());
        if (!fileId) { stopPreview(); return; }
        playUrl('https://nhactik.com/play/' + fileId + '.mp3', this);
    });

    // Dừng nhạc khi đóng modal settings
    $(document).on('hide.bs.modal', '#modalSettings', function () {
        stopPreview();
    });

})(jQuery);
</script>
