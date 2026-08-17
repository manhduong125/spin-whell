<?php
    if (! defined('ABSPATH')) {
        exit;
    }

    class WP_Spin_Wheel_Settings
    {
        const OPTION_KEY = 'wp_spin_wheel_settings';

        public function __construct()
        {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_init', array($this, 'register_settings'));
            add_action('rest_api_init', array($this, 'register_rest_routes'));
        }

        public function add_admin_menu()
        {
            if (! is_admin()) {
                return;
            }
        }

        public function register_settings()
        {
            register_setting('wp_spin_wheel', self::OPTION_KEY, array($this, 'sanitize_settings'));
        }

        public function sanitize_settings($input)
        {
            $input = is_array($input) ? $input : array();
            $options = $this->get_option();
            $schema = $this->get_setting_schema();

            foreach ($schema as $key => $field) {
                if (! array_key_exists($key, $input)) {
                    continue;
                }

                $options[$key] = $this->sanitize_setting_value($key, $input[$key], $field);
            }

            foreach ($input as $key => $value) {
                if (isset($schema[$key])) {
                    continue;
                }

                $options[$key] = $this->sanitize_setting_value($key, $value, array('type' => 'text'));
            }

            return $options;
        }

        public function get_setting_schema()
        {
            return array(
                'duration'                => array('type' => 'text',     'default' => '6'),
                'show_confetti'           => array('type' => 'checkbox', 'default' => 0),
                'auto_remove'             => array('type' => 'checkbox', 'default' => 0),
                'show_popup'              => array('type' => 'checkbox', 'default' => 0),
                'popup_label'             => array('type' => 'text',     'default' => ''),
                'show_remove_button'      => array('type' => 'checkbox', 'default' => 0),
                'show_hide_button'        => array('type' => 'checkbox', 'default' => 0),
                'switch_cover_img'        => array('type' => 'checkbox', 'default' => 0),
                'cover_img'               => array('type' => 'image',    'default' => ''),
                'wheel_title'             => array('type' => 'text',     'default' => ''),
                'wheel_description'       => array('type' => 'textarea', 'default' => ''),
                'wheel_background_color'  => array('type' => 'color',    'default' => ''),
                'wheel_button_text'       => array('type' => 'text',     'default' => ''),
                'wheel_button_color'      => array('type' => 'color',    'default' => ''),
                'wheel_button_text_color' => array('type' => 'color',    'default' => ''),
                'wheel_border_color'      => array('type' => 'color',    'default' => ''),
                'wheel_background_image'  => array('type' => 'image',    'default' => ''),
                'wheel_animation_duration' => array('type' => 'text',     'default' => '6'),
                'wheel_confetti'          => array('type' => 'checkbox', 'default' => 0),
                'wheel_segment_colors'    => array('type' => 'json',     'default' => array()),
                'wheel_extra_config'      => array('type' => 'json',     'default' => array()),
            );
        }

        public function get_default_settings()
        {
            $defaults = array();
            foreach ($this->get_setting_schema() as $key => $field) {
                $defaults[$key] = isset($field['default']) ? $field['default'] : '';
            }

            return $defaults;
        }

        public function get_option()
        {
            $saved = get_option(self::OPTION_KEY, array());
            if (! is_array($saved)) {
                $saved = array();
            }

            return wp_parse_args($saved, $this->get_default_settings());
        }

        public function sanitize_setting_value($key, $value, $field = array())
        {
            $field = is_array($field) ? $field : array();
            $type = isset($field['type']) ? $field['type'] : 'text';

            if ('checkbox' === $type) {
                return ! empty($value) ? 1 : 0;
            }

            if ('color' === $type) {
                return is_string($value) ? sanitize_hex_color($value) : '';
            }

            if ('image' === $type) {
                return is_string($value) ? esc_url_raw(trim($value)) : '';
            }

            if ('textarea' === $type) {
                return is_string($value) ? sanitize_textarea_field(wp_unslash($value)) : '';
            }

            if ('json' === $type) {
                if (is_array($value) || is_object($value)) {
                    return wp_unslash($value);
                }

                if (is_string($value)) {
                    $decoded = json_decode(wp_unslash($value), true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $decoded;
                    }
                    return sanitize_text_field(wp_unslash($value));
                }

                return array();
            }

            if ('select' === $type || 'text' === $type) {
                return is_string($value) ? sanitize_text_field(wp_unslash($value)) : '';
            }

            if (is_array($value) || is_object($value)) {
                return wp_unslash($value);
            }

            return is_string($value) ? sanitize_text_field(wp_unslash($value)) : '';
        }

        public function add_or_update_setting($key, $value, $type = 'text')
        {
            $options = $this->get_option();
            $options[$key] = $this->sanitize_setting_value($key, $value, array('type' => $type));
            update_option(self::OPTION_KEY, $options);
            return $options[$key];
        }

        public function delete_setting($key)
        {
            $options = $this->get_option();
            if (isset($options[$key])) {
                unset($options[$key]);
                update_option(self::OPTION_KEY, $options);
                return true;
            }

            return false;
        }

        public function render_settings_page()
        {
            if (! current_user_can('manage_options')) {
                return;
            }

            $options = $this->get_option();
            $form_action = esc_url(admin_url('admin.php?page=wp-spin-wheel-settings'));
    ?>
<div class="wrap wp-spin-wheel-settings-page">
    <h1><?php esc_html_e('Spin Wheel Settings', 'wp-spin-wheel'); ?></h1>

    <?php if (! empty($_GET['updated'])) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Đã lưu thành công.', 'wp-spin-wheel'); ?></p>
        </div>
    <?php endif; ?>
</div>
<?php
        }

        public function register_rest_routes()
        {
            register_rest_route('spin-wheel/v1', '/settings', array(
                array(
                    'methods' => 'GET',
                    'callback' => array($this, 'rest_get_settings'),
                    'permission_callback' => function () {
                        return current_user_can('edit_theme_options');
                    }
                ),
                array(
                    'methods' => 'POST',
                    'callback' => array($this, 'rest_update_settings'),
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    }
                ),
            ));

            register_rest_route('spin-wheel/v1', '/settings/option', array(
                array(
                    'methods' => 'POST',
                    'callback' => array($this, 'rest_manage_setting'),
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    }
                ),
            ));
        }

        public function rest_get_settings($request)
        {
            return rest_ensure_response($this->get_option());
        }

        public function rest_update_settings($request)
        {
            $params = $request->get_json_params();
            $san = $this->sanitize_settings($params);
            update_option(self::OPTION_KEY, $san);
            return rest_ensure_response($san);
        }

        public function rest_manage_setting($request)
        {
            $params = $request->get_json_params();
            if (! is_array($params)) {
                $params = $request->get_params();
            }

            $action = isset($params['action']) ? sanitize_text_field($params['action']) : 'update';
            $key = isset($params['key']) ? sanitize_key($params['key']) : '';

            if (empty($key)) {
                return new WP_Error('missing_key', __('Thiếu key option.', 'wp-spin-wheel'), array('status' => 400));
            }

            if ('delete' === $action) {
                $deleted = $this->delete_setting($key);
                return rest_ensure_response(array('deleted' => $deleted, 'key' => $key));
            }

            $type = isset($params['type']) ? sanitize_key($params['type']) : 'text';
            $value = isset($params['value']) ? $params['value'] : '';
            $saved_value = $this->add_or_update_setting($key, $value, $type);

            return rest_ensure_response(array(
                'saved' => true,
                'key' => $key,
                'type' => $type,
                'value' => $saved_value,
            ));
        }
    }