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
            $template = WP_SPIN_WHEEL_PATH . 'templates/wheel-user.php';
            if ( ! file_exists( $template ) ) {
                $template = WP_SPIN_WHEEL_PATH . 'templates/wheel-default.php';
            }
            include $template;
            return ob_get_clean();
        }

        ob_start();
        include WP_SPIN_WHEEL_PATH . 'templates/wheel-default.php';
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

