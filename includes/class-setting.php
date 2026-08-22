<?php
if (! defined('ABSPATH')) {
    exit;
}

class WP_Spin_Wheel_Settings
{
    const WHEEL_OPTION_KEY = 'wp_spin_wheel_settings';
    const BOX_OPTION_KEY   = 'wp_spin_box_settings';

    // Giữ const cũ để tương thích ngược
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

        // Trang Cài đặt Vòng quay (Spin Wheel)
        add_submenu_page(
            'edit.php?post_type=spin_wheel',
            __('Cài đặt Spin Wheel', 'wp-spin-wheel'),
            __('Cài đặt', 'wp-spin-wheel'),
            'manage_options',
            'wp-spin-wheel-settings',
            array($this, 'render_settings_page')
        );

        // Trang Cài đặt Hộp quà may mắn (Lucky Box)
        add_submenu_page(
            'edit.php?post_type=spin_box',
            __('Cài đặt Hộp quà', 'wp-spin-wheel'),
            __('Cài đặt', 'wp-spin-wheel'),
            'manage_options',
            'wp-spin-box-settings',
            array($this, 'render_box_settings_page')
        );
    }

    public function register_settings()
    {
        // 1. Cài đặt Vòng quay (Spin Wheel) - Độc lập
        register_setting('wp_spin_wheel', self::WHEEL_OPTION_KEY, array($this, 'sanitize_wheel_settings'));

        // 2. Cài đặt Hộp quà may mắn (Lucky Box) - Độc lập
        register_setting('wp_spin_box', self::BOX_OPTION_KEY, array($this, 'sanitize_box_settings'));
    }

    // ══════════════════════════════════════════════════════════
    // SCHEMAS & DEFAULTS
    // ══════════════════════════════════════════════════════════

    public function get_wheel_setting_schema()
    {
        return array(
            'wheel_title'             => array('type' => 'text',     'default' => 'Vòng quay may mắn'),
            'wheel_description'       => array('type' => 'textarea', 'default' => 'Nhấn vào nút quay để bắt đầu.'),
            'duration'                => array('type' => 'text',     'default' => '6'),
            'show_confetti'           => array('type' => 'checkbox', 'default' => 1),
            'auto_remove'             => array('type' => 'checkbox', 'default' => 0),
            'show_popup'              => array('type' => 'checkbox', 'default' => 1),
            'popup_label'             => array('type' => 'text',     'default' => 'Bạn đã quay vào ô'),
            'show_remove_button'      => array('type' => 'checkbox', 'default' => 1),
            'start_sound'             => array('type' => 'text',     'default' => '0'),
            'start_sound_file'        => array('type' => 'text',     'default' => ''),
            'end_sound'               => array('type' => 'text',     'default' => '0'),
            'end_sound_file'          => array('type' => 'text',     'default' => ''),
            'wheel_background_color'  => array('type' => 'color',    'default' => '#ffffff'),
            'wheel_background_image'  => array('type' => 'image',    'default' => ''),
            'wheel_border_color'      => array('type' => 'color',    'default' => '#ff4d00'),
            'wheel_diamond_color'     => array('type' => 'color',    'default' => '#f6fa00'),
            'wheel_button_text'       => array('type' => 'text',     'default' => 'QUAY'),
            'wheel_button_color'      => array('type' => 'color',    'default' => '#ff0000'),
            'wheel_button_text_color' => array('type' => 'color',    'default' => '#ffffff'),
            'wheel_animation_duration'=> array('type' => 'text',     'default' => '6'),
            'wheel_confetti'          => array('type' => 'checkbox', 'default' => 1),
        );
    }

    public function get_box_setting_schema()
    {
        return array(
            'box_title'        => array('type' => 'text',     'default' => 'HỘP QUÀ MAY MẮN ONLINE'),
            'template'         => array('type' => 'select',   'default' => 'tpl-jib'),
            'luotchoi'         => array('type' => 'text',     'default' => '3'),
            'sound'            => array('type' => 'text',     'default' => 'winner'),
            'sound_file'       => array('type' => 'text',     'default' => ''),
            'noti_sound'       => array('type' => 'text',     'default' => 'concainit'),
            'noti_sound_file'  => array('type' => 'text',     'default' => ''),
            'popup_title'      => array('type' => 'text',     'default' => 'Hộp quà có'),
            'confetti'         => array('type' => 'checkbox', 'default' => 1),
            'bg_color'         => array('type' => 'color',    'default' => '#dc3545'),
            'color'            => array('type' => 'color',    'default' => '#ffffff'),
            'bg_img'           => array('type' => 'image',    'default' => defined('WP_SPIN_WHEEL_URL') ? WP_SPIN_WHEEL_URL . 'assets/img/christmas-2.jpg' : '/wp-content/plugins/spin-whell/assets/img/christmas-2.jpg'),
            'bg_gradient'      => array('type' => 'textarea', 'default' => ''),
            'btn_bg_color'     => array('type' => 'color',    'default' => '#dc3545'),
            'btn_color'        => array('type' => 'color',    'default' => '#ffffff'),
            'show_particle'    => array('type' => 'checkbox', 'default' => 1),
            'default_gifts'    => array('type' => 'textarea', 'default' => "100k\nỐp lưng iphone\n50k\nChúc bạn may mắn\n200k\nBút Montblanc\nVí da 500k\nSổ tay\nGối tựa lưng\nBình giữ nhiệt\nLy sứ\nHộp đựng cơm"),
        );
    }

    public function get_setting_schema()
    {
        return $this->get_wheel_setting_schema();
    }

    public function get_wheel_defaults()
    {
        $defaults = array();
        foreach ($this->get_wheel_setting_schema() as $key => $field) {
            $defaults[$key] = isset($field['default']) ? $field['default'] : '';
        }
        return $defaults;
    }

    public function get_box_defaults()
    {
        $defaults = array();
        foreach ($this->get_box_setting_schema() as $key => $field) {
            $defaults[$key] = isset($field['default']) ? $field['default'] : '';
        }
        return $defaults;
    }

    public function get_default_settings()
    {
        return $this->get_wheel_defaults();
    }

    // ══════════════════════════════════════════════════════════
    // GETTERS
    // ══════════════════════════════════════════════════════════

    public function get_wheel_options()
    {
        $saved = get_option(self::WHEEL_OPTION_KEY, array());
        if (! is_array($saved)) {
            $saved = array();
        }
        return wp_parse_args($saved, $this->get_wheel_defaults());
    }

    public function get_box_options()
    {
        $saved = get_option(self::BOX_OPTION_KEY, array());
        if (! is_array($saved)) {
            $saved = array();
        }
        return wp_parse_args($saved, $this->get_box_defaults());
    }

    public function get_option()
    {
        return $this->get_wheel_options();
    }

    // ══════════════════════════════════════════════════════════
    // SANITIZERS
    // ══════════════════════════════════════════════════════════

    public function sanitize_wheel_settings($input)
    {
        $input   = is_array($input) ? $input : array();
        $options = $this->get_wheel_options();
        $schema  = $this->get_wheel_setting_schema();

        foreach ($schema as $key => $field) {
            if ($field['type'] === 'checkbox') {
                $options[$key] = ! empty($input[$key]) ? 1 : 0;
            } elseif (array_key_exists($key, $input)) {
                $options[$key] = $this->sanitize_setting_value($key, $input[$key], $field);
            }
        }

        return $options;
    }

    public function sanitize_box_settings($input)
    {
        $input   = is_array($input) ? $input : array();
        $options = $this->get_box_options();
        $schema  = $this->get_box_setting_schema();

        foreach ($schema as $key => $field) {
            if ($field['type'] === 'checkbox') {
                $options[$key] = ! empty($input[$key]) ? 1 : 0;
            } elseif (array_key_exists($key, $input)) {
                $options[$key] = $this->sanitize_setting_value($key, $input[$key], $field);
            }
        }

        return $options;
    }

    public function sanitize_settings($input)
    {
        return $this->sanitize_wheel_settings($input);
    }

    public function sanitize_setting_value($key, $value, $field = array())
    {
        $field = is_array($field) ? $field : array();
        $type  = isset($field['type']) ? $field['type'] : 'text';

        if ('checkbox' === $type) {
            return ! empty($value) ? 1 : 0;
        }

        if ('color' === $type) {
            return is_string($value) ? sanitize_hex_color($value) : '';
        }

        if ('image' === $type) {
            if (! is_string($value) || '' === trim($value)) {
                return '';
            }
            $resolved = WP_Spin_Wheel_Helper::resolve_asset_url($value);
            return esc_url_raw(trim($resolved ?: $value));
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

    public function add_or_update_setting($key, $value, $type = 'text', $target = 'wheel')
    {
        if ('box' === $target) {
            $options = $this->get_box_options();
            $options[$key] = $this->sanitize_setting_value($key, $value, array('type' => $type));
            update_option(self::BOX_OPTION_KEY, $options);
            return $options[$key];
        }

        $options = $this->get_wheel_options();
        $options[$key] = $this->sanitize_setting_value($key, $value, array('type' => $type));
        update_option(self::WHEEL_OPTION_KEY, $options);
        return $options[$key];
    }

    public function delete_setting($key, $target = 'wheel')
    {
        $opt_key = ('box' === $target) ? self::BOX_OPTION_KEY : self::WHEEL_OPTION_KEY;
        $options = ('box' === $target) ? $this->get_box_options() : $this->get_wheel_options();
        if (isset($options[$key])) {
            unset($options[$key]);
            update_option($opt_key, $options);
            return true;
        }
        return false;
    }

    // ══════════════════════════════════════════════════════════
    // ADMIN PAGES RENDER
    // ══════════════════════════════════════════════════════════

    public function render_settings_page()
    {
        $this->render_tabbed_settings_page('wheel');
    }

    public function render_box_settings_page()
    {
        $this->render_tabbed_settings_page('box');
    }

    public function render_tabbed_settings_page($default_tab = 'wheel')
    {
        if (! current_user_can('manage_options')) {
            wp_die(__('Bạn không có quyền truy cập trang cài đặt.', 'wp-spin-wheel'));
        }

        $active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : (isset($_GET['page']) && $_GET['page'] === 'wp-spin-box-settings' ? 'box' : $default_tab);

        $wheel_opts = $this->get_wheel_options();
        $box_opts   = $this->get_box_options();

        $audios_start = WP_Spin_Wheel_Helper::get_audio_library('start') ?: array();
        $audios_end   = WP_Spin_Wheel_Helper::get_audio_library('end') ?: array();
        ?>
        <div class="wrap wp-spin-wheel-settings-wrap">
            <h1 class="wp-heading-inline">
                <?php esc_html_e('Cài đặt Plugin Spin Wheel & Lucky Box', 'wp-spin-wheel'); ?>
            </h1>
            <hr class="wp-header-end">

            <?php if (! empty($_GET['settings-updated'])) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Đã lưu cấu hình cài đặt thành công!', 'wp-spin-wheel'); ?></p>
                </div>
            <?php endif; ?>

            <div class="nav-tab-wrapper" style="margin-bottom: 20px;">
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=spin_wheel&page=wp-spin-wheel-settings&tab=wheel')); ?>" class="nav-tab <?php echo $active_tab === 'wheel' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-chart-pie" style="vertical-align: text-bottom; margin-right: 4px;"></span>
                    <?php esc_html_e('Cài đặt Vòng quay (Spin Wheel)', 'wp-spin-wheel'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=spin_box&page=wp-spin-box-settings&tab=box')); ?>" class="nav-tab <?php echo $active_tab === 'box' ? 'nav-tab-active' : ''; ?>">
                    <span class="dashicons dashicons-archive" style="vertical-align: text-bottom; margin-right: 4px;"></span>
                    <?php esc_html_e('Cài đặt Hộp quà (Lucky Box)', 'wp-spin-wheel'); ?>
                </a>
            </div>

            <?php if ($active_tab === 'wheel') : ?>
                <!-- ══════════════════════════════════════════════════ -->
                <!-- TAB 1: CÀI ĐẶT VÒNG QUAY (SPIN WHEEL)               -->
                <!-- ══════════════════════════════════════════════════ -->
                <form method="post" action="options.php" id="form-spin-wheel-settings" class="card" style="max-width: 900px; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.08);">
                    <?php
                    settings_fields('wp_spin_wheel');
                    ?>
                    <h2 class="title" style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee; color: #1d2327;">
                        <?php esc_html_e('Cấu hình mặc định cho Vòng quay', 'wp-spin-wheel'); ?>
                    </h2>
                    <p class="description" style="margin-bottom: 20px;">
                        <?php esc_html_e('Các thiết lập này chỉ áp dụng cho Vòng quay (Spin Wheel), hoàn toàn tách biệt và không ảnh hưởng đến Hộp quà.', 'wp-spin-wheel'); ?>
                    </p>

                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="wheel_title"><?php esc_html_e('Tiêu đề mặc định', 'wp-spin-wheel'); ?></label></th>
                            <td>
                                <input name="wp_spin_wheel_settings[wheel_title]" type="text" id="wheel_title" value="<?php echo esc_attr($wheel_opts['wheel_title'] ?? ''); ?>" class="regular-text" />
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="wheel_description"><?php esc_html_e('Mô tả mặc định', 'wp-spin-wheel'); ?></label></th>
                            <td>
                                <textarea name="wp_spin_wheel_settings[wheel_description]" id="wheel_description" rows="3" class="large-text"><?php echo esc_textarea($wheel_opts['wheel_description'] ?? ''); ?></textarea>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="wheel_duration"><?php esc_html_e('Thời gian quay (giây)', 'wp-spin-wheel'); ?></label></th>
                            <td>
                                <select name="wp_spin_wheel_settings[duration]" id="wheel_duration">
                                    <option value="4" <?php selected($wheel_opts['duration'], '4'); ?>><?php esc_html_e('4 giây (Nhanh)', 'wp-spin-wheel'); ?></option>
                                    <option value="6" <?php selected($wheel_opts['duration'], '6'); ?>><?php esc_html_e('6 giây (Tiêu chuẩn)', 'wp-spin-wheel'); ?></option>
                                    <option value="8" <?php selected($wheel_opts['duration'], '8'); ?>><?php esc_html_e('8 giây (Chậm)', 'wp-spin-wheel'); ?></option>
                                    <option value="12" <?php selected($wheel_opts['duration'], '12'); ?>><?php esc_html_e('12 giây (Rất chậm)', 'wp-spin-wheel'); ?></option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><?php esc_html_e('Hiệu ứng & Popup', 'wp-spin-wheel'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="wheel_show_confetti">
                                        <input name="wp_spin_wheel_settings[show_confetti]" type="checkbox" id="wheel_show_confetti" value="1" <?php checked(! empty($wheel_opts['show_confetti'])); ?> />
                                        <?php esc_html_e('Bắn hoa giấy confetti khi trúng thưởng', 'wp-spin-wheel'); ?>
                                    </label>
                                    <br>
                                    <label for="wheel_auto_remove" style="margin-top: 6px; display: inline-block;">
                                        <input name="wp_spin_wheel_settings[auto_remove]" type="checkbox" id="wheel_auto_remove" value="1" <?php checked(! empty($wheel_opts['auto_remove'])); ?> />
                                        <?php esc_html_e('Tự động xóa ô kết quả sau 5 giây', 'wp-spin-wheel'); ?>
                                    </label>
                                    <br>
                                    <label for="wheel_show_popup" style="margin-top: 6px; display: inline-block;">
                                        <input name="wp_spin_wheel_settings[show_popup]" type="checkbox" id="wheel_show_popup" value="1" <?php checked(! empty($wheel_opts['show_popup'])); ?> />
                                        <?php esc_html_e('Hiển thị popup kết quả trúng thưởng', 'wp-spin-wheel'); ?>
                                    </label>
                                    <br>
                                    <label for="wheel_show_remove_button" style="margin-top: 6px; display: inline-block;">
                                        <input name="wp_spin_wheel_settings[show_remove_button]" type="checkbox" id="wheel_show_remove_button" value="1" <?php checked(! empty($wheel_opts['show_remove_button'])); ?> />
                                        <?php esc_html_e('Hiển thị nút "Xóa ô này" trong popup', 'wp-spin-wheel'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="popup_label"><?php esc_html_e('Tiêu đề popup kết quả', 'wp-spin-wheel'); ?></label></th>
                            <td>
                                <input name="wp_spin_wheel_settings[popup_label]" type="text" id="popup_label" value="<?php echo esc_attr($wheel_opts['popup_label'] ?? 'Bạn đã quay vào ô'); ?>" class="regular-text" />
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="start_sound"><?php esc_html_e('Âm thanh khi bắt đầu quay', 'wp-spin-wheel'); ?></label></th>
                            <td>
                                <select name="wp_spin_wheel_settings[start_sound]" id="start_sound" class="regular-text">
                                    <option value="0" <?php selected($wheel_opts['start_sound'] ?? '0', '0'); ?>><?php esc_html_e('Tắt tiếng', 'wp-spin-wheel'); ?></option>
                                    <option value="random" <?php selected($wheel_opts['start_sound'] ?? '', 'random'); ?>><?php esc_html_e('Ngẫu nhiên', 'wp-spin-wheel'); ?></option>
                                    <?php foreach ($audios_start as $as) : ?>
                                        <option value="<?php echo esc_attr($as['id']); ?>" <?php selected($wheel_opts['start_sound'] ?? '', $as['id']); ?>><?php echo esc_html($as['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="end_sound"><?php esc_html_e('Âm thanh khi kết thúc quay', 'wp-spin-wheel'); ?></label></th>
                            <td>
                                <select name="wp_spin_wheel_settings[end_sound]" id="end_sound" class="regular-text">
                                    <option value="0" <?php selected($wheel_opts['end_sound'] ?? '0', '0'); ?>><?php esc_html_e('Tắt tiếng', 'wp-spin-wheel'); ?></option>
                                    <option value="random" <?php selected($wheel_opts['end_sound'] ?? '', 'random'); ?>><?php esc_html_e('Ngẫu nhiên', 'wp-spin-wheel'); ?></option>
                                    <option value="read" <?php selected($wheel_opts['end_sound'] ?? '', 'read'); ?>><?php esc_html_e('Đọc kết quả giọng nói', 'wp-spin-wheel'); ?></option>
                                    <?php foreach ($audios_end as $ae) : ?>
                                        <option value="<?php echo esc_attr($ae['id']); ?>" <?php selected($wheel_opts['end_sound'] ?? '', $ae['id']); ?>><?php echo esc_html($ae['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="wheel_border_color"><?php esc_html_e('Màu viền vòng quay', 'wp-spin-wheel'); ?></label></th>
                            <td>
                                <input name="wp_spin_wheel_settings[wheel_border_color]" type="text" id="wheel_border_color" value="<?php echo esc_attr($wheel_opts['wheel_border_color'] ?? '#ff4d00'); ?>" class="regular-text" />
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="wheel_button_text"><?php esc_html_e('Nút quay chữ', 'wp-spin-wheel'); ?></label></th>
                            <td>
                                <input name="wp_spin_wheel_settings[wheel_button_text]" type="text" id="wheel_button_text" value="<?php echo esc_attr($wheel_opts['wheel_button_text'] ?? 'QUAY'); ?>" class="regular-text" />
                            </td>
                        </tr>
                    </table>

                    <?php submit_button(__('Lưu cài đặt Vòng quay', 'wp-spin-wheel'), 'primary', 'submit_wheel'); ?>
                </form>

                <!-- ══════════════════════════════════════════════════ -->
                <!-- IMPORT DỮ LIỆU DEMO VÒNG QUAY                   -->
                <!-- ══════════════════════════════════════════════════ -->
                <div class="card" style="max-width: 900px; margin-top: 20px; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.08);">
                    <h2 class="title" style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee; color: #1d2327;">
                        🎡 <?php esc_html_e('Dữ liệu demo Vòng quay', 'wp-spin-wheel'); ?>
                    </h2>
                    <p class="description" style="margin-bottom: 16px; color: #64748b;">
                        <?php esc_html_e('Nhấn nút bên dưới để import TOÀN BỘ theme từ thư viện themes.json (Mặc định, Mạng xã hội, Học sinh, Phụ nữ, Chủ đề, Theo mùa, Màu cờ...). Mỗi theme sẽ tạo 1 vòng quay với màu viền/kim, ảnh nền, âm thanh và giải thưởng lấy màu từ palette của theme đó. Theme đã import trước đó sẽ được bỏ qua (trùng lặp).', 'wp-spin-wheel'); ?>
                    </p>
                    <button type="button" class="button button-primary" id="sw-import-wheel-demo-btn">
                        <span class="dashicons dashicons-download" style="vertical-align: middle; margin-right: 4px;"></span>
                        <?php esc_html_e('Import dữ liệu demo Vòng quay', 'wp-spin-wheel'); ?>
                    </button>
                    <span id="sw-import-wheel-demo-status" style="margin-left: 10px; font-weight: 600; color: #22c55e;"></span>
                </div>

                <script>
                (function() {
                    var btn = document.getElementById('sw-import-wheel-demo-btn');
                    var status = document.getElementById('sw-import-wheel-demo-status');
                    if (!btn) return;

                    btn.addEventListener('click', function() {
                        var params = (typeof wp_spin_wheel_admin_params !== 'undefined') ? wp_spin_wheel_admin_params : {};
                        if (!params.ajax_url || !params.nonce) {
                            status.style.color = '#dc2626';
                            status.textContent = '<?php echo esc_js(__('Thiếu cấu hình AJAX.', 'wp-spin-wheel')); ?>';
                            return;
                        }

                        btn.disabled = true;
                        btn.textContent = '<?php echo esc_js(__('Đang import...', 'wp-spin-wheel')); ?>';
                        status.textContent = '';

                        jQuery.post(params.ajax_url, {
                            action: 'spin_wheel_import_wheel_demo',
                            nonce: params.nonce
                        }).done(function(res) {
                            if (res && res.success) {
                                var imported = res.data.imported || 0;
                                var duplicate = res.data.duplicate || 0;
                                status.style.color = '#22c55e';
                                status.textContent = '✓ ' +
                                    '<?php echo esc_js(__('Đã import thành công', 'wp-spin-wheel')); ?>: ' + imported +
                                    ' | ' + '<?php echo esc_js(__('Trùng lặp', 'wp-spin-wheel')); ?>: ' + duplicate;
                                setTimeout(function() { window.location.reload(); }, 1200);
                            } else {
                                status.style.color = '#dc2626';
                                status.textContent = (res && res.data && res.data.message) ? res.data.message : '<?php echo esc_js(__('Import thất bại.', 'wp-spin-wheel')); ?>';
                            }
                        }).fail(function() {
                            status.style.color = '#dc2626';
                            status.textContent = '<?php echo esc_js(__('Lỗi kết nối máy chủ.', 'wp-spin-wheel')); ?>';
                        }).always(function() {
                            btn.disabled = false;
                            btn.textContent = '<?php echo esc_js(__('Import dữ liệu demo Vòng quay', 'wp-spin-wheel')); ?>';
                        });
                    });
                })();
                </script>

            <?php else : ?>
                <!-- ══════════════════════════════════════════════════ -->
                <!-- TAB 2: CÀI ĐẶT HỘP QUÀ (LUCKY BOX)                 -->
                <!-- ══════════════════════════════════════════════════ -->
                <form method="post" action="options.php" id="form-spin-box-settings" class="card" style="max-width: 900px; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.08);">
                    <?php
                    settings_fields('wp_spin_box');
                    ?>
                    <h2 class="title" style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee; color: #1d2327;">
                        <?php esc_html_e('Cấu hình mặc định cho Hộp quà may mắn (Lucky Box)', 'wp-spin-wheel'); ?>
                    </h2>
                    <p class="description" style="margin-bottom: 20px;">
                        <?php esc_html_e('Các thiết lập này chỉ áp dụng riêng cho Hộp quà may mắn, hoàn toàn độc lập với Vòng quay.', 'wp-spin-wheel'); ?>
                    </p>

                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><label for="box_title"><?php esc_html_e('Tiêu đề Hộp quà', 'wp-spin-wheel'); ?></label></th>
                            <td>
                                <input name="wp_spin_box_settings[box_title]" type="text" id="box_title" value="<?php echo esc_attr($box_opts['box_title'] ?? 'HỘP QUÀ MAY MẮN ONLINE'); ?>" class="regular-text" />
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="box_template"><?php esc_html_e('Chủ đề mẫu mặc định', 'wp-spin-wheel'); ?></label></th>
                            <td>
                                <select name="wp_spin_box_settings[template]" id="box_template" class="regular-text">
                                    <option value="tpl-jib" <?php selected($box_opts['template'], 'tpl-jib'); ?>>📦 Ball in box (Mặc định)</option>
                                    <option value="tpl-default" <?php selected($box_opts['template'], 'tpl-default'); ?>>🎁 Hộp quà truyền thống</option>
                                    <option value="tpl-christmas" <?php selected($box_opts['template'], 'tpl-christmas'); ?>>🎄 Giáng sinh Christmas</option>
                                    <option value="tpl-money-bag" <?php selected($box_opts['template'], 'tpl-money-bag'); ?>>💰 Túi tiền tài lộc</option>
                                    <option value="tpl-blind-bag" <?php selected($box_opts['template'], 'tpl-blind-bag'); ?>>🛍️ Túi mù bí ẩn</option>
                                    <option value="tpl-money" <?php selected($box_opts['template'], 'tpl-money'); ?>>🧧 Bao lì xì Tết</option>
                                    <option value="tpl-egg" <?php selected($box_opts['template'], 'tpl-egg'); ?>>🥚 Đập trứng vàng</option>
                                    <option value="tpl-jar" <?php selected($box_opts['template'], 'tpl-jar'); ?>>🏺 Đập lu đất</option>
                                    <option value="tpl-rat" <?php selected($box_opts['template'], 'tpl-rat'); ?>>🐭 Đập chuột may mắn</option>
                                    <option value="tpl-ghost" <?php selected($box_opts['template'], 'tpl-ghost'); ?>>👻 Halloween Diệt ma</option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="box_luotchoi"><?php esc_html_e('Số lượt mở mặc định', 'wp-spin-wheel'); ?></label></th>
                            <td>
                                <select name="wp_spin_box_settings[luotchoi]" id="box_luotchoi">
                                    <?php for ($i = 1; $i <= 12; $i++) : ?>
                                        <option value="<?php echo esc_attr($i); ?>" <?php selected((int) ($box_opts['luotchoi'] ?? 3), $i); ?>><?php echo sprintf(esc_html__('%d lượt mở', 'wp-spin-wheel'), $i); ?></option>
                                    <?php endfor; ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="box_default_gifts"><?php esc_html_e('Danh sách quà mặc định (mỗi dòng 1 quà)', 'wp-spin-wheel'); ?></label></th>
                            <td>
                                <textarea name="wp_spin_box_settings[default_gifts]" id="box_default_gifts" rows="8" class="large-text"><?php echo esc_textarea($box_opts['default_gifts'] ?? ''); ?></textarea>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="box_sound"><?php esc_html_e('Âm thanh khi mở hộp', 'wp-spin-wheel'); ?></label></th>
                            <td>
                                <select name="wp_spin_box_settings[sound]" id="box_sound" class="regular-text">
                                    <option value="0" <?php selected($box_opts['sound'] ?? '', '0'); ?>><?php esc_html_e('Tắt tiếng', 'wp-spin-wheel'); ?></option>
                                    <option value="random" <?php selected($box_opts['sound'] ?? '', 'random'); ?>><?php esc_html_e('Ngẫu nhiên', 'wp-spin-wheel'); ?></option>
                                    <option value="winner" <?php selected($box_opts['sound'] ?? 'winner', 'winner'); ?>><?php esc_html_e('Winner (Mặc định)', 'wp-spin-wheel'); ?></option>
                                    <option value="congratulation" <?php selected($box_opts['sound'] ?? '', 'congratulation'); ?>><?php esc_html_e('Chúc mừng', 'wp-spin-wheel'); ?></option>
                                    <option value="bell" <?php selected($box_opts['sound'] ?? '', 'bell'); ?>><?php esc_html_e('Tiếng chuông', 'wp-spin-wheel'); ?></option>
                                    <option value="votay" <?php selected($box_opts['sound'] ?? '', 'votay'); ?>><?php esc_html_e('Vỗ tay', 'wp-spin-wheel'); ?></option>
                                    <option value="phaohoano" <?php selected($box_opts['sound'] ?? '', 'phaohoano'); ?>><?php esc_html_e('Pháo hoa nổ', 'wp-spin-wheel'); ?></option>
                                    <?php foreach ($audios_start as $as) : ?>
                                        <option value="<?php echo esc_attr($as['id']); ?>" <?php selected($box_opts['sound'] ?? '', $as['id']); ?>><?php echo esc_html($as['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="box_noti_sound"><?php esc_html_e('Âm thanh khi hết lượt', 'wp-spin-wheel'); ?></label></th>
                            <td>
                                <select name="wp_spin_box_settings[noti_sound]" id="box_noti_sound" class="regular-text">
                                    <option value="0" <?php selected($box_opts['noti_sound'] ?? '', '0'); ?>><?php esc_html_e('Tắt tiếng', 'wp-spin-wheel'); ?></option>
                                    <option value="random" <?php selected($box_opts['noti_sound'] ?? '', 'random'); ?>><?php esc_html_e('Ngẫu nhiên', 'wp-spin-wheel'); ?></option>
                                    <option value="concainit" <?php selected($box_opts['noti_sound'] ?? 'concainit', 'concainit'); ?>><?php esc_html_e('Còn cái nịt', 'wp-spin-wheel'); ?></option>
                                    <option value="complete" <?php selected($box_opts['noti_sound'] ?? '', 'complete'); ?>><?php esc_html_e('Hoàn thành', 'wp-spin-wheel'); ?></option>
                                    <option value="slot_end" <?php selected($box_opts['noti_sound'] ?? '', 'slot_end'); ?>><?php esc_html_e('Slot End', 'wp-spin-wheel'); ?></option>
                                    <option value="fanfare" <?php selected($box_opts['noti_sound'] ?? '', 'fanfare'); ?>><?php esc_html_e('Fanfare', 'wp-spin-wheel'); ?></option>
                                    <?php foreach ($audios_end as $ae) : ?>
                                        <option value="<?php echo esc_attr($ae['id']); ?>" <?php selected($box_opts['noti_sound'] ?? '', $ae['id']); ?>><?php echo esc_html($ae['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="box_popup_title"><?php esc_html_e('Tiêu đề popup trúng thưởng', 'wp-spin-wheel'); ?></label></th>
                            <td>
                                <input name="wp_spin_box_settings[popup_title]" type="text" id="box_popup_title" value="<?php echo esc_attr($box_opts['popup_title'] ?? 'Hộp quà có'); ?>" class="regular-text" />
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><?php esc_html_e('Hiệu ứng', 'wp-spin-wheel'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="box_confetti">
                                        <input name="wp_spin_box_settings[confetti]" type="checkbox" id="box_confetti" value="1" <?php checked(! empty($box_opts['confetti'])); ?> />
                                        <?php esc_html_e('Bắn hoa giấy khi mở trúng hộp quà', 'wp-spin-wheel'); ?>
                                    </label>
                                    <br>
                                    <label for="box_show_particle" style="margin-top: 6px; display: inline-block;">
                                        <input name="wp_spin_box_settings[show_particle]" type="checkbox" id="box_show_particle" value="1" <?php checked(! empty($box_opts['show_particle'])); ?> />
                                        <?php esc_html_e('Hiệu ứng hạt nền chuyển động', 'wp-spin-wheel'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="box_bg_color"><?php esc_html_e('Màu nền giao diện', 'wp-spin-wheel'); ?></label></th>
                            <td>
                                <input name="wp_spin_box_settings[bg_color]" type="text" id="box_bg_color" value="<?php echo esc_attr($box_opts['bg_color'] ?? '#dc3545'); ?>" class="regular-text" />
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="box_bg_img"><?php esc_html_e('Ảnh nền giao diện (Mặc định)', 'wp-spin-wheel'); ?></label></th>
                            <td>
                                <input name="wp_spin_box_settings[bg_img]" type="text" id="box_bg_img" value="<?php echo esc_attr($box_opts['bg_img'] ?? (defined('WP_SPIN_WHEEL_URL') ? WP_SPIN_WHEEL_URL . 'assets/img/christmas-2.jpg' : '/wp-content/plugins/spin-whell/assets/img/christmas-2.jpg')); ?>" class="regular-text" />
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="box_btn_bg_color"><?php esc_html_e('Màu nút bấm & thẻ tiêu đề', 'wp-spin-wheel'); ?></label></th>
                            <td>
                                <input name="wp_spin_box_settings[btn_bg_color]" type="text" id="box_btn_bg_color" value="<?php echo esc_attr($box_opts['btn_bg_color'] ?? '#dc3545'); ?>" class="regular-text" />
                            </td>
                        </tr>
                    </table>

                    <?php submit_button(__('Lưu cài đặt Hộp quà', 'wp-spin-wheel'), 'primary', 'submit_box'); ?>
                </form>

                <!-- ══════════════════════════════════════════════════ -->
                <!-- IMPORT DỮ LIỆU DEMO HỘP QUÀ                      -->
                <!-- ══════════════════════════════════════════════════ -->
                <div class="card" style="max-width: 900px; margin-top: 20px; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.08);">
                    <h2 class="title" style="margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee; color: #1d2327;">
                        🎁 <?php esc_html_e('Dữ liệu demo Hộp quà', 'wp-spin-wheel'); ?>
                    </h2>
                    <p class="description" style="margin-bottom: 16px; color: #64748b;">
                        <?php esc_html_e('Nhấn nút bên dưới để tạo nhanh 5 hộp quà mẫu (Shiratori, Giáng sinh, Tết, Halloween, Mùa hè) kèm quà tặng và giao diện tương ứng từ file assets/data/box-demo.json.', 'wp-spin-wheel'); ?>
                    </p>
                    <button type="button" class="button button-primary" id="sw-import-box-demo-btn">
                        <span class="dashicons dashicons-download" style="vertical-align: middle; margin-right: 4px;"></span>
                        <?php esc_html_e('Import dữ liệu demo Hộp quà', 'wp-spin-wheel'); ?>
                    </button>
                    <span id="sw-import-box-demo-status" style="margin-left: 10px; font-weight: 600; color: #22c55e;"></span>
                </div>

                <script>
                (function() {
                    var btn = document.getElementById('sw-import-box-demo-btn');
                    var status = document.getElementById('sw-import-box-demo-status');
                    if (!btn) return;

                    btn.addEventListener('click', function() {
                        var params = (typeof wp_spin_wheel_admin_params !== 'undefined') ? wp_spin_wheel_admin_params : {};
                        if (!params.ajax_url || !params.nonce) {
                            status.style.color = '#dc2626';
                            status.textContent = '<?php echo esc_js(__('Thiếu cấu hình AJAX.', 'wp-spin-wheel')); ?>';
                            return;
                        }

                        btn.disabled = true;
                        btn.textContent = '<?php echo esc_js(__('Đang import...', 'wp-spin-wheel')); ?>';
                        status.textContent = '';

                        jQuery.post(params.ajax_url, {
                            action: 'spin_wheel_import_box_demo',
                            nonce: params.nonce
                        }).done(function(res) {
                            if (res && res.success) {
                                var imported = res.data.imported || 0;
                                var duplicate = res.data.duplicate || 0;
                                status.style.color = '#22c55e';
                                status.textContent = '✓ ' +
                                    '<?php echo esc_js(__('Đã import thành công', 'wp-spin-wheel')); ?>: ' + imported +
                                    ' | ' + '<?php echo esc_js(__('Trùng lặp', 'wp-spin-wheel')); ?>: ' + duplicate;
                                setTimeout(function() { window.location.reload(); }, 1200);
                            } else {
                                status.style.color = '#dc2626';
                                status.textContent = (res && res.data && res.data.message) ? res.data.message : '<?php echo esc_js(__('Import thất bại.', 'wp-spin-wheel')); ?>';
                            }
                        }).fail(function() {
                            status.style.color = '#dc2626';
                            status.textContent = '<?php echo esc_js(__('Lỗi kết nối máy chủ.', 'wp-spin-wheel')); ?>';
                        }).always(function() {
                            btn.disabled = false;
                            btn.textContent = '<?php echo esc_js(__('Import dữ liệu demo Hộp quà', 'wp-spin-wheel')); ?>';
                        });
                    });
                })();
                </script>
            <?php endif; ?>
        </div>
        <?php
    }

    // ══════════════════════════════════════════════════════════
    // REST API ROUTES
    // ══════════════════════════════════════════════════════════

    public function register_rest_routes()
    {
        // Settings Vòng quay
        register_rest_route('spin-wheel/v1', '/settings', array(
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'rest_get_settings'),
                'permission_callback' => function () {
                    return current_user_can('edit_theme_options');
                }
            ),
            array(
                'methods'             => 'POST',
                'callback'            => array($this, 'rest_update_settings'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ),
        ));

        // Settings Hộp quà
        register_rest_route('spin-wheel/v1', '/box-settings', array(
            array(
                'methods'             => 'GET',
                'callback'            => array($this, 'rest_get_box_settings'),
                'permission_callback' => function () {
                    return current_user_can('edit_theme_options');
                }
            ),
            array(
                'methods'             => 'POST',
                'callback'            => array($this, 'rest_update_box_settings'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ),
        ));

        // Quản lý option đơn lẻ (wheel hoặc box)
        register_rest_route('spin-wheel/v1', '/settings/option', array(
            array(
                'methods'             => 'POST',
                'callback'            => array($this, 'rest_manage_setting'),
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                }
            ),
        ));
    }

    public function rest_get_settings($request)
    {
        return rest_ensure_response($this->get_wheel_options());
    }

    public function rest_update_settings($request)
    {
        $params = $request->get_json_params();
        $san    = $this->sanitize_wheel_settings($params);
        update_option(self::WHEEL_OPTION_KEY, $san);
        return rest_ensure_response($san);
    }

    public function rest_get_box_settings($request)
    {
        return rest_ensure_response($this->get_box_options());
    }

    public function rest_update_box_settings($request)
    {
        $params = $request->get_json_params();
        $san    = $this->sanitize_box_settings($params);
        update_option(self::BOX_OPTION_KEY, $san);
        return rest_ensure_response($san);
    }

    public function rest_manage_setting($request)
    {
        $params = $request->get_json_params();
        if (! is_array($params)) {
            $params = $request->get_params();
        }

        $action = isset($params['action']) ? sanitize_text_field($params['action']) : 'update';
        $key    = isset($params['key']) ? sanitize_key($params['key']) : '';
        $target = isset($params['target']) && $params['target'] === 'box' ? 'box' : 'wheel';

        if (empty($key)) {
            return new WP_Error('missing_key', __('Thiếu key option.', 'wp-spin-wheel'), array('status' => 400));
        }

        if ('delete' === $action) {
            $deleted = $this->delete_setting($key, $target);
            return rest_ensure_response(array('deleted' => $deleted, 'key' => $key, 'target' => $target));
        }

        $type        = isset($params['type']) ? sanitize_key($params['type']) : 'text';
        $value       = isset($params['value']) ? $params['value'] : '';
        $saved_value = $this->add_or_update_setting($key, $value, $type, $target);

        return rest_ensure_response(array(
            'saved'  => true,
            'key'    => $key,
            'type'   => $type,
            'target' => $target,
            'value'  => $saved_value,
        ));
    }
}
