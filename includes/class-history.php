<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_History {

    /**
     * Ghi nhận 1 lượt quay mới vào database
     *
     * @param int   $wheel_id
     * @param int   $prize_id
     * @param array $form_data
     * @return int|false ID bản ghi hoặc false nếu lỗi
     */
    public function record_spin( $wheel_id, $prize_id, $form_data = array() ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spin_history';

        $wheel_id = absint( $wheel_id );
        $prize_id = absint( $prize_id );

        // Lấy thông tin user đăng nhập nếu chưa có form_data
        $name  = sanitize_text_field( $form_data['name'] ?? '' );
        $email = sanitize_email( $form_data['email'] ?? '' );
        $phone = sanitize_text_field( $form_data['phone'] ?? '' );

        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            if ( empty( $name ) ) {
                $name = $current_user->display_name ?: $current_user->user_login;
            }
            if ( empty( $email ) ) {
                $email = $current_user->user_email;
            }
        }

        if ( empty( $name ) ) {
            $name = __( 'Khách ẩn danh', 'wp-spin-wheel' );
        }

        // Tìm tên giải thưởng
        $prize_title = sanitize_text_field( $form_data['prize_title'] ?? '' );
        if ( empty( $prize_title ) && $prize_id > 0 ) {
            $prize_table = $wpdb->prefix . 'spin_prizes';
            $db_title    = $wpdb->get_var( $wpdb->prepare( "SELECT title FROM {$prize_table} WHERE id = %d", $prize_id ) );
            if ( $db_title ) {
                $prize_title = $db_title;
            }
        }
        if ( empty( $prize_title ) ) {
            $prize_title = __( 'Giải thưởng', 'wp-spin-wheel' );
        }

        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

        $data = array(
            'wheel_id'    => $wheel_id,
            'prize_id'    => $prize_id,
            'prize_title' => $prize_title,
            'name'        => $name,
            'email'       => $email,
            'phone'       => $phone,
            'address'     => sanitize_text_field( $form_data['address'] ?? '' ),
            'company'     => sanitize_text_field( $form_data['company'] ?? '' ),
            'ip'          => $ip,
            'created_at'  => current_time( 'mysql' ),
        );

        $inserted = $wpdb->insert(
            $table,
            $data,
            array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
        );

        if ( $inserted ) {
            $record_id = (int) $wpdb->insert_id;
            // Cập nhật tổng số lượt quay trong meta
            if ( $wheel_id > 0 ) {
                $current_spins = (int) get_post_meta( $wheel_id, '_spin_wheel_total_spins', true );
                update_post_meta( $wheel_id, '_spin_wheel_total_spins', $current_spins + 1 );
            }
            return $record_id;
        }

        return false;
    }

    /**
     * Ghi nhận lượt mở/nhận quà của Hộp quà may mắn vào database
     *
     * @param int    $box_id
     * @param string $gift_name
     * @param string $reward_code
     * @param array  $form_data
     * @return array|false
     */
    public function record_box_claim( $box_id, $gift_name, $reward_code = '', $form_data = array() ) {
        global $wpdb;
        $table  = $wpdb->prefix . 'spin_history';
        $box_id = absint( $box_id );
        $gift   = sanitize_text_field( $gift_name );

        $name  = sanitize_text_field( $form_data['name'] ?? '' );
        $email = sanitize_email( $form_data['email'] ?? '' );
        $phone = sanitize_text_field( $form_data['phone'] ?? '' );

        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            if ( empty( $name ) ) {
                $name = $current_user->display_name ?: $current_user->user_login;
            }
            if ( empty( $email ) ) {
                $email = $current_user->user_email;
            }
        }

        if ( empty( $name ) ) {
            $name = __( 'Khách mở hộp quà', 'wp-spin-wheel' );
        }

        if ( empty( $reward_code ) ) {
            $reward_code = 'HQ-' . wp_rand( 100000, 999999 );
        }

        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

        $data = array(
            'wheel_id'    => $box_id,
            'prize_id'    => 0,
            'prize_title' => $gift,
            'name'        => $name,
            'email'       => $email,
            'phone'       => $phone,
            'address'     => sanitize_text_field( $form_data['address'] ?? '' ),
            'company'     => sanitize_text_field( $form_data['company'] ?? '' ),
            'ip'          => $ip,
            'status'      => 'claimed',
            'reward_code' => $reward_code,
            'created_at'  => current_time( 'mysql' ),
        );

        $inserted = $wpdb->insert(
            $table,
            $data,
            array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
        );

        if ( $inserted ) {
            $record_id = (int) $wpdb->insert_id;
            if ( $box_id > 0 ) {
                $total_opens = (int) get_post_meta( $box_id, '_spin_box_total_opens', true );
                update_post_meta( $box_id, '_spin_box_total_opens', $total_opens + 1 );
            }
            return array(
                'id'          => $record_id,
                'reward_code' => $reward_code,
                'gift_name'   => $gift,
            );
        }

        return false;
    }

    /**
     * Lấy danh sách lịch sử quay/nhận quà với bộ lọc và phân trang
     *
     * @param array $args
     * @return array { items, total, total_pages, page, per_page }
     */
    public function get_history( $args = array() ) {
        global $wpdb;
        $table_history = $wpdb->prefix . 'spin_history';
        $table_prizes  = $wpdb->prefix . 'spin_prizes';
        $table_posts   = $wpdb->posts;

        $defaults = array(
            'wheel_id'  => 0,
            'box_id'    => 0,
            'post_type' => '', // 'spin_wheel', 'spin_box', hoặc rỗng (tất cả)
            'email'     => '',
            'name'      => '',
            'phone'     => '',
            'search'    => '',
            'from_date' => '',
            'to_date'   => '',
            'page'      => 1,
            'per_page'  => 20,
            'orderby'   => 'id',
            'order'     => 'DESC',
        );
        $params = wp_parse_args( $args, $defaults );

        $where  = array( '1=1' );
        $values = array();

        // Lọc theo post_type (spin_wheel hoặc spin_box)
        if ( ! empty( $params['post_type'] ) ) {
            $pt = sanitize_key( $params['post_type'] );
            $where[]  = 'w.post_type = %s';
            $values[] = $pt;
        }

        // Lọc theo wheel_id hoặc box_id
        $target_item_id = ! empty( $params['box_id'] ) ? $params['box_id'] : $params['wheel_id'];
        if ( ! empty( $target_item_id ) ) {
            if ( is_array( $target_item_id ) ) {
                $clean_ids = array_filter( array_map( 'absint', $target_item_id ) );
                if ( ! empty( $clean_ids ) ) {
                    $placeholders = implode( ',', array_fill( 0, count( $clean_ids ), '%d' ) );
                    $where[]      = "h.wheel_id IN ($placeholders)";
                    foreach ( $clean_ids as $wid ) {
                        $values[] = $wid;
                    }
                }
            } else {
                $where[]  = 'h.wheel_id = %d';
                $values[] = absint( $target_item_id );
            }
        }

        // Lọc theo email
        if ( ! empty( $params['email'] ) ) {
            $where[]  = 'h.email LIKE %s';
            $values[] = '%' . $wpdb->esc_like( sanitize_email( $params['email'] ) ) . '%';
        }

        // Lọc theo tên
        if ( ! empty( $params['name'] ) ) {
            $where[]  = 'h.name LIKE %s';
            $values[] = '%' . $wpdb->esc_like( sanitize_text_field( $params['name'] ) ) . '%';
        }

        // Lọc theo số điện thoại
        if ( ! empty( $params['phone'] ) ) {
            $where[]  = 'h.phone LIKE %s';
            $values[] = '%' . $wpdb->esc_like( sanitize_text_field( $params['phone'] ) ) . '%';
        }

        // Tìm kiếm chung (search keyword)
        if ( ! empty( $params['search'] ) ) {
            $kw       = '%' . $wpdb->esc_like( sanitize_text_field( $params['search'] ) ) . '%';
            $where[]  = '(h.name LIKE %s OR h.email LIKE %s OR h.phone LIKE %s OR p.title LIKE %s OR w.post_title LIKE %s)';
            $values[] = $kw;
            $values[] = $kw;
            $values[] = $kw;
            $values[] = $kw;
            $values[] = $kw;
        }

        // Lọc theo khoảng ngày
        if ( ! empty( $params['from_date'] ) ) {
            $from_dt = sanitize_text_field( $params['from_date'] );
            if ( strlen( $from_dt ) === 10 ) {
                $from_dt .= ' 00:00:00';
            }
            $where[]  = 'h.created_at >= %s';
            $values[] = $from_dt;
        }

        if ( ! empty( $params['to_date'] ) ) {
            $to_dt = sanitize_text_field( $params['to_date'] );
            if ( strlen( $to_dt ) === 10 ) {
                $to_dt .= ' 23:59:59';
            }
            $where[]  = 'h.created_at <= %s';
            $values[] = $to_dt;
        }

        $where_sql = implode( ' AND ', $where );

        // Đếm tổng số bản ghi
        $count_sql = "SELECT COUNT(DISTINCT h.id)
                      FROM {$table_history} h
                      LEFT JOIN {$table_prizes} p ON h.prize_id = p.id
                      LEFT JOIN {$table_posts} w ON h.wheel_id = w.ID
                      WHERE {$where_sql}";

        if ( ! empty( $values ) ) {
            $total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $values ) );
        } else {
            $total = (int) $wpdb->get_var( $count_sql );
        }

        $per_page    = max( 1, min( 500, absint( $params['per_page'] ) ) );
        $total_pages = $total > 0 ? (int) ceil( $total / $per_page ) : 1;
        $page        = max( 1, min( $total_pages, absint( $params['page'] ) ) );
        $offset      = ( $page - 1 ) * $per_page;

        // Order & Sort
        $allowed_orderby = array(
            'id'         => 'h.id',
            'created_at' => 'h.created_at',
            'name'       => 'h.name',
            'email'      => 'h.email',
            'wheel_id'   => 'h.wheel_id',
            'prize_id'   => 'h.prize_id',
        );
        $orderby_key     = strtolower( (string) $params['orderby'] );
        $orderby_sql     = $allowed_orderby[ $orderby_key ] ?? 'h.id';
        $order_sql       = strtoupper( (string) $params['order'] ) === 'ASC' ? 'ASC' : 'DESC';

        $select_sql = "SELECT h.*,
                              COALESCE(NULLIF(h.prize_title, ''), p.title, CONCAT('Giải #', h.prize_id)) AS prize_name,
                              COALESCE(w.post_title, CONCAT('#', h.wheel_id)) AS wheel_name,
                              w.post_type
                       FROM {$table_history} h
                       LEFT JOIN {$table_prizes} p ON h.prize_id = p.id
                       LEFT JOIN {$table_posts} w ON h.wheel_id = w.ID
                       WHERE {$where_sql}
                       ORDER BY {$orderby_sql} {$order_sql}
                       LIMIT %d OFFSET %d";

        $query_values = array_merge( $values, array( $per_page, $offset ) );
        $raw_items    = $wpdb->get_results( $wpdb->prepare( $select_sql, $query_values ), ARRAY_A );

        $items = array();
        if ( is_array( $raw_items ) ) {
            foreach ( $raw_items as $row ) {
                $created_time = ! empty( $row['created_at'] ) ? strtotime( $row['created_at'] ) : time();
                $items[]      = array(
                    'id'                   => (int) $row['id'],
                    'wheel_id'             => (int) $row['wheel_id'],
                    'wheel_title'          => $row['wheel_name'] ?: sprintf( __( 'Vòng quay #%d', 'wp-spin-wheel' ), $row['wheel_id'] ),
                    'prize_id'             => (int) $row['prize_id'],
                    'prize_title'          => $row['prize_name'] ?: __( 'Giải thưởng', 'wp-spin-wheel' ),
                    'player_id'            => (int) ( $row['player_id'] ?? 0 ),
                    'name'                 => $row['name'] ?: __( 'Khách ẩn danh', 'wp-spin-wheel' ),
                    'email'                => $row['email'] ?: '',
                    'phone'                => $row['phone'] ?: '',
                    'address'              => $row['address'] ?? '',
                    'company'              => $row['company'] ?? '',
                    'ip'                   => $row['ip'] ?: '',
                    'created_at'           => $row['created_at'],
                    'created_at_formatted' => date_i18n( 'd/m/Y H:i', $created_time ),
                    'time_ago'             => self::human_time_ago( $row['created_at'] ),
                );
            }
        }

        return array(
            'items'       => $items,
            'total'       => $total,
            'total_pages' => $total_pages,
            'page'        => $page,
            'per_page'    => $per_page,
        );
    }

    /**
     * Lấy danh sách người trúng gần đây cho widget/shortcode/popup
     *
     * @param int $limit
     * @param int $wheel_id
     * @return array
     */
    public function get_recent_winners( $limit = 10, $wheel_id = 0 ) {
        $result = $this->get_history( array(
            'wheel_id' => $wheel_id,
            'per_page' => max( 1, min( 50, absint( $limit ) ) ),
            'page'     => 1,
            'orderby'  => 'id',
            'order'    => 'DESC',
        ) );

        return $result['items'] ?? array();
    }

    /**
     * Xóa 1 bản ghi lịch sử theo ID
     *
     * @param int $id
     * @return bool
     */
    public function delete_entry( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spin_history';
        $id    = absint( $id );
        if ( $id <= 0 ) {
            return false;
        }
        $deleted = $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
        return false !== $deleted;
    }

    /**
     * Xóa nhiều bản ghi theo danh sách IDs
     *
     * @param array $ids
     * @return int Số bản ghi đã xóa
     */
    public function delete_entries( $ids = array() ) {
        global $wpdb;
        $table = $wpdb->prefix . 'spin_history';
        $clean = array_filter( array_map( 'absint', (array) $ids ) );
        if ( empty( $clean ) ) {
            return 0;
        }
        $placeholders = implode( ',', array_fill( 0, count( $clean ), '%d' ) );
        $deleted      = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE id IN ($placeholders)", $clean ) );
        return (int) $deleted;
    }

    /**
     * Xóa sạch lịch sử quay (của 1 vòng quay hoặc toàn bộ)
     *
     * @param int $wheel_id (0 = xóa tất cả)
     * @return bool
     */
    public function clear_history( $wheel_id = 0 ) {
        global $wpdb;
        $table    = $wpdb->prefix . 'spin_history';
        $wheel_id = absint( $wheel_id );

        if ( $wheel_id > 0 ) {
            $res = $wpdb->delete( $table, array( 'wheel_id' => $wheel_id ), array( '%d' ) );
        } else {
            $res = $wpdb->query( "TRUNCATE TABLE {$table}" );
        }

        return false !== $res;
    }

    /**
     * Thống kê dành riêng cho Hộp quà may mắn (Lucky Box)
     *
     * @param int $box_id
     * @return array
     */
    public function get_box_stats( $box_id = 0 ) {
        global $wpdb;
        $history_table = $wpdb->prefix . 'spin_history';
        $box_id        = absint( $box_id );

        if ( $box_id > 0 ) {
            $total_claims  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$history_table} WHERE wheel_id = %d", $box_id ) );
            $today_claims  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$history_table} WHERE wheel_id = %d AND DATE(created_at) = CURDATE()", $box_id ) );
            $total_players = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT email) FROM {$history_table} WHERE wheel_id = %d AND email != ''", $box_id ) );
            $top_gift      = $wpdb->get_var( $wpdb->prepare( "SELECT prize_title FROM {$history_table} WHERE wheel_id = %d AND prize_title IS NOT NULL AND prize_title != '' GROUP BY prize_title ORDER BY COUNT(*) DESC LIMIT 1", $box_id ) );
            $views         = (int) get_post_meta( $box_id, '_spin_box_views', true );
            if ( $views <= 0 ) {
                $views = (int) get_post_meta( $box_id, '_spin_box_total_opens', true );
            }

            return array(
                'total_claims'  => $total_claims,
                'today_claims'  => $today_claims,
                'total_players' => $total_players ?: max( 1, $total_claims ),
                'top_gift'      => $top_gift ?: __( 'N/A', 'wp-spin-wheel' ),
                'views'         => max( $views, $total_claims ),
            );
        }

        // Toàn bộ các hộp quà (post_type = 'spin_box')
        $total_claims  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$history_table} h INNER JOIN {$wpdb->posts} w ON h.wheel_id = w.ID WHERE w.post_type = 'spin_box'" );
        $today_claims  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$history_table} h INNER JOIN {$wpdb->posts} w ON h.wheel_id = w.ID WHERE w.post_type = 'spin_box' AND DATE(h.created_at) = CURDATE()" );
        $total_players = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT h.email) FROM {$history_table} h INNER JOIN {$wpdb->posts} w ON h.wheel_id = w.ID WHERE w.post_type = 'spin_box' AND h.email != ''" );
        $top_gift      = $wpdb->get_var( "SELECT h.prize_title FROM {$history_table} h INNER JOIN {$wpdb->posts} w ON h.wheel_id = w.ID WHERE w.post_type = 'spin_box' AND h.prize_title IS NOT NULL AND h.prize_title != '' GROUP BY h.prize_title ORDER BY COUNT(*) DESC LIMIT 1" );
        $top_box       = $wpdb->get_var( "SELECT w.post_title FROM {$history_table} h INNER JOIN {$wpdb->posts} w ON h.wheel_id = w.ID WHERE w.post_type = 'spin_box' GROUP BY h.wheel_id ORDER BY COUNT(*) DESC LIMIT 1" );

        return array(
            'total_claims'  => $total_claims,
            'today_claims'  => $today_claims,
            'total_players' => $total_players ?: max( 1, $total_claims ),
            'top_gift'      => $top_gift ?: __( 'N/A', 'wp-spin-wheel' ),
            'top_box'       => $top_box ?: __( 'N/A', 'wp-spin-wheel' ),
        );
    }

    /**
     * Xóa sạch lịch sử nhận thưởng của Hộp quà
     *
     * @param int $box_id (0 = xóa toàn bộ lịch sử các hộp quà)
     * @return bool
     */
    public function clear_box_history( $box_id = 0 ) {
        global $wpdb;
        $table  = $wpdb->prefix . 'spin_history';
        $box_id = absint( $box_id );

        if ( $box_id > 0 ) {
            $res = $wpdb->delete( $table, array( 'wheel_id' => $box_id ), array( '%d' ) );
        } else {
            $res = $wpdb->query( "DELETE h FROM {$table} h INNER JOIN {$wpdb->posts} w ON h.wheel_id = w.ID WHERE w.post_type = 'spin_box'" );
        }

        return false !== $res;
    }
    public function get_stats( $wheel_id = 0 ) {
        global $wpdb;
        $history_table = $wpdb->prefix . 'spin_history';
        $prize_table   = $wpdb->prefix . 'spin_prizes';

        $wheel_id = absint( $wheel_id );
        if ( $wheel_id > 0 ) {
            return $this->get_wheel_stats( $wheel_id );
        }

        $total_spins   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$history_table}" );
        $total_players = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT email) FROM {$history_table} WHERE email != ''" );
        $today_spins   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$history_table} WHERE DATE(created_at) = CURDATE()" );

        $top_prize = $wpdb->get_var( "SELECT p.title FROM {$history_table} h INNER JOIN {$prize_table} p ON h.prize_id = p.id GROUP BY h.prize_id ORDER BY COUNT(*) DESC LIMIT 1" );
        $top_wheel = $wpdb->get_var( "SELECT w.post_title FROM {$history_table} h INNER JOIN {$wpdb->posts} w ON h.wheel_id = w.ID GROUP BY h.wheel_id ORDER BY COUNT(*) DESC LIMIT 1" );

        return array(
            'total_spins'   => $total_spins,
            'total_players' => $total_players ?: max( 1, $total_spins ),
            'today_spins'   => $today_spins,
            'top_prize'     => $top_prize ?: __( 'N/A', 'wp-spin-wheel' ),
            'top_wheel'     => $top_wheel ?: __( 'N/A', 'wp-spin-wheel' ),
        );
    }

    /**
     * Lấy chi tiết thông tin và thống kê của một vòng quay cụ thể
     *
     * @param int $wheel_id
     * @return array
     */
    public function get_wheel_stats( $wheel_id ) {
        global $wpdb;
        $table    = $wpdb->prefix . 'spin_history';
        $wheel_id = absint( $wheel_id );

        $post = get_post( $wheel_id );

        // Lượt xem
        $views = (int) get_post_meta( $wheel_id, '_spin_wheel_views', true );
        if ( $views <= 0 ) {
            $views = max( 1, (int) get_post_meta( $wheel_id, 'views', true ) );
        }

        // Tổng lượt quay
        $total_spins = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE wheel_id = %d", $wheel_id ) );
        if ( $total_spins <= 0 ) {
            $total_spins = (int) get_post_meta( $wheel_id, '_spin_wheel_total_spins', true );
        }

        // Lượt quay hôm nay
        $today_spins = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE wheel_id = %d AND DATE(created_at) = CURDATE()", $wheel_id ) );

        // Tác giả / Người tạo
        $creator_name  = __( 'Admin', 'wp-spin-wheel' );
        $creator_email = '';
        if ( $post && ! empty( $post->post_author ) ) {
            $author = get_userdata( $post->post_author );
            if ( $author ) {
                $creator_name  = $author->display_name ?: $author->user_login;
                $creator_email = $author->user_email;
            }
        }

        // Cấu hình giới hạn quay
        $settings       = WP_Spin_Wheel_Helper::get_wheel_settings( $wheel_id );
        $limit_spins    = $settings['limit_spins'] ?? ( $settings['spin_limit'] ?? 0 );
        $max_spins_text = __( 'Không giới hạn (∞)', 'wp-spin-wheel' );
        if ( ! empty( $limit_spins ) && (int) $limit_spins > 0 ) {
            $max_spins_text = sprintf( __( '%d lần', 'wp-spin-wheel' ), (int) $limit_spins );
        }

        return array(
            'wheel_id'       => $wheel_id,
            'title'          => $post ? $post->post_title : sprintf( __( 'Vòng quay #%d', 'wp-spin-wheel' ), $wheel_id ),
            'views'          => $views,
            'total_spins'    => $total_spins,
            'today_spins'    => $today_spins,
            'creator'        => $creator_name,
            'creator_email'  => $creator_email,
            'max_spins'      => $max_spins_text,
            'created_at'     => $post ? get_the_date( 'd/m/Y', $post->ID ) : '',
        );
    }

    /**
     * Tăng số lượt xem của vòng quay
     *
     * @param int $wheel_id
     * @return int Lượt xem mới
     */
    public function increment_views( $wheel_id ) {
        $wheel_id = absint( $wheel_id );
        if ( $wheel_id <= 0 ) {
            return 0;
        }

        $views = (int) get_post_meta( $wheel_id, '_spin_wheel_views', true );
        $views++;
        update_post_meta( $wheel_id, '_spin_wheel_views', $views );
        return $views;
    }

    /**
     * Chuyển đổi datetime sang định dạng "thời gian tương đối" tiếng Việt
     *
     * @param string $datetime
     * @return string
     */
    public static function human_time_ago( $datetime ) {
        if ( empty( $datetime ) ) {
            return '';
        }

        $timestamp = strtotime( $datetime );
        if ( ! $timestamp ) {
            return $datetime;
        }

        $diff = current_time( 'timestamp' ) - $timestamp;

        if ( $diff < 60 ) {
            return __( 'Vừa xong', 'wp-spin-wheel' );
        } elseif ( $diff < 3600 ) {
            $mins = max( 1, floor( $diff / 60 ) );
            return sprintf( __( '%d phút trước', 'wp-spin-wheel' ), $mins );
        } elseif ( $diff < 86400 ) {
            $hours = floor( $diff / 3600 );
            return sprintf( __( '%d giờ trước', 'wp-spin-wheel' ), $hours );
        } elseif ( $diff < 86400 * 7 ) {
            $days = floor( $diff / 86400 );
            return sprintf( __( '%d ngày trước', 'wp-spin-wheel' ), $days );
        }

        return date_i18n( 'd/m/Y H:i', $timestamp );
    }
}

