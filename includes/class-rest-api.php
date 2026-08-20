<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_REST_API {
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes() {
        register_rest_route( 'spin-wheel/v1', '/wheels/(?P<id>\d+)', array(
            'methods'  => 'GET',
            'callback' => array( $this, 'get_wheel' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( 'spin-wheel/v1', '/wheel/(?P<id>\d+)', array(
            'methods'  => 'GET',
            'callback' => array( $this, 'get_wheel' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( 'spin-wheel/v1', '/wheels/(?P<id>\d+)/spin', array(
            'methods'  => 'POST',
            'callback' => array( $this, 'post_spin' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( 'spin-wheel/v1', '/spin', array(
            'methods'  => 'POST',
            'callback' => array( $this, 'post_spin' ),
            'permission_callback' => '__return_true',
        ) );

        // CRUD for wheels - allow authenticated users to create/manage their wheels
        register_rest_route( 'spin-wheel/v1', '/wheels', array(
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'create_wheel' ),
                'permission_callback' => function() { return is_user_logged_in(); },
            ),
        ) );

        register_rest_route( 'spin-wheel/v1', '/wheels/(?P<id>\d+)', array(
            array(
                'methods'             => 'PUT',
                'callback'            => array( $this, 'update_wheel' ),
                'permission_callback' => function() { return is_user_logged_in(); },
            ),
            array(
                'methods'             => 'DELETE',
                'callback'            => array( $this, 'delete_wheel' ),
                'permission_callback' => function() { return is_user_logged_in(); },
            ),
        ) );

        // User wheel for logged-in users (1 user = 1 spin_wheel post)
        register_rest_route( 'spin-wheel/v1', '/user-wheel', array(
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_user_wheel' ),
                'permission_callback' => function() { return is_user_logged_in(); },
            ),
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'save_user_wheel' ),
                'permission_callback' => function() { return is_user_logged_in(); },
            ),
        ) );

        // Danh sách và quản lý vòng quay của User
        register_rest_route( 'spin-wheel/v1', '/user/wheels', array(
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_user_wheels_list' ),
                'permission_callback' => function() { return is_user_logged_in(); },
            ),
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'create_user_wheel_route' ),
                'permission_callback' => function() { return is_user_logged_in(); },
            ),
        ) );

        register_rest_route( 'spin-wheel/v1', '/user/wheels/(?P<id>\d+)', array(
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_single_user_wheel' ),
                'permission_callback' => function() { return is_user_logged_in(); },
            ),
            array(
                'methods'             => array( 'POST', 'PUT', 'PATCH' ),
                'callback'            => array( $this, 'update_single_user_wheel' ),
                'permission_callback' => function() { return is_user_logged_in(); },
            ),
            array(
                'methods'             => 'DELETE',
                'callback'            => array( $this, 'delete_user_wheel_route' ),
                'permission_callback' => function() { return is_user_logged_in(); },
            ),
        ) );

        register_rest_route( 'spin-wheel/v1', '/user/wheels/(?P<id>\d+)/duplicate', array(
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'duplicate_user_wheel_route' ),
                'permission_callback' => function() { return is_user_logged_in(); },
            ),
        ) );

        register_rest_route( 'spin-wheel/v1', '/user/wheels/copy', array(
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'duplicate_user_wheel_route' ),
                'permission_callback' => function() { return is_user_logged_in(); },
            ),
        ) );
        // User options for logged-in users
        register_rest_route( 'spin-wheel/v1', '/users/me/options', array(
            array(
                'methods' => 'GET',
                'callback' => array( $this, 'get_user_options' ),
                'permission_callback' => function() { return is_user_logged_in(); },
            ),
            array(
                'methods' => 'POST',
                'callback' => array( $this, 'save_user_options' ),
                'permission_callback' => function() { return is_user_logged_in(); },
            ),
        ) );

        // Cập nhật thông tin profile của user
        register_rest_route( 'spin-wheel/v1', '/user/profile', array(
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'update_user_profile' ),
                'permission_callback' => function() { return is_user_logged_in(); },
            ),
        ) );

        // Lịch sử quay (Spin History) - lấy danh sách, phân trang, lọc theo wheel/email/ngày
        register_rest_route( 'spin-wheel/v1', '/history', array(
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_history' ),
                'permission_callback' => '__return_true',
            ),
            array(
                'methods'             => 'DELETE',
                'callback'            => array( $this, 'clear_history' ),
                'permission_callback' => function() {
                    return current_user_can( 'manage_options' ) || is_user_logged_in();
                },
            ),
        ) );

        register_rest_route( 'spin-wheel/v1', '/wheels/(?P<id>\d+)/history', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_wheel_history' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( 'spin-wheel/v1', '/history/(?P<id>\d+)', array(
            'methods'             => 'DELETE',
            'callback'            => array( $this, 'delete_history_item' ),
            'permission_callback' => function() {
                return current_user_can( 'manage_options' ) || is_user_logged_in();
            },
        ) );

        // Danh sách người trúng gần đây cho widget / shortcode / popup
        register_rest_route( 'spin-wheel/v1', '/recent-winners', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_recent_winners' ),
            'permission_callback' => '__return_true',
        ) );

        // Thống kê chi tiết của vòng quay (views, creator, total_spins, max_spins)
        register_rest_route( 'spin-wheel/v1', '/wheels/(?P<id>\d+)/stats', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_wheel_stats_route' ),
            'permission_callback' => '__return_true',
        ) );

        // Tăng lượt xem vòng quay
        register_rest_route( 'spin-wheel/v1', '/wheels/(?P<id>\d+)/view', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'increment_wheel_views_route' ),
            'permission_callback' => '__return_true',
        ) );
    }

    public function get_wheel( $request ) {
        $wheel_id = absint( $request['id'] );
        if ( ! $wheel_id || get_post_type( $wheel_id ) !== 'spin_wheel' ) {
            return new WP_Error( 'invalid_wheel', __( 'Wheel not found.', 'wp-spin-wheel' ), array( 'status' => 404 ) );
        }

        $settings = WP_Spin_Wheel_Helper::get_wheel_settings( $wheel_id );
        $prizes = WP_Spin_Wheel_Prize::get_prizes( $wheel_id );

        return rest_ensure_response( array(
            'id'          => $wheel_id,
            'title'       => get_the_title( $wheel_id ),
            'description' => get_post_field( 'post_content', $wheel_id ),
            'settings'    => $settings,
            'prizes'      => $this->get_safe_prizes( $prizes ),
        ) );
    }

    public function post_spin( $request ) {
        $wheel_id = absint( $request->get_param( 'id' ) ?: $request->get_param( 'wheel_id' ) );
        if ( ! $wheel_id ) {
            return new WP_Error( 'invalid_wheel', __( 'Invalid wheel.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
        }

        if ( ! $this->verify_nonce( $request ) ) {
            return new WP_Error( 'invalid_nonce', __( 'Invalid nonce.', 'wp-spin-wheel' ), array( 'status' => 403 ) );
        }

        $form_data = $request->get_param( 'form' );
        if ( is_string( $form_data ) ) {
            $form_data = json_decode( $form_data, true );
        }
        $form_data = is_array( $form_data ) ? $form_data : array();

        $client_prizes = $request->get_param( 'prizes' );
        if ( is_string( $client_prizes ) ) {
            $client_prizes = json_decode( $client_prizes, true );
        }
        $client_prizes = is_array( $client_prizes ) ? $client_prizes : array();

        $winner = WP_Spin_Wheel_Wheel::spin_wheel( $wheel_id, $form_data, $client_prizes );
        if ( is_wp_error( $winner ) ) {
            return $winner;
        }

        return rest_ensure_response( array( 'prize' => $this->format_prize( $winner ) ) );
    }

    public function create_wheel( $request ) {
        $user_id = get_current_user_id();
        $title = sanitize_text_field( $request->get_param( 'title' ) ?? '' );
        $content = $request->get_param( 'content' ) ?? '';
        $settings = $request->get_param( 'settings' ) ?? array();
        $prizes = $request->get_param( 'prizes' ) ?? array();

        if ( empty( $title ) ) {
            return new WP_Error( 'missing_title', __( 'Title is required.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
        }

        $post_id = wp_insert_post( array(
            'post_title'   => $title,
            'post_content' => wp_kses_post( $content ),
            'post_status'  => 'publish',
            'post_type'    => 'spin_wheel',
            'post_author'  => $user_id,
        ) );

        if ( is_wp_error( $post_id ) || ! $post_id ) {
            return new WP_Error( 'create_failed', __( 'Unable to create wheel.', 'wp-spin-wheel' ), array( 'status' => 500 ) );
        }

        if ( ! empty( $settings['overrides'] ) && is_array( $settings['overrides'] ) ) {
            update_post_meta( $post_id, '_spin_wheel_overrides', wp_json_encode( $settings['overrides'] ) );
        }

        // sync prizes to db
        $this->sync_prizes_db( $post_id, $prizes );

        return rest_ensure_response( array( 'id' => $post_id ) );
    }

    public function update_wheel( $request ) {
        $wheel_id = absint( $request['id'] );
        $post = get_post( $wheel_id );
        if ( ! $post || $post->post_type !== 'spin_wheel' ) {
            return new WP_Error( 'invalid_wheel', __( 'Wheel not found.', 'wp-spin-wheel' ), array( 'status' => 404 ) );
        }

        $current = wp_get_current_user();
        if ( ! current_user_can( 'manage_options' ) && intval( $post->post_author ) !== intval( $current->ID ) ) {
            return new WP_Error( 'forbidden', __( 'You are not allowed to edit this wheel.', 'wp-spin-wheel' ), array( 'status' => 403 ) );
        }

        $title = $request->get_param( 'title' );
        $content = $request->get_param( 'content' );
        $settings = $request->get_param( 'settings' );
        $prizes = $request->get_param( 'prizes' );

        $update = array( 'ID' => $wheel_id );
        if ( isset( $title ) ) {
            $update['post_title'] = sanitize_text_field( $title );
        }
        if ( isset( $content ) ) {
            $update['post_content'] = wp_kses_post( $content );
        }
        wp_update_post( $update );

        if ( isset( $settings['overrides'] ) && is_array( $settings['overrides'] ) ) {
            update_post_meta( $wheel_id, '_spin_wheel_overrides', wp_json_encode( $settings['overrides'] ) );
        }

        if ( is_array( $prizes ) ) {
            $this->sync_prizes_db( $wheel_id, $prizes );
        }

        return rest_ensure_response( array( 'id' => $wheel_id ) );
    }

    public function delete_wheel( $request ) {
        $wheel_id = absint( $request['id'] );
        $post = get_post( $wheel_id );
        if ( ! $post || $post->post_type !== 'spin_wheel' ) {
            return new WP_Error( 'invalid_wheel', __( 'Wheel not found.', 'wp-spin-wheel' ), array( 'status' => 404 ) );
        }

        $current = wp_get_current_user();
        if ( ! current_user_can( 'manage_options' ) && intval( $post->post_author ) !== intval( $current->ID ) ) {
            return new WP_Error( 'forbidden', __( 'You are not allowed to delete this wheel.', 'wp-spin-wheel' ), array( 'status' => 403 ) );
        }

        wp_delete_post( $wheel_id, true );
        global $wpdb;
        $table = $wpdb->prefix . 'spin_prizes';
        $wpdb->delete( $table, array( 'wheel_id' => $wheel_id ), array( '%d' ) );

        return rest_ensure_response( array( 'deleted' => true ) );
    }

    public function get_user_wheel() {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return new WP_Error( 'not_logged_in', __( 'User not logged in.', 'wp-spin-wheel' ), array( 'status' => 401 ) );
        }

        $wheel_id = WP_Spin_Wheel_Wheel::get_or_create_user_wheel( $user_id );
        if ( ! $wheel_id ) {
            return new WP_Error( 'not_found', __( 'Wheel not found.', 'wp-spin-wheel' ), array( 'status' => 404 ) );
        }

        $settings = WP_Spin_Wheel_Helper::get_wheel_overrides( $wheel_id );
        $prizes   = WP_Spin_Wheel_Prize::get_prizes( $wheel_id );

        return rest_ensure_response( array(
            'id'          => $wheel_id,
            'title'       => get_the_title( $wheel_id ),
            'description' => get_post_field( 'post_content', $wheel_id ),
            'settings'    => $settings,
            'prizes'      => $prizes,
        ) );
    }

    public function save_user_wheel( $request ) {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return new WP_Error( 'not_logged_in', __( 'User not logged in.', 'wp-spin-wheel' ), array( 'status' => 401 ) );
        }

        // Lấy đúng wheel_id được gửi từ client hoặc fallback về vòng quay mặc định của user
        $target_id = absint( $request->get_param( 'wheel_id' ) ?: ( $request->get_param( 'id' ) ?: 0 ) );
        $wheel_id = 0;
        if ( $target_id ) {
            $post = get_post( $target_id );
            if ( $post && $post->post_type === 'spin_wheel' && ( (int) $post->post_author === $user_id || current_user_can( 'manage_options' ) ) ) {
                $wheel_id = $target_id;
            }
        }
        if ( ! $wheel_id ) {
            $wheel_id = WP_Spin_Wheel_Wheel::get_or_create_user_wheel( $user_id );
        }

        if ( ! $wheel_id ) {
            return new WP_Error( 'create_failed', __( 'Unable to find or create wheel for user.', 'wp-spin-wheel' ), array( 'status' => 500 ) );
        }

        $title       = $request->get_param( 'title' );
        $description = $request->get_param( 'description' );
        $settings    = $request->get_param( 'settings' );
        $prizes      = $request->get_param( 'prizes' );

        $update_post = array( 'ID' => $wheel_id );
        if ( isset( $title ) && '' !== trim( $title ) ) {
            $update_post['post_title'] = sanitize_text_field( $title );
        }
        if ( isset( $description ) ) {
            $update_post['post_content'] = wp_kses_post( $description );
        }
        wp_update_post( $update_post );

        if ( ! empty( $settings ) && is_array( $settings ) ) {
            update_post_meta( $wheel_id, '_spin_wheel_overrides', wp_json_encode( $settings ) );
            update_post_meta( $wheel_id, '_spin_wheel_design', wp_json_encode( $settings ) );
        }

        if ( is_array( $prizes ) ) {
            $this->sync_prizes_db( $wheel_id, $prizes );
            update_post_meta( $wheel_id, '_spin_wheel_prizes_json', wp_json_encode( $prizes ) );
        }

        return rest_ensure_response( array(
            'success'  => true,
            'wheel_id' => $wheel_id,
            'title'    => get_the_title( $wheel_id ),
            'message'  => __( 'Đã lưu vòng quay thành công vào tài khoản của bạn.', 'wp-spin-wheel' ),
        ) );
    }

    public function get_user_wheels_list() {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return new WP_Error( 'not_logged_in', __( 'User not logged in.', 'wp-spin-wheel' ), array( 'status' => 401 ) );
        }
        $wheels = WP_Spin_Wheel_Wheel::get_user_wheels( $user_id );
        return rest_ensure_response( $wheels );
    }

    public function create_user_wheel_route( $request ) {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return new WP_Error( 'not_logged_in', __( 'User not logged in.', 'wp-spin-wheel' ), array( 'status' => 401 ) );
        }
        $title    = $request->get_param( 'title' ) ?? '';
        $settings = $request->get_param( 'settings' ) ?? array();
        $prizes   = $request->get_param( 'prizes' ) ?? array();

        $wheel_id = WP_Spin_Wheel_Wheel::create_user_wheel( $user_id, $title, $settings, $prizes );
        if ( ! $wheel_id ) {
            return new WP_Error( 'create_failed', __( 'Unable to create new wheel.', 'wp-spin-wheel' ), array( 'status' => 500 ) );
        }

        return rest_ensure_response( array(
            'success'  => true,
            'wheel_id' => $wheel_id,
            'title'    => get_the_title( $wheel_id ),
            'message'  => __( 'Đã tạo vòng quay mới thành công.', 'wp-spin-wheel' ),
        ) );
    }

    public function get_single_user_wheel( $request ) {
        $user_id = get_current_user_id();
        $wheel_id = absint( $request['id'] );
        $post = get_post( $wheel_id );
        if ( ! $post || $post->post_type !== 'spin_wheel' || (int) $post->post_author !== $user_id ) {
            return new WP_Error( 'not_found', __( 'Wheel not found or access denied.', 'wp-spin-wheel' ), array( 'status' => 404 ) );
        }

        $settings = WP_Spin_Wheel_Helper::get_wheel_overrides( $wheel_id );
        $prizes   = WP_Spin_Wheel_Prize::get_prizes( $wheel_id );

        return rest_ensure_response( array(
            'id'          => $wheel_id,
            'title'       => get_the_title( $wheel_id ),
            'description' => get_post_field( 'post_content', $wheel_id ),
            'settings'    => $settings,
            'prizes'      => $prizes,
        ) );
    }

    public function update_single_user_wheel( $request ) {
        $user_id = get_current_user_id();
        $wheel_id = absint( $request['id'] );
        $post = get_post( $wheel_id );
        if ( ! $post || $post->post_type !== 'spin_wheel' || (int) $post->post_author !== $user_id ) {
            return new WP_Error( 'not_found', __( 'Wheel not found or access denied.', 'wp-spin-wheel' ), array( 'status' => 404 ) );
        }

        $title       = $request->get_param( 'title' );
        $description = $request->get_param( 'description' );
        $settings    = $request->get_param( 'settings' );
        $prizes      = $request->get_param( 'prizes' );

        $update_post = array( 'ID' => $wheel_id );
        if ( isset( $title ) && '' !== trim( $title ) ) {
            $update_post['post_title'] = sanitize_text_field( $title );
        }
        if ( isset( $description ) ) {
            $update_post['post_content'] = wp_kses_post( $description );
        }
        wp_update_post( $update_post );

        if ( ! empty( $settings ) && is_array( $settings ) ) {
            update_post_meta( $wheel_id, '_spin_wheel_overrides', wp_json_encode( $settings ) );
            update_post_meta( $wheel_id, '_spin_wheel_design', wp_json_encode( $settings ) );
        }

        if ( ! empty( $prizes ) && is_array( $prizes ) ) {
            $this->sync_prizes_db( $wheel_id, $prizes );
            update_post_meta( $wheel_id, '_spin_wheel_prizes_json', wp_json_encode( $prizes ) );
        }

        return rest_ensure_response( array(
            'success'  => true,
            'wheel_id' => $wheel_id,
            'title'    => get_the_title( $wheel_id ),
            'message'  => __( 'Đã cập nhật tiêu đề vòng quay thành công.', 'wp-spin-wheel' ),
        ) );
    }

    public function duplicate_user_wheel_route( $request ) {
        $user_id = get_current_user_id();
        $wheel_id = absint( $request['id'] ?? ( $request->get_param( 'wheel_id' ) ?: 0 ) );
        $custom_data = $request->get_json_params() ?: $request->get_params();

        $new_id = WP_Spin_Wheel_Wheel::duplicate_user_wheel( $wheel_id, $user_id, $custom_data );
        if ( ! $new_id ) {
            return new WP_Error( 'duplicate_failed', __( 'Không thể sao chép vòng quay.', 'wp-spin-wheel' ), array( 'status' => 500 ) );
        }

        return rest_ensure_response( array(
            'success'   => true,
            'wheel_id'  => $new_id,
            'title'     => get_the_title( $new_id ),
            'permalink' => get_permalink( $new_id ),
            'home_url'  => home_url( '/?wheel_id=' . $new_id ),
            'message'   => __( 'Đã sao chép vòng quay thành công.', 'wp-spin-wheel' ),
        ) );
    }

    public function delete_user_wheel_route( $request ) {
        $user_id = get_current_user_id();
        $wheel_id = absint( $request['id'] );
        $deleted = WP_Spin_Wheel_Wheel::delete_user_wheel( $wheel_id, $user_id );
        if ( ! $deleted ) {
            return new WP_Error( 'delete_failed', __( 'Unable to delete wheel.', 'wp-spin-wheel' ), array( 'status' => 403 ) );
        }

        return rest_ensure_response( array(
            'success' => true,
            'message' => __( 'Đã xóa vòng quay thành công.', 'wp-spin-wheel' ),
        ) );
    }

    public function get_user_options() {
        $user_id = get_current_user_id();
        $opts = get_user_meta( $user_id, 'wp_spin_wheel_options', true );
        return rest_ensure_response( is_array( $opts ) ? $opts : array() );
    }

    public function save_user_options( $request ) {
        $user_id = get_current_user_id();
        $data = $request->get_params();
        if ( isset( $data['options'] ) && is_array( $data['options'] ) ) {
            update_user_meta( $user_id, 'wp_spin_wheel_options', $data['options'] );
            return rest_ensure_response( array( 'saved' => true ) );
        }
        return new WP_Error( 'invalid', __( 'Invalid options payload.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
    }

    public function get_history( $request ) {
        $history = new WP_Spin_Wheel_History();

        $wheel_id  = $request->get_param( 'wheel_id' ) ?: $request->get_param( 'id' );
        $email     = $request->get_param( 'email' );
        $name      = $request->get_param( 'name' );
        $phone     = $request->get_param( 'phone' );
        $search    = $request->get_param( 'search' ) ?: $request->get_param( 's' );
        $from_date = $request->get_param( 'from_date' ) ?: $request->get_param( 'from' );
        $to_date   = $request->get_param( 'to_date' ) ?: $request->get_param( 'to' );
        $page      = absint( $request->get_param( 'page' ) ?: 1 );
        $per_page  = absint( $request->get_param( 'per_page' ) ?: ( $request->get_param( 'limit' ) ?: 20 ) );
        $orderby   = sanitize_text_field( $request->get_param( 'orderby' ) ?: 'id' );
        $order     = sanitize_text_field( $request->get_param( 'order' ) ?: 'DESC' );

        $args = array(
            'wheel_id'  => $wheel_id ? absint( $wheel_id ) : 0,
            'email'     => $email ? sanitize_email( $email ) : '',
            'name'      => $name ? sanitize_text_field( $name ) : '',
            'phone'     => $phone ? sanitize_text_field( $phone ) : '',
            'search'    => $search ? sanitize_text_field( $search ) : '',
            'from_date' => $from_date ? sanitize_text_field( $from_date ) : '',
            'to_date'   => $to_date ? sanitize_text_field( $to_date ) : '',
            'page'      => $page,
            'per_page'  => $per_page,
            'orderby'   => $orderby,
            'order'     => $order,
        );

        // Nếu người dùng đăng nhập không phải admin và muốn xem lịch sử của chính mình
        if ( is_user_logged_in() && ! current_user_can( 'manage_options' ) && empty( $wheel_id ) && empty( $email ) ) {
            $current_user = wp_get_current_user();
            $args['email'] = $current_user->user_email;
        }

        $result = $history->get_history( $args );
        return rest_ensure_response( $result );
    }

    public function get_wheel_history( $request ) {
        $wheel_id = absint( $request['id'] );
        $request->set_param( 'wheel_id', $wheel_id );
        return $this->get_history( $request );
    }

    public function delete_history_item( $request ) {
        $id = absint( $request['id'] );
        if ( ! $id ) {
            return new WP_Error( 'invalid_id', __( 'ID không hợp lệ.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
        }

        $history = new WP_Spin_Wheel_History();
        $deleted = $history->delete_entry( $id );

        if ( ! $deleted ) {
            return new WP_Error( 'delete_failed', __( 'Không thể xóa bản ghi lịch sử.', 'wp-spin-wheel' ), array( 'status' => 500 ) );
        }

        return rest_ensure_response( array(
            'success' => true,
            'message' => __( 'Đã xóa bản ghi lịch sử thành công.', 'wp-spin-wheel' ),
        ) );
    }

    public function clear_history( $request ) {
        $wheel_id = absint( $request->get_param( 'wheel_id' ) ?: 0 );
        $history  = new WP_Spin_Wheel_History();
        $cleared  = $history->clear_history( $wheel_id );

        if ( ! $cleared ) {
            return new WP_Error( 'clear_failed', __( 'Không thể xóa sạch lịch sử.', 'wp-spin-wheel' ), array( 'status' => 500 ) );
        }

        return rest_ensure_response( array(
            'success' => true,
            'message' => $wheel_id ? __( 'Đã xóa toàn bộ lịch sử của vòng quay này.', 'wp-spin-wheel' ) : __( 'Đã xóa toàn bộ lịch sử quay thưởng.', 'wp-spin-wheel' ),
        ) );
    }

    public function get_recent_winners( $request ) {
        $limit    = absint( $request->get_param( 'limit' ) ?: 10 );
        $wheel_id = absint( $request->get_param( 'wheel_id' ) ?: 0 );

        $history = new WP_Spin_Wheel_History();
        $winners = $history->get_recent_winners( $limit, $wheel_id );

        return rest_ensure_response( array(
            'success' => true,
            'items'   => $winners,
        ) );
    }

    public function get_wheel_stats_route( $request ) {
        $wheel_id = absint( $request['id'] );
        if ( ! $wheel_id ) {
            return new WP_Error( 'invalid_wheel', __( 'Vòng quay không hợp lệ.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
        }

        $history = new WP_Spin_Wheel_History();
        $stats   = $history->get_wheel_stats( $wheel_id );

        return rest_ensure_response( $stats );
    }

    public function increment_wheel_views_route( $request ) {
        $wheel_id = absint( $request['id'] );
        if ( ! $wheel_id ) {
            return new WP_Error( 'invalid_wheel', __( 'Vòng quay không hợp lệ.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
        }

        $history = new WP_Spin_Wheel_History();
        $views   = $history->increment_views( $wheel_id );

        return rest_ensure_response( array(
            'success' => true,
            'views'   => $views,
        ) );
    }

    public function sync_prizes_db( $wheel_id, $prizes ) {
        return WP_Spin_Wheel_Prize::sync_prizes( $wheel_id, $prizes );
    }

    private function verify_nonce( $request ) {
        $nonce = $request->get_header( 'x_wp_nonce' );
        if ( empty( $nonce ) ) {
            $nonce = $request->get_param( 'nonce' );
        }
        if ( empty( $nonce ) ) {
            return true;
        }
        return wp_verify_nonce( $nonce, 'wp_rest' );
    }

    private function get_safe_prizes( $prizes ) {
        if ( empty( $prizes ) || ! is_array( $prizes ) ) {
            return array();
        }

        return array_values( array_map( array( $this, 'format_prize' ), $prizes ) );
    }

    private function format_prize( $prize ) {
        return array(
            'id'          => isset( $prize['id'] ) ? intval( $prize['id'] ) : 0,
            'title'       => sanitize_text_field( $prize['title'] ?? '' ),
            'description' => sanitize_text_field( $prize['description'] ?? '' ),
            'color'       => sanitize_text_field( $prize['color'] ?? '' ),
            'image'       => esc_url_raw( $prize['image'] ?? '' ),
            'icon'        => esc_url_raw( $prize['icon'] ?? '' ),
        );
    }

    public function update_user_profile( $request ) {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return new WP_Error( 'not_logged_in', __( 'Bạn chưa đăng nhập.', 'wp-spin-wheel' ), array( 'status' => 401 ) );
        }

        $display_name = sanitize_text_field( $request->get_param( 'display_name' ) );
        $email        = sanitize_email( $request->get_param( 'email' ) );
        $password     = $request->get_param( 'password' );

        $userdata = array( 'ID' => $user_id );
        if ( ! empty( $display_name ) ) {
            $userdata['display_name'] = $display_name;
        }
        if ( ! empty( $email ) ) {
            $userdata['user_email'] = $email;
        }
        if ( ! empty( $password ) ) {
            $userdata['user_pass'] = $password;
        }

        $res = wp_update_user( $userdata );
        if ( is_wp_error( $res ) ) {
            return new WP_Error( 'update_failed', $res->get_error_message(), array( 'status' => 400 ) );
        }

        return rest_ensure_response( array(
            'success' => true,
            'message' => __( 'Cập nhật thông tin thành công.', 'wp-spin-wheel' ),
        ) );
    }
}
