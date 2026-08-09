<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Frontend {
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function enqueue_assets() {
        wp_enqueue_script( 'wp-spin-wheel-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), '5.3.3', true );
        wp_enqueue_style( 'wp-spin-wheel-bootstrap', WP_SPIN_WHEEL_URL . 'assets/css/bootstrap.min.css', array(), WP_SPIN_WHEEL_VERSION );
        wp_enqueue_style( 'wp-spin-wheel-frontend', WP_SPIN_WHEEL_URL . 'assets/css/frontend.css', array(), WP_SPIN_WHEEL_VERSION );
        wp_enqueue_script( 'wp-spin-wheel-wheel', WP_SPIN_WHEEL_URL . 'assets/js/wheel.js', array( 'jquery', 'wp-spin-wheel-bootstrap' ), WP_SPIN_WHEEL_VERSION, true );
        wp_enqueue_script( 'wp-spin-wheel-confetti', WP_SPIN_WHEEL_URL . 'assets/js/confetti.js', array(), WP_SPIN_WHEEL_VERSION, true );

        wp_localize_script( 'wp-spin-wheel-wheel', 'wp_spin_wheel_params', array(
            'ajax_url'   => admin_url( 'admin-ajax.php' ),
            'text_win'   => __( 'Bạn đã trúng', 'wp-spin-wheel' ),
            'text_error' => __( 'Đã có lỗi xảy ra.', 'wp-spin-wheel' ),
        ) );
    }
}
