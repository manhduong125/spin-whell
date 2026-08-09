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
