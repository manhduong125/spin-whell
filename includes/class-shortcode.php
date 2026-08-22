<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Shortcode {
    public function __construct() {
        add_shortcode( 'spin_wheel', array( $this, 'render_shortcode' ) );
        add_shortcode( 'spin_wheel_recent_winners', array( $this, 'render_recent_winners_shortcode' ) );
        add_shortcode( 'spin_wheel_winners', array( $this, 'render_recent_winners_shortcode' ) );
        add_shortcode( 'spin_wheel_history', array( $this, 'render_recent_winners_shortcode' ) );
        add_shortcode( 'spin_wheel_user_wheels', array( $this, 'render_user_wheels_shortcode' ) );
        add_shortcode( 'user_spin_wheels', array( $this, 'render_user_wheels_shortcode' ) );
        add_shortcode( 'my_spin_wheels', array( $this, 'render_user_wheels_shortcode' ) );
        add_shortcode( 'spin_wheel_my_wheels', array( $this, 'render_user_wheels_shortcode' ) );
        add_shortcode( 'spin_wheel_collection', array( $this, 'render_user_wheels_shortcode' ) );
        add_shortcode( 'spin_wheel_stats', array( $this, 'render_stats_shortcode' ) );
        add_shortcode( 'vqmm_stats', array( $this, 'render_stats_shortcode' ) );
        add_shortcode( 'spin_wheel_statistics', array( $this, 'render_stats_shortcode' ) );
        add_shortcode( 'lucky_box', array( $this, 'render_box_shortcode' ) );
        add_shortcode( 'mystery_box', array( $this, 'render_box_shortcode' ) );
        add_shortcode( 'hop_qua', array( $this, 'render_box_shortcode' ) );
        add_shortcode( 'spin_wheel_box', array( $this, 'render_box_shortcode' ) );
        add_shortcode( 'box_gallery', array( $this, 'render_user_boxes_shortcode' ) );
        add_shortcode( 'user_boxes', array( $this, 'render_user_boxes_shortcode' ) );
        add_shortcode( 'my_boxes', array( $this, 'render_user_boxes_shortcode' ) );
        add_shortcode( 'spin_wheel_box_list', array( $this, 'render_user_boxes_shortcode' ) );

        add_action( 'widgets_init', array( $this, 'register_widgets' ) );
    }

    public function register_widgets() {
        if ( class_exists( 'WP_Widget' ) ) {
            register_widget( 'WP_Spin_Wheel_Recent_Winners_Widget' );
        }
    }

    public function render_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'id' => 0,
        ), $atts, 'spin_wheel' );

        $post_id = absint( $atts['id'] );
        if ( $post_id && get_post_type( $post_id ) === 'spin_wheel' ) {
            ob_start();
            $template = WP_SPIN_WHEEL_PATH . 'templates/wheel/wheel-user.php';
            if ( ! file_exists( $template ) ) {
                $template = WP_SPIN_WHEEL_PATH . 'templates/wheel/wheel-default.php';
            }
            if ( ! file_exists( $template ) ) {
                $template = WP_SPIN_WHEEL_PATH . 'templates/wheel-user.php';
            }
            include $template;
            return ob_get_clean();
        }

        ob_start();
        $default_tpl = WP_SPIN_WHEEL_PATH . 'templates/wheel/wheel-default.php';
        if ( ! file_exists( $default_tpl ) ) {
            $default_tpl = WP_SPIN_WHEEL_PATH . 'templates/wheel-default.php';
        }
        include $default_tpl;
        return ob_get_clean();
    }

    /**
     * Render Shortcode hiển thị Người trúng thưởng gần đây
     * [spin_wheel_recent_winners limit="10" wheel_id="0" style="ticker|list|table" title="Người trúng thưởng gần đây"]
     */
    public function render_recent_winners_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'wheel_id'     => 0,
            'limit'        => 10,
            'style'        => 'list', // 'list', 'ticker', 'table'
            'title'        => __( '🎉 Người trúng thưởng gần đây', 'wp-spin-wheel' ),
            'mask_name'    => 'false',
            'auto_refresh' => 30, // Giây (0 = tắt)
        ), $atts, 'spin_wheel_recent_winners' );

        $wheel_id     = absint( $atts['wheel_id'] );
        $limit        = max( 1, min( 50, absint( $atts['limit'] ) ) );
        $style        = in_array( $atts['style'], array( 'list', 'ticker', 'table' ), true ) ? $atts['style'] : 'list';
        $title        = sanitize_text_field( $atts['title'] );
        $mask_name    = filter_var( $atts['mask_name'], FILTER_VALIDATE_BOOLEAN );
        $auto_refresh = absint( $atts['auto_refresh'] );

        $history_obj = new WP_Spin_Wheel_History();
        $winners     = $history_obj->get_recent_winners( $limit, $wheel_id );
        $unique_id   = 'sw-recent-winners-' . wp_rand( 1000, 9999 );

        ob_start();
        ?>
        <div class="sw-recent-winners-container sw-style-<?php echo esc_attr( $style ); ?>" id="<?php echo esc_attr( $unique_id ); ?>" data-wheel-id="<?php echo esc_attr( $wheel_id ); ?>" data-limit="<?php echo esc_attr( $limit ); ?>" data-refresh="<?php echo esc_attr( $auto_refresh ); ?>">
            <?php if ( ! empty( $title ) && 'ticker' !== $style ) : ?>
                <div class="sw-recent-winners-header">
                    <h4 class="sw-recent-winners-title">
                        <span class="sw-trophy-icon">🏆</span>
                        <?php echo esc_html( $title ); ?>
                    </h4>
                    <span class="sw-live-pulse" title="<?php esc_attr_e( 'Cập nhật trực tiếp', 'wp-spin-wheel' ); ?>"></span>
                </div>
            <?php endif; ?>

            <?php if ( 'ticker' === $style ) : ?>
                <!-- Dạng Ticker chạy ngang -->
                <div class="sw-ticker-wrap">
                    <?php if ( ! empty( $title ) ) : ?>
                        <div class="sw-ticker-badge"><?php echo esc_html( $title ); ?></div>
                    <?php endif; ?>
                    <div class="sw-ticker-move">
                        <?php if ( ! empty( $winners ) ) : ?>
                            <?php foreach ( $winners as $w ) :
                                $display_name = $mask_name ? self::mask_player_name( $w['name'] ) : $w['name'];
                            ?>
                                <div class="sw-ticker-item">
                                    <span class="sw-winner-name">👤 <?php echo esc_html( $display_name ); ?></span>
                                    <span class="sw-winner-action"><?php esc_html_e( 'vừa trúng', 'wp-spin-wheel' ); ?></span>
                                    <span class="sw-winner-prize">🎁 <?php echo esc_html( $w['prize_title'] ); ?></span>
                                    <span class="sw-winner-time">(<?php echo esc_html( $w['time_ago'] ); ?>)</span>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="sw-ticker-item"><?php esc_html_e( 'Chưa có lượt quay nào gần đây.', 'wp-spin-wheel' ); ?></div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ( 'table' === $style ) : ?>
                <!-- Dạng Bảng -->
                <div class="sw-table-responsive">
                    <table class="sw-winners-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Người chơi', 'wp-spin-wheel' ); ?></th>
                                <th><?php esc_html_e( 'Giải thưởng', 'wp-spin-wheel' ); ?></th>
                                <th><?php esc_html_e( 'Thời gian', 'wp-spin-wheel' ); ?></th>
                            </tr>
                        </thead>
                        <tbody class="sw-winners-list-body">
                            <?php if ( ! empty( $winners ) ) : ?>
                                <?php foreach ( $winners as $w ) :
                                    $display_name = $mask_name ? self::mask_player_name( $w['name'] ) : $w['name'];
                                ?>
                                    <tr>
                                        <td><strong><?php echo esc_html( $display_name ); ?></strong></td>
                                        <td><span class="sw-prize-badge"><?php echo esc_html( $w['prize_title'] ); ?></span></td>
                                        <td><small class="sw-time-ago"><?php echo esc_html( $w['time_ago'] ); ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="3" class="sw-empty-text"><?php esc_html_e( 'Chưa có người chơi nào trúng thưởng.', 'wp-spin-wheel' ); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            <?php else : ?>
                <!-- Dạng Danh sách Card (Mặc định) -->
                <div class="sw-winners-list-wrap">
                    <ul class="sw-winners-list">
                        <?php if ( ! empty( $winners ) ) : ?>
                            <?php foreach ( $winners as $w ) :
                                $display_name = $mask_name ? self::mask_player_name( $w['name'] ) : $w['name'];
                                $initial      = mb_strtoupper( mb_substr( $display_name, 0, 1, 'UTF-8' ), 'UTF-8' );
                            ?>
                                <li class="sw-winner-item">
                                    <div class="sw-avatar-circle"><?php echo esc_html( $initial ?: '★' ); ?></div>
                                    <div class="sw-winner-info">
                                        <div class="sw-winner-top">
                                            <span class="sw-winner-name"><?php echo esc_html( $display_name ); ?></span>
                                            <span class="sw-winner-time"><?php echo esc_html( $w['time_ago'] ); ?></span>
                                        </div>
                                        <div class="sw-winner-bottom">
                                            <span class="sw-prize-tag">🎁 <?php echo esc_html( $w['prize_title'] ); ?></span>
                                            <?php if ( empty( $wheel_id ) && ! empty( $w['wheel_title'] ) ) : ?>
                                                <span class="sw-wheel-tag">🎡 <?php echo esc_html( $w['wheel_title'] ); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <li class="sw-empty-item"><?php esc_html_e( 'Chưa có lượt quay nào được ghi nhận.', 'wp-spin-wheel' ); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <style>
        .sw-recent-winners-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            padding: 16px;
            margin: 16px 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif;
            border: 1px solid #e2e8f0;
        }
        .sw-recent-winners-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        .sw-recent-winners-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .sw-live-pulse {
            width: 10px;
            height: 10px;
            background: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            animation: sw-pulse 1.8s infinite;
        }
        @keyframes sw-pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }
        .sw-winners-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-height: 380px;
            overflow-y: auto;
        }
        .sw-winner-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
            transition: all 0.2s ease;
        }
        .sw-winner-item:hover {
            background: #f1f5f9;
            transform: translateX(3px);
        }
        .sw-avatar-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            flex-shrink: 0;
        }
        .sw-winner-info {
            flex: 1;
            min-width: 0;
        }
        .sw-winner-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }
        .sw-winner-name {
            font-weight: 600;
            font-size: 14px;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sw-winner-time {
            font-size: 11px;
            color: #94a3b8;
            flex-shrink: 0;
            margin-left: 8px;
        }
        .sw-winner-bottom {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .sw-prize-tag {
            font-size: 12px;
            font-weight: 600;
            color: #4f46e5;
            background: #eef2ff;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .sw-wheel-tag {
            font-size: 11px;
            color: #64748b;
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 4px;
        }
        /* Style Ticker */
        .sw-style-ticker {
            padding: 6px 12px;
            overflow: hidden;
            border-radius: 9999px;
            background: #0f172a;
            color: #fff;
        }
        .sw-ticker-wrap {
            display: flex;
            align-items: center;
            width: 100%;
            overflow: hidden;
        }
        .sw-ticker-badge {
            background: #f59e0b;
            color: #000;
            font-weight: 700;
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 9999px;
            white-space: nowrap;
            margin-right: 12px;
            flex-shrink: 0;
        }
        .sw-ticker-move {
            display: flex;
            gap: 24px;
            white-space: nowrap;
            animation: sw-ticker 25s linear infinite;
        }
        .sw-ticker-move:hover {
            animation-play-state: paused;
        }
        .sw-ticker-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }
        .sw-ticker-item .sw-winner-prize {
            color: #fbbf24;
            font-weight: 600;
        }
        .sw-ticker-item .sw-winner-time {
            color: #94a3b8;
            font-size: 11px;
        }
        @keyframes sw-ticker {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        /* Style Table */
        .sw-table-responsive {
            overflow-x: auto;
        }
        .sw-winners-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .sw-winners-table th, .sw-winners-table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
        }
        .sw-winners-table th {
            font-weight: 600;
            color: #64748b;
            background: #f8fafc;
        }
        .sw-prize-badge {
            display: inline-block;
            background: #e0e7ff;
            color: #3730a3;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 4px;
        }
        .sw-empty-text, .sw-empty-item {
            text-align: center;
            padding: 16px;
            color: #94a3b8;
            font-size: 13px;
        }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Mask tên người chơi để bảo vệ riêng tư (nếu cần)
     */
    public static function mask_player_name( $name ) {
        if ( empty( $name ) ) {
            return __( 'Khách ẩn danh', 'wp-spin-wheel' );
        }
        $len = mb_strlen( $name, 'UTF-8' );
        if ( $len <= 3 ) {
            return $name;
        }
        $start = mb_substr( $name, 0, 2, 'UTF-8' );
        $end   = mb_substr( $name, -1, 1, 'UTF-8' );
        return $start . '***' . $end;
    }

    /**
     * Render Shortcode hiển thị danh sách các vòng quay do User tạo (theo mẫu setting.html)
     * [spin_wheel_user_wheels user_id="current" limit="12" columns="4" title="Vòng quay của tôi" show_search="true" show_sort="true"]
     */
    public function render_user_wheels_shortcode( $atts, $content = '', $tag = '' ) {
        $default_user_id = ( 'spin_wheel_collection' === $tag ) ? 'all' : 'current';
        $default_title   = ( 'spin_wheel_collection' === $tag ) ? __( 'Bộ sưu tập vòng quay công khai', 'wp-spin-wheel' ) : __( 'Vòng quay của tôi', 'wp-spin-wheel' );

        $atts = shortcode_atts( array(
            'user_id'     => $default_user_id, // 'current', 'all', hoặc ID user cụ thể
            'limit'       => 12,
            'columns'     => 4,
            'title'       => $default_title,
            'show_search' => 'true',
            'show_sort'   => 'true',
            'orderby'     => 'date', // 'date', 'views', 'title'
            'order'       => 'DESC',
        ), $atts, $tag ?: 'spin_wheel_user_wheels' );

        $limit        = max( 1, min( 60, absint( $atts['limit'] ) ) );
        $columns      = in_array( (int) $atts['columns'], array( 1, 2, 3, 4, 6 ), true ) ? (int) $atts['columns'] : 4;
        $title        = sanitize_text_field( $atts['title'] );
        $show_search  = filter_var( $atts['show_search'], FILTER_VALIDATE_BOOLEAN );
        $show_sort    = filter_var( $atts['show_sort'], FILTER_VALIDATE_BOOLEAN );
        $orderby      = sanitize_key( $_GET['sw_order_by'] ?? $atts['orderby'] );
        $keyword      = sanitize_text_field( $_GET['sw_keyword'] ?? '' );
        $current_page = max( 1, absint( $_GET['sw_page'] ?? 1 ) );

        $query_author = null;
        if ( 'current' === $atts['user_id'] ) {
            if ( ! is_user_logged_in() ) {
                ob_start();
                ?>
                <div class="container-fluid py-4 text-center">
                    <div class="alert alert-info py-4 rounded-3 border">
                        <h5 class="fw-bold mb-2"><?php esc_html_e( 'Vui lòng đăng nhập', 'wp-spin-wheel' ); ?></h5>
                        <p class="mb-3 text-muted"><?php esc_html_e( 'Bạn cần đăng nhập tài khoản để xem và quản lý danh sách các vòng quay may mắn đã tạo.', 'wp-spin-wheel' ); ?></p>
                        <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="btn btn-primary btn-sm px-4 rounded-pill">
                            <?php esc_html_e( 'Đăng nhập ngay', 'wp-spin-wheel' ); ?>
                        </a>
                    </div>
                </div>
                <?php
                return ob_get_clean();
            }
            $query_author = get_current_user_id();
        } elseif ( 'all' !== $atts['user_id'] && is_numeric( $atts['user_id'] ) ) {
            $query_author = absint( $atts['user_id'] );
        }

        $query_args = array(
            'post_type'      => 'spin_wheel',
            'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
            'posts_per_page' => $limit,
            'paged'          => $current_page,
        );

        if ( null !== $query_author && $query_author > 0 ) {
            $query_args['author'] = $query_author;
        }

        if ( ! empty( $keyword ) ) {
            $query_args['s'] = $keyword;
        }

        if ( 'views' === $orderby ) {
            $query_args['meta_key'] = '_spin_wheel_views';
            $query_args['orderby']  = 'meta_value_num';
            $query_args['order']    = 'DESC';
        } elseif ( 'title' === $orderby ) {
            $query_args['orderby'] = 'title';
            $query_args['order']   = 'ASC';
        } else {
            $query_args['orderby'] = 'date';
            $query_args['order']   = 'DESC';
        }

        $query = new WP_Query( $query_args );
        $wheels = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id     = get_the_ID();
                $author_id   = get_the_author_meta( 'ID' );
                $author_name = get_the_author_meta( 'display_name' ) ?: get_the_author_meta( 'user_login' );

                $prizes = WP_Spin_Wheel_Prize::get_prizes( $post_id );
                if ( empty( $prizes ) ) {
                    $prizes = array(
                        array( 'title' => 'Tuấn', 'color' => '#D6392E' ),
                        array( 'title' => 'Thông', 'color' => '#3369E8' ),
                        array( 'title' => 'Sơn', 'color' => '#4F9A29' ),
                        array( 'title' => 'Dũng', 'color' => '#EEB331' ),
                    );
                }

                $settings = WP_Spin_Wheel_Helper::get_wheel_overrides( $post_id );
                $views    = (int) get_post_meta( $post_id, '_spin_wheel_views', true );
                if ( $views <= 0 ) {
                    $views = (int) get_post_meta( $post_id, '_spin_wheel_total_spins', true );
                }

                $border_color  = $settings['wheel']['border_color'] ?? '#ff4d00';
                $diamond_color = $settings['wheel']['diamond_color'] ?? '#f6fa00';
                $button_label  = $settings['button']['text'] ?? 'Quay';
                $button_img    = $settings['button']['background_image'] ?? '';
                $button_color  = $settings['button']['color'] ?? '#d6392e';

                $wheels[] = array(
                    'id'            => $post_id,
                    'title'         => wp_spin_wheel_fix_mangled_unicode( get_the_title() ) ?: sprintf( __( 'Vòng quay #%d', 'wp-spin-wheel' ), $post_id ),
                    'permalink'     => get_permalink( $post_id ),
                    'views'         => $views,
                    'author_id'     => $author_id,
                    'author_name'   => $author_name,
                    'created_date'  => get_the_date( 'd/m/Y H:i', $post_id ),
                    'time_ago'      => WP_Spin_Wheel_History::human_time_ago( get_the_date( 'Y-m-d H:i:s', $post_id ) ),
                    'prizes'        => $prizes,
                    'settings'      => $settings,
                    'border_color'  => $border_color,
                    'diamond_color' => $diamond_color,
                    'button_label'  => $button_label,
                    'button_img'    => $button_img,
                    'button_color'  => $button_color,
                );
            }
            wp_reset_postdata();
        }

        $total_pages = $query->max_num_pages;

        ob_start();
        $template = WP_SPIN_WHEEL_PATH . 'templates/wheel/user-wheels-list.php';
        if ( ! file_exists( $template ) ) {
            $template = WP_SPIN_WHEEL_PATH . 'templates/user-wheels-list.php';
        }
        if ( file_exists( $template ) ) {
            include $template;
        }
        return ob_get_clean();
    }

    /**
     * Render Shortcode hiển thị thanh thống kê hệ thống theo mẫu setting.html
     * [spin_wheel_stats]
     * [vqmm_stats]
     */
    public function render_stats_shortcode( $atts ) {
        // Đếm thực tế từ WordPress
        $user_count_data = count_users();
        $real_users      = (int) ( $user_count_data['total_users'] ?? 0 );

        $post_count_data = wp_count_posts( 'spin_wheel' );
        $real_public     = (int) ( $post_count_data->publish ?? 0 );
        $real_private    = (int) ( ( $post_count_data->private ?? 0 ) + ( $post_count_data->draft ?? 0 ) + ( $post_count_data->pending ?? 0 ) );
        $real_total      = $real_public + $real_private;

        $atts = shortcode_atts( array(
            'members'        => null,
            'public_links'   => null,
            'private_links'  => null,
            'total_links'    => null,
            'offset_members' => 0,
            'offset_public'  => 0,
            'offset_private' => 0,
            'offset_total'   => 0,
            'class'          => '',
        ), $atts, 'spin_wheel_stats' );

        $members_val = ( null !== $atts['members'] && '' !== $atts['members'] )
            ? absint( $atts['members'] )
            : ( $real_users + absint( $atts['offset_members'] ) );

        $public_val = ( null !== $atts['public_links'] && '' !== $atts['public_links'] )
            ? absint( $atts['public_links'] )
            : ( $real_public + absint( $atts['offset_public'] ) );

        $private_val = ( null !== $atts['private_links'] && '' !== $atts['private_links'] )
            ? absint( $atts['private_links'] )
            : ( $real_private + absint( $atts['offset_private'] ) );

        $total_val = ( null !== $atts['total_links'] && '' !== $atts['total_links'] )
            ? absint( $atts['total_links'] )
            : ( $real_total + absint( $atts['offset_total'] ) );

        $extra_class = sanitize_text_field( $atts['class'] );

        ob_start();
        ?>
        <div class="row g-0 text-light mb-3 <?php echo esc_attr( $extra_class ); ?>" id="vqmm-stats">
            <div class="col-6 col-md-3">
                <div class="bg-primary p-2">
                    <div class="mb-3 small">
                        <?php esc_html_e( 'Thành viên', 'wp-spin-wheel' ); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="fs-2 fw-bold"><?php echo esc_html( number_format_i18n( $members_val ) ); ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-danger p-2">
                    <div class="mb-3 small">
                        <?php esc_html_e( 'Link công khai', 'wp-spin-wheel' ); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="fs-2 fw-bold"><?php echo esc_html( number_format_i18n( $public_val ) ); ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-warning p-2 text-dark">
                    <div class="mb-3 small">
                        <?php esc_html_e( 'Link riêng tư', 'wp-spin-wheel' ); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="fs-2 fw-bold"><?php echo esc_html( number_format_i18n( $private_val ) ); ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-success p-2">
                    <div class="mb-3 small">
                        <?php esc_html_e( 'Tổng link', 'wp-spin-wheel' ); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="fs-2 fw-bold"><?php echo esc_html( number_format_i18n( $total_val ) ); ?></div>
                </div>
            </div>
        </div>
        <style>
        #vqmm-stats .bg-primary,
        #vqmm-stats .bg-danger,
        #vqmm-stats .bg-warning,
        #vqmm-stats .bg-success {
            min-height: 90px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        #vqmm-stats svg {
            vertical-align: middle;
            width: 16px;
            height: 16px;
        }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Shortcode Hộp quà may mắn (Mystery Box)
     * [lucky_box] / [mystery_box] / [spin_wheel_box id="123"]
     */
    public function render_box_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'id' => 0,
        ), $atts, 'lucky_box' );

        $box_id = absint( $atts['id'] );
        ob_start();
        $template = WP_SPIN_WHEEL_PATH . 'templates/box/wheel-box.php';
        if ( ! file_exists( $template ) ) {
            $template = WP_SPIN_WHEEL_PATH . 'templates/wheel-box.php';
        }
        if ( file_exists( $template ) ) {
            include $template;
        }
        return ob_get_clean();
    }

    /**
     * Render Shortcode Danh sách Hộp quà may mắn
     * [box_gallery] / [user_boxes] / [my_boxes]
     */
    public function render_user_boxes_shortcode( $atts, $content = '', $tag = '' ) {
        $default_user_id = ( 'box_gallery' === $tag ) ? 'all' : 'current';
        $default_title   = ( 'box_gallery' === $tag ) ? __( 'Bộ sưu tập hộp quà may mắn', 'wp-spin-wheel' ) : __( 'Hộp quà của tôi', 'wp-spin-wheel' );

        $atts = shortcode_atts( array(
            'user_id'     => $default_user_id,
            'limit'       => 12,
            'columns'     => 4,
            'title'       => $default_title,
            'show_search' => 'true',
            'show_sort'   => 'true',
            'orderby'     => 'date',
            'order'       => 'DESC',
        ), $atts, $tag ?: 'user_boxes' );

        $limit        = max( 1, min( 60, absint( $atts['limit'] ) ) );
        $columns      = in_array( (int) $atts['columns'], array( 1, 2, 3, 4, 6 ), true ) ? (int) $atts['columns'] : 4;
        $title        = sanitize_text_field( $atts['title'] );
        $show_search  = filter_var( $atts['show_search'], FILTER_VALIDATE_BOOLEAN );
        $show_sort    = filter_var( $atts['show_sort'], FILTER_VALIDATE_BOOLEAN );
        $orderby      = sanitize_key( $_GET['sw_order_by'] ?? $atts['orderby'] );
        $keyword      = sanitize_text_field( $_GET['sw_keyword'] ?? '' );
        $current_page = max( 1, absint( $_GET['sw_page'] ?? 1 ) );

        $query_author = null;
        if ( 'current' === $atts['user_id'] ) {
            if ( ! is_user_logged_in() ) {
                ob_start();
                ?>
                <div class="container-fluid py-4 text-center">
                    <div class="alert alert-info py-4 rounded-3 border">
                        <h5 class="fw-bold mb-2"><?php esc_html_e( 'Vui lòng đăng nhập', 'wp-spin-wheel' ); ?></h5>
                        <p class="mb-3 text-muted"><?php esc_html_e( 'Bạn cần đăng nhập tài khoản để xem và quản lý danh sách hộp quà may mắn đã tạo.', 'wp-spin-wheel' ); ?></p>
                        <a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="btn btn-primary btn-sm px-4 rounded-pill">
                            <?php esc_html_e( 'Đăng nhập ngay', 'wp-spin-wheel' ); ?>
                        </a>
                    </div>
                </div>
                <?php
                return ob_get_clean();
            }
            $query_author = get_current_user_id();
        } elseif ( 'all' !== $atts['user_id'] && is_numeric( $atts['user_id'] ) ) {
            $query_author = absint( $atts['user_id'] );
        }

        $query_args = array(
            'post_type'      => 'spin_box',
            'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
            'posts_per_page' => $limit,
            'paged'          => $current_page,
        );

        if ( null !== $query_author && $query_author > 0 ) {
            $query_args['author'] = $query_author;
        }

        if ( ! empty( $keyword ) ) {
            $query_args['s'] = $keyword;
        }

        if ( 'views' === $orderby ) {
            $query_args['meta_key'] = '_spin_box_views';
            $query_args['orderby']  = 'meta_value_num';
            $query_args['order']    = 'DESC';
        } elseif ( 'title' === $orderby ) {
            $query_args['orderby'] = 'title';
            $query_args['order']   = 'ASC';
        } else {
            $query_args['orderby'] = 'date';
            $query_args['order']   = 'DESC';
        }

        $query = new WP_Query( $query_args );
        $boxes = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id     = get_the_ID();
                $author_id   = get_the_author_meta( 'ID' );
                $author_name = get_the_author_meta( 'display_name' ) ?: get_the_author_meta( 'user_login' );

                $gifts    = WP_Spin_Wheel_Box::get_box_gifts( $post_id );
                $settings = WP_Spin_Wheel_Box::get_box_settings( $post_id );
                $views    = (int) get_post_meta( $post_id, '_spin_box_views', true );
                if ( $views <= 0 ) {
                    $views = (int) get_post_meta( $post_id, '_spin_box_total_opens', true );
                }

                $boxes[] = array(
                    'id'            => $post_id,
                    'title'         => wp_spin_wheel_fix_mangled_unicode( get_the_title() ) ?: sprintf( __( 'Hộp quà #%d', 'wp-spin-wheel' ), $post_id ),
                    'permalink'     => get_permalink( $post_id ),
                    'views'         => $views,
                    'author_id'     => $author_id,
                    'author_name'   => $author_name,
                    'created_date'  => get_the_date( 'd/m/Y H:i', $post_id ),
                    'time_ago'      => WP_Spin_Wheel_History::human_time_ago( get_the_date( 'Y-m-d H:i:s', $post_id ) ),
                    'prizes'        => $gifts,
                    'settings'      => $settings,
                    'template'      => $settings['template'] ?? 'tpl-jib',
                    'conlai'        => $settings['luotchoi'] ?? 3,
                );
            }
            wp_reset_postdata();
        }

        $total_pages = $query->max_num_pages;

        ob_start();
        $template = WP_SPIN_WHEEL_PATH . 'templates/box/user-boxs-list.php';
        if ( ! file_exists( $template ) ) {
            $template = WP_SPIN_WHEEL_PATH . 'templates/user-boxs-list.php';
        }
        if ( file_exists( $template ) ) {
            include $template;
        }
        return ob_get_clean();
    }
}

