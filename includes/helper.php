<?php
if (! defined('ABSPATH')) {
    exit;
}

class WP_Spin_Wheel_Helper
{
    public static function sanitize_text($text)
    {
        return sanitize_text_field(wp_unslash($text));
    }

    public static function get_post_meta($post_id, $meta_key, $single = true)
    {
        return get_post_meta($post_id, $meta_key, $single);
    }

    public static function resolve_asset_url($url)
    {
        if (empty($url)) {
            return '';
        }
        $url = trim((string) $url);
        // Nếu đã là full URL hợp lệ hoặc data URI
        if (preg_match('/^(https?:|\/\/|data:)/i', $url)) {
            $url = preg_replace('/^https?:\/\/:\/?/', '/', $url);
            $url = preg_replace('/^:\/?\/?/', '/', $url);
            if (preg_match('/^(https?:|\/\/|data:)/i', $url)) {
                return $url;
            }
        }
        $clean = ltrim($url, '/');
        // Bỏ prefix wp-content/plugins/spin-whell/ nếu có
        $clean = preg_replace('/^wp-content\/plugins\/spin-whell\//', '', $clean);
        return WP_SPIN_WHEEL_URL . ltrim($clean, '/');
    }

    public static function get_wheel_overrides($post_id)
    {
        $overrides = self::get_post_meta($post_id, '_spin_wheel_overrides', true);
        if (is_string($overrides)) {
            $overrides = json_decode($overrides, true);
        }
        if (! is_array($overrides)) {
            $overrides = array();
        }

        if (empty($overrides)) {
            $legacy_design = self::get_post_meta($post_id, '_spin_wheel_design', true);
            if (is_string($legacy_design)) {
                $legacy_design = json_decode($legacy_design, true);
            }
            if (is_array($legacy_design)) {
                $overrides = $legacy_design;
            }
        }

        // Tự động chuẩn hóa URL ảnh nền và ảnh nút quay
        if (! empty($overrides['background']['image'])) {
            $overrides['background']['image'] = self::resolve_asset_url($overrides['background']['image']);
        }
        if (! empty($overrides['button']['background_image'])) {
            $overrides['button']['background_image'] = self::resolve_asset_url($overrides['button']['background_image']);
        }

        return $overrides;
    }

    public static function get_global_settings()
    {
        $settings = get_option('wp_spin_wheel_settings', array());
        return is_array($settings) ? $settings : array();
    }

    public static function get_setting_items($type)
    {
        $stored = get_option('wp_spin_wheel_setting_items', array());
        if (! is_array($stored)) {
            return array();
        }

        $group_key = self::normalize_setting_group($type);
        if (! is_array($stored[$group_key] ?? null)) {
            return array();
        }

        return $stored[$group_key];
    }

    private static function normalize_setting_group($type)
    {
        $type = is_string($type) ? sanitize_key($type) : '';

        if (in_array($type, array('audio_start', 'audios_start', 'spin_wheel_audio_start'), true)) {
            return 'audios_start';
        }

        if (in_array($type, array('audio_end', 'audios_end', 'spin_wheel_audio_end'), true)) {
            return 'audios_end';
        }

        return $type;
    }

    public static function get_setting_item_config($type, $item_id)
    {
        $item_id = (string) $item_id;
        if ('' === $item_id) {
            return array();
        }

        foreach (self::get_setting_items($type) as $item) {
            if (! is_array($item)) {
                continue;
            }

            if ((string) ($item['id'] ?? '') === $item_id) {
                return is_array($item['config'] ?? null) ? $item['config'] : array();
            }
        }

        return array();
    }

    /**
     * Lấy danh sách audio từ thư mục assets/audio tương ứng.
     * Tên hiển thị lấy từ file audio-name-{type}.txt, khớp theo thứ tự dòng với audio-link-{type}.txt.
     *
     * @param string $type 'start' | 'end'
     * @return array
     */
    public static function get_audio_library($type)
    {
        $type     = ('end' === $type) ? 'end' : 'start';
        $dir_name = 'audio-' . $type;
        $dir_path = WP_SPIN_WHEEL_PATH . 'assets/audio/' . $dir_name;
        $dir_url  = WP_SPIN_WHEEL_URL . 'assets/audio/' . $dir_name;

        $name_file = $dir_path . '/audio-name-' . $type . '.txt';
        $link_file = WP_SPIN_WHEEL_PATH . 'assets/audio/audio-link-' . $type . '.txt';

        // Đọc danh sách tên audio
        $names = array();
        if (is_readable($name_file)) {
            $names = array_values(
                array_filter(
                    array_map('trim', (array) file($name_file))
                )
            );
        }

        // Đọc danh sách link audio fallback
        $links = array();
        if (is_readable($link_file)) {
            $links = array_values(
                array_filter(
                    array_map('trim', (array) file($link_file))
                )
            );
        }

        $items = array();

        // 1. Kiểm tra file mp3 cục bộ trong thư mục assets/audio/audio-{type}/
        if (is_dir($dir_path)) {
            $files = glob(
                $dir_path . '/*.{mp3,ogg,wav,m4a,aac}',
                GLOB_BRACE
            );

            if (! empty($files)) {
                sort($files);
                foreach ($files as $i => $file_path) {
                    $file     = basename($file_path);
                    $fallback = pathinfo($file, PATHINFO_FILENAME);

                    $items[] = array(
                        'id'     => $fallback,
                        'name'   => isset($names[$i]) && '' !== $names[$i]
                            ? $names[$i]
                            : $fallback,
                        'config' => array(
                            'file' => $dir_url . '/' . $file,
                        ),
                    );
                }
            }
        }

        // 2. Nếu chưa có file cục bộ -> dùng danh sách từ file link/name
        if (empty($items) && ! empty($links)) {
            foreach ($links as $i => $link_url) {
                $file_name = pathinfo(parse_url($link_url, PHP_URL_PATH), PATHINFO_FILENAME);
                $lbl       = isset($names[$i]) && '' !== $names[$i] ? $names[$i] : $file_name;
                $items[]   = array(
                    'id'     => $file_name ?: ('audio_' . ($i + 1)),
                    'name'   => $lbl,
                    'config' => array(
                        'file' => $link_url,
                    ),
                );
            }
        }

        return $items;
    }

