<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Shortcode {
    public function __construct() {
        add_shortcode( 'spin_wheel', array( $this, 'render_shortcode' ) );
    }

    public function render_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'id' => 0,
        ), $atts, 'spin_wheel' );

        $post_id = absint( $atts['id'] );
        if ( $post_id && get_post_type( $post_id ) === 'spin_wheel' ) {
            ob_start();
            include WP_SPIN_WHEEL_PATH . 'templates/wheel.php';
            return ob_get_clean();
        }

        ob_start();
        include WP_SPIN_WHEEL_PATH . 'templates/wheel-default.php';
        return ob_get_clean();
    }
}
