<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Frontend {
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    public function enqueue_assets() {
        wp_enqueue_script( 'wp-spin-wheel-bootstrap', WP_SPIN_WHEEL_URL . 'assets/js/bootstrap.bundle.min.js', array( 'jquery' ), WP_SPIN_WHEEL_VERSION, true );
        wp_enqueue_style( 'wp-spin-wheel-bootstrap', WP_SPIN_WHEEL_URL . 'assets/css/bootstrap.min.css', array(), WP_SPIN_WHEEL_VERSION );
        wp_enqueue_style( 'wp-spin-wheel-frontend', WP_SPIN_WHEEL_URL . 'assets/css/frontend.css', array(), WP_SPIN_WHEEL_VERSION );
        wp_enqueue_script( 'wp-spin-wheel-wheel', WP_SPIN_WHEEL_URL . 'assets/js/wheel.js', array( 'jquery', 'wp-spin-wheel-bootstrap' ), WP_SPIN_WHEEL_VERSION, true );
        wp_enqueue_script( 'wp-spin-wheel-confetti', WP_SPIN_WHEEL_URL . 'assets/js/confetti.js', array(), WP_SPIN_WHEEL_VERSION, true );
        wp_enqueue_script( 'wp-spin-wheel-particles', WP_SPIN_WHEEL_URL . 'assets/js/particles.min.js', array(), '2.0.0', true );

        $is_logged_in = is_user_logged_in();
        $user_id = get_current_user_id();
        $user_wheel_id = 0;
        $user_wheel_data = null;

        if ( $is_logged_in && $user_id ) {
            $user_wheel_id = WP_Spin_Wheel_Wheel::get_or_create_user_wheel( $user_id );
            if ( $user_wheel_id ) {
                $saved_settings = WP_Spin_Wheel_Helper::get_wheel_overrides( $user_wheel_id );
                $prizes         = WP_Spin_Wheel_Prize::get_prizes( $user_wheel_id );
                $user_wheel_data = array(
                    'id'          => $user_wheel_id,
                    'title'       => get_the_title( $user_wheel_id ),
                    'description' => get_post_field( 'post_content', $user_wheel_id ),
                    'settings'    => ! empty( $saved_settings ) ? $saved_settings : null,
                    'prizes'      => ! empty( $prizes ) ? $prizes : null,
                );
            }
        }

        wp_localize_script( 'wp-spin-wheel-wheel', 'wp_spin_wheel_params', array(
            'home_url'        => esc_url_raw( home_url( '/' ) ),
            'rest_url'        => esc_url_raw( rest_url( 'spin-wheel/v1/' ) ),
            'nonce'           => wp_create_nonce( 'wp_rest' ),
            'text_win'        => __( 'Bạn đã trúng', 'wp-spin-wheel' ),
            'text_error'      => __( 'Đã có lỗi xảy ra.', 'wp-spin-wheel' ),
            'plugin_url'      => WP_SPIN_WHEEL_URL,
            'themes_json_url' => WP_SPIN_WHEEL_URL . 'assets/data/themes.json',
            'is_logged_in'    => $is_logged_in,
            'user_id'         => $user_id,
            'user_wheel_id'   => $user_wheel_id,
            'user_wheel_data' => $user_wheel_data,
        ) );
    }
}
