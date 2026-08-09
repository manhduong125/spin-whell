<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Prize {
    public static function get_prizes( $wheel_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spin_prizes';
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE wheel_id = %d", $wheel_id ), ARRAY_A );
        if ( empty( $results ) ) {
            return array();
        }
        return $results;
    }
}
