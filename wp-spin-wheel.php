<?php
/**
 * Plugin Name: WP Spin Wheel
 * Plugin URI:  https://example.com/wp-spin-wheel
 * Description: Spin Wheel plugin with custom post type, prize management, weighted random draws, history logging, dashboard stats, shortcode, and REST API.
 * Version:     1.0.0
 * Author:      WP Spin Wheel
 * Text Domain: wp-spin-wheel
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'WP_SPIN_WHEEL_VERSION' ) ) {
    define( 'WP_SPIN_WHEEL_VERSION', '1.0.0' );
}

if ( ! defined( 'WP_SPIN_WHEEL_FILE' ) ) {
    define( 'WP_SPIN_WHEEL_FILE', __FILE__ );
}

if ( ! defined( 'WP_SPIN_WHEEL_PATH' ) ) {
    define( 'WP_SPIN_WHEEL_PATH', plugin_dir_path( WP_SPIN_WHEEL_FILE ) );
}

if ( ! defined( 'WP_SPIN_WHEEL_URL' ) ) {
    define( 'WP_SPIN_WHEEL_URL', plugin_dir_url( WP_SPIN_WHEEL_FILE ) );
}

require_once WP_SPIN_WHEEL_PATH . 'includes/helper.php';
require_once WP_SPIN_WHEEL_PATH . 'includes/class-install.php';
require_once WP_SPIN_WHEEL_PATH . 'includes/class-post-type.php';
require_once WP_SPIN_WHEEL_PATH . 'includes/class-preset.php';
require_once WP_SPIN_WHEEL_PATH . 'includes/class-meta-box.php';
require_once WP_SPIN_WHEEL_PATH . 'includes/class-admin.php';
require_once WP_SPIN_WHEEL_PATH . 'includes/class-frontend.php';
require_once WP_SPIN_WHEEL_PATH . 'includes/class-shortcode.php';
require_once WP_SPIN_WHEEL_PATH . 'includes/class-wheel.php';
require_once WP_SPIN_WHEEL_PATH . 'includes/class-random.php';
require_once WP_SPIN_WHEEL_PATH . 'includes/class-form.php';
require_once WP_SPIN_WHEEL_PATH . 'includes/class-prize.php';
require_once WP_SPIN_WHEEL_PATH . 'includes/class-player.php';
require_once WP_SPIN_WHEEL_PATH . 'includes/class-history.php';
require_once WP_SPIN_WHEEL_PATH . 'includes/class-rest-api.php';
require_once WP_SPIN_WHEEL_PATH . 'includes/class-export.php';

function wp_spin_wheel_init() {
    new WP_Spin_Wheel_Post_Type();
    new WP_Spin_Wheel_Preset();
    new WP_Spin_Wheel_Meta_Box();
    new WP_Spin_Wheel_Admin();
    new WP_Spin_Wheel_Frontend();
    new WP_Spin_Wheel_Shortcode();
    new WP_Spin_Wheel_Wheel();
    new WP_Spin_Wheel_Random();
    new WP_Spin_Wheel_Form();
    new WP_Spin_Wheel_Prize();
    new WP_Spin_Wheel_Player();
    new WP_Spin_Wheel_History();
    new WP_Spin_Wheel_REST_API();
}
add_action( 'plugins_loaded', 'wp_spin_wheel_init' );
add_action( 'init', array( 'WP_Spin_Wheel_Preset', 'create_default_presets' ), 20 );

function wp_spin_wheel_activate() {
    WP_Spin_Wheel_Install::activate();
}
register_activation_hook( WP_SPIN_WHEEL_FILE, 'wp_spin_wheel_activate' );
