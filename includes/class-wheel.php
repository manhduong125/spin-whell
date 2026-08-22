<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Wheel {
    public function __construct() {
        add_action( 'wp_ajax_spin_wheel_spin', array( $this, 'ajax_spin' ) );
        add_action( 'wp_ajax_nopriv_spin_wheel_spin', array( $this, 'ajax_spin' ) );
    }

    public function ajax_spin() {
        check_ajax_referer( 'spin_wheel_nonce', 'nonce' );

        $wheel_id = absint( $_POST['wheel_id'] ?? 0 );
        $form_data = isset( $_POST['form'] ) ? wp_unslash( $_POST['form'] ) : array();

        $result = self::spin_wheel( $wheel_id, $form_data );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        wp_send_json_success( array( 'prize' => $result ) );
    }

    public static function spin_wheel( $wheel_id, $form_data = array(), $client_prizes = array() ) {
        $wheel_id = absint( $wheel_id );
        $settings = $wheel_id ? WP_Spin_Wheel_Helper::get_wheel_settings( $wheel_id ) : array();
        $form_data = is_array( $form_data ) ? $form_data : array();

        if ( $wheel_id ) {
            $validation = self::validate_spin_request( $wheel_id, $settings, $form_data );
            if ( is_wp_error( $validation ) ) {
                return $validation;
            }
        }

        $prizes = $wheel_id ? WP_Spin_Wheel_Prize::get_prizes( $wheel_id ) : array();

        // Nếu DB chưa có giải nhưng client gửi danh sách giải thưởng lên
        if ( empty( $prizes ) && ! empty( $client_prizes ) && is_array( $client_prizes ) ) {
            $prizes = $client_prizes;
            if ( $wheel_id ) {
                WP_Spin_Wheel_Prize::sync_prizes( $wheel_id, $client_prizes );
                update_post_meta( $wheel_id, '_spin_wheel_prizes_json', wp_json_encode( $client_prizes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
                $prizes = WP_Spin_Wheel_Prize::get_prizes( $wheel_id ) ?: $client_prizes;
            }
        }

        // Fallback tạo giải thưởng mặc định nếu chưa có
        if ( empty( $prizes ) ) {
            $default_prizes = array(
                array( 'title' => 'Giải 1', 'color' => '#f87171', 'weight' => 10, 'stock' => 9999 ),
                array( 'title' => 'Giải 2', 'color' => '#60a5fa', 'weight' => 10, 'stock' => 9999 ),
                array( 'title' => 'Giải 3', 'color' => '#34d399', 'weight' => 10, 'stock' => 9999 ),
                array( 'title' => 'Giải 4', 'color' => '#fbbf24', 'weight' => 10, 'stock' => 9999 ),
                array( 'title' => 'Giải 5', 'color' => '#a78bfa', 'weight' => 10, 'stock' => 9999 ),
                array( 'title' => 'Giải 6', 'color' => '#f472b6', 'weight' => 10, 'stock' => 9999 ),
            );
            if ( $wheel_id ) {
                WP_Spin_Wheel_Prize::sync_prizes( $wheel_id, $default_prizes );
                update_post_meta( $wheel_id, '_spin_wheel_prizes_json', wp_json_encode( $default_prizes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
                $prizes = WP_Spin_Wheel_Prize::get_prizes( $wheel_id ) ?: $default_prizes;
            } else {
                $prizes = $default_prizes;
            }
        }

        $winner = WP_Spin_Wheel_Random::pick_prize( $prizes );
        if ( ! $winner ) {
            $winner = $prizes[ array_rand( $prizes ) ];
        }

        if ( ! empty( $winner['id'] ) ) {
            WP_Spin_Wheel_Prize::decrease_stock( $winner['id'] );
        }

        if ( $wheel_id ) {
            $history = new WP_Spin_Wheel_History();
            $history->record_spin( $wheel_id, $winner['id'] ?? 0, $form_data );
        }

        return $winner;
    }

    private static function validate_spin_request( $wheel_id, $settings, $form_data ) {
        $required_fields = is_array( $settings['form_fields'] ?? array() ) ? $settings['form_fields'] : array();
        foreach ( $required_fields as $field ) {
            $value = isset( $form_data[ $field ] ) ? trim( $form_data[ $field ] ) : '';
            if ( '' === $value ) {
                return new WP_Error( 'missing_field', sprintf( __( 'Field %s is required.', 'wp-spin-wheel' ), esc_html( $field ) ), array( 'status' => 400 ) );
            }
        }

        $limit = absint( $settings['spin_limit'] ?? 0 );
        $limit_type = $settings['spin_limit_type'] ?? 'none';
        if ( $limit > 0 && $limit_type !== 'none' ) {
            $limit_check = self::check_spin_limit( $wheel_id, $limit_type, $limit, $form_data );
            if ( is_wp_error( $limit_check ) ) {
                return $limit_check;
            }
        }

        return true;
    }

    private static function check_spin_limit( $wheel_id, $limit_type, $limit, $form_data ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spin_history';

        switch ( $limit_type ) {
            case 'per_email':
                $email = sanitize_email( $form_data['email'] ?? '' );
                if ( empty( $email ) ) {
                    return new WP_Error( 'missing_email', __( 'Email is required to spin.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
                }
                $count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE wheel_id = %d AND email = %s", $wheel_id, $email ) );
                break;
            case 'per_phone':
                $phone = sanitize_text_field( $form_data['phone'] ?? '' );
                if ( empty( $phone ) ) {
                    return new WP_Error( 'missing_phone', __( 'Phone is required to spin.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
                }
                $count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE wheel_id = %d AND phone = %s", $wheel_id, $phone ) );
                break;
            case 'per_cookie':
                $cookie_id = sanitize_text_field( $form_data['cookie_id'] ?? ( $_COOKIE['wp_spin_wheel_cookie'] ?? '' ) );
                if ( empty( $cookie_id ) ) {
                    return new WP_Error( 'missing_cookie', __( 'Cookie identifier is required to spin.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
                }
                $count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE wheel_id = %d AND cookie = %s", $wheel_id, $cookie_id ) );
                break;
            case 'per_ip':
            default:
                $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
                if ( empty( $ip ) ) {
                    return new WP_Error( 'missing_ip', __( 'Unable to detect your IP address.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
                }
                $count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE wheel_id = %d AND ip = %s", $wheel_id, $ip ) );
                break;
        }

                if ( $count >= $limit ) {
                    return new WP_Error( 'spin_limit_reached', __( 'You have reached the spin limit for this wheel.', 'wp-spin-wheel' ), array( 'status' => 429 ) );
                }
                return true;
            }

            /**
             * Lấy hoặc tạo bài viết spin_wheel duy nhất cho mỗi User ID
             *
             * @param int $user_id
             * @return int Wheel Post ID
             */
            public static function get_or_create_user_wheel( $user_id ) {
                $user_id = absint( $user_id );
                if ( ! $user_id ) {
                    return 0;
                }

                // Tìm bài viết spin_wheel do user này tạo
                $args = array(
                    'post_type'      => 'spin_wheel',
                    'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
                    'author'         => $user_id,
                    'posts_per_page' => 1,
                    'orderby'        => 'ID',
                    'order'          => 'ASC',
                    'fields'         => 'ids',
                );
                $wheels = get_posts( $args );

                if ( ! empty( $wheels ) ) {
                    return absint( $wheels[0] );
                }

                // Kiểm tra theo meta _user_id nếu có
                $meta_wheels = get_posts( array(
                    'post_type'      => 'spin_wheel',
                    'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
                    'posts_per_page' => 1,
                    'meta_key'       => '_user_id',
                    'meta_value'     => $user_id,
                    'fields'         => 'ids',
                ) );

                if ( ! empty( $meta_wheels ) ) {
                    return absint( $meta_wheels[0] );
                }

                // Chưa có -> Tạo mới 1 vòng quay cho user này
                $user = get_userdata( $user_id );
                $user_name = $user ? ( $user->display_name ?: $user->user_login ) : 'User #' . $user_id;
                $title = sprintf( __( 'Vòng quay của %s', 'wp-spin-wheel' ), $user_name );

                $post_id = wp_insert_post( array(
                    'post_title'   => $title,
                    'post_content' => '',
                    'post_status'  => 'publish',
                    'post_type'    => 'spin_wheel',
                    'post_author'  => $user_id,
                ) );

                if ( is_wp_error( $post_id ) || ! $post_id ) {
                    return 0;
                }

                update_post_meta( $post_id, '_user_id', $user_id );

                // Khởi tạo giải thưởng mặc định ban đầu cho bài viết wheel mới
                $default_prizes = array(
                    array( 'title' => 'Giải 1', 'color' => '#f87171', 'weight' => 10, 'stock' => 9999 ),
                    array( 'title' => 'Giải 2', 'color' => '#60a5fa', 'weight' => 10, 'stock' => 9999 ),
                    array( 'title' => 'Giải 3', 'color' => '#34d399', 'weight' => 10, 'stock' => 9999 ),
                    array( 'title' => 'Giải 4', 'color' => '#fbbf24', 'weight' => 10, 'stock' => 9999 ),
                    array( 'title' => 'Giải 5', 'color' => '#a78bfa', 'weight' => 10, 'stock' => 9999 ),
                    array( 'title' => 'Giải 6', 'color' => '#f472b6', 'weight' => 10, 'stock' => 9999 ),
                );
                WP_Spin_Wheel_Prize::sync_prizes( $post_id, $default_prizes );
                update_post_meta( $post_id, '_spin_wheel_prizes_json', wp_json_encode( $default_prizes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );

                return absint( $post_id );
            }

            /**
             * Lấy danh sách tất cả vòng quay của một User
             *
             * @param int $user_id
             * @return array
             */
            public static function get_user_wheels( $user_id ) {
                $user_id = absint( $user_id );
                if ( ! $user_id ) {
                    return array();
                }

                $args = array(
                    'post_type'      => 'spin_wheel',
                    'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
                    'author'         => $user_id,
                    'posts_per_page' => 50,
                    'orderby'        => 'ID',
                    'order'          => 'DESC',
                );
                $posts = get_posts( $args );
                $list = array();

                foreach ( $posts as $p ) {
                    $prizes = WP_Spin_Wheel_Prize::get_prizes( $p->ID );
                    $list[] = array(
                        'id'           => $p->ID,
                        'title'        => $p->post_title ?: sprintf( __( 'Vòng quay #%d', 'wp-spin-wheel' ), $p->ID ),
                        'created_at'   => get_the_date( 'd/m/Y H:i', $p->ID ),
                        'prizes_count' => is_array( $prizes ) ? count( $prizes ) : 0,
                        'shortcode'    => '[spin_wheel id="' . $p->ID . '"]',
                        'permalink'    => get_permalink( $p->ID ),
                    );
                }

                return $list;
            }

            /**
             * Tạo mới vòng quay cho User
             *
             * @param int $user_id
             * @param string $title
             * @param array $settings
             * @param array $prizes
             * @param string $content
             * @return int Wheel Post ID
             */
            public static function create_user_wheel( $user_id, $title = '', $settings = array(), $prizes = array(), $content = '' ) {
                $user_id = absint( $user_id );
                if ( ! $user_id ) {
                    return 0;
                }

                $title = trim( $title ) ?: sprintf( __( 'Vòng quay mới (%s)', 'wp-spin-wheel' ), current_time( 'd/m/Y H:i' ) );

                $post_id = wp_insert_post( array(
                    'post_title'   => sanitize_text_field( $title ),
                    'post_content' => wp_kses_post( $content ),
                    'post_status'  => 'publish',
                    'post_type'    => 'spin_wheel',
                    'post_author'  => $user_id,
                ) );

                if ( is_wp_error( $post_id ) || ! $post_id ) {
                    return 0;
                }

                update_post_meta( $post_id, '_user_id', $user_id );
                update_post_meta( $post_id, '_spin_wheel_views', 0 );
                update_post_meta( $post_id, '_spin_wheel_total_spins', 0 );

                if ( ! empty( $settings ) && is_array( $settings ) ) {
                    update_post_meta( $post_id, '_spin_wheel_overrides', wp_json_encode( $settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
                    update_post_meta( $post_id, '_spin_wheel_design', wp_json_encode( $settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
                }

                if ( ! empty( $prizes ) && is_array( $prizes ) ) {
                    WP_Spin_Wheel_Prize::sync_prizes( $post_id, $prizes );
                    update_post_meta( $post_id, '_spin_wheel_prizes_json', wp_json_encode( $prizes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
                } else {
                    $default_prizes = array(
                        array( 'title' => 'Giải 1', 'color' => '#f87171', 'weight' => 10, 'stock' => 9999 ),
                        array( 'title' => 'Giải 2', 'color' => '#60a5fa', 'weight' => 10, 'stock' => 9999 ),
                        array( 'title' => 'Giải 3', 'color' => '#34d399', 'weight' => 10, 'stock' => 9999 ),
                        array( 'title' => 'Giải 4', 'color' => '#fbbf24', 'weight' => 10, 'stock' => 9999 ),
                        array( 'title' => 'Giải 5', 'color' => '#a78bfa', 'weight' => 10, 'stock' => 9999 ),
                        array( 'title' => 'Giải 6', 'color' => '#f472b6', 'weight' => 10, 'stock' => 9999 ),
                    );
                    WP_Spin_Wheel_Prize::sync_prizes( $post_id, $default_prizes );
                    update_post_meta( $post_id, '_spin_wheel_prizes_json', wp_json_encode( $default_prizes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
                }

                return absint( $post_id );
            }

            /**
             * Nhân bản / Sao chép vòng quay cho user
             *
             * @param int $wheel_id
             * @param int $user_id
             * @param array $custom_data
             * @return int New Wheel Post ID
             */
            public static function duplicate_user_wheel( $wheel_id, $user_id, $custom_data = array() ) {
                $wheel_id = absint( $wheel_id );
                $user_id  = absint( $user_id );
                if ( ! $user_id ) {
                    return 0;
                }

                $title    = '';
                $content  = '';
                $settings = array();
                $prizes   = array();

                if ( $wheel_id > 0 ) {
                    $post = get_post( $wheel_id );
                    if ( $post ) {
                        $title    = $post->post_title;
                        $content  = $post->post_content;
                        $settings = WP_Spin_Wheel_Helper::get_wheel_overrides( $wheel_id );
                        $prizes   = WP_Spin_Wheel_Prize::get_prizes( $wheel_id );
                    }
                }

                // Nhận ghi đè từ custom_data từ client nếu có
                if ( ! empty( $custom_data['title'] ) ) {
                    $title = sanitize_text_field( $custom_data['title'] );
                }
                if ( ! empty( $custom_data['description'] ) || ! empty( $custom_data['content'] ) ) {
                    $content = wp_kses_post( $custom_data['description'] ?? $custom_data['content'] );
                }
                if ( ! empty( $custom_data['settings'] ) && is_array( $custom_data['settings'] ) ) {
                    $settings = $custom_data['settings'];
                }
                if ( ! empty( $custom_data['prizes'] ) && is_array( $custom_data['prizes'] ) ) {
                    $prizes = $custom_data['prizes'];
                }

                if ( ! empty( $title ) ) {
                    $new_title = ( strpos( $title, '(Bản sao)' ) !== false ) ? $title : ( $title . ' (Bản sao)' );
                } else {
                    $new_title = sprintf( __( 'Vòng quay (Bản sao %s)', 'wp-spin-wheel' ), current_time( 'd/m/Y H:i' ) );
                }

                return self::create_user_wheel( $user_id, $new_title, $settings, $prizes, $content );
            }

            /**
             * Xóa vòng quay của user
             *
             * @param int $wheel_id
             * @param int $user_id
             * @return bool
             */
            public static function delete_user_wheel( $wheel_id, $user_id ) {
                $wheel_id = absint( $wheel_id );
                $user_id  = absint( $user_id );
                if ( ! $wheel_id || ! $user_id ) {
                    return false;
                }

                $post = get_post( $wheel_id );
                if ( ! $post || (int) $post->post_author !== $user_id ) {
                    return false;
                }

                wp_delete_post( $wheel_id, true );
                return true;
            }
        }
