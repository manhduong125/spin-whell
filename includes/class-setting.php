<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Settings {
    const OPTION_KEY = 'wp_spin_wheel_settings';
    const ITEM_OPTION_KEY = 'wp_spin_wheel_setting_items';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_init', array( $this, 'handle_setting_item_form' ) );
        add_action( 'init', array( $this, 'ensure_default_setting_items' ), 20 );
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
    }

    public function ensure_default_setting_items() {
        $items = $this->get_item_option();
        $changed = false;

        foreach ( $this->get_default_item_groups() as $group_key => $defaults ) {
            if ( empty( $items[ $group_key ] ) || ! is_array( $items[ $group_key ] ) ) {
                $items[ $group_key ] = $defaults;
                $changed = true;
            }
        }

        if ( $changed ) {
            update_option( self::ITEM_OPTION_KEY, $items );
        }
    }

    private function get_default_item_groups() {
        return array(
            'backgrounds' => array(
                array(
                    'id'     => 'background-default',
                    'name'   => __( 'Default Background', 'wp-spin-wheel' ),
                    'config' => array( 'type' => 'color', 'value' => '#f8fafc', 'image' => '' ),
                ),
            ),
            'buttons' => array(
                array(
                    'id'     => 'button-default',
                    'name'   => __( 'Primary Button', 'wp-spin-wheel' ),
                    'config' => array( 'text' => __( 'QUAY', 'wp-spin-wheel' ), 'color' => '#2563eb', 'text_color' => '#ffffff', 'radius' => 50, 'background_image' => '' ),
                ),
            ),
            'pointers' => array(
                array(
                    'id'     => 'pointer-default',
                    'name'   => __( 'Default Pointer', 'wp-spin-wheel' ),
                    'config' => array( 'image' => '', 'size' => 80 ),
                ),
            ),
        );
    }

    private function get_group_key_from_type( $type ) {
        $type = is_string( $type ) ? sanitize_key( $type ) : '';

        if ( in_array( $type, array( 'background', 'backgrounds', 'spin_wheel_background' ), true ) ) {
            return 'backgrounds';
        }

        if ( in_array( $type, array( 'button', 'buttons', 'spin_wheel_button' ), true ) ) {
            return 'buttons';
        }

        if ( in_array( $type, array( 'pointer', 'pointers', 'spin_wheel_pointer' ), true ) ) {
            return 'pointers';
        }

        return $type;
    }

    public function get_item_option() {
        $saved = get_option( self::ITEM_OPTION_KEY, array() );
        if ( ! is_array( $saved ) ) {
            $saved = array();
        }

        return $saved;
    }

    public function get_setting_items( $type ) {
        $group_key = $this->get_group_key_from_type( $type );
        $items = $this->get_item_option();

        if ( ! is_array( $items[ $group_key ] ?? null ) ) {
            return array();
        }

        return $items[ $group_key ];
    }

    public function save_setting_items( $type, $items ) {
        $group_key = $this->get_group_key_from_type( $type );
        $stored = $this->get_item_option();
        $stored[ $group_key ] = is_array( $items ) ? $items : array();
        update_option( self::ITEM_OPTION_KEY, $stored );
    }

    private function build_item_config( $group_key, $payload ) {
        $payload = is_array( $payload ) ? $payload : array();

        if ( 'backgrounds' === $group_key ) {
            return array(
                'type'  => sanitize_text_field( wp_unslash( $payload['spin_wheel_setting_background_type'] ?? 'color' ) ),
                'value' => sanitize_text_field( wp_unslash( $payload['spin_wheel_setting_background_color'] ?? '#ffffff' ) ),
                'image' => esc_url_raw( wp_unslash( $payload['spin_wheel_setting_background_image'] ?? '' ) ),
            );
        }

        if ( 'buttons' === $group_key ) {
            return array(
                'text'             => sanitize_text_field( wp_unslash( $payload['spin_wheel_setting_button_text'] ?? __( 'QUAY', 'wp-spin-wheel' ) ) ),
                'color'            => sanitize_text_field( wp_unslash( $payload['spin_wheel_setting_button_color'] ?? '#2563eb' ) ),
                'text_color'       => sanitize_text_field( wp_unslash( $payload['spin_wheel_setting_button_text_color'] ?? '#ffffff' ) ),
                'radius'           => max( 0, intval( wp_unslash( $payload['spin_wheel_setting_button_radius'] ?? 50 ) ) ),
                'background_image' => esc_url_raw( wp_unslash( $payload['spin_wheel_setting_button_background_image'] ?? '' ) ),
            );
        }

        if ( 'pointers' === $group_key ) {
            return array(
                'image' => esc_url_raw( wp_unslash( $payload['spin_wheel_setting_pointer_image'] ?? '' ) ),
                'size'  => max( 20, intval( wp_unslash( $payload['spin_wheel_setting_pointer_size'] ?? 80 ) ) ),
            );
        }

        return array();
    }

    public function handle_setting_item_form() {
        if ( empty( $_POST['spin_wheel_setting_item_action'] ) ) {
            return;
        }

        if ( empty( $_POST['spin_wheel_setting_item_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['spin_wheel_setting_item_nonce'] ), 'spin_wheel_setting_items' ) ) {
            return;
        }

        $action = sanitize_text_field( wp_unslash( $_POST['spin_wheel_setting_item_action'] ) );
        $group_key = $this->get_group_key_from_type( wp_unslash( $_POST['spin_wheel_setting_item_group'] ?? '' ) );

        if ( ! in_array( $group_key, array( 'backgrounds', 'buttons', 'pointers' ), true ) ) {
            return;
        }

        $items = $this->get_setting_items( $group_key );
        $item_id = sanitize_text_field( wp_unslash( $_POST['spin_wheel_setting_item_id'] ?? '' ) );
        $item_name = sanitize_text_field( wp_unslash( $_POST['spin_wheel_setting_item_name'] ?? '' ) );

        if ( 'delete' === $action ) {
            foreach ( $items as $index => $item ) {
                if ( (string) ( $item['id'] ?? '' ) === $item_id ) {
                    unset( $items[ $index ] );
                    break;
                }
            }

            $this->save_setting_items( $group_key, array_values( $items ) );
            wp_safe_redirect( add_query_arg( array( 'page' => 'wp-spin-wheel-settings', 'updated' => '1' ), admin_url( 'admin.php' ) ) );
            exit;
        }

        if ( empty( $item_name ) ) {
            return;
        }

        if ( empty( $item_id ) ) {
            $item_id = $group_key . '-' . wp_unique_id();
        }

        $config = $this->build_item_config( $group_key, $_POST );
        $item_payload = array(
            'id'     => $item_id,
            'name'   => $item_name,
            'config' => $config,
        );

        $updated = false;
        foreach ( $items as $index => $item ) {
            if ( (string) ( $item['id'] ?? '' ) === $item_id ) {
                $items[ $index ] = $item_payload;
                $updated = true;
                break;
            }
        }

        if ( ! $updated ) {
            $items[] = $item_payload;
        }

        $this->save_setting_items( $group_key, array_values( $items ) );
        wp_safe_redirect( add_query_arg( array( 'page' => 'wp-spin-wheel-settings', 'updated' => '1' ), admin_url( 'admin.php' ) ) );
        exit;
    }

    public function add_admin_menu() {
        if ( ! is_admin() ) {
            return;
        }
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
            'wheel_animation_duration' => array( 'type' => 'text', 'default' => '6' ),
            'wheel_confetti' => array( 'type' => 'checkbox', 'default' => 1 ),
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

            <div class="postbox" style="margin-top: 24px;">
                <h2 class="hndle"><?php esc_html_e( 'Setting items', 'wp-spin-wheel' ); ?></h2>
                <div class="inside">
                    <?php
                    $group_definitions = array(
                        'backgrounds' => array(
                            'title' => __( 'Background items', 'wp-spin-wheel' ),
                            'name'  => __( 'Background', 'wp-spin-wheel' ),
                        ),
                        'buttons' => array(
                            'title' => __( 'Button items', 'wp-spin-wheel' ),
                            'name'  => __( 'Button', 'wp-spin-wheel' ),
                        ),
                        'pointers' => array(
                            'title' => __( 'Pointer items', 'wp-spin-wheel' ),
                            'name'  => __( 'Pointer', 'wp-spin-wheel' ),
                        ),
                    );
                    $editing_group = isset( $_GET['edit_setting_group'] ) ? sanitize_key( wp_unslash( $_GET['edit_setting_group'] ) ) : '';
                    $editing_id = isset( $_GET['edit_setting_item'] ) ? sanitize_text_field( wp_unslash( $_GET['edit_setting_item'] ) ) : '';
                    $editing_item = array();

                    if ( ! empty( $editing_group ) && ! empty( $editing_id ) ) {
                        foreach ( $this->get_setting_items( $editing_group ) as $item ) {
                            if ( (string) ( $item['id'] ?? '' ) === $editing_id ) {
                                $editing_item = $item;
                                break;
                            }
                        }
                    }
                    ?>
                    <?php foreach ( $group_definitions as $group_key => $group_info ) : ?>
                        <?php $items = $this->get_setting_items( $group_key ); ?>
                        <h3><?php echo esc_html( $group_info['title'] ); ?></h3>
                        <?php if ( empty( $items ) ) : ?>
                            <p><?php esc_html_e( 'No items yet.', 'wp-spin-wheel' ); ?></p>
                        <?php else : ?>
                            <table class="widefat fixed" style="margin-bottom: 12px;">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Name', 'wp-spin-wheel' ); ?></th>
                                        <th><?php esc_html_e( 'Value', 'wp-spin-wheel' ); ?></th>
                                        <th><?php esc_html_e( 'Action', 'wp-spin-wheel' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $items as $item ) : ?>
                                        <tr>
                                            <td><?php echo esc_html( $item['name'] ?? '' ); ?></td>
                                            <td>
                                                <?php
                                                $config = $item['config'] ?? array();
                                                if ( 'backgrounds' === $group_key ) {
                                                    echo esc_html( $config['type'] ?? '' );
                                                } elseif ( 'buttons' === $group_key ) {
                                                    echo esc_html( $config['text'] ?? '' );
                                                } elseif ( 'pointers' === $group_key ) {
                                                    echo esc_html( $config['image'] ?? '' );
                                                } else {
                                                    echo esc_html( $config['image'] ?? '' );
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'wp-spin-wheel-settings', 'edit_setting_group' => $group_key, 'edit_setting_item' => $item['id'] ?? '' ) ) ); ?>"><?php esc_html_e( 'Edit', 'wp-spin-wheel' ); ?></a>
                                                |
                                                <form method="post" style="display: inline;">
                                                    <?php wp_nonce_field( 'spin_wheel_setting_items' ); ?>
                                                    <input type="hidden" name="spin_wheel_setting_item_group" value="<?php echo esc_attr( $group_key ); ?>" />
                                                    <input type="hidden" name="spin_wheel_setting_item_id" value="<?php echo esc_attr( $item['id'] ?? '' ); ?>" />
                                                    <input type="hidden" name="spin_wheel_setting_item_action" value="delete" />
                                                    <button type="submit" class="button-link-delete" style="padding: 0; border: 0; background: none; color: #b32d2e; cursor: pointer;"><?php esc_html_e( 'Delete', 'wp-spin-wheel' ); ?></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>

                        <form method="post" class="wp-spin-wheel-setting-item-form">
                            <?php wp_nonce_field( 'spin_wheel_setting_items' ); ?>
                            <input type="hidden" name="spin_wheel_setting_item_group" value="<?php echo esc_attr( $group_key ); ?>" />
                            <input type="hidden" name="spin_wheel_setting_item_id" value="<?php echo esc_attr( $editing_item['id'] ?? '' ); ?>" />
                            <input type="hidden" name="spin_wheel_setting_item_action" value="save" />
                            <p>
                                <label><?php esc_html_e( 'Name', 'wp-spin-wheel' ); ?><br />
                                <input type="text" name="spin_wheel_setting_item_name" value="<?php echo esc_attr( $editing_item['name'] ?? '' ); ?>" class="widefat" /></label>
                            </p>

                            <?php if ( 'backgrounds' === $group_key ) : ?>
                                <p>
                                    <label><?php esc_html_e( 'Type', 'wp-spin-wheel' ); ?><br />
                                    <select name="spin_wheel_setting_background_type" class="widefat">
                                        <option value="color" <?php selected( $editing_item['config']['type'] ?? '', 'color' ); ?>><?php esc_html_e( 'Color', 'wp-spin-wheel' ); ?></option>
                                        <option value="image" <?php selected( $editing_item['config']['type'] ?? '', 'image' ); ?>><?php esc_html_e( 'Image', 'wp-spin-wheel' ); ?></option>
                                    </select></label>
                                </p>
                                <p>
                                    <label><?php esc_html_e( 'Color', 'wp-spin-wheel' ); ?><br />
                                    <input type="text" name="spin_wheel_setting_background_color" value="<?php echo esc_attr( $editing_item['config']['value'] ?? '#ffffff' ); ?>" class="widefat wp-spin-wheel-color-field" /></label>
                                </p>
                                <p>
                                    <label><?php esc_html_e( 'Image URL', 'wp-spin-wheel' ); ?><br />
                                    <input type="text" name="spin_wheel_setting_background_image" value="<?php echo esc_attr( $editing_item['config']['image'] ?? '' ); ?>" class="widefat" /></label>
                                </p>
                            <?php elseif ( 'buttons' === $group_key ) : ?>
                                <p>
                                    <label><?php esc_html_e( 'Text', 'wp-spin-wheel' ); ?><br />
                                    <input type="text" name="spin_wheel_setting_button_text" value="<?php echo esc_attr( $editing_item['config']['text'] ?? __( 'QUAY', 'wp-spin-wheel' ) ); ?>" class="widefat" /></label>
                                </p>
                                <p>
                                    <label><?php esc_html_e( 'Color', 'wp-spin-wheel' ); ?><br />
                                    <input type="text" name="spin_wheel_setting_button_color" value="<?php echo esc_attr( $editing_item['config']['color'] ?? '#2563eb' ); ?>" class="widefat wp-spin-wheel-color-field" /></label>
                                </p>
                                <p>
                                    <label><?php esc_html_e( 'Text color', 'wp-spin-wheel' ); ?><br />
                                    <input type="text" name="spin_wheel_setting_button_text_color" value="<?php echo esc_attr( $editing_item['config']['text_color'] ?? '#ffffff' ); ?>" class="widefat wp-spin-wheel-color-field" /></label>
                                </p>
                                <p>
                                    <label><?php esc_html_e( 'Radius', 'wp-spin-wheel' ); ?><br />
                                    <input type="number" name="spin_wheel_setting_button_radius" value="<?php echo esc_attr( $editing_item['config']['radius'] ?? 50 ); ?>" class="small-text" /></label>
                                </p>
                                <p>
                                    <label><?php esc_html_e( 'Background image', 'wp-spin-wheel' ); ?><br />
                                    <input type="text" name="spin_wheel_setting_button_background_image" value="<?php echo esc_attr( $editing_item['config']['background_image'] ?? '' ); ?>" class="widefat" /></label>
                                </p>
                            <?php elseif ( 'pointers' === $group_key ) : ?>
                                <p>
                                    <label><?php esc_html_e( 'Image URL', 'wp-spin-wheel' ); ?><br />
                                    <input type="text" name="spin_wheel_setting_pointer_image" value="<?php echo esc_attr( $editing_item['config']['image'] ?? '' ); ?>" class="widefat" /></label>
                                </p>
                                <p>
                                    <label><?php esc_html_e( 'Size', 'wp-spin-wheel' ); ?><br />
                                    <input type="number" name="spin_wheel_setting_pointer_size" value="<?php echo esc_attr( $editing_item['config']['size'] ?? 80 ); ?>" class="small-text" /></label>
                                </p>
                            <?php endif; ?>

                            <p>
                                <button type="submit" class="button button-primary"><?php esc_html_e( 'Save item', 'wp-spin-wheel' ); ?></button>
                                <?php if ( ! empty( $editing_item ) ) : ?>
                                    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'wp-spin-wheel-settings' ) ) ); ?>" class="button"><?php esc_html_e( 'Cancel', 'wp-spin-wheel' ); ?></a>
                                <?php endif; ?>
                            </p>
                        </form>
                        <hr />
                    <?php endforeach; ?>
                </div>
            </div>
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
