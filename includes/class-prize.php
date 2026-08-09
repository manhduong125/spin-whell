<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Prize {
    public static function get_prizes( $wheel_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spin_prizes';
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE wheel_id = %d ORDER BY sort_order ASC, id ASC", $wheel_id ), ARRAY_A );
        if ( empty( $results ) ) {
            return array();
        }
        return $results;
    }

    public static function decrease_stock( $prize_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spin_prizes';
        $prize_id = absint( $prize_id );
        if ( ! $prize_id ) {
            return false;
        }

        $updated = $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET stock = stock - 1, updated_at = CURRENT_TIMESTAMP WHERE id = %d AND stock > 0", $prize_id ) );
        return $updated !== false && $updated > 0;
    }
}
