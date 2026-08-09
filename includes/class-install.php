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
        $table_prizes     = $wpdb->prefix . 'spin_prizes';
        $table_history    = $wpdb->prefix . 'spin_history';

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql_prizes = "CREATE TABLE {$table_prizes} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            wheel_id bigint(20) unsigned NOT NULL,
            title varchar(255) NOT NULL,
            color varchar(50) DEFAULT NULL,
            weight int(11) NOT NULL DEFAULT 1,
            image varchar(255) DEFAULT NULL,
            stock int(11) NOT NULL DEFAULT 0,
            description text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY wheel_id (wheel_id)
        ) {$charset_collate};";

        $sql_history = "CREATE TABLE {$table_history} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            wheel_id bigint(20) unsigned NOT NULL,
            prize_id bigint(20) unsigned NOT NULL,
            name varchar(255) DEFAULT NULL,
            email varchar(255) DEFAULT NULL,
            phone varchar(100) DEFAULT NULL,
            address varchar(255) DEFAULT NULL,
            company varchar(255) DEFAULT NULL,
            ip varchar(100) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY wheel_id (wheel_id),
            KEY prize_id (prize_id)
        ) {$charset_collate};";

        dbDelta( $sql_prizes );
        dbDelta( $sql_history );
    }

    public static function flush_rewrite() {
        flush_rewrite_rules();
    }
}
