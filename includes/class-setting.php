<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Settings {
    const OPTION_KEY = 'wp_spin_wheel_settings';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
    }

    public function add_admin_menu() {
        add_options_page(
            __( 'Spin Wheel Settings', 'wp-spin-wheel' ),
            __( 'Spin Wheel', 'wp-spin-wheel' ),
            'manage_options',
            'wp-spin-wheel-settings',
            array( $this, 'render_settings_page' )
        );
    }

    public function register_settings() {
        register_setting( 'wp_spin_wheel', self::OPTION_KEY, array( $this, 'sanitize_settings' ) );
    }

    public function sanitize_settings( $input ) {
        $allowed = array();
        $allowed['start_sound'] = isset( $input['start_sound'] ) ? sanitize_text_field( $input['start_sound'] ) : '';
        $allowed['start_sound_file'] = isset( $input['start_sound_file'] ) ? sanitize_text_field( $input['start_sound_file'] ) : '';
        $allowed['end_sound'] = isset( $input['end_sound'] ) ? sanitize_text_field( $input['end_sound'] ) : '';
        $allowed['end_sound_file'] = isset( $input['end_sound_file'] ) ? sanitize_text_field( $input['end_sound_file'] ) : '';
        $allowed['duration'] = isset( $input['duration'] ) ? floatval( $input['duration'] ) : 0.991;
        $allowed['show_confetti'] = ! empty( $input['show_confetti'] ) ? 1 : 0;
        $allowed['auto_remove'] = ! empty( $input['auto_remove'] ) ? 1 : 0;
        $allowed['show_popup'] = ! empty( $input['show_popup'] ) ? 1 : 0;
        $allowed['popup_label'] = isset( $input['popup_label'] ) ? sanitize_text_field( $input['popup_label'] ) : '';
        $allowed['show_remove_button'] = ! empty( $input['show_remove_button'] ) ? 1 : 0;
        $allowed['show_hide_button'] = ! empty( $input['show_hide_button'] ) ? 1 : 0;
        $allowed['switch_cover_img'] = ! empty( $input['switch_cover_img'] ) ? 1 : 0;
        $allowed['cover_img'] = isset( $input['cover_img'] ) ? esc_url_raw( $input['cover_img'] ) : '';
        return $allowed;
    }

    public function get_option() {
        return wp_parse_args( (array) get_option( self::OPTION_KEY, array() ), array(
            'start_sound' => '',
            'start_sound_file' => '',
            'end_sound' => '',
            'end_sound_file' => '',
            'duration' => '0.991',
            'show_confetti' => 1,
            'auto_remove' => 0,
            'show_popup' => 1,
            'popup_label' => 'Bạn đã quay vào ô',
            'show_remove_button' => 0,
            'show_hide_button' => 0,
            'switch_cover_img' => 0,
            'cover_img' => '',
        ) );
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $options = $this->get_option();
        ?>
        <div class="wrap wp-spin-wheel-settings-page">
            <h1><?php esc_html_e( 'Spin Wheel Settings', 'wp-spin-wheel' ); ?></h1>
            <form id="spin-wheel-settings-form" method="post" action="options.php">
                <?php settings_fields( 'wp_spin_wheel' ); ?>
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs" id="spinWheelSettingsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="gen-setting-tab" data-bs-toggle="tab" data-bs-target="#gen-setting-tab-pane" type="button" role="tab" aria-controls="gen-setting-tab-pane" aria-selected="true"><?php esc_html_e( 'Chung', 'wp-spin-wheel' ); ?></button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="appearance-tab" data-bs-toggle="tab" data-bs-target="#appearance-tab-pane" type="button" role="tab" aria-controls="appearance-tab-pane" aria-selected="false"><?php esc_html_e( 'Giao diện', 'wp-spin-wheel' ); ?></button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="media-tab" data-bs-toggle="tab" data-bs-target="#media-tab-pane" type="button" role="tab" aria-controls="media-tab-pane" aria-selected="false"><?php esc_html_e( 'Thư viện', 'wp-spin-wheel' ); ?></button>
                            </li>
                        </ul>
                        <div class="tab-content pt-4" id="spinWheelSettingsContent">
                            <div class="tab-pane fade show active" id="gen-setting-tab-pane" role="tabpanel" aria-labelledby="gen-setting-tab">
                                <div class="group-or mb-4">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><?php esc_html_e( '♪ Bắt đầu', 'wp-spin-wheel' ); ?></span>
                                        <select class="form-select" name="<?php echo esc_attr( self::OPTION_KEY . '[start_sound]' ); ?>" id="start_sound">
                                            <option value="" <?php selected( $options['start_sound'], '' ); ?>><?php esc_html_e( 'Tắt tiếng', 'wp-spin-wheel' ); ?></option>
                                            <option value="random" <?php selected( $options['start_sound'], 'random' ); ?>><?php esc_html_e( 'Ngẫu nhiên', 'wp-spin-wheel' ); ?></option>
                                            <option value="slot_start" <?php selected( $options['start_sound'], 'slot_start' ); ?>><?php esc_html_e( 'Slot start', 'wp-spin-wheel' ); ?></option>
                                            <option value="conquay" <?php selected( $options['start_sound'], 'conquay' ); ?>><?php esc_html_e( 'Con quay', 'wp-spin-wheel' ); ?></option>
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary" id="btn-start-sound-play"><span data-feather="play"></span></button>
                                    </div>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><a class="text-decoration-none" target="_blank" href="/huong-dan-lay-file_id-tren-nhactik-com/">♪ nhactik.com</a></span>
                                        <input type="text" class="form-control" id="start_sound_file" name="<?php echo esc_attr( self::OPTION_KEY . '[start_sound_file]' ); ?>" value="<?php echo esc_attr( $options['start_sound_file'] ); ?>" placeholder="File ID" />
                                        <button type="button" class="btn btn-outline-secondary" id="btn-start-sound-play-file"><span data-feather="play"></span></button>
                                    </div>
                                </div>
                                <div class="group-or mb-4">
                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><?php esc_html_e( '♪ Kết thúc', 'wp-spin-wheel' ); ?></span>
                                        <select class="form-select" name="<?php echo esc_attr( self::OPTION_KEY . '[end_sound]' ); ?>" id="end_sound">
                                            <option value="" <?php selected( $options['end_sound'], '' ); ?>><?php esc_html_e( 'Tắt tiếng', 'wp-spin-wheel' ); ?></option>
                                            <option value="random" <?php selected( $options['end_sound'], 'random' ); ?>><?php esc_html_e( 'Ngẫu nhiên', 'wp-spin-wheel' ); ?></option>
                                            <option value="read" <?php selected( $options['end_sound'], 'read' ); ?>><?php esc_html_e( 'Đọc kết quả', 'wp-spin-wheel' ); ?></option>
                                            <option value="slot_end" <?php selected( $options['end_sound'], 'slot_end' ); ?>><?php esc_html_e( 'Slot end', 'wp-spin-wheel' ); ?></option>
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary" id="btn-end-sound-play"><span data-feather="play"></span></button>
                                    </div>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><a class="text-decoration-none" target="_blank" href="/huong-dan-lay-file_id-tren-nhactik-com/">♪ nhactik.com</a></span>
                                        <input type="text" class="form-control" id="end_sound_file" name="<?php echo esc_attr( self::OPTION_KEY . '[end_sound_file]' ); ?>" value="<?php echo esc_attr( $options['end_sound_file'] ); ?>" placeholder="File ID" />
                                        <button type="button" class="btn btn-outline-secondary" id="btn-end-sound-play-file"><span data-feather="play"></span></button>
                                    </div>
                                </div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text"><?php esc_html_e( 'Thời gian', 'wp-spin-wheel' ); ?></span>
                                    <select class="form-select" id="duration" name="<?php echo esc_attr( self::OPTION_KEY . '[duration]' ); ?>">
                                        <option value="0.98" <?php selected( $options['duration'], '0.98' ); ?>><?php esc_html_e( 'Ngắn hơn', 'wp-spin-wheel' ); ?></option>
                                        <option value="0.991" <?php selected( $options['duration'], '0.991' ); ?>><?php esc_html_e( 'Tiêu chuẩn', 'wp-spin-wheel' ); ?></option>
                                        <option value="0.995" <?php selected( $options['duration'], '0.995' ); ?>><?php esc_html_e( 'Dài hơn', 'wp-spin-wheel' ); ?></option>
                                        <option value="0.998" <?php selected( $options['duration'], '0.998' ); ?>><?php esc_html_e( 'Dài hơn nữa', 'wp-spin-wheel' ); ?></option>
                                    </select>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="show_confetti" name="<?php echo esc_attr( self::OPTION_KEY . '[show_confetti]' ); ?>" value="1" <?php checked( $options['show_confetti'], 1 ); ?> />
                                    <label class="form-check-label" for="show_confetti"><?php esc_html_e( 'Bắn hoa giấy khi kết thúc', 'wp-spin-wheel' ); ?></label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="auto_remove" name="<?php echo esc_attr( self::OPTION_KEY . '[auto_remove]' ); ?>" value="1" <?php checked( $options['auto_remove'], 1 ); ?> />
                                    <label class="form-check-label" for="auto_remove"><?php esc_html_e( 'Tự động xóa kết quả sau 5 giây', 'wp-spin-wheel' ); ?></label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="show_popup" name="<?php echo esc_attr( self::OPTION_KEY . '[show_popup]' ); ?>" value="1" <?php checked( $options['show_popup'], 1 ); ?> />
                                    <label class="form-check-label" for="show_popup"><?php esc_html_e( 'Popup kết quả với tiêu đề', 'wp-spin-wheel' ); ?></label>
                                </div>
                                <div class="mb-3">
                                    <input type="text" class="form-control" id="popup_label" name="<?php echo esc_attr( self::OPTION_KEY . '[popup_label]' ); ?>" value="<?php echo esc_attr( $options['popup_label'] ); ?>" placeholder="<?php esc_attr_e( 'Bạn đã quay vào ô', 'wp-spin-wheel' ); ?>" />
                                </div>
                                <div class="form-check ms-3 mb-3">
                                    <input class="form-check-input" type="checkbox" id="show_remove_button" name="<?php echo esc_attr( self::OPTION_KEY . '[show_remove_button]' ); ?>" value="1" <?php checked( $options['show_remove_button'], 1 ); ?> />
                                    <label class="form-check-label" for="show_remove_button"><?php esc_html_e( 'Hiển thị nút "Xóa ô này"', 'wp-spin-wheel' ); ?></label>
                                </div>
                                <div class="form-check ms-3 mb-3">
                                    <input class="form-check-input" type="checkbox" id="show_hide_button" name="<?php echo esc_attr( self::OPTION_KEY . '[show_hide_button]' ); ?>" value="1" <?php checked( $options['show_hide_button'], 1 ); ?> />
                                    <label class="form-check-label" for="show_hide_button"><?php esc_html_e( 'Hiển thị nút "Ẩn"', 'wp-spin-wheel' ); ?></label>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="appearance-tab-pane" role="tabpanel" aria-labelledby="appearance-tab">
                                <fieldset class="border border-2 px-2 py-3">
                                    <legend class="float-none w-auto p-2 fs-6 fw-bold"><?php esc_html_e( 'Vòng quay', 'wp-spin-wheel' ); ?></legend>
                                    <div class="row justify-content-center align-items-center mb-3">
                                        <div class="col-5 text-end">
                                            <button type="button" class="btn mb-1" id="btn_color_wheel">
                                                <img decoding="async" src="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/images/color-wheel.png" width="36" alt="Color wheel">
                                            </button>
                                            <label for="btn_color_wheel"><?php esc_html_e( 'Màu cho mỗi phần', 'wp-spin-wheel' ); ?></label>
                                        </div>
                                        <div class="col-2 text-center">
                                            <div class="form-check form-switch p-0 d-inline-block mx-auto">
                                                <input class="form-check-input ms-0" style="background-color: #0d6efd; border-color: #0d6efd;" type="checkbox" role="switch" id="switch_cover_img" name="<?php echo esc_attr( self::OPTION_KEY . '[switch_cover_img]' ); ?>" value="1" <?php checked( $options['switch_cover_img'], 1 ); ?>>
                                            </div>
                                        </div>
                                        <div class="col-5 text-start">
                                            <button type="button" class="btn mb-1" id="btn_cover_wheel">
                                                <img decoding="async" src="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/images/cover-wheel.jpg" width="36" class="rounded-pill" alt="Cover wheel">
                                            </button>
                                            <label for="btn_cover_wheel"><?php esc_html_e( 'Ảnh nền vòng quay', 'wp-spin-wheel' ); ?></label>
                                        </div>
                                    </div>
                                    <div class="mb-3<?php echo empty( $options['switch_cover_img'] ) ? ' d-none' : ''; ?>" id="form_cover_img">
                                        <div class="input-group justify-content-center mb-3">
                                            <input type="text" class="form-control" id="cover_img" name="<?php echo esc_attr( self::OPTION_KEY . '[cover_img]' ); ?>" value="<?php echo esc_attr( $options['cover_img'] ); ?>" placeholder="https://example.com/cover800.jpg" />
                                            <span class="input-group-text">
                                                <img decoding="async" src="<?php echo esc_url( $options['cover_img'] ?: 'https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/images/cover-wheel.jpg' ); ?>" width="36" height="36" id="cover_img_display" class="rounded-pill" alt="default cover image">
                                            </span>
                                            <button type="button" class="btn btn-secondary" id="btn-select-cover-img"><?php esc_html_e( 'Chọn', 'wp-spin-wheel' ); ?></button>
                                            <span class="input-group-text">
                                                <label for="upload_cover_img" id="btn_upload_cover_img" data-bs-toggle="tooltip" title="<?php esc_attr_e( 'Kích thước khuyên dùng: 800 x 800 (px)', 'wp-spin-wheel' ); ?>"><span data-feather="image"></span></label>
                                            </span>
                                            <input type="file" id="upload_cover_img" data-maxsize="2" class="d-none" accept="image/*" />
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                            <div class="tab-pane fade" id="media-tab-pane" role="tabpanel" aria-labelledby="media-tab">
                                <div class="mb-3">
                                    <p><?php esc_html_e( 'Quản lý thư viện và các tệp âm thanh tại đây.', 'wp-spin-wheel' ); ?></p>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><?php esc_html_e( 'Start audio file', 'wp-spin-wheel' ); ?></span>
                                        <input type="text" class="form-control" value="<?php echo esc_attr( $options['start_sound_file'] ); ?>" disabled />
                                    </div>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><?php esc_html_e( 'End audio file', 'wp-spin-wheel' ); ?></span>
                                        <input type="text" class="form-control" value="<?php echo esc_attr( $options['end_sound_file'] ); ?>" disabled />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php submit_button( __( 'Save Settings', 'wp-spin-wheel' ) ); ?>
            </form>
        </div>
        <?php
    }

    public function register_rest_routes() {
        register_rest_route( 'spin-wheel/v1', '/settings', array(
            array(
                'methods' => 'GET',
                'callback' => array( $this, 'rest_get_settings' ),
                'permission_callback' => function() { return current_user_can( 'edit_theme_options' ); }
            ),
            array(
                'methods' => 'POST',
                'callback' => array( $this, 'rest_update_settings' ),
                'permission_callback' => function() { return current_user_can( 'manage_options' ); }
            ),
        ) );
    }

    public function rest_get_settings( $request ) {
        return rest_ensure_response( $this->get_option() );
    }

    public function rest_update_settings( $request ) {
        $params = $request->get_json_params();
        $san = $this->sanitize_settings( $params );
        update_option( self::OPTION_KEY, $san );
        return rest_ensure_response( $san );
    }
}

new WP_Spin_Wheel_Settings();
