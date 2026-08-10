<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

$table_history = $wpdb->prefix . 'spin_history';
$table_prizes  = $wpdb->prefix . 'spin_prizes';

$wpdb->query( "DROP TABLE IF EXISTS {$table_history}" );
$wpdb->query( "DROP TABLE IF EXISTS {$table_prizes}" );

$posts = get_posts( array(
    'post_type'      => 'spin_wheel',
    'posts_per_page' => -1,
    'post_status'    => 'any',
    'fields'         => 'ids',
) );

foreach ( $posts as $post_id ) {
    wp_delete_post( $post_id, true );
}

$preset_posts = get_posts( array(
    'post_type'      => 'spin_wheel_preset',
    'posts_per_page' => -1,
    'post_status'    => 'any',
    'fields'         => 'ids',
) );

foreach ( $preset_posts as $post_id ) {
    wp_delete_post( $post_id, true );
}

delete_option( 'wp_spin_wheel_default_presets_created' );
delete_option( 'wp_spin_wheel_default_setting_items_created' );
