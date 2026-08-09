<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Install {
    public static function activate() {
        self::create_tables();
        self::flush_rewrite();
    }

    public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_prizes  = $wpdb->prefix . 'spin_prizes';
        $table_players = $wpdb->prefix . 'spin_players';
        $table_history = $wpdb->prefix . 'spin_history';

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql_prizes = "CREATE TABLE {$table_prizes} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            wheel_id bigint(20) unsigned NOT NULL,
            title varchar(255) NOT NULL,
            description text DEFAULT NULL,
            color varchar(50) DEFAULT NULL,
            image varchar(255) DEFAULT NULL,
            icon varchar(255) DEFAULT NULL,
            weight int(11) NOT NULL DEFAULT 1,
            stock int(11) NOT NULL DEFAULT 0,
            initial_stock int(11) NOT NULL DEFAULT 0,
            status varchar(50) NOT NULL DEFAULT 'active',
            sort_order int(11) NOT NULL DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY wheel_id (wheel_id),
            KEY wheel_stock (wheel_id, stock),
            KEY status (status)
        ) {$charset_collate};";

        $sql_players = "CREATE TABLE {$table_players} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            name varchar(255) DEFAULT NULL,
            email varchar(255) DEFAULT NULL,
            phone varchar(100) DEFAULT NULL,
            ip varchar(100) DEFAULT NULL,
            total_spins int(11) NOT NULL DEFAULT 0,
            total_wins int(11) NOT NULL DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY email (email),
            KEY phone (phone)
        ) {$charset_collate};";

        $sql_history = "CREATE TABLE {$table_history} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            wheel_id bigint(20) unsigned NOT NULL,
            prize_id bigint(20) unsigned NOT NULL,
            player_id bigint(20) unsigned DEFAULT 0,
            name varchar(255) DEFAULT NULL,
            email varchar(255) DEFAULT NULL,
            phone varchar(100) DEFAULT NULL,
            ip varchar(100) DEFAULT NULL,
            cookie varchar(255) DEFAULT NULL,
            user_agent varchar(255) DEFAULT NULL,
            status varchar(50) DEFAULT 'won',
            reward_code varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY wheel_id (wheel_id),
            KEY prize_id (prize_id),
            KEY player_id (player_id),
            KEY created_at (created_at),
            KEY wheel_created_at (wheel_id, created_at),
            KEY email (email),
            KEY phone (phone)
        ) {$charset_collate};";

        dbDelta( $sql_prizes );
        dbDelta( $sql_players );
        dbDelta( $sql_history );
    }

    public static function flush_rewrite() {
        flush_rewrite_rules();
    }
}
