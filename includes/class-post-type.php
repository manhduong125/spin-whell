<?php
if (! defined('ABSPATH')) {
    exit;
}

class WP_Spin_Wheel_Post_Type
{
    public function __construct()
    {
        add_action('init', array($this, 'register_post_type'));
        add_filter('template_include', array($this, 'load_single_template'));
        add_filter('wp_insert_post_data', array($this, 'custom_spin_wheel_slug'), 10, 2);
    }

    public function register_post_type()
    {
        $labels = array(
            'name'               => __('Spin Wheel', 'wp-spin-wheel'),
            'singular_name'      => __('Spin Wheel', 'wp-spin-wheel'),
            'menu_name'          => __('Spin Wheel', 'wp-spin-wheel'),
            'name_admin_bar'     => __('Spin Wheel', 'wp-spin-wheel'),
            'add_new'            => __('Add New', 'wp-spin-wheel'),
            'add_new_item'       => __('Add New Spin Wheel', 'wp-spin-wheel'),
            'new_item'           => __('New Spin Wheel', 'wp-spin-wheel'),
            'edit_item'          => __('Edit Spin Wheel', 'wp-spin-wheel'),
            'view_item'          => __('View Spin Wheel', 'wp-spin-wheel'),
            'all_items'          => __('All Spin Wheels', 'wp-spin-wheel'),
            'search_items'       => __('Search Spin Wheels', 'wp-spin-wheel'),
            'not_found'          => __('No Spin Wheels found.', 'wp-spin-wheel'),
            'not_found_in_trash' => __('No Spin Wheels found in Trash.', 'wp-spin-wheel'),
        );

        register_post_type('spin_wheel', array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'menu_position'      => 25,
            'menu_icon'          => 'dashicons-chart-pie',
            'supports'           => array('title', 'editor', 'thumbnail', 'author'),
            'has_archive'        => false,
            'rewrite'            => array('slug' => 'spin-wheel', 'with_front' => false),
            'capability_type'    => 'post',
        ));

        // ══════════════════════════════════════════════════════════
        // REGISTER POST TYPE: SPIN BOX / HỘP QUÀ MAY MẮN
        // ══════════════════════════════════════════════════════════
        $box_labels = array(
            'name'               => __('Hộp quà may mắn', 'wp-spin-wheel'),
            'singular_name'      => __('Hộp quà', 'wp-spin-wheel'),
            'menu_name'          => __('Hộp quà may mắn', 'wp-spin-wheel'),
            'name_admin_bar'     => __('Hộp quà', 'wp-spin-wheel'),
            'add_new'            => __('Tạo hộp quà mới', 'wp-spin-wheel'),
            'add_new_item'       => __('Thêm Hộp quà mới', 'wp-spin-wheel'),
            'new_item'           => __('Hộp quà mới', 'wp-spin-wheel'),
            'edit_item'          => __('Sửa Hộp quà', 'wp-spin-wheel'),
            'view_item'          => __('Xem Hộp quà', 'wp-spin-wheel'),
            'all_items'          => __('Tất cả Hộp quà', 'wp-spin-wheel'),
            'search_items'       => __('Tìm kiếm Hộp quà', 'wp-spin-wheel'),
            'not_found'          => __('Chưa tìm thấy Hộp quà nào.', 'wp-spin-wheel'),
            'not_found_in_trash' => __('Không có Hộp quà trong thùng rác.', 'wp-spin-wheel'),
        );

        register_post_type('spin_box', array(
            'labels'             => $box_labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'menu_position'      => 26,
            'menu_icon'          => 'dashicons-archive',
            'supports'           => array('title', 'editor', 'thumbnail', 'author'),
            'has_archive'        => false,
            'rewrite'            => array('slug' => 'hop-qua', 'with_front' => false),
            'capability_type'    => 'post',
        ));
    }

    public function load_single_template($template)
    {
        if (is_singular('spin_wheel')) {
            $plugin_template = WP_SPIN_WHEEL_PATH . 'templates/wheel/single-spin_wheel.php';
            if (! file_exists($plugin_template)) {
                $plugin_template = WP_SPIN_WHEEL_PATH . 'templates/single-spin_wheel.php';
            }
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        } elseif (is_singular('spin_box')) {
            $plugin_template = WP_SPIN_WHEEL_PATH . 'templates/box/single-box.php';
            if (! file_exists($plugin_template)) {
                $plugin_template = WP_SPIN_WHEEL_PATH . 'templates/single-box.php';
            }
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        return $template;
    }

    public function custom_spin_wheel_slug($data, $postarr)
    {
        if (isset($data['post_type'])) {
            $post_id = isset($postarr['ID']) ? absint($postarr['ID']) : 0;
            if ('spin_wheel' === $data['post_type']) {
                if (empty($data['post_name']) || ! preg_match('/^vq[A-Z0-9]{8}$/', $data['post_name'])) {
                    $data['post_name'] = self::generate_unique_vq_slug($post_id);
                }
            } elseif ('spin_box' === $data['post_type']) {
                if (empty($data['post_name']) || ! preg_match('/^hq[A-Z0-9]{8}$/', $data['post_name'])) {
                    $data['post_name'] = self::generate_unique_hq_slug($post_id);
                }
            }
        }
        return $data;
    }

    public static function generate_unique_hq_slug($post_id = 0)
    {
        global $wpdb;
        $attempts = 0;
        do {
            $slug   = 'hq' . self::generate_random_code(8);
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND ID != %d LIMIT 1",
                $slug,
                $post_id
            ));
            $attempts++;
        } while ($exists && $attempts < 100);

        return $slug;
    }

    public static function generate_random_code($length = 8)
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code  = '';
        $max   = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, $max)];
        }
        return $code;
    }

    public static function generate_unique_vq_slug($post_id = 0)
    {
        global $wpdb;
        $attempts = 0;
        do {
            $slug   = 'vq' . self::generate_random_code(8);
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND ID != %d LIMIT 1",
                $slug,
                $post_id
            ));
            $attempts++;
        } while ($exists && $attempts < 100);

        return $slug;
    }
}
