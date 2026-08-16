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

        $winner = WP_Spin_Wheel_Wheel::spin_wheel( $wheel_id, $form_data );
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

    private function sync_prizes_db( $wheel_id, $prizes ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spin_prizes';
        $wpdb->delete( $table, array( 'wheel_id' => $wheel_id ), array( '%d' ) );
        foreach ( $prizes as $prize ) {
            if ( empty( $prize['title'] ) ) {
                continue;
            }
            $wpdb->insert(
                $table,
                array(
                    'wheel_id'    => $wheel_id,
                    'title'       => sanitize_text_field( $prize['title'] ),
                    'description' => sanitize_textarea_field( $prize['description'] ?? '' ),
                    'color'       => sanitize_text_field( $prize['color'] ?? '' ),
                    'image'       => esc_url_raw( $prize['image'] ?? '' ),
                    'icon'        => esc_url_raw( $prize['icon'] ?? '' ),
                    'weight'      => max( 1, intval( $prize['weight'] ?? 1 ) ),
                    'stock'       => max( 0, intval( $prize['stock'] ?? 0 ) ),
                    'initial_stock' => max( 0, intval( $prize['stock'] ?? 0 ) ),
                    'status'      => sanitize_text_field( $prize['status'] ?? 'active' ),
                    'sort_order'  => intval( $prize['sort_order'] ?? 0 ),
                    'created_at'  => current_time( 'mysql' ),
                ),
                array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%d', '%s' )
            );
        }
    }

    private function verify_nonce( $request ) {
        $nonce = $request->get_header( 'x_wp_nonce' );
        if ( empty( $nonce ) ) {
            $nonce = $request->get_param( 'nonce' );
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
}
