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

        if ( ! $wheel_id || get_post_type( $wheel_id ) !== 'spin_wheel' ) {
            wp_send_json_error( array( 'message' => __( 'Invalid wheel.', 'wp-spin-wheel' ) ) );
        }

        $helper = new WP_Spin_Wheel_Helper();
        $settings = $helper->get_wheel_settings( $wheel_id );
        $prizes = WP_Spin_Wheel_Prize::get_prizes( $wheel_id );

        if ( empty( $prizes ) ) {
            wp_send_json_error( array( 'message' => __( 'No prizes configured.', 'wp-spin-wheel' ) ) );
        }

        $winner = WP_Spin_Wheel_Random::pick_prize( $prizes );
        if ( ! $winner ) {
            wp_send_json_error( array( 'message' => __( 'No prize selected.', 'wp-spin-wheel' ) ) );
        }

        $history = new WP_Spin_Wheel_History();
        $history->record_spin( $wheel_id, $winner['id'] ?? 0, $form_data );

        wp_send_json_success( array(
            'prize' => $winner,
        ) );
    }
}
