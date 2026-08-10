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
        $input = is_array( $input ) ? $input : array();
        $options = $this->get_option();
        $schema = $this->get_setting_schema();

        foreach ( $schema as $key => $field ) {
            if ( ! array_key_exists( $key, $input ) ) {
                continue;
            }

            $options[ $key ] = $this->sanitize_setting_value( $key, $input[ $key ], $field );
        }

        foreach ( $input as $key => $value ) {
            if ( isset( $schema[ $key ] ) ) {
                continue;
            }

            $options[ $key ] = $this->sanitize_setting_value( $key, $value, array( 'type' => 'text' ) );
        }

        return $options;
    }

    public function get_setting_schema() {
        return array(
            'start_sound' => array( 'type' => 'select', 'default' => '' ),
            'start_sound_file' => array( 'type' => 'text', 'default' => '' ),
            'end_sound' => array( 'type' => 'select', 'default' => '' ),
            'end_sound_file' => array( 'type' => 'text', 'default' => '' ),
            'duration' => array( 'type' => 'text', 'default' => '0.991' ),
            'show_confetti' => array( 'type' => 'checkbox', 'default' => 1 ),
            'auto_remove' => array( 'type' => 'checkbox', 'default' => 0 ),
            'show_popup' => array( 'type' => 'checkbox', 'default' => 1 ),
            'popup_label' => array( 'type' => 'text', 'default' => 'Bạn đã quay vào ô' ),
            'show_remove_button' => array( 'type' => 'checkbox', 'default' => 0 ),
            'show_hide_button' => array( 'type' => 'checkbox', 'default' => 0 ),
            'switch_cover_img' => array( 'type' => 'checkbox', 'default' => 0 ),
            'cover_img' => array( 'type' => 'image', 'default' => '' ),
            'wheel_title' => array( 'type' => 'text', 'default' => 'Vòng quay may mắn' ),
            'wheel_description' => array( 'type' => 'textarea', 'default' => 'Nhấn vào nút quay để bắt đầu.' ),
            'wheel_background_color' => array( 'type' => 'color', 'default' => '#f8fafc' ),
            'wheel_button_text' => array( 'type' => 'text', 'default' => 'Quay ngay' ),
            'wheel_button_color' => array( 'type' => 'color', 'default' => '#3b82f6' ),
            'wheel_button_text_color' => array( 'type' => 'color', 'default' => '#ffffff' ),
            'wheel_border_color' => array( 'type' => 'color', 'default' => '#ffffff' ),
            'wheel_background_image' => array( 'type' => 'image', 'default' => '' ),
            'wheel_pointer_image' => array( 'type' => 'image', 'default' => '' ),
            'wheel_pointer_size' => array( 'type' => 'text', 'default' => '90' ),
            'wheel_font_family' => array( 'type' => 'text', 'default' => 'Poppins' ),
            'wheel_font_size' => array( 'type' => 'text', 'default' => '18' ),
            'wheel_animation_duration' => array( 'type' => 'text', 'default' => '6' ),
            'wheel_confetti' => array( 'type' => 'checkbox', 'default' => 1 ),
            'wheel_background_presets' => array( 'type' => 'json', 'default' => array() ),
            'wheel_button_presets' => array( 'type' => 'json', 'default' => array() ),
            'wheel_font_presets' => array( 'type' => 'json', 'default' => array() ),
            'wheel_pointer_presets' => array( 'type' => 'json', 'default' => array() ),
            'wheel_selected_background_preset' => array( 'type' => 'text', 'default' => '' ),
            'wheel_selected_button_preset' => array( 'type' => 'text', 'default' => '' ),
            'wheel_selected_font_preset' => array( 'type' => 'text', 'default' => '' ),
            'wheel_selected_pointer_preset' => array( 'type' => 'text', 'default' => '' ),
            'wheel_segment_colors' => array( 'type' => 'json', 'default' => array() ),
            'wheel_extra_config' => array( 'type' => 'json', 'default' => array() ),
        );
    }

    public function get_default_settings() {
        $defaults = array();
        foreach ( $this->get_setting_schema() as $key => $field ) {
            $defaults[ $key ] = isset( $field['default'] ) ? $field['default'] : '';
        }

        return $defaults;
    }

    public function get_option() {
        $saved = get_option( self::OPTION_KEY, array() );
        if ( ! is_array( $saved ) ) {
            $saved = array();
        }

        return wp_parse_args( $saved, $this->get_default_settings() );
    }

    public function sanitize_setting_value( $key, $value, $field = array() ) {
        $field = is_array( $field ) ? $field : array();
        $type = isset( $field['type'] ) ? $field['type'] : 'text';

        if ( 'checkbox' === $type ) {
            return ! empty( $value ) ? 1 : 0;
        }

        if ( 'color' === $type ) {
            return is_string( $value ) ? sanitize_hex_color( $value ) : '';
        }

        if ( 'image' === $type ) {
            return is_string( $value ) ? esc_url_raw( trim( $value ) ) : '';
        }

        if ( 'textarea' === $type ) {
            return is_string( $value ) ? sanitize_textarea_field( wp_unslash( $value ) ) : '';
        }

        if ( 'json' === $type ) {
            if ( is_array( $value ) || is_object( $value ) ) {
                return wp_unslash( $value );
            }

            if ( is_string( $value ) ) {
                $decoded = json_decode( wp_unslash( $value ), true );
                if ( json_last_error() === JSON_ERROR_NONE ) {
                    return $decoded;
                }
                return sanitize_text_field( wp_unslash( $value ) );
            }

            return array();
        }

        if ( 'select' === $type || 'text' === $type ) {
            return is_string( $value ) ? sanitize_text_field( wp_unslash( $value ) ) : '';
        }

        if ( is_array( $value ) || is_object( $value ) ) {
            return wp_unslash( $value );
        }

        return is_string( $value ) ? sanitize_text_field( wp_unslash( $value ) ) : '';
    }

    public function add_or_update_setting( $key, $value, $type = 'text' ) {
        $options = $this->get_option();
        $options[ $key ] = $this->sanitize_setting_value( $key, $value, array( 'type' => $type ) );
        update_option( self::OPTION_KEY, $options );
        return $options[ $key ];
    }

    public function delete_setting( $key ) {
        $options = $this->get_option();
        if ( isset( $options[ $key ] ) ) {
            unset( $options[ $key ] );
            update_option( self::OPTION_KEY, $options );
            return true;
        }

        return false;
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

                                    <div class="row g-3 mt-2">
                                        <div class="col-md-6">
                                            <label class="form-label" for="wheel_title"><?php esc_html_e( 'Tiêu đề vòng quay', 'wp-spin-wheel' ); ?></label>
                                            <input type="text" class="form-control" id="wheel_title" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_title]' ); ?>" value="<?php echo esc_attr( $options['wheel_title'] ); ?>" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="wheel_button_text"><?php esc_html_e( 'Chữ nút quay', 'wp-spin-wheel' ); ?></label>
                                            <input type="text" class="form-control" id="wheel_button_text" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_button_text]' ); ?>" value="<?php echo esc_attr( $options['wheel_button_text'] ); ?>" />
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="wheel_description"><?php esc_html_e( 'Mô tả vòng quay', 'wp-spin-wheel' ); ?></label>
                                            <textarea class="form-control" id="wheel_description" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_description]' ); ?>" rows="3"><?php echo esc_textarea( $options['wheel_description'] ); ?></textarea>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="wheel_background_color"><?php esc_html_e( 'Màu nền', 'wp-spin-wheel' ); ?></label>
                                            <input type="color" class="form-control form-control-color" id="wheel_background_color" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_background_color]' ); ?>" value="<?php echo esc_attr( $options['wheel_background_color'] ); ?>" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="wheel_button_color"><?php esc_html_e( 'Màu nút', 'wp-spin-wheel' ); ?></label>
                                            <input type="color" class="form-control form-control-color" id="wheel_button_color" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_button_color]' ); ?>" value="<?php echo esc_attr( $options['wheel_button_color'] ); ?>" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="wheel_button_text_color"><?php esc_html_e( 'Màu chữ nút', 'wp-spin-wheel' ); ?></label>
                                            <input type="color" class="form-control form-control-color" id="wheel_button_text_color" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_button_text_color]' ); ?>" value="<?php echo esc_attr( $options['wheel_button_text_color'] ); ?>" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="wheel_border_color"><?php esc_html_e( 'Màu viền', 'wp-spin-wheel' ); ?></label>
                                            <input type="color" class="form-control form-control-color" id="wheel_border_color" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_border_color]' ); ?>" value="<?php echo esc_attr( $options['wheel_border_color'] ); ?>" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="wheel_background_image"><?php esc_html_e( 'Ảnh nền vòng quay', 'wp-spin-wheel' ); ?></label>
                                            <input type="text" class="form-control" id="wheel_background_image" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_background_image]' ); ?>" value="<?php echo esc_attr( $options['wheel_background_image'] ); ?>" placeholder="https://example.com/bg.jpg" />
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label" for="wheel_pointer_image"><?php esc_html_e( 'Ảnh pointer', 'wp-spin-wheel' ); ?></label>
                                            <input type="text" class="form-control" id="wheel_pointer_image" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_pointer_image]' ); ?>" value="<?php echo esc_attr( $options['wheel_pointer_image'] ); ?>" placeholder="https://example.com/pointer.png" />
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="wheel_pointer_size"><?php esc_html_e( 'Kích thước pointer', 'wp-spin-wheel' ); ?></label>
                                            <input type="number" class="form-control" id="wheel_pointer_size" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_pointer_size]' ); ?>" value="<?php echo esc_attr( $options['wheel_pointer_size'] ); ?>" />
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="wheel_font_family"><?php esc_html_e( 'Font chữ', 'wp-spin-wheel' ); ?></label>
                                            <input type="text" class="form-control" id="wheel_font_family" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_font_family]' ); ?>" value="<?php echo esc_attr( $options['wheel_font_family'] ); ?>" />
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="wheel_font_size"><?php esc_html_e( 'Cỡ chữ', 'wp-spin-wheel' ); ?></label>
                                            <input type="number" class="form-control" id="wheel_font_size" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_font_size]' ); ?>" value="<?php echo esc_attr( $options['wheel_font_size'] ); ?>" />
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="wheel_animation_duration"><?php esc_html_e( 'Thời gian quay', 'wp-spin-wheel' ); ?></label>
                                            <input type="number" step="0.1" class="form-control" id="wheel_animation_duration" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_animation_duration]' ); ?>" value="<?php echo esc_attr( $options['wheel_animation_duration'] ); ?>" />
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="wheel_confetti" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_confetti]' ); ?>" value="1" <?php checked( $options['wheel_confetti'], 1 ); ?> />
                                                <label class="form-check-label" for="wheel_confetti"><?php esc_html_e( 'Bật hiệu ứng confetti', 'wp-spin-wheel' ); ?></label>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="wheel_selected_background_preset"><?php esc_html_e( 'Preset nền đang dùng (ID)', 'wp-spin-wheel' ); ?></label>
                                            <input type="text" class="form-control" id="wheel_selected_background_preset" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_selected_background_preset]' ); ?>" value="<?php echo esc_attr( $options['wheel_selected_background_preset'] ); ?>" />
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="wheel_background_presets"><?php esc_html_e( 'Danh sách preset nền (JSON)', 'wp-spin-wheel' ); ?></label>
                                            <textarea class="form-control" id="wheel_background_presets" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_background_presets]' ); ?>" rows="4"><?php echo esc_textarea( is_array( $options['wheel_background_presets'] ) ? wp_json_encode( $options['wheel_background_presets'] ) : (string) $options['wheel_background_presets'] ); ?></textarea>
                                            <small class="text-muted">Ví dụ: [{"id":"bg-1","name":"Nền xanh","image":"https://...","color":"#0f766e"}]</small>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="wheel_selected_button_preset"><?php esc_html_e( 'Preset nút đang dùng (ID)', 'wp-spin-wheel' ); ?></label>
                                            <input type="text" class="form-control" id="wheel_selected_button_preset" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_selected_button_preset]' ); ?>" value="<?php echo esc_attr( $options['wheel_selected_button_preset'] ); ?>" />
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="wheel_button_presets"><?php esc_html_e( 'Danh sách preset nút (JSON)', 'wp-spin-wheel' ); ?></label>
                                            <textarea class="form-control" id="wheel_button_presets" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_button_presets]' ); ?>" rows="4"><?php echo esc_textarea( is_array( $options['wheel_button_presets'] ) ? wp_json_encode( $options['wheel_button_presets'] ) : (string) $options['wheel_button_presets'] ); ?></textarea>
                                            <small class="text-muted">Ví dụ: [{"id":"btn-1","name":"Nút xanh","text":"Quay","color":"#2563eb","text_color":"#ffffff"}]</small>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="wheel_selected_font_preset"><?php esc_html_e( 'Preset font đang dùng (ID)', 'wp-spin-wheel' ); ?></label>
                                            <input type="text" class="form-control" id="wheel_selected_font_preset" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_selected_font_preset]' ); ?>" value="<?php echo esc_attr( $options['wheel_selected_font_preset'] ); ?>" />
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="wheel_font_presets"><?php esc_html_e( 'Danh sách preset font (JSON)', 'wp-spin-wheel' ); ?></label>
                                            <textarea class="form-control" id="wheel_font_presets" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_font_presets]' ); ?>" rows="4"><?php echo esc_textarea( is_array( $options['wheel_font_presets'] ) ? wp_json_encode( $options['wheel_font_presets'] ) : (string) $options['wheel_font_presets'] ); ?></textarea>
                                            <small class="text-muted">Ví dụ: [{"id":"font-1","name":"Poppins","family":"Poppins","size":18}]</small>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="wheel_selected_pointer_preset"><?php esc_html_e( 'Preset pointer đang dùng (ID)', 'wp-spin-wheel' ); ?></label>
                                            <input type="text" class="form-control" id="wheel_selected_pointer_preset" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_selected_pointer_preset]' ); ?>" value="<?php echo esc_attr( $options['wheel_selected_pointer_preset'] ); ?>" />
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="wheel_pointer_presets"><?php esc_html_e( 'Danh sách preset pointer (JSON)', 'wp-spin-wheel' ); ?></label>
                                            <textarea class="form-control" id="wheel_pointer_presets" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_pointer_presets]' ); ?>" rows="4"><?php echo esc_textarea( is_array( $options['wheel_pointer_presets'] ) ? wp_json_encode( $options['wheel_pointer_presets'] ) : (string) $options['wheel_pointer_presets'] ); ?></textarea>
                                            <small class="text-muted">Ví dụ: [{"id":"ptr-1","name":"Pointer đỏ","image":"https://...","size":90}]</small>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="wheel_segment_colors"><?php esc_html_e( 'Màu các phần (JSON)', 'wp-spin-wheel' ); ?></label>
                                            <textarea class="form-control" id="wheel_segment_colors" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_segment_colors]' ); ?>" rows="3"><?php echo esc_textarea( is_array( $options['wheel_segment_colors'] ) ? wp_json_encode( $options['wheel_segment_colors'] ) : (string) $options['wheel_segment_colors'] ); ?></textarea>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label" for="wheel_extra_config"><?php esc_html_e( 'Cấu hình mở rộng (JSON)', 'wp-spin-wheel' ); ?></label>
                                            <textarea class="form-control" id="wheel_extra_config" name="<?php echo esc_attr( self::OPTION_KEY . '[wheel_extra_config]' ); ?>" rows="3"><?php echo esc_textarea( is_array( $options['wheel_extra_config'] ) ? wp_json_encode( $options['wheel_extra_config'] ) : (string) $options['wheel_extra_config'] ); ?></textarea>
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

        register_rest_route( 'spin-wheel/v1', '/settings/option', array(
            array(
                'methods' => 'POST',
                'callback' => array( $this, 'rest_manage_setting' ),
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

    public function rest_manage_setting( $request ) {
        $params = $request->get_json_params();
        if ( ! is_array( $params ) ) {
            $params = $request->get_params();
        }

        $action = isset( $params['action'] ) ? sanitize_text_field( $params['action'] ) : 'update';
        $key = isset( $params['key'] ) ? sanitize_key( $params['key'] ) : '';

        if ( empty( $key ) ) {
            return new WP_Error( 'missing_key', __( 'Thiếu key option.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
        }

        if ( 'delete' === $action ) {
            $deleted = $this->delete_setting( $key );
            return rest_ensure_response( array( 'deleted' => $deleted, 'key' => $key ) );
        }

        $type = isset( $params['type'] ) ? sanitize_key( $params['type'] ) : 'text';
        $value = isset( $params['value'] ) ? $params['value'] : '';
        $saved_value = $this->add_or_update_setting( $key, $value, $type );

        return rest_ensure_response( array(
            'saved' => true,
            'key' => $key,
            'type' => $type,
            'value' => $saved_value,
        ) );
    }
}
