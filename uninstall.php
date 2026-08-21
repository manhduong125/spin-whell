<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Drop toàn bộ bảng của plugin (thiếu spin_players ở bản cũ)
$tables = array( 'spin_history', 'spin_prizes', 'spin_players' );
foreach ( $tables as $table ) {
    $table_name = $wpdb->prefix . $table;
    $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
}

// Xóa toàn bộ bài viết của plugin (thêm spin_box cho bản cũ)
$post_types = array( 'spin_wheel', 'spin_box', 'spin_wheel_preset' );
foreach ( $post_types as $post_type ) {
    $posts = get_posts( array(
        'post_type'      => $post_type,
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'fields'         => 'ids',
    ) );

    foreach ( $posts as $post_id ) {
        wp_delete_post( $post_id, true );
    }
}

// Dọn các option đã lưu
delete_option( 'wp_spin_wheel_settings' );
delete_option( 'wp_spin_box_settings' );
delete_option( 'wp_spin_wheel_default_presets_created' );
delete_option( 'wp_spin_wheel_default_setting_items_created' );
