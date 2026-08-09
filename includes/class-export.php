<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Export {
    public static function export_history_csv() {
        global $wpdb;
        $table = $wpdb->prefix . 'spin_history';

        $results = $wpdb->get_results( "SELECT * FROM {$table}", ARRAY_A );
        if ( empty( $results ) ) {
            return false;
        }

        $filename = 'spin-wheel-history-' . date( 'Y-m-d' ) . '.csv';
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );

        $output = fopen( 'php://output', 'w' );
        fputcsv( $output, array_keys( $results[0] ) );
        foreach ( $results as $row ) {
            fputcsv( $output, $row );
        }
        fclose( $output );
        exit;
    }
}