    public static function get_wheel_settings($post_id)
    {
        $overrides = self::get_wheel_overrides($post_id);
        $global_settings = self::get_global_settings();

        $defaults = array(
            'title'        => get_the_title($post_id),
            'description'  => get_post_field('post_content', $post_id),
            'background'   => array('type' => 'color', 'value' => '#ffffff', 'image' => ''),
            'logo'         => '',
            'music'        => '',
            'sound'        => '',
            'effect'       => '',
            'spin_limit'   => 0,
            'spin_limit_type' => 'none',
            'form_fields'  => array(),
            'wheel'        => array('size' => 500, 'border' => 8, 'border_color' => '#ff4d00', 'shadow' => true),
            'button'       => array('text' => __('QUAY', 'wp-spin-wheel'), 'color' => '#ff0000', 'text_color' => '#ffffff', 'radius' => 50, 'background_image' => ''),
            'pointer'      => array('image' => '', 'size' => 80),
            'animation'    => array('duration' => 6, 'confetti' => true),
            'audio'        => array('spin' => '', 'win' => ''),
            'custom_css'   => '',
        );

        $settings = array_replace_recursive($defaults, $overrides);

        if (! empty($global_settings['wheel_title'])) {
            $settings['title'] = sanitize_text_field($global_settings['wheel_title']);
        }
        if (! empty($global_settings['wheel_description'])) {
            $settings['description'] = sanitize_textarea_field($global_settings['wheel_description']);
        }

        $selected_background_item = self::get_setting_item_config('background', $overrides['selected_background_id'] ?? '');
        if (! empty($selected_background_item)) {
            $settings['background'] = array(
                'type'  => ! empty($selected_background_item['image']) ? 'image' : (! empty($selected_background_item['type']) ? $selected_background_item['type'] : 'color'),
                'value' => ! empty($selected_background_item['value']) ? sanitize_hex_color($selected_background_item['value']) : (isset($settings['background']['value']) ? $settings['background']['value'] : '#ffffff'),
                'image' => ! empty($selected_background_item['image']) ? esc_url_raw($selected_background_item['image']) : '',
            );
        } elseif (! empty($global_settings['wheel_background_color'])) {
            $settings['background'] = array(
                'type'  => ! empty($global_settings['wheel_background_image']) ? 'image' : 'color',
                'value' => sanitize_hex_color($global_settings['wheel_background_color']),
                'image' => ! empty($global_settings['wheel_background_image']) ? esc_url_raw($global_settings['wheel_background_image']) : '',
            );
        }

        $selected_button_item = self::get_setting_item_config('button', $overrides['selected_button_id'] ?? '');
        if (! empty($selected_button_item)) {
            $settings['button'] = array_replace_recursive($settings['button'], array(
                'text'             => ! empty($selected_button_item['text']) ? sanitize_text_field($selected_button_item['text']) : $settings['button']['text'],
                'color'            => ! empty($selected_button_item['color']) ? sanitize_hex_color($selected_button_item['color']) : $settings['button']['color'],
                'text_color'       => ! empty($selected_button_item['text_color']) ? sanitize_hex_color($selected_button_item['text_color']) : $settings['button']['text_color'],
                'radius'           => ! empty($selected_button_item['radius']) ? intval($selected_button_item['radius']) : $settings['button']['radius'],
                'background_image' => ! empty($selected_button_item['background_image']) ? esc_url_raw($selected_button_item['background_image']) : $settings['button']['background_image'],
            ));
        } elseif (! empty($global_settings['wheel_button_text'])) {
            $settings['button']['text'] = sanitize_text_field($global_settings['wheel_button_text']);
            if (! empty($global_settings['wheel_button_color'])) {
                $settings['button']['color'] = sanitize_hex_color($global_settings['wheel_button_color']);
            }
            if (! empty($global_settings['wheel_button_text_color'])) {
                $settings['button']['text_color'] = sanitize_hex_color($global_settings['wheel_button_text_color']);
            }
        }

        $selected_pointer_item = self::get_setting_item_config('pointer', $overrides['selected_pointer_id'] ?? '');
        if (! empty($selected_pointer_item)) {
            $settings['pointer'] = array_replace_recursive($settings['pointer'], array(
                'image' => ! empty($selected_pointer_item['image']) ? esc_url_raw($selected_pointer_item['image']) : $settings['pointer']['image'],
                'size'  => ! empty($selected_pointer_item['size']) ? intval($selected_pointer_item['size']) : $settings['pointer']['size'],
            ));
        } elseif (! empty($global_settings['wheel_pointer_image'])) {
            $settings['pointer']['image'] = esc_url_raw($global_settings['wheel_pointer_image']);
            if (! empty($global_settings['wheel_pointer_size'])) {
                $settings['pointer']['size'] = intval($global_settings['wheel_pointer_size']);
            }
        }

        if (isset($global_settings['wheel_animation_duration'])) {
            $settings['animation']['duration'] = floatval($global_settings['wheel_animation_duration']);
        }
        if (isset($global_settings['wheel_confetti'])) {
            $settings['animation']['confetti'] = ! empty($global_settings['wheel_confetti']);
        }

        return $settings;
    }
}
