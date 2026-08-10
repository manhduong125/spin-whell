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
        add_action( 'wp_ajax_spin_wheel_save_library_item', array( $this, 'ajax_save_library_item' ) );
        add_action( 'wp_ajax_spin_wheel_apply_library_item', array( $this, 'ajax_apply_library_item' ) );
        add_action( 'wp_ajax_spin_wheel_delete_preset', array( $this, 'ajax_delete_preset' ) );
    }

    public function enqueue_assets( $hook ) {
        $screen = get_current_screen();
        $screen_id = $screen ? $screen->id : '';
        $allowed_screens = array(
            'spin_wheel_page_wp-spin-wheel-library',
            'settings_page_wp-spin-wheel-settings',
            'post.php',
            'post-new.php',
            'edit-spin_wheel',
            'edit-spin_wheel_preset',
            'spin_wheel',
            'spin_wheel_preset',
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
            'text_saved' => __( 'Preset đã lưu.', 'wp-spin-wheel' ),
            'text_applied' => __( 'Đã áp dụng vào vòng quay.', 'wp-spin-wheel' ),
            'text_select_wheel' => __( 'Vui lòng chọn một vòng quay.', 'wp-spin-wheel' ),
            'text_deleted' => __( 'Preset đã xóa.', 'wp-spin-wheel' ),
            'text_delete_confirm' => __( 'Bạn có chắc muốn xóa preset này không?', 'wp-spin-wheel' ),
            'media_title' => __( 'Chọn tệp phương tiện', 'wp-spin-wheel' ),
            'media_button' => __( 'Chọn', 'wp-spin-wheel' ),
        ) );

        // admin settings script: handles settings page REST load/save
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

    public function render_library_page() {
        $library = $this->get_library_items();
        ?>
        <div class="wrap">
            <div class="container-fluid py-4">
                <div class="row mb-4">
                    <div class="col-12">
                        <h1 class="display-6"><?php esc_html_e( 'Preset Library', 'wp-spin-wheel' ); ?></h1>
                        <p class="text-muted"><?php esc_html_e( 'Browse wheel styles, backgrounds, buttons, pointers, sounds, and animations.', 'wp-spin-wheel' ); ?></p>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label for="library-wheel-select" class="form-label"><?php esc_html_e( 'Apply to wheel', 'wp-spin-wheel' ); ?></label>
                            <select id="library-wheel-select" class="form-select">
                                <option value=""><?php esc_html_e( 'Select a wheel', 'wp-spin-wheel' ); ?></option>
                                <?php foreach ( $this->get_wheels() as $wheel ) : ?>
                                    <option value="<?php echo esc_attr( $wheel->ID ); ?>"><?php echo esc_html( $wheel->post_title ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="library-action-message"></div>
                    </div>
                </div>
                <?php $saved_presets = get_posts( array(
                    'post_type'      => 'spin_wheel_preset',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                ) ); ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-secondary text-white">
                                <h2 class="h5 mb-0"><?php esc_html_e( 'Saved Presets', 'wp-spin-wheel' ); ?></h2>
                            </div>
                            <div class="card-body">
                                <?php if ( ! empty( $saved_presets ) ) : ?>
                                    <div class="row g-3">
                                        <?php foreach ( $saved_presets as $preset ) : ?>
                                            <div class="col-md-4">
                                                <div class="card h-100 border-secondary">
                                                    <div class="card-body p-3 d-flex flex-column">
                                                        <h3 class="h6 mb-2"><?php echo esc_html( $preset->post_title ); ?></h3>
                                                        <p class="small text-muted mb-3"><?php echo esc_html( wp_trim_words( $preset->post_content, 15 ) ); ?></p>
                                                        <div class="mt-auto d-flex gap-2 flex-wrap">
                                                            <a href="<?php echo esc_url( get_edit_post_link( $preset->ID ) ); ?>" class="btn btn-sm btn-outline-primary"><?php esc_html_e( 'Edit', 'wp-spin-wheel' ); ?></a>
                                                            <button type="button" class="btn btn-sm btn-outline-danger delete-library-preset" data-preset-id="<?php echo esc_attr( $preset->ID ); ?>"><?php esc_html_e( 'Delete', 'wp-spin-wheel' ); ?></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else : ?>
                                    <p class="text-muted"><?php esc_html_e( 'No saved presets yet.', 'wp-spin-wheel' ); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-4">
                    <?php foreach ( $library as $category => $items ) : ?>
                        <div class="col-lg-6">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h2 class="h5 mb-0"><?php echo esc_html( $category ); ?></h2>
                                </div>
                                <div class="card-body">
                                    <div class="row gx-3 gy-3">
                                        <?php foreach ( $items as $item ) : ?>
                                            <div class="col-12 col-md-6">
                                                <div class="card border-secondary h-100">
                                                    <div class="card-body p-3 d-flex flex-column">
                                                        <h3 class="h6 mb-2"><?php echo esc_html( $item['title'] ); ?></h3>
                                                        <?php if ( ! empty( $item['subtitle'] ) ) : ?>
                                                            <p class="text-muted small mb-3"><?php echo esc_html( $item['subtitle'] ); ?></p>
                                                        <?php endif; ?>
                                                        <div class="mt-auto d-flex gap-2 flex-wrap">
                                                            <button type="button" class="btn btn-sm btn-outline-primary w-100 save-library-item" data-config="<?php echo esc_attr( wp_json_encode( $item['config'] ) ); ?>" data-title="<?php echo esc_attr( $item['title'] ); ?>"><?php esc_html_e( 'Save as Preset', 'wp-spin-wheel' ); ?></button>
                                                            <button type="button" class="btn btn-sm btn-primary w-100 apply-library-item" data-config="<?php echo esc_attr( wp_json_encode( $item['config'] ) ); ?>" data-title="<?php echo esc_attr( $item['title'] ); ?>"><?php esc_html_e( 'Apply to Wheel', 'wp-spin-wheel' ); ?></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }

    private function get_library_items() {
        return array(
            __( 'Wheel Styles', 'wp-spin-wheel' ) => array(
                array(
                    'title' => __( 'Style 01', 'wp-spin-wheel' ),
                    'subtitle' => __( 'Modern minimal wheel with contrast segments.', 'wp-spin-wheel' ),
                    'config' => array(
                        'wheel' => array(
                            'size' => 520,
                            'border' => 12,
                            'border_color' => '#2d3748',
                            'shadow' => true,
                        ),
                        'button' => array(
                            'text' => 'QUAY',
                            'color' => '#1d4ed8',
                            'text_color' => '#ffffff',
                            'radius' => 30,
                        ),
                    ),
                ),
                array(
                    'title' => __( 'Style 02', 'wp-spin-wheel' ),
                    'subtitle' => __( 'Gradient ring and flat labels.', 'wp-spin-wheel' ),
                    'config' => array(
                        'wheel' => array(
                            'size' => 520,
                            'border' => 8,
                            'border_color' => '#ffffff',
                            'shadow' => true,
                        ),
                        'button' => array(
                            'text' => 'SPIN',
                            'color' => '#ec4899',
                            'text_color' => '#ffffff',
                            'radius' => 35,
                        ),
                    ),
                ),
                array(
                    'title' => __( 'Style 03', 'wp-spin-wheel' ),
                    'subtitle' => __( 'Bold border with large pointer.', 'wp-spin-wheel' ),
                    'config' => array(
                        'wheel' => array(
                            'size' => 540,
                            'border' => 14,
                            'border_color' => '#facc15',
                            'shadow' => true,
                        ),
                        'button' => array(
                            'text' => 'PLAY',
                            'color' => '#0f766e',
                            'text_color' => '#ffffff',
                            'radius' => 40,
                        ),
                        'pointer' => array(
                            'image' => '',
                            'size' => 100,
                        ),
                    ),
                ),
            ),
            __( 'Backgrounds', 'wp-spin-wheel' ) => array(
                array(
                    'title' => __( 'BG 01', 'wp-spin-wheel' ),
                    'subtitle' => __( 'Soft pastel background.', 'wp-spin-wheel' ),
                    'config' => array(
                        'background' => array(
                            'type' => 'color',
                            'value' => '#f8fafc',
                        ),
                        'custom_css' => '.wp-spin-wheel-wrapper { background-image: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); }',
                    ),
                ),
                array(
                    'title' => __( 'BG 02', 'wp-spin-wheel' ),
                    'subtitle' => __( 'Dark gradient holiday feel.', 'wp-spin-wheel' ),
                    'config' => array(
                        'background' => array(
                            'type' => 'color',
                            'value' => '#111827',
                        ),
                        'custom_css' => '.wp-spin-wheel-wrapper { background-image: radial-gradient(circle at top, rgba(236,72,153,0.2), transparent 40%), radial-gradient(circle at bottom, rgba(59,130,246,0.15), transparent 35%); }',
                    ),
                ),
                array(
                    'title' => __( 'BG 03', 'wp-spin-wheel' ),
                    'subtitle' => __( 'Light textured paper style.', 'wp-spin-wheel' ),
                    'config' => array(
                        'background' => array(
                            'type' => 'image',
                            'image' => WP_SPIN_WHEEL_URL . 'assets/images/bg-paper.png',
                        ),
                    ),
                ),
            ),
            __( 'Buttons', 'wp-spin-wheel' ) => array(
                array(
                    'title' => __( 'Button 01', 'wp-spin-wheel' ),
                    'subtitle' => __( 'Rounded red CTA button.', 'wp-spin-wheel' ),
                    'config' => array(
                        'button' => array(
                            'text' => 'QUAY',
                            'color' => '#d32f2f',
                            'text_color' => '#ffffff',
                            'radius' => 50,
                        ),
                    ),
                ),
                array(
                    'title' => __( 'Button 02', 'wp-spin-wheel' ),
                    'subtitle' => __( 'Yellow outlined action button.', 'wp-spin-wheel' ),
                    'config' => array(
                        'button' => array(
                            'text' => 'TRY NOW',
                            'color' => '#f59e0b',
                            'text_color' => '#111827',
                            'radius' => 35,
                        ),
                    ),
                ),
                array(
                    'title' => __( 'Button 03', 'wp-spin-wheel' ),
                    'subtitle' => __( 'Solid dark button with white text.', 'wp-spin-wheel' ),
                    'config' => array(
                        'button' => array(
                            'text' => 'PLAY',
                            'color' => '#111827',
                            'text_color' => '#ffffff',
                            'radius' => 45,
                        ),
                    ),
                ),
            ),
            __( 'Pointers', 'wp-spin-wheel' ) => array(
                array(
                    'title' => __( 'Pointer 01', 'wp-spin-wheel' ),
                    'subtitle' => __( 'Classic arrow pointer.', 'wp-spin-wheel' ),
                    'config' => array(
                        'pointer' => array(
                            'image' => '',
                            'size' => 80,
                        ),
                    ),
                ),
                array(
                    'title' => __( 'Pointer 02', 'wp-spin-wheel' ),
                    'subtitle' => __( 'Modern triangular pointer.', 'wp-spin-wheel' ),
                    'config' => array(
                        'pointer' => array(
                            'image' => '',
                            'size' => 90,
                        ),
                    ),
                ),
            ),
            __( 'Sounds', 'wp-spin-wheel' ) => array(
                array(
                    'title' => __( 'Spin Sound 01', 'wp-spin-wheel' ),
                    'subtitle' => __( 'Light mechanical spin effect.', 'wp-spin-wheel' ),
                    'config' => array(
                        'audio' => array(
                            'spin' => WP_SPIN_WHEEL_URL . 'assets/sounds/spin-1.mp3',
                            'win' => '',
                        ),
                    ),
                ),
                array(
                    'title' => __( 'Win Sound 01', 'wp-spin-wheel' ),
                    'subtitle' => __( 'Celebratory chime on win.', 'wp-spin-wheel' ),
                    'config' => array(
                        'audio' => array(
                            'spin' => '',
                            'win' => WP_SPIN_WHEEL_URL . 'assets/sounds/win-1.mp3',
                        ),
                    ),
                ),
            ),
            __( 'Animations', 'wp-spin-wheel' ) => array(
                array(
                    'title' => __( 'Animation 01', 'wp-spin-wheel' ),
                    'subtitle' => __( 'Smooth slow spin with easing.', 'wp-spin-wheel' ),
                    'config' => array(
                        'animation' => array(
                            'duration' => 8,
                            'confetti' => true,
                        ),
                    ),
                ),
                array(
                    'title' => __( 'Animation 02', 'wp-spin-wheel' ),
                    'subtitle' => __( 'Fast spin with dramatic stop.', 'wp-spin-wheel' ),
                    'config' => array(
                        'animation' => array(
                            'duration' => 4,
                            'confetti' => false,
                        ),
                    ),
                ),
            ),
        );
    }

    private function get_wheels() {
        return get_posts( array(
            'post_type'      => 'spin_wheel',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );
    }

    public function ajax_save_library_item() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'wp-spin-wheel' ) ) );
        }

        check_ajax_referer( 'spin_wheel_admin', 'nonce' );

        $title = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
        $config = json_decode( wp_unslash( $_POST['config'] ?? '{}' ), true );

        if ( empty( $title ) || ! is_array( $config ) ) {
            wp_send_json_error( array( 'message' => __( 'Invalid library item.', 'wp-spin-wheel' ) ) );
        }

        $preset_id = $this->create_preset_from_library_item( $title, $config );
        if ( ! $preset_id ) {
            wp_send_json_error( array( 'message' => __( 'Unable to save preset.', 'wp-spin-wheel' ) ) );
        }

        wp_send_json_success( array( 'message' => __( 'Preset saved successfully.', 'wp-spin-wheel' ), 'preset_id' => $preset_id ) );
    }

    public function ajax_apply_library_item() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'wp-spin-wheel' ) ) );
        }

        check_ajax_referer( 'spin_wheel_admin', 'nonce' );

        $title = sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) );
        $config = json_decode( wp_unslash( $_POST['config'] ?? '{}' ), true );
        $wheel_id = absint( $_POST['wheel_id'] ?? 0 );

        if ( empty( $title ) || ! is_array( $config ) || ! $wheel_id ) {
            wp_send_json_error( array( 'message' => __( 'Invalid request.', 'wp-spin-wheel' ) ) );
        }

        if ( get_post_type( $wheel_id ) !== 'spin_wheel' ) {
            wp_send_json_error( array( 'message' => __( 'Wheel not found.', 'wp-spin-wheel' ) ) );
        }

        $preset_id = $this->create_preset_from_library_item( $title, $config );
        if ( ! $preset_id ) {
            wp_send_json_error( array( 'message' => __( 'Unable to create preset.', 'wp-spin-wheel' ) ) );
        }

        $preset_overrides = $this->merge_library_config( $config );

        update_post_meta( $wheel_id, '_spin_wheel_preset_id', $preset_id );
        update_post_meta( $wheel_id, '_spin_wheel_overrides', wp_json_encode( $preset_overrides ) );
        delete_post_meta( $wheel_id, '_spin_wheel_design' );

        wp_send_json_success( array( 'message' => __( 'Preset applied to wheel.', 'wp-spin-wheel' ), 'preset_id' => $preset_id ) );
    }

    public function ajax_delete_preset() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'wp-spin-wheel' ) ) );
        }

        check_ajax_referer( 'spin_wheel_admin', 'nonce' );

        $preset_id = absint( $_POST['preset_id'] ?? 0 );
        if ( ! $preset_id || get_post_type( $preset_id ) !== 'spin_wheel_preset' ) {
            wp_send_json_error( array( 'message' => __( 'Invalid preset.', 'wp-spin-wheel' ) ) );
        }

        wp_delete_post( $preset_id, true );
        wp_send_json_success( array( 'message' => __( 'Preset deleted successfully.', 'wp-spin-wheel' ) ) );
    }

    private function create_preset_from_library_item( $title, $config ) {
        $preset_title = sprintf( __( 'Library: %s', 'wp-spin-wheel' ), $title );
        $preset_id = wp_insert_post( array(
            'post_title'   => $preset_title,
            'post_content' => sprintf( __( 'Preset created from library item: %s', 'wp-spin-wheel' ), $title ),
            'post_status'  => 'publish',
            'post_type'    => 'spin_wheel_preset',
        ), true );

        if ( is_wp_error( $preset_id ) || ! $preset_id ) {
            return false;
        }

        $preset_config = $this->merge_library_config( $config );
        update_post_meta( $preset_id, '_spin_wheel_preset_config', wp_json_encode( $preset_config ) );
        return $preset_id;
    }

    private function merge_library_config( $config ) {
        $default = array(
            'background' => array( 'type' => 'color', 'value' => '#ffffff' ),
            'wheel'      => array( 'size' => 500, 'border' => 8, 'border_color' => '#ffffff', 'shadow' => true ),
            'button'     => array( 'text' => 'QUAY', 'color' => '#ff0000', 'text_color' => '#ffffff', 'radius' => 50 ),
            'pointer'    => array( 'image' => '', 'size' => 80 ),
            'font'       => array( 'family' => 'Arial', 'size' => 20 ),
            'animation'  => array( 'duration' => 6, 'confetti' => true ),
            'audio'      => array( 'spin' => '', 'win' => '' ),
        );

        return array_replace_recursive( $default, $config );
    }

    public function ajax_get_stats() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        $history = new WP_Spin_Wheel_History();
        wp_send_json_success( $history->get_stats() );
    }
}
