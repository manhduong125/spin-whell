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
        global $post;

        if ( $this->is_new_spin_wheel( $post ) ) {
            return;
        }

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

    private function is_new_spin_wheel( $post ) {
        if ( ! $post || 'spin_wheel' !== get_post_type( $post ) ) {
            return false;
        }

        return empty( $post->ID ) || 'auto-draft' === $post->post_status;
    }

    public function render_settings_meta_box( $post ) {
        wp_nonce_field( 'spin_wheel_save', 'spin_wheel_nonce' );

        $settings = WP_Spin_Wheel_Helper::get_wheel_settings( $post->ID );
        $background = $settings['background'] ?? array( 'type' => 'color', 'value' => '#ffffff', 'image' => '' );
        $logo = $settings['logo'] ?? '';
        $music = $settings['music'] ?? '';
        $sound = $settings['sound'] ?? '';
        $effect = $settings['effect'] ?? '';
        $spin_limit = $settings['spin_limit'] ?? 0;
        $spin_limit_type = $settings['spin_limit_type'] ?? 'none';
        $form_fields = is_array( $settings['form_fields'] ) ? $settings['form_fields'] : array();
        $selected_background_id = sanitize_text_field( wp_unslash( $settings['selected_background_id'] ?? '' ) );
        $selected_button_id = sanitize_text_field( wp_unslash( $settings['selected_button_id'] ?? '' ) );
        $selected_pointer_id = sanitize_text_field( wp_unslash( $settings['selected_pointer_id'] ?? '' ) );
        ?>
        <p>
            <label for="spin_wheel_selected_background_id"><?php esc_html_e( 'Background item', 'wp-spin-wheel' ); ?></label><br />
            <select id="spin_wheel_selected_background_id" name="spin_wheel_selected_background_id" class="widefat">
                <option value=""><?php esc_html_e( 'Use default', 'wp-spin-wheel' ); ?></option>
                <?php $background_items = WP_Spin_Wheel_Helper::get_setting_items( 'background' ); ?>
                <?php foreach ( $background_items as $item ) : ?>
                    <option value="<?php echo esc_attr( $item['id'] ?? '' ); ?>" <?php selected( $selected_background_id, $item['id'] ?? '' ); ?>><?php echo esc_html( $item['name'] ?? '' ); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="spin_wheel_selected_button_id"><?php esc_html_e( 'Button item', 'wp-spin-wheel' ); ?></label><br />
            <select id="spin_wheel_selected_button_id" name="spin_wheel_selected_button_id" class="widefat">
                <option value=""><?php esc_html_e( 'Use default', 'wp-spin-wheel' ); ?></option>
                <?php $button_items = WP_Spin_Wheel_Helper::get_setting_items( 'button' ); ?>
                <?php foreach ( $button_items as $item ) : ?>
                    <option value="<?php echo esc_attr( $item['id'] ?? '' ); ?>" <?php selected( $selected_button_id, $item['id'] ?? '' ); ?>><?php echo esc_html( $item['name'] ?? '' ); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="spin_wheel_selected_pointer_id"><?php esc_html_e( 'Pointer item', 'wp-spin-wheel' ); ?></label><br />
            <select id="spin_wheel_selected_pointer_id" name="spin_wheel_selected_pointer_id" class="widefat">
                <option value=""><?php esc_html_e( 'Use default', 'wp-spin-wheel' ); ?></option>
                <?php $pointer_items = WP_Spin_Wheel_Helper::get_setting_items( 'pointer' ); ?>
                <?php foreach ( $pointer_items as $item ) : ?>
                    <option value="<?php echo esc_attr( $item['id'] ?? '' ); ?>" <?php selected( $selected_pointer_id, $item['id'] ?? '' ); ?>><?php echo esc_html( $item['name'] ?? '' ); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="spin_wheel_background"><?php esc_html_e( 'Background color fallback', 'wp-spin-wheel' ); ?></label><br />
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
        <p>
            <label for="spin_wheel_limit_type"><?php esc_html_e( 'Limit type', 'wp-spin-wheel' ); ?></label><br />
            <select id="spin_wheel_limit_type" name="spin_wheel_limit_type" class="widefat">
                <option value="none" <?php selected( $spin_limit_type, 'none' ); ?>><?php esc_html_e( 'None', 'wp-spin-wheel' ); ?></option>
                <option value="per_ip" <?php selected( $spin_limit_type, 'per_ip' ); ?>><?php esc_html_e( 'Per IP', 'wp-spin-wheel' ); ?></option>
                <option value="per_email" <?php selected( $spin_limit_type, 'per_email' ); ?>><?php esc_html_e( 'Per Email', 'wp-spin-wheel' ); ?></option>
                <option value="per_phone" <?php selected( $spin_limit_type, 'per_phone' ); ?>><?php esc_html_e( 'Per Phone', 'wp-spin-wheel' ); ?></option>
            </select>
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
        $prizes = WP_Spin_Wheel_Prize::get_prizes( $post->ID );
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

        $overrides = array();

        if ( isset( $_POST['spin_wheel_background'] ) ) {
            $background = sanitize_text_field( wp_unslash( $_POST['spin_wheel_background'] ) );
            $overrides['background'] = array( 'type' => 'color', 'value' => $background );
        }
        if ( isset( $_POST['spin_wheel_selected_background_id'] ) ) {
            $selected_background_id = sanitize_text_field( wp_unslash( $_POST['spin_wheel_selected_background_id'] ) );
            $overrides['selected_background_id'] = $selected_background_id;
        }
        if ( isset( $_POST['spin_wheel_selected_button_id'] ) ) {
            $selected_button_id = sanitize_text_field( wp_unslash( $_POST['spin_wheel_selected_button_id'] ) );
            $overrides['selected_button_id'] = $selected_button_id;
        }
        if ( isset( $_POST['spin_wheel_selected_pointer_id'] ) ) {
            $selected_pointer_id = sanitize_text_field( wp_unslash( $_POST['spin_wheel_selected_pointer_id'] ) );
            $overrides['selected_pointer_id'] = $selected_pointer_id;
        }
        if ( isset( $_POST['spin_wheel_logo'] ) ) {
            $logo = esc_url_raw( wp_unslash( $_POST['spin_wheel_logo'] ) );
            $overrides['logo'] = $logo;
        }
        if ( isset( $_POST['spin_wheel_music'] ) ) {
            $music = esc_url_raw( wp_unslash( $_POST['spin_wheel_music'] ) );
            $overrides['music'] = $music;
        }
        if ( isset( $_POST['spin_wheel_sound'] ) ) {
            $sound = esc_url_raw( wp_unslash( $_POST['spin_wheel_sound'] ) );
            $overrides['sound'] = $sound;
        }
        if ( isset( $_POST['spin_wheel_effect'] ) ) {
            $effect = sanitize_text_field( wp_unslash( $_POST['spin_wheel_effect'] ) );
            $overrides['effect'] = $effect;
        }
        if ( isset( $_POST['spin_wheel_limit'] ) ) {
            $spin_limit = intval( wp_unslash( $_POST['spin_wheel_limit'] ) );
            $overrides['spin_limit'] = $spin_limit;
        }
        if ( isset( $_POST['spin_wheel_limit_type'] ) ) {
            $limit_type = sanitize_text_field( wp_unslash( $_POST['spin_wheel_limit_type'] ) );
            if ( in_array( $limit_type, array( 'none', 'per_ip', 'per_email', 'per_phone', 'per_cookie' ), true ) ) {
                $overrides['spin_limit_type'] = $limit_type;
            }
        }
        if ( isset( $_POST['spin_wheel_form_fields'] ) && is_array( $_POST['spin_wheel_form_fields'] ) ) {
            $fields = array_map( 'sanitize_text_field', wp_unslash( $_POST['spin_wheel_form_fields'] ) );
            $overrides['form_fields'] = $fields;
        }

        if ( ! empty( $overrides ) ) {
            update_post_meta( $post_id, '_spin_wheel_overrides', wp_json_encode( $overrides ) );
        } else {
            delete_post_meta( $post_id, '_spin_wheel_overrides' );
        }

        delete_post_meta( $post_id, '_spin_wheel_design' );
        delete_post_meta( $post_id, '_spin_wheel_background' );
        delete_post_meta( $post_id, '_spin_wheel_logo' );
        delete_post_meta( $post_id, '_spin_wheel_music' );
        delete_post_meta( $post_id, '_spin_wheel_sound' );
        delete_post_meta( $post_id, '_spin_wheel_effect' );
        delete_post_meta( $post_id, '_spin_wheel_limit' );
        delete_post_meta( $post_id, '_spin_wheel_form_fields' );

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

        delete_post_meta( $post_id, '_spin_wheel_design' );
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
