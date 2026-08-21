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

    public static function find_or_create_player( $data = array() ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spin_players';

        $email = sanitize_email( $data['email'] ?? '' );
        $phone = sanitize_text_field( $data['phone'] ?? '' );
        $ip = sanitize_text_field( $data['ip'] ?? '' );

        if ( $email ) {
            $player = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE email = %s LIMIT 1", $email ), ARRAY_A );
            if ( $player ) {
                return intval( $player['id'] );
            }
        }

        if ( $phone ) {
            $player = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE phone = %s LIMIT 1", $phone ), ARRAY_A );
            if ( $player ) {
                return intval( $player['id'] );
            }
        }

        if ( $ip ) {
            $player = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE ip = %s LIMIT 1", $ip ), ARRAY_A );
            if ( $player ) {
                return intval( $player['id'] );
            }
        }

        if ( ! $email && ! $phone && ! $ip ) {
            return 0;
        }

        $wpdb->insert(
            $table,
            array(
                'name'        => sanitize_text_field( $data['name'] ?? '' ),
                'email'       => $email,
                'phone'       => $phone,
                'ip'          => $ip,
                'total_spins' => 0,
                'total_wins'  => 0,
                'created_at'  => current_time( 'mysql' ),
                'updated_at'  => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s' )
        );

        return intval( $wpdb->insert_id );
    }

    public static function update_stats( $player_id, $spins = 0, $wins = 0 ) {
        $player_id = absint( $player_id );
        if ( ! $player_id || ( $spins === 0 && $wins === 0 ) ) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'spin_players';

        $sets  = array();
        $args  = array();

        if ( $spins !== 0 ) {
            $sets[]  = 'total_spins = total_spins + %d';
            $args[]  = $spins;
        }

        if ( $wins !== 0 ) {
            $sets[]  = 'total_wins = total_wins + %d';
            $args[]  = $wins;
        }

        if ( empty( $sets ) ) {
            return;
        }

        $sets[] = 'updated_at = %s';
        $args[] = current_time( 'mysql' );
        $args[] = $player_id;

        $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET " . implode( ', ', $sets ) . ' WHERE id = %d', $args ) );
    }
}
