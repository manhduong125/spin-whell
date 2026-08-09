<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Preset {
    public function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta_boxes' ) );
    }

    public function register_post_type() {
        $labels = array(
            'name'                  => __( 'Presets', 'wp-spin-wheel' ),
            'singular_name'         => __( 'Preset', 'wp-spin-wheel' ),
            'menu_name'             => __( 'Presets', 'wp-spin-wheel' ),
            'name_admin_bar'        => __( 'Preset', 'wp-spin-wheel' ),
            'add_new'               => __( 'Add New', 'wp-spin-wheel' ),
            'add_new_item'          => __( 'Add New Preset', 'wp-spin-wheel' ),
            'new_item'              => __( 'New Preset', 'wp-spin-wheel' ),
            'edit_item'             => __( 'Edit Preset', 'wp-spin-wheel' ),
            'view_item'             => __( 'View Preset', 'wp-spin-wheel' ),
            'all_items'             => __( 'All Presets', 'wp-spin-wheel' ),
            'search_items'          => __( 'Search Presets', 'wp-spin-wheel' ),
            'not_found'             => __( 'No Presets found.', 'wp-spin-wheel' ),
            'not_found_in_trash'    => __( 'No Presets found in Trash.', 'wp-spin-wheel' ),
        );

        register_post_type( 'spin_wheel_preset', array(
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => 'edit.php?post_type=spin_wheel',
            'menu_position'      => 26,
            'menu_icon'          => 'dashicons-admin-customizer',
            'supports'           => array( 'title', 'editor', 'thumbnail' ),
            'has_archive'        => false,
            'rewrite'            => false,
            'capability_type'    => array( 'spin_wheel_preset', 'spin_wheel_presets' ),
            'map_meta_cap'       => true,
            'capabilities'       => array(
                'edit_post'          => 'edit_spin_wheel_preset',
                'read_post'          => 'read_spin_wheel_preset',
                'delete_post'        => 'delete_spin_wheel_preset',
                'edit_posts'         => 'edit_spin_wheel_presets',
                'edit_others_posts'  => 'edit_others_spin_wheel_presets',
                'publish_posts'      => 'publish_spin_wheel_presets',
                'read_private_posts' => 'read_private_spin_wheel_presets',
                'create_posts'       => 'create_spin_wheel_presets',
            ),
        ) );
    }

    public function register_meta_boxes() {
        add_meta_box(
            'spin_wheel_preset_config',
            __( 'Preset Configuration', 'wp-spin-wheel' ),
            array( $this, 'render_preset_meta_box' ),
            'spin_wheel_preset',
            'normal',
            'default'
        );
    }

    public function render_preset_meta_box( $post ) {
        wp_nonce_field( 'spin_wheel_preset_save', 'spin_wheel_preset_nonce' );

        $config = WP_Spin_Wheel_Helper::get_preset_config( $post->ID );
        $background_type = $config['background']['type'] ?? 'color';
        $background_color = $config['background']['value'] ?? '#ffffff';
        $background_image = $config['background']['image'] ?? '';
        $wheel_size = $config['wheel']['size'] ?? 500;
        $wheel_border = $config['wheel']['border'] ?? 8;
        $wheel_border_color = $config['wheel']['border_color'] ?? '#ffffff';
        $wheel_shadow = ! empty( $config['wheel']['shadow'] );
        $button_text = $config['button']['text'] ?? __( 'QUAY', 'wp-spin-wheel' );
        $button_color = $config['button']['color'] ?? '#ff0000';
        $button_text_color = $config['button']['text_color'] ?? '#ffffff';
        $button_radius = $config['button']['radius'] ?? 50;
        $button_background_image = $config['button']['background_image'] ?? '';
        $pointer_image = $config['pointer']['image'] ?? '';
        $pointer_size = $config['pointer']['size'] ?? 80;
        $font_family = $config['font']['family'] ?? 'Arial';
        $font_size = $config['font']['size'] ?? 20;
        $animation_duration = $config['animation']['duration'] ?? 6;
        $animation_confetti = ! empty( $config['animation']['confetti'] );
        $audio_spin = $config['audio']['spin'] ?? '';
        $audio_win = $config['audio']['win'] ?? '';
        $custom_css = $config['custom_css'] ?? '';
        ?>
        <fieldset>
            <legend><?php esc_html_e( 'Background', 'wp-spin-wheel' ); ?></legend>
            <p>
                <label><?php esc_html_e( 'Background type', 'wp-spin-wheel' ); ?></label><br />
                <label><input type="radio" name="spin_wheel_preset_background_type" value="color" <?php checked( $background_type, 'color' ); ?> /> <?php esc_html_e( 'Color', 'wp-spin-wheel' ); ?></label>
                <label class="ms-3"><input type="radio" name="spin_wheel_preset_background_type" value="image" <?php checked( $background_type, 'image' ); ?> /> <?php esc_html_e( 'Image', 'wp-spin-wheel' ); ?></label>
            </p>
            <p>
                <label for="spin_wheel_preset_background_color"><?php esc_html_e( 'Background color', 'wp-spin-wheel' ); ?></label><br />
                <input type="text" id="spin_wheel_preset_background_color" name="spin_wheel_preset_background_color" value="<?php echo esc_attr( $background_color ); ?>" class="widefat wp-spin-wheel-color-field" />
            </p>
            <p>
                <label for="spin_wheel_preset_background_image"><?php esc_html_e( 'Background image URL', 'wp-spin-wheel' ); ?></label><br />
                <input type="text" id="spin_wheel_preset_background_image" name="spin_wheel_preset_background_image" value="<?php echo esc_attr( $background_image ); ?>" class="widefat" />
                <button type="button" class="button wp-spin-wheel-media-upload-button mt-2" data-target="spin_wheel_preset_background_image"><?php esc_html_e( 'Choose image', 'wp-spin-wheel' ); ?></button>
            </p>
        </fieldset>
        <fieldset>
            <legend><?php esc_html_e( 'Wheel', 'wp-spin-wheel' ); ?></legend>
            <p>
                <label for="spin_wheel_preset_wheel_size"><?php esc_html_e( 'Size', 'wp-spin-wheel' ); ?></label><br />
                <input type="number" id="spin_wheel_preset_wheel_size" name="spin_wheel_preset_wheel_size" value="<?php echo esc_attr( $wheel_size ); ?>" class="small-text" min="100" />
            </p>
            <p>
                <label for="spin_wheel_preset_wheel_border"><?php esc_html_e( 'Border', 'wp-spin-wheel' ); ?></label><br />
                <input type="number" id="spin_wheel_preset_wheel_border" name="spin_wheel_preset_wheel_border" value="<?php echo esc_attr( $wheel_border ); ?>" class="small-text" min="0" />
            </p>
            <p>
                <label for="spin_wheel_preset_wheel_border_color"><?php esc_html_e( 'Border color', 'wp-spin-wheel' ); ?></label><br />
                <input type="text" id="spin_wheel_preset_wheel_border_color" name="spin_wheel_preset_wheel_border_color" value="<?php echo esc_attr( $wheel_border_color ); ?>" class="widefat wp-spin-wheel-color-field" />
            </p>
            <p>
                <label>
                    <input type="checkbox" id="spin_wheel_preset_wheel_shadow" name="spin_wheel_preset_wheel_shadow" value="1" <?php checked( $wheel_shadow ); ?> />
                    <?php esc_html_e( 'Shadow', 'wp-spin-wheel' ); ?>
                </label>
            </p>
        </fieldset>
        <fieldset>
            <legend><?php esc_html_e( 'Button', 'wp-spin-wheel' ); ?></legend>
            <p>
                <label for="spin_wheel_preset_button_text"><?php esc_html_e( 'Text', 'wp-spin-wheel' ); ?></label><br />
                <input type="text" id="spin_wheel_preset_button_text" name="spin_wheel_preset_button_text" value="<?php echo esc_attr( $button_text ); ?>" class="widefat" />
            </p>
            <p>
                <label for="spin_wheel_preset_button_color"><?php esc_html_e( 'Button color', 'wp-spin-wheel' ); ?></label><br />
                <input type="text" id="spin_wheel_preset_button_color" name="spin_wheel_preset_button_color" value="<?php echo esc_attr( $button_color ); ?>" class="widefat wp-spin-wheel-color-field" />
            </p>
            <p>
                <label for="spin_wheel_preset_button_text_color"><?php esc_html_e( 'Text color', 'wp-spin-wheel' ); ?></label><br />
                <input type="text" id="spin_wheel_preset_button_text_color" name="spin_wheel_preset_button_text_color" value="<?php echo esc_attr( $button_text_color ); ?>" class="widefat wp-spin-wheel-color-field" />
            </p>
            <p>
                <label for="spin_wheel_preset_button_radius"><?php esc_html_e( 'Border radius', 'wp-spin-wheel' ); ?></label><br />
                <input type="number" id="spin_wheel_preset_button_radius" name="spin_wheel_preset_button_radius" value="<?php echo esc_attr( $button_radius ); ?>" class="small-text" min="0" />
            </p>
            <p>
                <label for="spin_wheel_preset_button_background_image"><?php esc_html_e( 'Button background image', 'wp-spin-wheel' ); ?></label><br />
                <input type="text" id="spin_wheel_preset_button_background_image" name="spin_wheel_preset_button_background_image" value="<?php echo esc_attr( $button_background_image ); ?>" class="widefat" />
                <button type="button" class="button wp-spin-wheel-media-upload-button mt-2" data-target="spin_wheel_preset_button_background_image"><?php esc_html_e( 'Choose image', 'wp-spin-wheel' ); ?></button>
            </p>
        </fieldset>
        <fieldset>
            <legend><?php esc_html_e( 'Pointer', 'wp-spin-wheel' ); ?></legend>
            <p>
                <label for="spin_wheel_preset_pointer_image"><?php esc_html_e( 'Pointer image URL', 'wp-spin-wheel' ); ?></label><br />
                <input type="text" id="spin_wheel_preset_pointer_image" name="spin_wheel_preset_pointer_image" value="<?php echo esc_attr( $pointer_image ); ?>" class="widefat" />
                <button type="button" class="button wp-spin-wheel-media-upload-button mt-2" data-target="spin_wheel_preset_pointer_image"><?php esc_html_e( 'Choose image', 'wp-spin-wheel' ); ?></button>
            </p>
            <p>
                <label for="spin_wheel_preset_pointer_size"><?php esc_html_e( 'Pointer size', 'wp-spin-wheel' ); ?></label><br />
                <input type="number" id="spin_wheel_preset_pointer_size" name="spin_wheel_preset_pointer_size" value="<?php echo esc_attr( $pointer_size ); ?>" class="small-text" min="20" />
            </p>
        </fieldset>
        <fieldset>
            <legend><?php esc_html_e( 'Font', 'wp-spin-wheel' ); ?></legend>
            <p>
                <label for="spin_wheel_preset_font_family"><?php esc_html_e( 'Font family', 'wp-spin-wheel' ); ?></label><br />
                <input type="text" id="spin_wheel_preset_font_family" name="spin_wheel_preset_font_family" value="<?php echo esc_attr( $font_family ); ?>" class="widefat" />
            </p>
            <p>
                <label for="spin_wheel_preset_font_size"><?php esc_html_e( 'Font size', 'wp-spin-wheel' ); ?></label><br />
                <input type="number" id="spin_wheel_preset_font_size" name="spin_wheel_preset_font_size" value="<?php echo esc_attr( $font_size ); ?>" class="small-text" min="10" />
            </p>
        </fieldset>
        <fieldset>
            <legend><?php esc_html_e( 'Animation', 'wp-spin-wheel' ); ?></legend>
            <p>
                <label for="spin_wheel_preset_animation_duration"><?php esc_html_e( 'Duration (seconds)', 'wp-spin-wheel' ); ?></label><br />
                <input type="number" id="spin_wheel_preset_animation_duration" name="spin_wheel_preset_animation_duration" value="<?php echo esc_attr( $animation_duration ); ?>" class="small-text" min="1" />
            </p>
            <p>
                <label>
                    <input type="checkbox" id="spin_wheel_preset_animation_confetti" name="spin_wheel_preset_animation_confetti" value="1" <?php checked( $animation_confetti ); ?> />
                    <?php esc_html_e( 'Confetti', 'wp-spin-wheel' ); ?>
                </label>
            </p>
        </fieldset>
        <fieldset>
            <legend><?php esc_html_e( 'Audio', 'wp-spin-wheel' ); ?></legend>
            <p>
                <label for="spin_wheel_preset_audio_spin"><?php esc_html_e( 'Spin sound URL', 'wp-spin-wheel' ); ?></label><br />
                <input type="text" id="spin_wheel_preset_audio_spin" name="spin_wheel_preset_audio_spin" value="<?php echo esc_attr( $audio_spin ); ?>" class="widefat" />
                <button type="button" class="button wp-spin-wheel-media-upload-button mt-2" data-target="spin_wheel_preset_audio_spin"><?php esc_html_e( 'Choose file', 'wp-spin-wheel' ); ?></button>
            </p>
            <p>
                <label for="spin_wheel_preset_audio_win"><?php esc_html_e( 'Win sound URL', 'wp-spin-wheel' ); ?></label><br />
                <input type="text" id="spin_wheel_preset_audio_win" name="spin_wheel_preset_audio_win" value="<?php echo esc_attr( $audio_win ); ?>" class="widefat" />
                <button type="button" class="button wp-spin-wheel-media-upload-button mt-2" data-target="spin_wheel_preset_audio_win"><?php esc_html_e( 'Choose file', 'wp-spin-wheel' ); ?></button>
            </p>
        </fieldset>
        <fieldset>
            <legend><?php esc_html_e( 'Custom CSS', 'wp-spin-wheel' ); ?></legend>
            <p>
                <label for="spin_wheel_preset_custom_css"><?php esc_html_e( 'Custom CSS', 'wp-spin-wheel' ); ?></label><br />
                <textarea id="spin_wheel_preset_custom_css" name="spin_wheel_preset_custom_css" class="widefat" rows="6"><?php echo esc_textarea( $custom_css ); ?></textarea>
            </p>
        </fieldset>
        <?php
    }

    public function save_meta_boxes( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( empty( $_POST['spin_wheel_preset_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['spin_wheel_preset_nonce'] ), 'spin_wheel_preset_save' ) ) {
            return;
        }

        if ( get_post_type( $post_id ) !== 'spin_wheel_preset' ) {
            return;
        }

        $background_type = sanitize_text_field( wp_unslash( $_POST['spin_wheel_preset_background_type'] ?? 'color' ) );
        if ( ! in_array( $background_type, array( 'color', 'image' ), true ) ) {
            $background_type = 'color';
        }

        $config = array(
            'background' => array(
                'type'  => $background_type,
                'value' => sanitize_text_field( wp_unslash( $_POST['spin_wheel_preset_background_color'] ?? '#ffffff' ) ),
                'image' => esc_url_raw( wp_unslash( $_POST['spin_wheel_preset_background_image'] ?? '' ) ),
            ),
            'wheel' => array(
                'size'         => max( 100, intval( wp_unslash( $_POST['spin_wheel_preset_wheel_size'] ?? 500 ) ) ),
                'border'       => max( 0, intval( wp_unslash( $_POST['spin_wheel_preset_wheel_border'] ?? 8 ) ) ),
                'border_color' => sanitize_text_field( wp_unslash( $_POST['spin_wheel_preset_wheel_border_color'] ?? '#ffffff' ) ),
                'shadow'       => isset( $_POST['spin_wheel_preset_wheel_shadow'] ) ? true : false,
            ),
            'button' => array(
                'text'             => sanitize_text_field( wp_unslash( $_POST['spin_wheel_preset_button_text'] ?? __( 'QUAY', 'wp-spin-wheel' ) ) ),
                'color'            => sanitize_text_field( wp_unslash( $_POST['spin_wheel_preset_button_color'] ?? '#ff0000' ) ),
                'text_color'       => sanitize_text_field( wp_unslash( $_POST['spin_wheel_preset_button_text_color'] ?? '#ffffff' ) ),
                'radius'           => max( 0, intval( wp_unslash( $_POST['spin_wheel_preset_button_radius'] ?? 50 ) ) ),
                'background_image' => esc_url_raw( wp_unslash( $_POST['spin_wheel_preset_button_background_image'] ?? '' ) ),
            ),
            'pointer' => array(
                'image' => esc_url_raw( wp_unslash( $_POST['spin_wheel_preset_pointer_image'] ?? '' ) ),
                'size'  => max( 20, intval( wp_unslash( $_POST['spin_wheel_preset_pointer_size'] ?? 80 ) ) ),
            ),
            'font' => array(
                'family' => sanitize_text_field( wp_unslash( $_POST['spin_wheel_preset_font_family'] ?? 'Arial' ) ),
                'size'   => max( 10, intval( wp_unslash( $_POST['spin_wheel_preset_font_size'] ?? 20 ) ) ),
            ),
            'animation' => array(
                'duration' => max( 1, intval( wp_unslash( $_POST['spin_wheel_preset_animation_duration'] ?? 6 ) ) ),
                'confetti' => isset( $_POST['spin_wheel_preset_animation_confetti'] ) ? true : false,
            ),
            'audio' => array(
                'spin' => esc_url_raw( wp_unslash( $_POST['spin_wheel_preset_audio_spin'] ?? '' ) ),
                'win'  => esc_url_raw( wp_unslash( $_POST['spin_wheel_preset_audio_win'] ?? '' ) ),
            ),
            'custom_css' => sanitize_textarea_field( wp_unslash( $_POST['spin_wheel_preset_custom_css'] ?? '' ) ),
        );

        update_post_meta( $post_id, '_spin_wheel_preset_config', wp_json_encode( $config ) );
    }

    public static function create_default_presets() {
        if ( get_option( 'wp_spin_wheel_default_presets_created' ) ) {
            return;
        }

        $existing = get_posts( array(
            'post_type'      => 'spin_wheel_preset',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ) );

        if ( ! empty( $existing ) ) {
            update_option( 'wp_spin_wheel_default_presets_created', 1 );
            return;
        }

        $defaults = array(
            array(
                'title'   => 'Noel',
                'content' => __( 'Noel preset with festive colors and shadow.', 'wp-spin-wheel' ),
                'config'  => array(
                    'background' => array( 'type' => 'color', 'value' => '#f4f7f8' ),
                    'wheel'      => array( 'size' => 500, 'border' => 8, 'border_color' => '#ffffff', 'shadow' => true ),
                    'button'     => array( 'text' => 'QUAY', 'color' => '#d32f2f', 'text_color' => '#ffffff', 'radius' => 50 ),
                    'pointer'    => array( 'image' => '', 'size' => 80 ),
                    'font'       => array( 'family' => 'Arial', 'size' => 20 ),
                    'animation'  => array( 'duration' => 6, 'confetti' => true ),
                    'audio'      => array( 'spin' => '', 'win' => '' ),
                ),
            ),
            array(
                'title'   => 'Tết',
                'content' => __( 'Tết preset with red and gold styling.', 'wp-spin-wheel' ),
                'config'  => array(
                    'background' => array( 'type' => 'color', 'value' => '#fff7e6' ),
                    'wheel'      => array( 'size' => 500, 'border' => 8, 'border_color' => '#ffd700', 'shadow' => true ),
                    'button'     => array( 'text' => 'QUAY', 'color' => '#c62828', 'text_color' => '#ffffff', 'radius' => 45 ),
                    'pointer'    => array( 'image' => '', 'size' => 80 ),
                    'font'       => array( 'family' => 'Arial', 'size' => 20 ),
                    'animation'  => array( 'duration' => 6, 'confetti' => true ),
                    'audio'      => array( 'spin' => '', 'win' => '' ),
                ),
            ),
            array(
                'title'   => 'Black Friday',
                'content' => __( 'Black Friday preset with dark theme and yellow highlights.', 'wp-spin-wheel' ),
                'config'  => array(
                    'background' => array( 'type' => 'color', 'value' => '#111111' ),
                    'wheel'      => array( 'size' => 500, 'border' => 8, 'border_color' => '#ffc107', 'shadow' => true ),
                    'button'     => array( 'text' => 'QUAY', 'color' => '#000000', 'text_color' => '#ffffff', 'radius' => 45 ),
                    'pointer'    => array( 'image' => '', 'size' => 80 ),
                    'font'       => array( 'family' => 'Arial', 'size' => 20 ),
                    'animation'  => array( 'duration' => 6, 'confetti' => true ),
                    'audio'      => array( 'spin' => '', 'win' => '' ),
                ),
            ),
            array(
                'title'   => 'Birthday',
                'content' => __( 'Birthday preset with cheerful pastel colors.', 'wp-spin-wheel' ),
                'config'  => array(
                    'background' => array( 'type' => 'color', 'value' => '#fff0f8' ),
                    'wheel'      => array( 'size' => 500, 'border' => 8, 'border_color' => '#ff80ab', 'shadow' => true ),
                    'button'     => array( 'text' => 'QUAY', 'color' => '#8e24aa', 'text_color' => '#ffffff', 'radius' => 45 ),
                    'pointer'    => array( 'image' => '', 'size' => 80 ),
                    'font'       => array( 'family' => 'Arial', 'size' => 20 ),
                    'animation'  => array( 'duration' => 6, 'confetti' => true ),
                    'audio'      => array( 'spin' => '', 'win' => '' ),
                ),
            ),
            array(
                'title'   => 'Default',
                'content' => __( 'Default preset with neutral styling.', 'wp-spin-wheel' ),
                'config'  => array(
                    'background' => array( 'type' => 'color', 'value' => '#ffffff' ),
                    'wheel'      => array( 'size' => 500, 'border' => 8, 'border_color' => '#dddddd', 'shadow' => true ),
                    'button'     => array( 'text' => 'QUAY', 'color' => '#2196f3', 'text_color' => '#ffffff', 'radius' => 45 ),
                    'pointer'    => array( 'image' => '', 'size' => 80 ),
                    'font'       => array( 'family' => 'Arial', 'size' => 20 ),
                    'animation'  => array( 'duration' => 6, 'confetti' => false ),
                    'audio'      => array( 'spin' => '', 'win' => '' ),
                ),
            ),
            array(
                'title'   => 'Custom',
                'content' => __( 'Custom preset for flexible wheel design.', 'wp-spin-wheel' ),
                'config'  => array(
                    'background' => array( 'type' => 'color', 'value' => '#ffffff' ),
                    'wheel'      => array( 'size' => 500, 'border' => 8, 'border_color' => '#ffffff', 'shadow' => false ),
                    'button'     => array( 'text' => 'QUAY', 'color' => '#4caf50', 'text_color' => '#ffffff', 'radius' => 45 ),
                    'pointer'    => array( 'image' => '', 'size' => 80 ),
                    'font'       => array( 'family' => 'Arial', 'size' => 20 ),
                    'animation'  => array( 'duration' => 6, 'confetti' => false ),
                    'audio'      => array( 'spin' => '', 'win' => '' ),
                ),
            ),
        );

        foreach ( $defaults as $preset ) {
            $preset_id = wp_insert_post( array(
                'post_title'   => $preset['title'],
                'post_content' => $preset['content'],
                'post_status'  => 'publish',
                'post_type'    => 'spin_wheel_preset',
            ) );

            if ( $preset_id && ! is_wp_error( $preset_id ) ) {
                update_post_meta( $preset_id, '_spin_wheel_preset_config', wp_json_encode( $preset['config'] ) );
            }
        }

        update_option( 'wp_spin_wheel_default_presets_created', 1 );
    }
}
