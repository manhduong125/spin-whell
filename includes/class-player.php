<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Player {
    public static function get_player_count() {
        global $wpdb;
        $table_history = $wpdb->prefix . 'spin_history';
        return (int) $wpdb->get_var( "SELECT COUNT(DISTINCT email) FROM {$table_history}" );
    }
}