/**
 * WordPress Widget: Người trúng thưởng gần đây
 */
class WP_Spin_Wheel_Recent_Winners_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'sw_recent_winners_widget',
            __( 'Vòng Quay - Người Trúng Gần Đây', 'wp-spin-wheel' ),
            array( 'description' => __( 'Hiển thị danh sách những người chơi vừa trúng thưởng gần đây.', 'wp-spin-wheel' ) )
        );
    }

    public function widget( $args, $instance ) {
        echo $args['before_widget'];

        $title    = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Người trúng gần đây', 'wp-spin-wheel' );
        $wheel_id = ! empty( $instance['wheel_id'] ) ? absint( $instance['wheel_id'] ) : 0;
        $limit    = ! empty( $instance['limit'] ) ? absint( $instance['limit'] ) : 5;
        $style    = ! empty( $instance['style'] ) ? $instance['style'] : 'list';

        $shortcode = new WP_Spin_Wheel_Shortcode();
        echo $shortcode->render_recent_winners_shortcode( array(
            'title'    => $title,
            'wheel_id' => $wheel_id,
            'limit'    => $limit,
            'style'    => $style,
        ) );

        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $title    = ! empty( $instance['title'] ) ? $instance['title'] : __( '🎉 Người trúng thưởng gần đây', 'wp-spin-wheel' );
        $wheel_id = ! empty( $instance['wheel_id'] ) ? absint( $instance['wheel_id'] ) : 0;
        $limit    = ! empty( $instance['limit'] ) ? absint( $instance['limit'] ) : 5;
        $style    = ! empty( $instance['style'] ) ? $instance['style'] : 'list';

        $wheels = get_posts( array(
            'post_type'      => 'spin_wheel',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ) );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Tiêu đề:', 'wp-spin-wheel' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'wheel_id' ) ); ?>"><?php esc_html_e( 'Chọn Vòng quay:', 'wp-spin-wheel' ); ?></label>
            <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'wheel_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'wheel_id' ) ); ?>">
                <option value="0"><?php esc_html_e( '-- Tất cả vòng quay --', 'wp-spin-wheel' ); ?></option>
                <?php foreach ( $wheels as $w ) : ?>
                    <option value="<?php echo esc_attr( $w->ID ); ?>" <?php selected( $wheel_id, $w->ID ); ?>>
                        <?php echo esc_html( $w->post_title ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php esc_html_e( 'Số lượng hiển thị:', 'wp-spin-wheel' ); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="number" step="1" min="1" max="50" value="<?php echo esc_attr( $limit ); ?>" size="3">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>"><?php esc_html_e( 'Kiểu hiển thị:', 'wp-spin-wheel' ); ?></label>
            <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'style' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'style' ) ); ?>">
                <option value="list" <?php selected( $style, 'list' ); ?>><?php esc_html_e( 'Danh sách Card', 'wp-spin-wheel' ); ?></option>
                <option value="ticker" <?php selected( $style, 'ticker' ); ?>><?php esc_html_e( 'Ticker chạy ngang', 'wp-spin-wheel' ); ?></option>
                <option value="table" <?php selected( $style, 'table' ); ?>><?php esc_html_e( 'Bảng dữ liệu', 'wp-spin-wheel' ); ?></option>
            </select>
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance             = array();
        $instance['title']    = ! empty( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['wheel_id'] = ! empty( $new_instance['wheel_id'] ) ? absint( $new_instance['wheel_id'] ) : 0;
        $instance['limit']    = ! empty( $new_instance['limit'] ) ? absint( $new_instance['limit'] ) : 5;
        $instance['style']    = ! empty( $new_instance['style'] ) ? sanitize_text_field( $new_instance['style'] ) : 'list';
        return $instance;
    }
}

