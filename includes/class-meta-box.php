<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Meta_Box {
    public function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'register_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta_boxes' ) );
    }

    public function register_meta_boxes() {
        add_meta_box(
            'spin_wheel_settings',
            __( 'Wheel Settings', 'wp-spin-wheel' ),
            array( $this, 'render_settings_meta_box' ),
            'spin_wheel',
            'normal',
            'default'
        );

        add_meta_box(
            'spin_wheel_prizes',
            __( 'Wheel Prizes', 'wp-spin-wheel' ),
            array( $this, 'render_prizes_meta_box' ),
            'spin_wheel',
            'normal',
            'default'
        );
    }

    public function render_settings_meta_box( $post ) {
        wp_nonce_field( 'spin_wheel_save', 'spin_wheel_nonce' );

        $background   = get_post_meta( $post->ID, '_spin_wheel_background', true );
        $logo         = get_post_meta( $post->ID, '_spin_wheel_logo', true );
        $music        = get_post_meta( $post->ID, '_spin_wheel_music', true );
        $sound        = get_post_meta( $post->ID, '_spin_wheel_sound', true );
        $effect       = get_post_meta( $post->ID, '_spin_wheel_effect', true );
        $spin_limit   = get_post_meta( $post->ID, '_spin_wheel_limit', true );
        $form_fields  = get_post_meta( $post->ID, '_spin_wheel_form_fields', true );
        $preset_id    = get_post_meta( $post->ID, '_spin_wheel_preset_id', true );
        $form_fields  = is_array( $form_fields ) ? $form_fields : array();

        $presets = get_posts( array(
            'post_type'      => 'spin_wheel_preset',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );
        ?>
        <p>
            <label for="spin_wheel_preset_id"><?php esc_html_e( 'Preset', 'wp-spin-wheel' ); ?></label><br />
            <select id="spin_wheel_preset_id" name="spin_wheel_preset_id" class="widefat">
                <option value=""><?php esc_html_e( 'Select preset', 'wp-spin-wheel' ); ?></option>
                <?php foreach ( $presets as $preset ) : ?>
                    <option value="<?php echo esc_attr( $preset->ID ); ?>" <?php selected( $preset_id, $preset->ID ); ?>><?php echo esc_html( $preset->post_title ); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="spin_wheel_background"><?php esc_html_e( 'Background color', 'wp-spin-wheel' ); ?></label><br />
            <input type="text" id="spin_wheel_background" name="spin_wheel_background" value="<?php echo esc_attr( $background ); ?>" class="widefat" />
        </p>
        <p>
            <label for="spin_wheel_logo"><?php esc_html_e( 'Logo URL', 'wp-spin-wheel' ); ?></label><br />
            <input type="text" id="spin_wheel_logo" name="spin_wheel_logo" value="<?php echo esc_attr( $logo ); ?>" class="widefat" />
        </p>
        <p>
            <label for="spin_wheel_music"><?php esc_html_e( 'Music URL', 'wp-spin-wheel' ); ?></label><br />
            <input type="text" id="spin_wheel_music" name="spin_wheel_music" value="<?php echo esc_attr( $music ); ?>" class="widefat" />
        </p>
        <p>
            <label for="spin_wheel_sound"><?php esc_html_e( 'Sound URL', 'wp-spin-wheel' ); ?></label><br />
            <input type="text" id="spin_wheel_sound" name="spin_wheel_sound" value="<?php echo esc_attr( $sound ); ?>" class="widefat" />
        </p>
        <p>
            <label for="spin_wheel_effect"><?php esc_html_e( 'Effect', 'wp-spin-wheel' ); ?></label><br />
            <input type="text" id="spin_wheel_effect" name="spin_wheel_effect" value="<?php echo esc_attr( $effect ); ?>" class="widefat" />
        </p>
        <p>
            <label for="spin_wheel_limit"><?php esc_html_e( 'Spin limit', 'wp-spin-wheel' ); ?></label><br />
            <input type="number" id="spin_wheel_limit" name="spin_wheel_limit" value="<?php echo esc_attr( $spin_limit ); ?>" class="small-text" min="0" />
        </p>
        <fieldset>
            <legend><?php esc_html_e( 'Form fields', 'wp-spin-wheel' ); ?></legend>
            <?php foreach ( array( 'name' => 'Họ tên', 'email' => 'Email', 'phone' => 'SĐT', 'address' => 'Địa chỉ', 'company' => 'Công ty' ) as $key => $label ) : ?>
                <p>
                    <label>
                        <input type="checkbox" name="spin_wheel_form_fields[]" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $form_fields, true ) ); ?> />
                        <?php echo esc_html( $label ); ?>
                    </label>
                </p>
            <?php endforeach; ?>
        </fieldset>
        <?php
    }

    public function render_prizes_meta_box( $post ) {
        $prizes = get_post_meta( $post->ID, '_spin_wheel_prizes', true );
        $prizes = is_array( $prizes ) ? $prizes : array();
        ?>
        <div id="spin-wheel-prizes">
            <p><button type="button" class="button button-primary" id="add-spin-prize"><?php esc_html_e( 'Add Prize', 'wp-spin-wheel' ); ?></button></p>
            <?php if ( empty( $prizes ) ) : ?>
                <p><?php esc_html_e( 'No prizes added yet.', 'wp-spin-wheel' ); ?></p>
            <?php endif; ?>
            <?php foreach ( $prizes as $index => $prize ) : ?>
                <div class="spin-prize-item">
                    <h4><?php echo esc_html( $prize['title'] ?? '' ); ?></h4>
                    <p>
                        <label><?php esc_html_e( 'Title', 'wp-spin-wheel' ); ?><br />
                        <input type="text" name="spin_wheel_prizes[<?php echo $index; ?>][title]" value="<?php echo esc_attr( $prize['title'] ?? '' ); ?>" class="widefat" /></label>
                    </p>
                    <p>
                        <label><?php esc_html_e( 'Color', 'wp-spin-wheel' ); ?><br />
                        <input type="text" name="spin_wheel_prizes[<?php echo $index; ?>][color]" value="<?php echo esc_attr( $prize['color'] ?? '' ); ?>" class="widefat" /></label>
                    </p>
                    <p>
                        <label><?php esc_html_e( 'Icon URL', 'wp-spin-wheel' ); ?><br />
                        <input type="text" name="spin_wheel_prizes[<?php echo $index; ?>][icon]" value="<?php echo esc_attr( $prize['icon'] ?? '' ); ?>" class="widefat" /></label>
                    </p>
                    <p>
                        <label><?php esc_html_e( 'Image URL', 'wp-spin-wheel' ); ?><br />
                        <input type="text" name="spin_wheel_prizes[<?php echo $index; ?>][image]" value="<?php echo esc_attr( $prize['image'] ?? '' ); ?>" class="widefat" /></label>
                    </p>
                    <p>
                        <label><?php esc_html_e( 'Weight', 'wp-spin-wheel' ); ?><br />
                        <input type="number" name="spin_wheel_prizes[<?php echo $index; ?>][weight]" value="<?php echo esc_attr( $prize['weight'] ?? '1' ); ?>" class="small-text" min="1" /></label>
                    </p>
                    <p>
                        <label><?php esc_html_e( 'Stock', 'wp-spin-wheel' ); ?><br />
                        <input type="number" name="spin_wheel_prizes[<?php echo $index; ?>][stock]" value="<?php echo esc_attr( $prize['stock'] ?? '0' ); ?>" class="small-text" min="0" /></label>
                    </p>
                    <p>
                        <label><?php esc_html_e( 'Description', 'wp-spin-wheel' ); ?><br />
                        <textarea name="spin_wheel_prizes[<?php echo $index; ?>][description]" class="widefat"><?php echo esc_textarea( $prize['description'] ?? '' ); ?></textarea></label>
                    </p>
                    <p><button type="button" class="button remove-spin-prize"><?php esc_html_e( 'Remove prize', 'wp-spin-wheel' ); ?></button></p>
                    <hr />
                </div>
            <?php endforeach; ?>
        </div>
        <script type="text/html" id="spin-wheel-prize-template">
            <div class="spin-prize-item">
                <h4><?php esc_html_e( 'New prize', 'wp-spin-wheel' ); ?></h4>
                <p>
                    <label><?php esc_html_e( 'Title', 'wp-spin-wheel' ); ?><br />
                    <input type="text" name="spin_wheel_prizes[__index__][title]" value="" class="widefat" /></label>
                </p>
                <p>
                    <label><?php esc_html_e( 'Color', 'wp-spin-wheel' ); ?><br />
                    <input type="text" name="spin_wheel_prizes[__index__][color]" value="" class="widefat" /></label>
                </p>
                <p>
                    <label><?php esc_html_e( 'Icon URL', 'wp-spin-wheel' ); ?><br />
                    <input type="text" name="spin_wheel_prizes[__index__][icon]" value="" class="widefat" /></label>
                </p>
                <p>
                    <label><?php esc_html_e( 'Image URL', 'wp-spin-wheel' ); ?><br />
                    <input type="text" name="spin_wheel_prizes[__index__][image]" value="" class="widefat" /></label>
                </p>
                <p>
                    <label><?php esc_html_e( 'Weight', 'wp-spin-wheel' ); ?><br />
                    <input type="number" name="spin_wheel_prizes[__index__][weight]" value="1" class="small-text" min="1" /></label>
                </p>
                <p>
                    <label><?php esc_html_e( 'Stock', 'wp-spin-wheel' ); ?><br />
                    <input type="number" name="spin_wheel_prizes[__index__][stock]" value="0" class="small-text" min="0" /></label>
                </p>
                <p>
                    <label><?php esc_html_e( 'Description', 'wp-spin-wheel' ); ?><br />
                    <textarea name="spin_wheel_prizes[__index__][description]" class="widefat"></textarea></label>
                </p>
                <p><button type="button" class="button remove-spin-prize"><?php esc_html_e( 'Remove prize', 'wp-spin-wheel' ); ?></button></p>
                <hr />
            </div>
        </script>
        <?php
    }

    public function save_meta_boxes( $post_id ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( empty( $_POST['spin_wheel_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['spin_wheel_nonce'] ), 'spin_wheel_save' ) ) {
            return;
        }

        if ( isset( $_POST['spin_wheel_preset_id'] ) ) {
            $preset_id = absint( wp_unslash( $_POST['spin_wheel_preset_id'] ) );
            update_post_meta( $post_id, '_spin_wheel_preset_id', $preset_id );
        } else {
            $preset_id = 0;
            delete_post_meta( $post_id, '_spin_wheel_preset_id' );
        }

        $manual_design = array();

        if ( isset( $_POST['spin_wheel_background'] ) ) {
            $background = sanitize_text_field( wp_unslash( $_POST['spin_wheel_background'] ) );
            update_post_meta( $post_id, '_spin_wheel_background', $background );
            $manual_design['background'] = array( 'type' => 'color', 'value' => $background );
        }
        if ( isset( $_POST['spin_wheel_logo'] ) ) {
            $logo = esc_url_raw( wp_unslash( $_POST['spin_wheel_logo'] ) );
            update_post_meta( $post_id, '_spin_wheel_logo', $logo );
            $manual_design['logo'] = $logo;
        }
        if ( isset( $_POST['spin_wheel_music'] ) ) {
            $music = esc_url_raw( wp_unslash( $_POST['spin_wheel_music'] ) );
            update_post_meta( $post_id, '_spin_wheel_music', $music );
            $manual_design['music'] = $music;
        }
        if ( isset( $_POST['spin_wheel_sound'] ) ) {
            $sound = esc_url_raw( wp_unslash( $_POST['spin_wheel_sound'] ) );
            update_post_meta( $post_id, '_spin_wheel_sound', $sound );
            $manual_design['sound'] = $sound;
        }
        if ( isset( $_POST['spin_wheel_effect'] ) ) {
            $effect = sanitize_text_field( wp_unslash( $_POST['spin_wheel_effect'] ) );
            update_post_meta( $post_id, '_spin_wheel_effect', $effect );
            $manual_design['effect'] = $effect;
        }
        if ( isset( $_POST['spin_wheel_limit'] ) ) {
            $spin_limit = intval( wp_unslash( $_POST['spin_wheel_limit'] ) );
            update_post_meta( $post_id, '_spin_wheel_limit', $spin_limit );
            $manual_design['spin_limit'] = $spin_limit;
        }
        if ( isset( $_POST['spin_wheel_form_fields'] ) && is_array( $_POST['spin_wheel_form_fields'] ) ) {
            $fields = array_map( 'sanitize_text_field', wp_unslash( $_POST['spin_wheel_form_fields'] ) );
            update_post_meta( $post_id, '_spin_wheel_form_fields', $fields );
            $manual_design['form_fields'] = $fields;
        } else {
            delete_post_meta( $post_id, '_spin_wheel_form_fields' );
        }

        if ( ! empty( $preset_id ) && get_post_type( $preset_id ) === 'spin_wheel_preset' ) {
            $preset_config = WP_Spin_Wheel_Helper::get_preset_config( $preset_id );
            if ( ! empty( $preset_config ) ) {
                $preset_config['preset_id'] = $preset_id;
                if ( ! empty( $manual_design ) ) {
                    $preset_config = array_replace_recursive( $preset_config, $manual_design );
                }
                update_post_meta( $post_id, '_spin_wheel_design', wp_json_encode( $preset_config ) );
            }
        } elseif ( ! empty( $manual_design ) ) {
            update_post_meta( $post_id, '_spin_wheel_design', wp_json_encode( $manual_design ) );
        } else {
            delete_post_meta( $post_id, '_spin_wheel_design' );
        }

        if ( isset( $_POST['spin_wheel_prizes'] ) && is_array( $_POST['spin_wheel_prizes'] ) ) {
            $prizes = array();
            foreach ( $_POST['spin_wheel_prizes'] as $prize ) {
                if ( empty( $prize['title'] ) ) {
                    continue;
                }
                $prizes[] = array(
                    'title'       => sanitize_text_field( $prize['title'] ),
                    'color'       => sanitize_text_field( $prize['color'] ),
                    'icon'        => esc_url_raw( $prize['icon'] ),
                    'image'       => esc_url_raw( $prize['image'] ),
                    'weight'      => max( 1, intval( $prize['weight'] ) ),
                    'stock'       => max( 0, intval( $prize['stock'] ) ),
                    'description' => sanitize_textarea_field( $prize['description'] ),
                );
            }
            update_post_meta( $post_id, '_spin_wheel_prizes', $prizes );
            $this->sync_prizes_to_db( $post_id, $prizes );
        } else {
            delete_post_meta( $post_id, '_spin_wheel_prizes' );
            $this->delete_prizes_from_db( $post_id );
        }

        if ( ! empty( $preset_id ) && get_post_type( $preset_id ) === 'spin_wheel_preset' ) {
            $preset_config = WP_Spin_Wheel_Helper::get_preset_config( $preset_id );
            if ( ! empty( $preset_config ) ) {
                update_post_meta( $post_id, '_spin_wheel_design', wp_json_encode( $preset_config ) );
            }
        } else {
            delete_post_meta( $post_id, '_spin_wheel_design' );
        }
    }

    private function sync_prizes_to_db( $post_id, $prizes ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spin_prizes';

        $wpdb->delete( $table, array( 'wheel_id' => $post_id ), array( '%d' ) );
        foreach ( $prizes as $prize ) {
            $wpdb->insert(
                $table,
                array(
                    'wheel_id'    => $post_id,
                    'title'       => $prize['title'],
                    'color'       => $prize['color'],
                    'weight'      => $prize['weight'],
                    'image'       => $prize['image'],
                    'stock'       => $prize['stock'],
                    'description' => $prize['description'],
                    'created_at'  => current_time( 'mysql' ),
                ),
                array( '%d', '%s', '%s', '%d', '%s', '%d', '%s', '%s' )
            );
        }
    }

    private function delete_prizes_from_db( $post_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spin_prizes';
        $wpdb->delete( $table, array( 'wheel_id' => $post_id ), array( '%d' ) );
    }
}
