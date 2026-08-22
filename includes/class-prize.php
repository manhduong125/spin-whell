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
                    return wp_spin_wheel_fix_mangled_unicode( $decoded );
                }
            }
            return array();
        }
        // Tự phục hồi tiêu đề giải thưởng bị hỏng Unicode dạng "Hu1ed8P"
        return wp_spin_wheel_fix_mangled_unicode( $results );
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

    public static function sync_prizes( $wheel_id, $prizes ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spin_prizes';
        $wheel_id = absint( $wheel_id );
        if ( ! $wheel_id || ! is_array( $prizes ) ) {
            return false;
        }

        $wpdb->delete( $table, array( 'wheel_id' => $wheel_id ), array( '%d' ) );
        $sort = 0;
        foreach ( $prizes as $prize ) {
            $title = sanitize_text_field( $prize['title'] ?? ( $prize['name'] ?? '' ) );
            if ( empty( $title ) ) {
                continue;
            }
            $wpdb->insert(
                $table,
                array(
                    'wheel_id'      => $wheel_id,
                    'title'         => $title,
                    'description'   => sanitize_textarea_field( $prize['description'] ?? '' ),
                    'color'         => sanitize_text_field( $prize['color'] ?? '#3b82f6' ),
                    'image'         => esc_url_raw( $prize['image'] ?? '' ),
                    'icon'          => esc_url_raw( $prize['icon'] ?? '' ),
                    'weight'        => max( 1, intval( $prize['weight'] ?? 10 ) ),
                    'stock'         => max( 0, intval( $prize['stock'] ?? 9999 ) ),
                    'initial_stock' => max( 0, intval( $prize['initial_stock'] ?? ( $prize['stock'] ?? 9999 ) ) ),
                    'status'        => sanitize_text_field( $prize['status'] ?? 'active' ),
                    'sort_order'    => $sort++,
                    'created_at'    => current_time( 'mysql' ),
                ),
                array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%d', '%s' )
            );
        }
        return true;
    }
}
