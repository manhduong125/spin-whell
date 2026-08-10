<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Admin {
    private $settings;

    public function __construct() {
        $this->settings = new WP_Spin_Wheel_Settings();

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_menu', array( $this, 'register_dashboard_page' ) );
        add_action( 'wp_ajax_spin_wheel_get_stats', array( $this, 'ajax_get_stats' ) );
    }

    public function enqueue_assets( $hook ) {
        $screen = get_current_screen();
        $screen_id = $screen ? $screen->id : '';
        $allowed_screens = array(
            'settings_page_wp-spin-wheel-settings',
            'post.php',
            'post-new.php',
            'edit-spin_wheel',
            'spin_wheel',
        );

        if ( ! in_array( $screen_id, $allowed_screens, true ) && strpos( $hook, 'spin_wheel' ) === false ) {
            return;
        }

        wp_enqueue_style( 'wp-spin-wheel-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), '5.3.3' );
        wp_enqueue_script( 'wp-spin-wheel-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array(), '5.3.3', true );
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_media();
        wp_enqueue_script( 'wp-spin-wheel-admin', WP_SPIN_WHEEL_URL . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker' ), WP_SPIN_WHEEL_VERSION, true );
        wp_enqueue_style( 'wp-spin-wheel-admin', WP_SPIN_WHEEL_URL . 'assets/css/admin.css', array(), WP_SPIN_WHEEL_VERSION );

        wp_localize_script( 'wp-spin-wheel-admin', 'wp_spin_wheel_admin_params', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'spin_wheel_admin' ),
            'rest_url' => esc_url( rest_url( 'spin-wheel/v1' ) ),
            'media_title' => __( 'Chọn tệp phương tiện', 'wp-spin-wheel' ),
            'media_button' => __( 'Chọn', 'wp-spin-wheel' ),
        ) );

        wp_enqueue_script( 'wp-spin-wheel-admin-settings', WP_SPIN_WHEEL_URL . 'assets/js/admin-settings.js', array( 'jquery' ), WP_SPIN_WHEEL_VERSION, true );
        wp_localize_script( 'wp-spin-wheel-admin-settings', 'wp_spin_wheel_settings_params', array(
            'rest_url' => esc_url( rest_url( 'spin-wheel/v1' ) ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'text_saved' => __( 'Settings saved.', 'wp-spin-wheel' ),
        ) );
    }

    public function register_dashboard_page() {
        add_submenu_page(
            'edit.php?post_type=spin_wheel',
            __( 'Spin Wheel Dashboard', 'wp-spin-wheel' ),
            __( 'Dashboard', 'wp-spin-wheel' ),
            'manage_options',
            'wp-spin-wheel-dashboard',
            array( $this, 'render_dashboard_page' )
        );

        add_submenu_page(
            'edit.php?post_type=spin_wheel',
            __( 'Spin Wheel Settings', 'wp-spin-wheel' ),
            __( 'Settings', 'wp-spin-wheel' ),
            'manage_options',
            'wp-spin-wheel-settings',
            array( $this->settings, 'render_settings_page' )
        );
    }

    public function render_dashboard_page() {
        $history = new WP_Spin_Wheel_History();
        $stats   = $history->get_stats();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Spin Wheel Dashboard', 'wp-spin-wheel' ); ?></h1>
            <div class="wp-spin-wheel-dashboard">
                <div class="stat-item"><strong><?php echo esc_html( $stats['total_spins'] ); ?></strong><span><?php esc_html_e( 'Tổng lượt quay', 'wp-spin-wheel' ); ?></span></div>
                <div class="stat-item"><strong><?php echo esc_html( $stats['total_players'] ); ?></strong><span><?php esc_html_e( 'Tổng người chơi', 'wp-spin-wheel' ); ?></span></div>
                <div class="stat-item"><strong><?php echo esc_html( $stats['today_spins'] ); ?></strong><span><?php esc_html_e( 'Lượt quay hôm nay', 'wp-spin-wheel' ); ?></span></div>
                <div class="stat-item"><strong><?php echo esc_html( $stats['top_prize'] ); ?></strong><span><?php esc_html_e( 'Top phần thưởng', 'wp-spin-wheel' ); ?></span></div>
                <div class="stat-item"><strong><?php echo esc_html( $stats['top_wheel'] ); ?></strong><span><?php esc_html_e( 'Top vòng quay', 'wp-spin-wheel' ); ?></span></div>
            </div>
        </div>
        <?php
    }

    public function ajax_get_stats() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        $history = new WP_Spin_Wheel_History();
        wp_send_json_success( $history->get_stats() );
    }
}
