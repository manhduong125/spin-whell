<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WP_Spin_Wheel_Admin {
    private $settings;

    public function __construct() {
        $this->settings = new WP_Spin_Wheel_Settings();

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_menu', array( $this, 'register_dashboard_page' ) );
        add_action( 'wp_ajax_spin_wheel_get_stats', array( $this, 'ajax_get_stats' ) );
    }

    public function enqueue_assets( $hook ) {
        $screen = get_current_screen();
        $screen_id = $screen ? $screen->id : '';
        $allowed_screens = array(
            'settings_page_wp-spin-wheel-settings',
            'post.php',
            'post-new.php',
            'edit-spin_wheel',
            'spin_wheel',
            'edit-spin_box',
            'spin_box',
        );

        if ( ! in_array( $screen_id, $allowed_screens, true ) && strpos( $hook, 'spin_wheel' ) === false ) {
            return;
        }

        wp_enqueue_style( 'wp-spin-wheel-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), '5.3.3' );
        wp_enqueue_script( 'wp-spin-wheel-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array(), '5.3.3', true );
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_media();
        wp_enqueue_script( 'wp-spin-wheel-admin', WP_SPIN_WHEEL_URL . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker' ), WP_SPIN_WHEEL_VERSION, true );
        wp_enqueue_style( 'wp-spin-wheel-admin', WP_SPIN_WHEEL_URL . 'assets/css/admin.css', array(), WP_SPIN_WHEEL_VERSION );

        wp_localize_script( 'wp-spin-wheel-admin', 'wp_spin_wheel_admin_params', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'spin_wheel_admin' ),
            'rest_url' => esc_url( rest_url( 'spin-wheel/v1' ) ),
            'media_title' => __( 'Chọn tệp phương tiện', 'wp-spin-wheel' ),
            'media_button' => __( 'Chọn', 'wp-spin-wheel' ),
        ) );

        wp_enqueue_script( 'wp-spin-wheel-admin-settings', WP_SPIN_WHEEL_URL . 'assets/js/admin-settings.js', array( 'jquery' ), WP_SPIN_WHEEL_VERSION, true );
        wp_localize_script( 'wp-spin-wheel-admin-settings', 'wp_spin_wheel_settings_params', array(
            'rest_url' => esc_url( rest_url( 'spin-wheel/v1' ) ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'text_saved' => __( 'Settings saved.', 'wp-spin-wheel' ),
        ) );
    }

    public function register_dashboard_page() {
        add_submenu_page(
            'edit.php?post_type=spin_wheel',
            __( 'Spin Wheel Dashboard', 'wp-spin-wheel' ),
            __( 'Dashboard', 'wp-spin-wheel' ),
            'manage_options',
            'wp-spin-wheel-dashboard',
            array( $this, 'render_dashboard_page' )
        );

        add_submenu_page(
            'edit.php?post_type=spin_wheel',
            __( 'Lịch sử quay thưởng', 'wp-spin-wheel' ),
            __( 'Lịch sử quay', 'wp-spin-wheel' ),
            'manage_options',
            'spin-wheel-history',
            array( $this, 'render_history_page' )
        );

        add_submenu_page(
            'edit.php?post_type=spin_wheel',
            __( 'Spin Wheel Settings', 'wp-spin-wheel' ),
            __( 'Settings', 'wp-spin-wheel' ),
            'manage_options',
            'wp-spin-wheel-settings',
            array( $this->settings, 'render_settings_page' )
        );
    }

    public function render_dashboard_page() {
        $history = new WP_Spin_Wheel_History();
        $stats   = $history->get_stats();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Spin Wheel Dashboard', 'wp-spin-wheel' ); ?></h1>
            <div class="wp-spin-wheel-dashboard">
                <div class="stat-item"><strong><?php echo esc_html( $stats['total_spins'] ); ?></strong><span><?php esc_html_e( 'Tổng lượt quay', 'wp-spin-wheel' ); ?></span></div>
                <div class="stat-item"><strong><?php echo esc_html( $stats['total_players'] ); ?></strong><span><?php esc_html_e( 'Tổng người chơi', 'wp-spin-wheel' ); ?></span></div>
                <div class="stat-item"><strong><?php echo esc_html( $stats['today_spins'] ); ?></strong><span><?php esc_html_e( 'Lượt quay hôm nay', 'wp-spin-wheel' ); ?></span></div>
                <div class="stat-item"><strong><?php echo esc_html( $stats['top_prize'] ); ?></strong><span><?php esc_html_e( 'Top phần thưởng', 'wp-spin-wheel' ); ?></span></div>
                <div class="stat-item"><strong><?php echo esc_html( $stats['top_wheel'] ); ?></strong><span><?php esc_html_e( 'Top vòng quay', 'wp-spin-wheel' ); ?></span></div>
            </div>
        </div>
        <?php
    }

    public function render_history_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Bạn không có quyền truy cập trang này.', 'wp-spin-wheel' ) );
        }

        $history_obj = new WP_Spin_Wheel_History();

        // Xử lý xuất file CSV nếu có yêu cầu
        if ( isset( $_GET['action'] ) && 'export_csv' === $_GET['action'] ) {
            check_admin_referer( 'spin_wheel_export_history' );
            $export_args = array(
                'wheel_id'  => ! empty( $_GET['wheel_id'] ) ? absint( $_GET['wheel_id'] ) : 0,
                'search'    => ! empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '',
                'from_date' => ! empty( $_GET['from_date'] ) ? sanitize_text_field( $_GET['from_date'] ) : '',
                'to_date'   => ! empty( $_GET['to_date'] ) ? sanitize_text_field( $_GET['to_date'] ) : '',
            );
            WP_Spin_Wheel_Export::export_history_csv( $export_args );
            exit;
        }

        // Xử lý xóa 1 mục
        if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] && ! empty( $_GET['id'] ) ) {
            $del_id = absint( $_GET['id'] );
            check_admin_referer( 'spin_wheel_delete_history_' . $del_id );
            $history_obj->delete_entry( $del_id );
            wp_safe_redirect( remove_query_arg( array( 'action', 'id', '_wpnonce' ) ) );
            exit;
        }

        // Xử lý xóa tất cả
        if ( isset( $_POST['action'] ) && 'clear_all' === $_POST['action'] ) {
            check_admin_referer( 'spin_wheel_clear_history' );
            $clear_wheel_id = ! empty( $_POST['wheel_id'] ) ? absint( $_POST['wheel_id'] ) : 0;
            $history_obj->clear_history( $clear_wheel_id );
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Đã xóa toàn bộ lịch sử thành công.', 'wp-spin-wheel' ) . '</p></div>';
        }

        // Xử lý xóa nhiều mục đã chọn (Bulk Delete)
        if ( isset( $_POST['action'] ) && 'bulk_delete' === $_POST['action'] && ! empty( $_POST['bulk_ids'] ) ) {
            check_admin_referer( 'spin_wheel_bulk_history' );
            $deleted_count = $history_obj->delete_entries( (array) $_POST['bulk_ids'] );
            echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( esc_html__( 'Đã xóa %d bản ghi lịch sử.', 'wp-spin-wheel' ), $deleted_count ) . '</p></div>';
        }

        // Bộ lọc & Phân trang
        $wheel_id  = ! empty( $_GET['wheel_id'] ) ? absint( $_GET['wheel_id'] ) : 0;
        $search    = ! empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
        $from_date = ! empty( $_GET['from_date'] ) ? sanitize_text_field( $_GET['from_date'] ) : '';
        $to_date   = ! empty( $_GET['to_date'] ) ? sanitize_text_field( $_GET['to_date'] ) : '';
        $paged     = ! empty( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
        $per_page  = 20;

        $history_result = $history_obj->get_history( array(
            'wheel_id'  => $wheel_id,
            'search'    => $search,
            'from_date' => $from_date,
            'to_date'   => $to_date,
            'page'      => $paged,
            'per_page'  => $per_page,
            'orderby'   => 'id',
            'order'     => 'DESC',
        ) );

        $items       = $history_result['items'];
        $total_items = $history_result['total'];
        $total_pages = $history_result['total_pages'];

        // Lấy danh sách tất cả vòng quay để đưa vào select
        $wheels = get_posts( array(
            'post_type'      => 'spin_wheel',
            'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'draft' ),
        ) );

        // Thống kê nhanh
        $stats = $history_obj->get_stats( $wheel_id );
        ?>
        <div class="wrap wp-spin-wheel-history-wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e( 'Lịch sử quay thưởng', 'wp-spin-wheel' ); ?></h1>

            <?php
            $export_url = add_query_arg( array(
                'action'    => 'export_csv',
                '_wpnonce'  => wp_create_nonce( 'spin_wheel_export_history' ),
                'wheel_id'  => $wheel_id,
                's'         => $search,
                'from_date' => $from_date,
                'to_date'   => $to_date,
            ) );
            ?>
            <a href="<?php echo esc_url( $export_url ); ?>" class="page-title-action">
                <span class="dashicons dashicons-media-spreadsheet" style="vertical-align: middle;"></span>
                <?php esc_html_e( 'Xuất file Excel / CSV', 'wp-spin-wheel' ); ?>
            </a>

            <!-- Thống kê nhanh -->
            <div class="wp-spin-wheel-dashboard" style="margin-bottom: 20px;">
                <div class="stat-item">
                    <strong><?php echo esc_html( number_format_i18n( $stats['total_spins'] ?? 0 ) ); ?></strong>
                    <span><?php esc_html_e( 'Tổng lượt quay', 'wp-spin-wheel' ); ?></span>
                </div>
                <div class="stat-item">
                    <strong><?php echo esc_html( number_format_i18n( $stats['today_spins'] ?? 0 ) ); ?></strong>
                    <span><?php esc_html_e( 'Lượt quay hôm nay', 'wp-spin-wheel' ); ?></span>
                </div>
                <div class="stat-item">
                    <strong><?php echo esc_html( number_format_i18n( $stats['total_players'] ?? ( $stats['views'] ?? 0 ) ) ); ?></strong>
                    <span><?php echo $wheel_id ? esc_html__( 'Lượt xem vòng quay', 'wp-spin-wheel' ) : esc_html__( 'Tổng người chơi', 'wp-spin-wheel' ); ?></span>
                </div>
            </div>

            <!-- Form Bộ lọc -->
            <form method="get" action="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>" style="margin-bottom: 15px; background: #fff; padding: 12px 16px; border: 1px solid #c3c4c7; border-radius: 4px;">
                <input type="hidden" name="post_type" value="spin_wheel" />
                <input type="hidden" name="page" value="spin-wheel-history" />

                <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                    <div>
                        <label for="filter-wheel-id" style="font-weight: 600; margin-right: 4px;"><?php esc_html_e( 'Vòng quay:', 'wp-spin-wheel' ); ?></label>
                        <select name="wheel_id" id="filter-wheel-id" style="max-width: 220px;">
                            <option value="0"><?php esc_html_e( '-- Tất cả vòng quay --', 'wp-spin-wheel' ); ?></option>
                            <?php foreach ( $wheels as $w ) : ?>
                                <option value="<?php echo esc_attr( $w->ID ); ?>" <?php selected( $wheel_id, $w->ID ); ?>>
                                    <?php echo esc_html( $w->post_title ); ?> (ID: <?php echo esc_html( $w->ID ); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="filter-from-date" style="font-weight: 600; margin-right: 4px;"><?php esc_html_e( 'Từ ngày:', 'wp-spin-wheel' ); ?></label>
                        <input type="date" name="from_date" id="filter-from-date" value="<?php echo esc_attr( $from_date ); ?>" />
                    </div>

                    <div>
                        <label for="filter-to-date" style="font-weight: 600; margin-right: 4px;"><?php esc_html_e( 'Đến ngày:', 'wp-spin-wheel' ); ?></label>
                        <input type="date" name="to_date" id="filter-to-date" value="<?php echo esc_attr( $to_date ); ?>" />
                    </div>

                    <div>
                        <input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Tìm theo tên, email, sđt, giải...', 'wp-spin-wheel' ); ?>" style="min-width: 200px;" />
                    </div>

                    <div>
                        <button type="submit" class="button button-primary"><?php esc_html_e( 'Lọc lịch sử', 'wp-spin-wheel' ); ?></button>
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=spin_wheel&page=spin-wheel-history' ) ); ?>" class="button"><?php esc_html_e( 'Đặt lại', 'wp-spin-wheel' ); ?></a>
                    </div>
                </div>
            </form>

            <!-- Form thao tác hàng loạt & bảng danh sách -->
            <form method="post" id="spin-history-table-form" onsubmit="return handleHistoryFormSubmit(this);">
                <?php wp_nonce_field( 'spin_wheel_bulk_history' ); ?>

                <div class="tablenav top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div class="alignleft actions bulkactions">
                        <select name="action" id="bulk-action-selector-top">
                            <option value="-1"><?php esc_html_e( 'Hành động hàng loạt', 'wp-spin-wheel' ); ?></option>
                            <option value="bulk_delete"><?php esc_html_e( 'Xóa các mục đã chọn', 'wp-spin-wheel' ); ?></option>
                        </select>
                        <input type="submit" class="button action" value="<?php esc_attr_e( 'Áp dụng', 'wp-spin-wheel' ); ?>" />
                    </div>

                    <div class="alignright actions">
                        <button type="button" class="button button-link-delete" onclick="confirmClearHistory();" style="color: #b32d2e; text-decoration: none;">
                            <span class="dashicons dashicons-trash" style="vertical-align: text-top; font-size: 16px;"></span>
                            <?php esc_html_e( 'Xóa tất cả lịch sử', 'wp-spin-wheel' ); ?>
                        </button>
                    </div>
                </div>

                <table class="wp-list-table widefat fixed striped table-view-list">
                    <thead>
                        <tr>
                            <td class="manage-column column-cb check-column" style="width: 32px;"><input type="checkbox" id="cb-select-all-1" /></td>
                            <th scope="col" style="width: 60px;"><?php esc_html_e( 'ID', 'wp-spin-wheel' ); ?></th>
                            <th scope="col" style="width: 180px;"><?php esc_html_e( 'Vòng quay', 'wp-spin-wheel' ); ?></th>
                            <th scope="col" style="width: 160px;"><?php esc_html_e( 'Người chơi', 'wp-spin-wheel' ); ?></th>
                            <th scope="col" style="width: 180px;"><?php esc_html_e( 'Email / SĐT', 'wp-spin-wheel' ); ?></th>
                            <th scope="col" style="width: 180px;"><?php esc_html_e( 'Giải thưởng trúng', 'wp-spin-wheel' ); ?></th>
                            <th scope="col" style="width: 120px;"><?php esc_html_e( 'Mã trúng', 'wp-spin-wheel' ); ?></th>
                            <th scope="col" style="width: 100px;"><?php esc_html_e( 'IP', 'wp-spin-wheel' ); ?></th>
                            <th scope="col" style="width: 150px;"><?php esc_html_e( 'Thời gian quay', 'wp-spin-wheel' ); ?></th>
                            <th scope="col" style="width: 80px; text-align: center;"><?php esc_html_e( 'Thao tác', 'wp-spin-wheel' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $items ) ) : ?>
                            <?php foreach ( $items as $item ) : ?>
                                <tr>
                                    <th scope="row" class="check-column">
                                        <input type="checkbox" name="bulk_ids[]" value="<?php echo esc_attr( $item['id'] ); ?>" />
                                    </th>
                                    <td><strong>#<?php echo esc_html( $item['id'] ); ?></strong></td>
                                    <td>
                                        <a href="<?php echo esc_url( get_edit_post_link( $item['wheel_id'] ) ); ?>" style="font-weight: 600;">
                                            <?php echo esc_html( $item['wheel_title'] ); ?>
                                        </a>
                                        <div class="row-actions">
                                            <span class="view"><a href="<?php echo esc_url( get_permalink( $item['wheel_id'] ) ); ?>" target="_blank"><?php esc_html_e( 'Xem trang', 'wp-spin-wheel' ); ?></a></span>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo esc_html( $item['name'] ); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ( ! empty( $item['email'] ) ) : ?>
                                            <div><a href="mailto:<?php echo esc_attr( $item['email'] ); ?>"><?php echo esc_html( $item['email'] ); ?></a></div>
                                        <?php endif; ?>
                                        <?php if ( ! empty( $item['phone'] ) ) : ?>
                                            <div style="color: #64748b; font-size: 12px;"><?php echo esc_html( $item['phone'] ); ?></div>
                                        <?php endif; ?>
                                        <?php if ( empty( $item['email'] ) && empty( $item['phone'] ) ) : ?>
                                            <span style="color: #94a3b8;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge" style="display: inline-block; padding: 4px 8px; border-radius: 4px; background: #e0e7ff; color: #3730a3; font-weight: 600;">
                                            <?php echo esc_html( $item['prize_title'] ); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ( ! empty( $item['reward_code'] ) ) : ?>
                                            <code><?php echo esc_html( $item['reward_code'] ); ?></code>
                                        <?php else : ?>
                                            <span style="color: #94a3b8;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span style="font-size: 12px; color: #64748b;"><?php echo esc_html( $item['ip'] ?: '—' ); ?></span></td>
                                    <td>
                                        <div><?php echo esc_html( $item['created_at_formatted'] ); ?></div>
                                        <div style="font-size: 11px; color: #94a3b8;"><?php echo esc_html( $item['time_ago'] ); ?></div>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php
                                        $del_url = wp_nonce_url(
                                            add_query_arg( array(
                                                'action' => 'delete',
                                                'id'     => $item['id'],
                                            ) ),
                                            'spin_wheel_delete_history_' . $item['id']
                                        );
                                        ?>
                                        <a href="<?php echo esc_url( $del_url ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Bạn có chắc chắn muốn xóa lượt quay này không?', 'wp-spin-wheel' ); ?>');" title="<?php esc_attr_e( 'Xóa', 'wp-spin-wheel' ); ?>">
                                            <span class="dashicons dashicons-trash" style="font-size: 15px; width: 15px; height: 15px; vertical-align: middle;"></span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 24px; color: #64748b;">
                                    <?php esc_html_e( 'Chưa có lượt quay nào được ghi nhận.', 'wp-spin-wheel' ); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Phân trang -->
                <?php if ( $total_pages > 1 ) : ?>
                    <div class="tablenav bottom" style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                        <div class="alignleft">
                            <span class="displaying-num"><?php echo sprintf( esc_html__( 'Tổng cộng %s lượt quay', 'wp-spin-wheel' ), number_format_i18n( $total_items ) ); ?></span>
                        </div>
                        <div class="tablenav-pages">
                            <?php
                            echo paginate_links( array(
                                'base'      => add_query_arg( 'paged', '%#%' ),
                                'format'    => '',
                                'prev_text' => '&laquo; ' . __( 'Trang trước', 'wp-spin-wheel' ),
                                'next_text' => __( 'Trang sau', 'wp-spin-wheel' ) . ' &raquo;',
                                'total'     => $total_pages,
                                'current'   => $paged,
                            ) );
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </form>

            <!-- Form ẩn để Xóa toàn bộ lịch sử -->
            <form method="post" id="form-clear-all-history" style="display: none;">
                <?php wp_nonce_field( 'spin_wheel_clear_history' ); ?>
                <input type="hidden" name="action" value="clear_all" />
                <input type="hidden" name="wheel_id" value="<?php echo esc_attr( $wheel_id ); ?>" />
            </form>

            <script>
            function confirmClearHistory() {
                var msg = "<?php echo $wheel_id ? esc_js( __( 'Bạn có chắc chắn muốn xóa toàn bộ lịch sử của vòng quay này?', 'wp-spin-wheel' ) ) : esc_js( __( 'CẢNH BÁO: Hành động này sẽ xóa vĩnh viễn toàn bộ lịch sử quay thưởng trên toàn hệ thống. Bạn có chắc không?', 'wp-spin-wheel' ) ); ?>";
                if (confirm(msg)) {
                    document.getElementById('form-clear-all-history').submit();
                }
            }

            function handleHistoryFormSubmit(form) {
                var action = document.getElementById('bulk-action-selector-top').value;
                if (action === 'bulk_delete') {
                    var checked = form.querySelectorAll('input[name="bulk_ids[]"]:checked');
                    if (checked.length === 0) {
                        alert("<?php echo esc_js( __( 'Vui lòng chọn ít nhất 1 mục để xóa.', 'wp-spin-wheel' ) ); ?>");
                        return false;
                    }
                    return confirm("<?php echo esc_js( __( 'Bạn có chắc chắn muốn xóa các mục đã chọn?', 'wp-spin-wheel' ) ); ?>");
                }
                return true;
            }

            // Select all checkboxes
            document.addEventListener('DOMContentLoaded', function() {
                var selectAll = document.getElementById('cb-select-all-1');
                if (selectAll) {
                    selectAll.addEventListener('change', function() {
                        var cbs = document.querySelectorAll('input[name="bulk_ids[]"]');
                        for (var i = 0; i < cbs.length; i++) {
                            cbs[i].checked = selectAll.checked;
                        }
                    });
                }
            });
            </script>
        </div>
        <?php
    }

    public function ajax_get_stats() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error();
        }
        $history = new WP_Spin_Wheel_History();
        wp_send_json_success( $history->get_stats() );
    }
}
