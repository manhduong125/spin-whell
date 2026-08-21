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
            KEY wheel_status (wheel_id, status),
            KEY wheel_stock (wheel_id, stock),
            KEY wheel_status_stock (wheel_id, status, stock),
            KEY wheel_sort (wheel_id, sort_order),
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
            KEY phone (phone),
            KEY ip (ip),
            KEY created_at (created_at)
        ) {$charset_collate};";

        $sql_history = "CREATE TABLE {$table_history} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            wheel_id bigint(20) unsigned NOT NULL,
            prize_id bigint(20) unsigned NOT NULL DEFAULT 0,
            prize_title varchar(255) DEFAULT NULL,
            player_id bigint(20) unsigned DEFAULT 0,
            name varchar(255) DEFAULT NULL,
            email varchar(255) DEFAULT NULL,
            phone varchar(100) DEFAULT NULL,
            address varchar(255) DEFAULT NULL,
            company varchar(255) DEFAULT NULL,
            ip varchar(100) DEFAULT NULL,
            cookie varchar(255) DEFAULT NULL,
            user_agent varchar(255) DEFAULT NULL,
            status varchar(50) DEFAULT 'won',
            reward_code varchar(255) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY wheel_id (wheel_id),
            KEY prize_id (prize_id),
            KEY prize_title (prize_title(191)),
            KEY player_id (player_id),
            KEY created_at (created_at),
            KEY wheel_created_at (wheel_id, created_at),
            KEY email (email),
            KEY phone (phone),
            KEY reward_code (reward_code),
            KEY cookie (cookie),
            KEY ip (ip)
        ) {$charset_collate};";

        dbDelta( $sql_prizes );
        dbDelta( $sql_players );
        dbDelta( $sql_history );
        // Nâng cấp bảng đã tồn tại trước khi plugin bổ sung cột address/company
        self::migrate_history_table();
        // ensure capabilities are assigned after tables are created
        self::add_capabilities();
    }

    public static function add_capabilities() {
        $roles = array( 'administrator', 'editor' );
        $caps = array(
            'edit_spin_wheel', 'read_spin_wheel', 'delete_spin_wheel',
            'edit_spin_wheels', 'edit_others_spin_wheels', 'publish_spin_wheels', 'read_private_spin_wheels', 'create_spin_wheels',
        );

        foreach ( $roles as $role_name ) {
            $role = get_role( $role_name );
            if ( ! $role ) {
                continue;
            }

            foreach ( $caps as $cap ) {
                if ( ! $role->has_cap( $cap ) ) {
                    $role->add_cap( $cap );
                }
            }
        }
    }

    /**
     * Nâng cấp bảng spin_history cho các site đã cài trước khi có cột address/company.
     * Idempotent: an toàn chạy lại nhiều lần.
     */
    public static function migrate_history_table() {
        global $wpdb;

        $table_history = $wpdb->prefix . 'spin_history';
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $table_exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_history ) );
        if ( $table_exists !== $table_history ) {
            return;
        }

        $existing_columns = $wpdb->get_col( "SHOW COLUMNS FROM {$table_history}" );

        // Thêm cột address nếu chưa có
        if ( ! in_array( 'address', $existing_columns, true ) ) {
            $wpdb->query( "ALTER TABLE {$table_history} ADD COLUMN address varchar(255) DEFAULT NULL AFTER phone" );
        }

        // Thêm cột company nếu chưa có
        if ( ! in_array( 'company', $existing_columns, true ) ) {
            $wpdb->query( "ALTER TABLE {$table_history} ADD COLUMN company varchar(255) DEFAULT NULL AFTER address" );
        }
    }

    public static function flush_rewrite() {
        flush_rewrite_rules();
    }
}
