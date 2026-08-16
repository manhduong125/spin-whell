<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Prize {
    public static function get_prizes( $wheel_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spin_prizes';
        $wheel_id = absint( $wheel_id );
        if ( ! $wheel_id ) {
            return array();
        }

        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE wheel_id = %d ORDER BY sort_order ASC, id ASC", $wheel_id ), ARRAY_A );
        if ( empty( $results ) ) {
            // Fallback lấy từ post meta nếu có
            $meta = get_post_meta( $wheel_id, '_spin_wheel_prizes_json', true );
            if ( ! empty( $meta ) ) {
                $decoded = is_array( $meta ) ? $meta : json_decode( $meta, true );
                if ( ! empty( $decoded ) && is_array( $decoded ) ) {
                    return $decoded;
                }
            }
            return array();
        }
        return $results;
    }

    public static function decrease_stock( $prize_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spin_prizes';
        $prize_id = absint( $prize_id );
        if ( ! $prize_id ) {
            return true;
        }

        $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET stock = stock - 1, updated_at = CURRENT_TIMESTAMP WHERE id = %d AND stock > 0", $prize_id ) );
        return true;
    }
}
