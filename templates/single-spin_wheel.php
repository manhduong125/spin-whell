<?php

/**
 * Single template for Spin Wheel post type (single-spin_wheel.php)
 *
 * @package WP_Spin_Wheel
 */

if (! defined('ABSPATH')) {
    exit;
}

get_header();

// Lấy ID của bài viết vòng quay hiện tại
$wheel_id = get_the_ID();

// Nạp giao diện vòng quay
include WP_SPIN_WHEEL_PATH . 'templates/wheel-default.php';

get_footer();
