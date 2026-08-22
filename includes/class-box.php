<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Box {

    /**
     * Default Box Settings
     */
    public static function get_default_settings() {
        $box_global = WP_Spin_Wheel_Helper::get_box_global_settings();
        $default_bg_img = defined('WP_SPIN_WHEEL_URL') ? WP_SPIN_WHEEL_URL . 'assets/img/christmas-2.jpg' : '/wp-content/plugins/spin-whell/assets/img/christmas-2.jpg';
        $defaults = array(
            'title'           => 'HỘP QUÀ MAY MẮN ONLINE',
            'template'        => 'tpl-jib',
            'luotchoi'        => 3,
            'sound'           => 'winner',
            'sound_file'      => '',
            'noti_sound'      => 'concainit',
            'noti_sound_file' => '',
            'popup_title'     => 'Hộp quà có',
            'confetti'        => true,
            'bg_color'        => '#dc3545',
            'color'           => '#ffffff',
            'bg_img'          => $default_bg_img,
            'bg_gradient'     => '',
            'btn_bg_color'    => '#dc3545',
            'btn_color'       => '#ffffff',
            'show_particle'   => true,
        );

        if ( ! empty( $box_global['box_title'] ) ) {
            $defaults['title'] = $box_global['box_title'];
        }
        if ( ! empty( $box_global['template'] ) ) {
            $defaults['template'] = $box_global['template'];
        }
        if ( isset( $box_global['luotchoi'] ) && '' !== $box_global['luotchoi'] ) {
            $defaults['luotchoi'] = absint( $box_global['luotchoi'] );
        }
        if ( ! empty( $box_global['sound'] ) ) {
            $defaults['sound'] = $box_global['sound'];
        }
        if ( isset( $box_global['sound_file'] ) ) {
            $defaults['sound_file'] = $box_global['sound_file'];
        }
        if ( ! empty( $box_global['noti_sound'] ) ) {
            $defaults['noti_sound'] = $box_global['noti_sound'];
        }
        if ( isset( $box_global['noti_sound_file'] ) ) {
            $defaults['noti_sound_file'] = $box_global['noti_sound_file'];
        }
        if ( ! empty( $box_global['popup_title'] ) ) {
            $defaults['popup_title'] = $box_global['popup_title'];
        }
        if ( isset( $box_global['confetti'] ) ) {
            $defaults['confetti'] = ! empty( $box_global['confetti'] );
        }
        if ( ! empty( $box_global['bg_color'] ) ) {
            $defaults['bg_color'] = $box_global['bg_color'];
        }
        if ( ! empty( $box_global['color'] ) ) {
            $defaults['color'] = $box_global['color'];
        }
        if ( ! empty( $box_global['bg_img'] ) ) {
            $defaults['bg_img'] = WP_Spin_Wheel_Helper::resolve_asset_url( $box_global['bg_img'] );
        }
        if ( ! empty( $box_global['bg_gradient'] ) ) {
            $defaults['bg_gradient'] = $box_global['bg_gradient'];
        }
        if ( ! empty( $box_global['btn_bg_color'] ) ) {
            $defaults['btn_bg_color'] = $box_global['btn_bg_color'];
        }
        if ( ! empty( $box_global['btn_color'] ) ) {
            $defaults['btn_color'] = $box_global['btn_color'];
        }
        if ( isset( $box_global['show_particle'] ) ) {
            $defaults['show_particle'] = ! empty( $box_global['show_particle'] );
        }

        return $defaults;
    }

    /**
     * Default Gifts List
     */
    public static function get_default_gifts() {
        $box_global = WP_Spin_Wheel_Helper::get_box_global_settings();
        if ( ! empty( $box_global['default_gifts'] ) ) {
            $lines = array_values( array_filter( array_map( 'trim', explode( "\n", $box_global['default_gifts'] ) ) ) );
            if ( ! empty( $lines ) ) {
                return $lines;
            }
        }
        return array(
            '100k',
            'Ốp lưng iphone',
            '50k',
            'Chúc bạn may mắn',
            '200k',
            'Bút Montblanc',
            'Ví da 500k',
            'Sổ tay',
            'Gối tựa lưng',
            'Bình giữ nhiệt',
            'Ly sứ',
            'Hộp đựng cơm',
        );
    }

    /**
     * Lấy cài đặt Box theo ID
     */
    public static function get_box_settings( $box_id ) {
        $box_id = absint( $box_id );
        if ( ! $box_id ) {
            return self::get_default_settings();
        }

        $raw = get_post_meta( $box_id, '_spin_box_overrides', true );
        if ( empty( $raw ) ) {
            $raw = get_post_meta( $box_id, '_spin_box_design', true );
        }

        $settings = ! empty( $raw ) ? json_decode( $raw, true ) : array();
        if ( ! is_array( $settings ) ) {
            $settings = array();
        }

        // Tự phục hồi chuỗi Unicode bị hỏng dạng "Hu1ed8P" (mất backslash trước uXXXX)
        $settings = self::fix_mangled_unicode( $settings );

        if ( ! empty( $settings['bg_img'] ) ) {
            $settings['bg_img'] = WP_Spin_Wheel_Helper::resolve_asset_url( $settings['bg_img'] );
        }

        return array_replace_recursive( self::get_default_settings(), $settings );
    }

    /**
     * Khôi phục chuỗi Unicode bị lỗi escape dạng "Hu1ed8P QUu00c0"
     * (ký tự \uXXXX mất backslash do wp_unslash/stripslashes chạy sau json_encode)
     *
     * @param mixed $value Giá trị bất kỳ (string/array)
     * @return mixed
     */
    public static function fix_mangled_unicode( $value ) {
        return wp_spin_wheel_fix_mangled_unicode( $value );
    }

    /**
     * Lấy danh sách quà tặng của Box theo ID
     */
    public static function get_box_gifts( $box_id ) {
        $box_id = absint( $box_id );
        if ( ! $box_id ) {
            return self::get_default_gifts();
        }

        $raw = get_post_meta( $box_id, '_spin_box_gifts_json', true );
        if ( ! empty( $raw ) ) {
            $gifts = json_decode( $raw, true );
            if ( is_array( $gifts ) && ! empty( $gifts ) ) {
                return self::fix_mangled_unicode( $gifts );
            }
        }

        // Kiểm tra qua prizes
        $prizes = WP_Spin_Wheel_Prize::get_prizes( $box_id );
        if ( ! empty( $prizes ) && is_array( $prizes ) ) {
            return array_map( function( $p ) {
                return is_array( $p ) ? ( $p['title'] ?? '' ) : $p;
            }, $prizes );
        }

        return self::get_default_gifts();
    }

    /**
     * Tạo mới hộp quà cho User
     */
    public static function create_user_box( $user_id, $title = '', $settings = array(), $gifts = array(), $content = '' ) {
        $user_id = absint( $user_id );
        if ( ! $user_id ) {
            return 0;
        }

        $title = trim( $title ) ?: sprintf( __( 'Hộp quà may mắn (%s)', 'wp-spin-wheel' ), current_time( 'd/m/Y H:i' ) );

        $post_id = wp_insert_post( array(
            'post_title'   => sanitize_text_field( $title ),
            'post_content' => wp_kses_post( $content ),
            'post_status'  => 'publish',
            'post_type'    => 'spin_box',
            'post_author'  => $user_id,
        ) );

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            return 0;
        }

        update_post_meta( $post_id, '_user_id', $user_id );
        update_post_meta( $post_id, '_spin_box_views', 0 );
        update_post_meta( $post_id, '_spin_box_total_opens', 0 );

        if ( ! empty( $settings ) && is_array( $settings ) ) {
            update_post_meta( $post_id, '_spin_box_overrides', wp_json_encode( $settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
            update_post_meta( $post_id, '_spin_box_design', wp_json_encode( $settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
        }

        if ( ! empty( $gifts ) && is_array( $gifts ) ) {
            update_post_meta( $post_id, '_spin_box_gifts_json', wp_json_encode( $gifts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
            // Đồng bộ format prizes
            $prizes_formatted = array_map( function( $g ) {
                $t = is_array( $g ) ? ( $g['title'] ?? '' ) : $g;
                return array( 'title' => $t, 'color' => '#dc3545', 'weight' => 10, 'stock' => 9999 );
            }, $gifts );
            WP_Spin_Wheel_Prize::sync_prizes( $post_id, $prizes_formatted );
        } else {
            $default_gifts = self::get_default_gifts();
            update_post_meta( $post_id, '_spin_box_gifts_json', wp_json_encode( $default_gifts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
        }

        return absint( $post_id );
    }

    /**
     * Nhân bản / Sao chép hộp quà
     */
    public static function duplicate_user_box( $box_id, $user_id, $custom_data = array() ) {
        $box_id  = absint( $box_id );
        $user_id = absint( $user_id );
        if ( ! $user_id ) {
            return 0;
        }

        $title    = '';
        $content  = '';
        $settings = array();
        $gifts    = array();

        if ( $box_id > 0 ) {
            $post = get_post( $box_id );
            if ( $post ) {
                $title    = $post->post_title;
                $content  = $post->post_content;
                $settings = self::get_box_settings( $box_id );
                $gifts    = self::get_box_gifts( $box_id );
            }
        }

        if ( ! empty( $custom_data['title'] ) ) {
            $title = sanitize_text_field( $custom_data['title'] );
        }
        if ( ! empty( $custom_data['content'] ) || ! empty( $custom_data['description'] ) ) {
            $content = wp_kses_post( $custom_data['content'] ?? $custom_data['description'] );
        }
        if ( ! empty( $custom_data['settings'] ) && is_array( $custom_data['settings'] ) ) {
            $settings = $custom_data['settings'];
        }
        if ( ! empty( $custom_data['gifts'] ) && is_array( $custom_data['gifts'] ) ) {
            $gifts = $custom_data['gifts'];
        } elseif ( ! empty( $custom_data['prizes'] ) && is_array( $custom_data['prizes'] ) ) {
            $gifts = array_map( function( $p ) { return is_array($p) ? ($p['title'] ?? '') : $p; }, $custom_data['prizes'] );
        }

        if ( ! empty( $title ) ) {
            $new_title = ( strpos( $title, '(Bản sao)' ) !== false ) ? $title : ( $title . ' (Bản sao)' );
        } else {
            $new_title = sprintf( __( 'Hộp quà (Bản sao %s)', 'wp-spin-wheel' ), current_time( 'd/m/Y H:i' ) );
        }

        return self::create_user_box( $user_id, $new_title, $settings, $gifts, $content );
    }

    /**
     * Xóa hộp quà
     */
    public static function delete_user_box( $box_id, $user_id ) {
        $box_id  = absint( $box_id );
        $user_id = absint( $user_id );
        if ( ! $box_id || ! $user_id ) {
            return false;
        }

        $post = get_post( $box_id );
        if ( ! $post || ( (int) $post->post_author !== $user_id && ! current_user_can( 'manage_options' ) ) ) {
            return false;
        }

        wp_delete_post( $box_id, true );
        return true;
    }

    /**
     * Lấy hoặc tạo hộp quà mặc định cho user
     */
    public static function get_or_create_user_box( $user_id ) {
        $user_id = absint( $user_id );
        if ( ! $user_id ) {
            return 0;
        }

        $boxes = get_posts( array(
            'post_type'      => 'spin_box',
            'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
            'author'         => $user_id,
            'posts_per_page' => 1,
            'orderby'        => 'ID',
            'order'          => 'ASC',
            'fields'         => 'ids',
        ) );

        if ( ! empty( $boxes ) ) {
            return absint( $boxes[0] );
        }

        $user = get_userdata( $user_id );
        $user_name = $user ? ( $user->display_name ?: $user->user_login ) : 'User #' . $user_id;
        $title = sprintf( __( 'Hộp quà của %s', 'wp-spin-wheel' ), $user_name );

        return self::create_user_box( $user_id, $title, self::get_default_settings(), self::get_default_gifts() );
    }

    /**
     * Lấy danh sách hộp quà của user
     */
    public static function get_user_boxes( $user_id, $args = array() ) {
        $user_id = absint( $user_id );
        if ( ! $user_id ) {
            return array();
        }

        $defaults = array(
            'post_type'      => 'spin_box',
            'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
            'author'         => $user_id,
            'posts_per_page' => 20,
            'orderby'        => 'ID',
            'order'          => 'DESC',
        );

        $query = new WP_Query( wp_parse_args( $args, $defaults ) );
        $list = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $pid = get_the_ID();
                $list[] = array(
                    'id'           => $pid,
                    'title'        => wp_spin_wheel_fix_mangled_unicode( get_the_title() ),
                    'permalink'    => get_permalink(),
                    'created_at'   => get_the_date( 'd/m/Y H:i', $pid ),
                    'gift_count'   => count( self::get_box_gifts( $pid ) ),
                    'settings'     => self::get_box_settings( $pid ),
                );
            }
            wp_reset_postdata();
        }

        return $list;
    }
}
