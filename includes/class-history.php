<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_History {
    public function record_spin( $wheel_id, $prize_id, $form_data = array() ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spin_history';

        $wpdb->insert(
            $table,
            array(
                'wheel_id' => $wheel_id,
                'prize_id' => $prize_id,
                'name'     => sanitize_text_field( $form_data['name'] ?? '' ),
                'email'    => sanitize_email( $form_data['email'] ?? '' ),
                'phone'    => sanitize_text_field( $form_data['phone'] ?? '' ),
                'address'  => sanitize_text_field( $form_data['address'] ?? '' ),
                'company'  => sanitize_text_field( $form_data['company'] ?? '' ),
                'ip'       => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
                'created_at' => current_time( 'mysql' ),
            ),
            array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
        );
    }

    public function get_stats() {
        global $wpdb;
        $history_table = $wpdb->prefix . 'spin_history';
        $prize_table = $wpdb->prefix . 'spin_prizes';

        $total_spins = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$history_table}" );
        $total_players = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT email) FROM {$history_table}" );
        $today_spins = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$history_table} WHERE DATE(created_at) = CURDATE()" );

        $top_prize = $wpdb->get_var( "SELECT p.title FROM {$history_table} h INNER JOIN {$prize_table} p ON h.prize_id = p.id GROUP BY h.prize_id ORDER BY COUNT(*) DESC LIMIT 1" );
        $top_wheel = $wpdb->get_var( "SELECT w.post_title FROM {$history_table} h INNER JOIN {$wpdb->posts} w ON h.wheel_id = w.ID GROUP BY h.wheel_id ORDER BY COUNT(*) DESC LIMIT 1" );

        return array(
            'total_spins'   => $total_spins,
            'total_players' => $total_players,
            'today_spins'   => $today_spins,
            'top_prize'     => $top_prize ?: __( 'N/A', 'wp-spin-wheel' ),
            'top_wheel'     => $top_wheel ?: __( 'N/A', 'wp-spin-wheel' ),
        );
    }
}
