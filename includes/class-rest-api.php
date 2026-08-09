<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_REST_API {
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function register_routes() {
        register_rest_route( 'spin-wheel/v1', '/wheel/(?P<id>\d+)', array(
            'methods'  => 'GET',
            'callback' => array( $this, 'get_wheel' ),
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
        $settings['prizes'] = WP_Spin_Wheel_Prize::get_prizes( $wheel_id );
        $settings['id'] = $wheel_id;
        $settings['title'] = get_the_title( $wheel_id );
        $settings['description'] = get_post_field( 'post_content', $wheel_id );

        return rest_ensure_response( $settings );
    }

    public function post_spin( $request ) {
        $wheel_id = absint( $request->get_param( 'wheel_id' ) );
        $form_data = $request->get_param( 'form' );

        $response = array();
        $help = new WP_Spin_Wheel_Helper();
        $settings = $help->get_wheel_settings( $wheel_id );
        $prizes = WP_Spin_Wheel_Prize::get_prizes( $wheel_id );

        if ( empty( $prizes ) ) {
            return new WP_Error( 'no_prizes', __( 'No prizes configured.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
        }

        $winner = WP_Spin_Wheel_Random::pick_prize( $prizes );
        if ( ! $winner ) {
            return new WP_Error( 'no_winner', __( 'No prize selected.', 'wp-spin-wheel' ), array( 'status' => 400 ) );
        }

        $history = new WP_Spin_Wheel_History();
        $history->record_spin( $wheel_id, $winner['id'] ?? 0, is_array( $form_data ) ? $form_data : array() );

        $response['prize'] = $winner;
        return rest_ensure_response( $response );
    }
}
