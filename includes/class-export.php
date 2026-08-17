<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Export {

    /**
     * Xuất lịch sử quay ra file CSV với UTF-8 BOM (tương thích hoàn hảo với Excel)
     *
     * @param array $args Bộ lọc (wheel_id, from_date, to_date, email, search)
     */
    public static function export_history_csv( $args = array() ) {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Bạn không có quyền thực hiện hành động này.', 'wp-spin-wheel' ) );
        }

        $history_obj = new WP_Spin_Wheel_History();
        $args['per_page'] = 10000; // Xuất tối đa 10.000 dòng
        $args['page']     = 1;
        $history_data     = $history_obj->get_history( $args );
        $items            = $history_data['items'] ?? array();

        $filename = 'lich-su-quay-' . date( 'Y-m-d_H-i-s' ) . '.csv';

        // Gửi headers
        header( 'Content-Type: text/csv; charset=UTF-8' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $output = fopen( 'php://output', 'w' );

        // Ghi UTF-8 BOM để Excel hiển thị đúng tiếng Việt có dấu
        fprintf( $output, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

        // Headers
        $headers = array(
            __( 'ID', 'wp-spin-wheel' ),
            __( 'Vòng quay', 'wp-spin-wheel' ),
            __( 'Giải thưởng', 'wp-spin-wheel' ),
            __( 'Tên người chơi', 'wp-spin-wheel' ),
            __( 'Email', 'wp-spin-wheel' ),
            __( 'Số điện thoại', 'wp-spin-wheel' ),
            __( 'Địa chỉ', 'wp-spin-wheel' ),
            __( 'Công ty', 'wp-spin-wheel' ),
            __( 'IP', 'wp-spin-wheel' ),
            __( 'Mã trúng thưởng', 'wp-spin-wheel' ),
            __( 'Thời gian quay', 'wp-spin-wheel' ),
        );
        fputcsv( $output, $headers );

        foreach ( $items as $item ) {
            $row = array(
                $item['id'],
                $item['wheel_title'],
                $item['prize_title'],
                $item['name'],
                $item['email'],
                $item['phone'],
                $item['address'],
                $item['company'],
                $item['ip'],
                $item['reward_code'],
                $item['created_at_formatted'] ?: $item['created_at'],
            );
            fputcsv( $output, $row );
        }

        fclose( $output );
        exit;
    }
}

