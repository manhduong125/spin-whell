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
            // Chỉ seed default khi key chưa tồn tại trong DB, không ghi đè nếu user đã xóa hết
            if ( ! array_key_exists( $group_key, $items ) || ! is_array( $items[ $group_key ] ) ) {
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
            'audios_start' => array(),
            'audios_end'   => array(),
        );
    }

    private function get_group_key_from_type( $type ) {
        $type = is_string( $type ) ? sanitize_key( $type ) : '';

        if ( in_array( $type, array( 'audio_start', 'audios_start', 'spin_wheel_audio_start' ), true ) ) {
            return 'audios_start';
        }

        if ( in_array( $type, array( 'audio_end', 'audios_end', 'spin_wheel_audio_end' ), true ) ) {
            return 'audios_end';
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

        if ( 'audios_start' === $group_key || 'audios_end' === $group_key ) {
            return array(
                'file' => esc_url_raw( wp_unslash( $payload['spin_wheel_setting_audio_file'] ?? '' ) ),
            );
        }

        return array();
    }

    public function handle_setting_item_form() {
        // Chỉ xử lý khi có action field của form này
        if ( empty( $_POST['spin_wheel_setting_item_action'] ) ) {
            return;
        }

        // Verify nonce
        if ( empty( $_POST['spin_wheel_setting_item_nonce'] )
            || ! wp_verify_nonce(
                wp_unslash( $_POST['spin_wheel_setting_item_nonce'] ),
                'spin_wheel_setting_items'
            )
        ) {
            wp_die( esc_html__( 'Nonce không hợp lệ.', 'wp-spin-wheel' ) );
        }

        // Chỉ admin mới được thực hiện
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Không có quyền.', 'wp-spin-wheel' ) );
        }

        $action    = sanitize_text_field( wp_unslash( $_POST['spin_wheel_setting_item_action'] ) );
        $group_key = $this->get_group_key_from_type( wp_unslash( $_POST['spin_wheel_setting_item_group'] ?? '' ) );

        if ( ! in_array( $group_key, array( 'audios_start', 'audios_end' ), true ) ) {
            return;
        }

        // Luôn load TOÀN BỘ option từ DB để tránh race condition
        $all_items = $this->get_item_option();
        // Đảm bảo group key tồn tại và là array
        if ( ! isset( $all_items[ $group_key ] ) || ! is_array( $all_items[ $group_key ] ) ) {
            $all_items[ $group_key ] = array();
        }
        $items = $all_items[ $group_key ];

        $item_id   = sanitize_text_field( wp_unslash( $_POST['spin_wheel_setting_item_id'] ?? '' ) );
        $item_name = sanitize_text_field( wp_unslash( $_POST['spin_wheel_setting_item_name'] ?? '' ) );

        // --- XÓA ---
        if ( 'delete' === $action ) {
            $items = array_values( array_filter( $items, function( $item ) use ( $item_id ) {
                return (string) ( $item['id'] ?? '' ) !== $item_id;
            } ) );
            $all_items[ $group_key ] = $items;
            update_option( self::ITEM_OPTION_KEY, $all_items );
            wp_safe_redirect( add_query_arg(
                array( 'page' => 'wp-spin-wheel-settings', 'updated' => '1' ),
                admin_url( 'admin.php' )
            ) );
            exit;
        }

        // --- THÊM / SỬA ---
        if ( empty( $item_name ) ) {
            wp_safe_redirect( add_query_arg(
                array( 'page' => 'wp-spin-wheel-settings', 'error' => 'empty_name' ),
                admin_url( 'admin.php' )
            ) );
            exit;
        }

        // Tạo ID mới nếu thêm mới (item_id rỗng)
        if ( empty( $item_id ) ) {
            $item_id = $group_key . '-' . uniqid( '', true );
        }

        $config       = $this->build_item_config( $group_key, $_POST );
        $item_payload = array(
            'id'     => $item_id,
            'name'   => $item_name,
            'config' => $config,
        );

        // Tìm và update nếu ID đã tồn tại, ngược lại append
        $found = false;
        foreach ( $items as $index => $item ) {
            if ( (string) ( $item['id'] ?? '' ) === $item_id ) {
                $items[ $index ] = $item_payload;
                $found = true;
                break;
            }
        }
        if ( ! $found ) {
            $items[] = $item_payload;
        }

        // Ghi đúng group và save toàn bộ
        $all_items[ $group_key ] = array_values( $items );
        update_option( self::ITEM_OPTION_KEY, $all_items );

        wp_safe_redirect( add_query_arg(
            array( 'page' => 'wp-spin-wheel-settings', 'updated' => '1' ),
            admin_url( 'admin.php' )
        ) );
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
            'start_sound'             => array( 'type' => 'select',   'default' => '' ),
            'start_sound_file'        => array( 'type' => 'text',     'default' => '' ),
            'end_sound'               => array( 'type' => 'select',   'default' => '' ),
            'end_sound_file'          => array( 'type' => 'text',     'default' => '' ),
            'duration'                => array( 'type' => 'text',     'default' => '6' ),
            'show_confetti'           => array( 'type' => 'checkbox', 'default' => 0 ),
            'auto_remove'             => array( 'type' => 'checkbox', 'default' => 0 ),
            'show_popup'              => array( 'type' => 'checkbox', 'default' => 0 ),
            'popup_label'             => array( 'type' => 'text',     'default' => '' ),
            'show_remove_button'      => array( 'type' => 'checkbox', 'default' => 0 ),
            'show_hide_button'        => array( 'type' => 'checkbox', 'default' => 0 ),
            'switch_cover_img'        => array( 'type' => 'checkbox', 'default' => 0 ),
            'cover_img'               => array( 'type' => 'image',    'default' => '' ),
            'wheel_title'             => array( 'type' => 'text',     'default' => '' ),
            'wheel_description'       => array( 'type' => 'textarea', 'default' => '' ),
            'wheel_background_color'  => array( 'type' => 'color',    'default' => '' ),
            'wheel_button_text'       => array( 'type' => 'text',     'default' => '' ),
            'wheel_button_color'      => array( 'type' => 'color',    'default' => '' ),
            'wheel_button_text_color' => array( 'type' => 'color',    'default' => '' ),
            'wheel_border_color'      => array( 'type' => 'color',    'default' => '' ),
            'wheel_background_image'  => array( 'type' => 'image',    'default' => '' ),
            'wheel_animation_duration'=> array( 'type' => 'text',     'default' => '6' ),
            'wheel_confetti'          => array( 'type' => 'checkbox', 'default' => 0 ),
            'wheel_segment_colors'    => array( 'type' => 'json',     'default' => array() ),
            'wheel_extra_config'      => array( 'type' => 'json',     'default' => array() ),
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

    /**
     * Render các trường input cho từng group, dùng chung cho form thêm và form sửa.
     *
     * @param string $group_key   'audios_start' | 'audios_end'
     * @param array  $cfg         Mảng config hiện tại (rỗng khi thêm mới)
     * @return string HTML các hàng <tr> của form-table
     */
    private function render_item_fields( $group_key, $cfg = array() ) {
        $cfg = is_array( $cfg ) ? $cfg : array();
        $out = '';

        if ( 'audios_start' === $group_key || 'audios_end' === $group_key ) {
            $file = $cfg['file'] ?? '';
            $out .= '<tr><th style="padding:4px 8px;width:160px">' . esc_html__( 'File nhạc (URL)', 'wp-spin-wheel' ) . '</th><td style="padding:4px 8px">';
            $out .= '<input type="text" name="spin_wheel_setting_audio_file" value="' . esc_attr( $file ) . '" class="widefat" placeholder="https://example.com/music.mp3" />';
            $out .= '<small style="display:block;margin-top:4px;color:#666;">' . esc_html__( 'Hỗ trợ: .mp3, .ogg, .wav', 'wp-spin-wheel' ) . '</small>';
            $out .= '</td></tr>';
        }

        return $out;
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $options = $this->get_option();
        $form_action = esc_url( admin_url( 'admin.php?page=wp-spin-wheel-settings' ) );
        ?>
        <div class="wrap wp-spin-wheel-settings-page">
            <h1><?php esc_html_e( 'Spin Wheel Settings', 'wp-spin-wheel' ); ?></h1>

            <?php if ( ! empty( $_GET['updated'] ) ) : ?>
            <div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Đã lưu thành công.', 'wp-spin-wheel' ); ?></p></div>
            <?php endif; ?>
            <?php if ( ! empty( $_GET['error'] ) && $_GET['error'] === 'empty_name' ) : ?>
            <div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Vui lòng nhập tên.', 'wp-spin-wheel' ); ?></p></div>
            <?php endif; ?>

            <div class="post-box" style="margin-top: 24px;">
                <h2 class="hndle"><?php esc_html_e( 'Setting items', 'wp-spin-wheel' ); ?></h2>
                <div class="inside">
                    <?php
                    $group_definitions = array(
                        'audios_start' => array(
                            'title' => __( 'Nhạc bắt đầu', 'wp-spin-wheel' ),
                            'name'  => __( 'Nhạc bắt đầu', 'wp-spin-wheel' ),
                        ),
                        'audios_end' => array(
                            'title' => __( 'Nhạc kết thúc', 'wp-spin-wheel' ),
                            'name'  => __( 'Nhạc kết thúc', 'wp-spin-wheel' ),
                        ),
                    );

                    $editing_group = isset( $_GET['edit_setting_group'] ) ? sanitize_key( wp_unslash( $_GET['edit_setting_group'] ) ) : '';
                    $editing_id    = isset( $_GET['edit_setting_item'] ) ? sanitize_text_field( wp_unslash( $_GET['edit_setting_item'] ) ) : '';
                    $cancel_url    = esc_url( add_query_arg( array( 'page' => 'wp-spin-wheel-settings' ), admin_url( 'admin.php' ) ) );
                    ?>
                    <?php foreach ( $group_definitions as $group_key => $group_info ) :
                        $items = $this->get_setting_items( $group_key );

                        $editing_item = array();
                        if ( $editing_group === $group_key && ! empty( $editing_id ) ) {
                            foreach ( $items as $item ) {
                                if ( (string) ( $item['id'] ?? '' ) === $editing_id ) {
                                    $editing_item = $item;
                                    break;
                                }
                            }
                        }
                        $is_editing = ! empty( $editing_item );
                    ?>
                        <h3 style="border-bottom:1px solid #ddd;padding-bottom:6px;"><?php echo esc_html( $group_info['title'] ); ?></h3>

                        <?php if ( ! empty( $items ) ) : ?>
                            <table class="widefat fixed striped" style="margin-bottom: 16px;">
                                <thead>
                                    <tr>
                                        <th style="width:35%"><?php esc_html_e( 'Tên', 'wp-spin-wheel' ); ?></th>
                                        <th><?php esc_html_e( 'Giá trị', 'wp-spin-wheel' ); ?></th>
                                        <th style="width:140px"><?php esc_html_e( 'Thao tác', 'wp-spin-wheel' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $items as $item ) :
                                        $cfg = $item['config'] ?? array();
                                        $is_this_row_editing = $is_editing && ( (string) ( $item['id'] ?? '' ) === $editing_id );
                                    ?>
                                        <?php if ( $is_this_row_editing ) : ?>
                                            <tr style="background:#fff8e1;">
                                                <td colspan="3" style="padding:12px;">
                                                    <strong><?php esc_html_e( 'Đang sửa:', 'wp-spin-wheel' ); ?> <?php echo esc_html( $item['name'] ?? '' ); ?></strong>
                                                    <form method="post" action="<?php echo $form_action; ?>" style="margin-top:8px;">
                                                        <?php wp_nonce_field( 'spin_wheel_setting_items', 'spin_wheel_setting_item_nonce' ); ?>
                                                        <input type="hidden" name="spin_wheel_setting_item_group" value="<?php echo esc_attr( $group_key ); ?>" />
                                                        <input type="hidden" name="spin_wheel_setting_item_id" value="<?php echo esc_attr( $item['id'] ?? '' ); ?>" />
                                                        <input type="hidden" name="spin_wheel_setting_item_action" value="save" />
                                                        <table class="form-table" style="margin:0;">
                                                            <tr>
                                                                <th style="padding:4px 8px;width:150px"><?php esc_html_e( 'Tên', 'wp-spin-wheel' ); ?></th>
                                                                <td style="padding:4px 8px"><input type="text" name="spin_wheel_setting_item_name" value="<?php echo esc_attr( $item['name'] ?? '' ); ?>" class="widefat" required /></td>
                                                            </tr>
                                                            <?php echo $this->render_item_fields( $group_key, $cfg ); // phpcs:ignore ?>
                                                        </table>
                                                        <p style="margin-top:8px;">
                                                            <button type="submit" class="button button-primary"><?php esc_html_e( 'Lưu', 'wp-spin-wheel' ); ?></button>
                                                            <a href="<?php echo $cancel_url; ?>" class="button"><?php esc_html_e( 'Hủy', 'wp-spin-wheel' ); ?></a>
                                                        </p>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php else : ?>
                                            <tr>
                                                <td><?php echo esc_html( $item['name'] ?? '' ); ?></td>
                                                <td>
                                                    <?php
                                                    if ( 'audios_start' === $group_key || 'audios_end' === $group_key ) {
                                                        $file = $cfg['file'] ?? '';
                                                        if ( $file ) {
                                                            echo '<a href="' . esc_url( $file ) . '" target="_blank">' . esc_html( basename( $file ) ) . '</a>';
                                                        } else {
                                                            echo '<em style="color:#999;">' . esc_html__( 'Chưa có file', 'wp-spin-wheel' ) . '</em>';
                                                        }
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'wp-spin-wheel-settings', 'edit_setting_group' => $group_key, 'edit_setting_item' => $item['id'] ?? '' ), admin_url( 'admin.php' ) ) ); ?>"
                                                        class="button button-small"><?php esc_html_e( 'Sửa', 'wp-spin-wheel' ); ?></a>
                                                    <form method="post" action="<?php echo $form_action; ?>" style="display:inline;margin-left:4px;"
                                                        onsubmit="return confirm('<?php esc_attr_e( 'Xác nhận xóa?', 'wp-spin-wheel' ); ?>')">
                                                        <?php wp_nonce_field( 'spin_wheel_setting_items', 'spin_wheel_setting_item_nonce' ); ?>
                                                        <input type="hidden" name="spin_wheel_setting_item_group" value="<?php echo esc_attr( $group_key ); ?>" />
                                                        <input type="hidden" name="spin_wheel_setting_item_id" value="<?php echo esc_attr( $item['id'] ?? '' ); ?>" />
                                                        <input type="hidden" name="spin_wheel_setting_item_action" value="delete" />
                                                        <button type="submit" class="button button-small" style="color:#b32d2e;"><?php esc_html_e( 'Xóa', 'wp-spin-wheel' ); ?></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else : ?>
                            <p style="color:#999;font-style:italic;"><?php esc_html_e( 'Chưa có mục nào.', 'wp-spin-wheel' ); ?></p>
                        <?php endif; ?>

                        <?php if ( ! $is_editing ) : ?>
                        <details style="margin-bottom:20px;">
                            <summary style="cursor:pointer;font-weight:600;padding:6px 0;color:#2271b1;">
                                &#43; <?php printf( esc_html__( 'Thêm %s mới', 'wp-spin-wheel' ), esc_html( strtolower( $group_info['name'] ) ) ); ?>
                            </summary>
                            <div style="background:#f9f9f9;border:1px solid #ddd;padding:16px;margin-top:8px;border-radius:4px;">
                                <form method="post" action="<?php echo $form_action; ?>">
                                    <?php wp_nonce_field( 'spin_wheel_setting_items', 'spin_wheel_setting_item_nonce' ); ?>
                                    <input type="hidden" name="spin_wheel_setting_item_group" value="<?php echo esc_attr( $group_key ); ?>" />
                                    <input type="hidden" name="spin_wheel_setting_item_id" value="" />
                                    <input type="hidden" name="spin_wheel_setting_item_action" value="save" />
                                    <table class="form-table" style="margin:0;">
                                        <tr>
                                            <th style="padding:4px 8px;width:150px"><?php esc_html_e( 'Tên', 'wp-spin-wheel' ); ?></th>
                                            <td style="padding:4px 8px">
                                                <input type="text" name="spin_wheel_setting_item_name" value="" class="widefat" required
                                                    placeholder="<?php esc_attr_e( 'Nhập tên...', 'wp-spin-wheel' ); ?>" />
                                            </td>
                                        </tr>
                                        <?php echo $this->render_item_fields( $group_key, array() ); // phpcs:ignore ?>
                                    </table>
                                    <p style="margin-top:8px;">
                                        <button type="submit" class="button button-primary"><?php esc_html_e( 'Thêm mới', 'wp-spin-wheel' ); ?></button>
                                    </p>
                                </form>
                            </div>
                        </details>
                        <?php endif; ?>

                        <hr style="margin:16px 0;" />
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
