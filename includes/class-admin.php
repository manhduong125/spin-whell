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
        add_action( 'wp_ajax_spin_wheel_import_box_demo', array( $this, 'ajax_import_box_demo' ) );
        add_action( 'wp_ajax_spin_wheel_import_wheel_demo', array( $this, 'ajax_import_wheel_demo' ) );
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

        if (
            ! in_array( $screen_id, $allowed_screens, true )
            && strpos( $hook, 'spin_wheel' ) === false
            && strpos( $hook, 'spin_box' ) === false
        ) {
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
            __( 'Lịch sử quay thưởng', 'wp-spin-wheel' ),
            __( 'Lịch sử quay', 'wp-spin-wheel' ),
            'manage_options',
            'spin-wheel-history',
            array( $this, 'render_history_page' )
        );

        add_submenu_page(
            'edit.php?post_type=spin_box',
            __( 'Lịch sử nhận thưởng Hộp quà', 'wp-spin-wheel' ),
            __( 'Lịch sử nhận quà', 'wp-spin-wheel' ),
            'manage_options',
            'spin-box-history',
            array( $this, 'render_box_history_page' )
        );
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
                'post_type' => 'spin_wheel',
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
            'post_type' => 'spin_wheel',
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

    public function render_box_history_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Bạn không có quyền truy cập trang này.', 'wp-spin-wheel' ) );
        }

        $history_obj = new WP_Spin_Wheel_History();

        // Xử lý xuất file CSV nếu có yêu cầu
        if ( isset( $_GET['action'] ) && 'export_csv' === $_GET['action'] ) {
            check_admin_referer( 'spin_box_export_history' );
            $export_args = array(
                'box_id'    => ! empty( $_GET['box_id'] ) ? absint( $_GET['box_id'] ) : 0,
                'post_type' => 'spin_box',
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
            check_admin_referer( 'spin_box_delete_history_' . $del_id );
            $history_obj->delete_entry( $del_id );
            wp_safe_redirect( remove_query_arg( array( 'action', 'id', '_wpnonce' ) ) );
            exit;
        }

        // Xử lý xóa tất cả lịch sử hộp quà
        if ( isset( $_POST['action'] ) && 'clear_all_box' === $_POST['action'] ) {
            check_admin_referer( 'spin_box_clear_history' );
            $clear_box_id = ! empty( $_POST['box_id'] ) ? absint( $_POST['box_id'] ) : 0;
            $history_obj->clear_box_history( $clear_box_id );
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Đã xóa toàn bộ lịch sử nhận thưởng hộp quà thành công.', 'wp-spin-wheel' ) . '</p></div>';
        }

        // Xử lý xóa nhiều mục đã chọn (Bulk Delete)
        if ( isset( $_POST['action'] ) && 'bulk_delete' === $_POST['action'] && ! empty( $_POST['bulk_ids'] ) ) {
            check_admin_referer( 'spin_box_bulk_history' );
            $deleted_count = $history_obj->delete_entries( (array) $_POST['bulk_ids'] );
            echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( esc_html__( 'Đã xóa %d bản ghi lịch sử nhận quà.', 'wp-spin-wheel' ), $deleted_count ) . '</p></div>';
        }

        // Bộ lọc & Phân trang
        $box_id    = ! empty( $_GET['box_id'] ) ? absint( $_GET['box_id'] ) : 0;
        $search    = ! empty( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
        $from_date = ! empty( $_GET['from_date'] ) ? sanitize_text_field( $_GET['from_date'] ) : '';
        $to_date   = ! empty( $_GET['to_date'] ) ? sanitize_text_field( $_GET['to_date'] ) : '';
        $paged     = ! empty( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
        $per_page  = 20;

        $history_result = $history_obj->get_history( array(
            'box_id'    => $box_id,
            'post_type' => 'spin_box',
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

        // Lấy danh sách tất cả Hộp quà
        $boxes = get_posts( array(
            'post_type'      => 'spin_box',
            'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'draft' ),
        ) );

        // Thống kê nhanh Hộp quà
        $stats = $history_obj->get_box_stats( $box_id );
        ?>
        <div class="wrap wp-spin-wheel-history-wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e( 'Lịch sử nhận thưởng Hộp quà may mắn', 'wp-spin-wheel' ); ?></h1>

            <?php
            $export_url = add_query_arg( array(
                'action'    => 'export_csv',
                '_wpnonce'  => wp_create_nonce( 'spin_box_export_history' ),
                'box_id'    => $box_id,
                's'         => $search,
                'from_date' => $from_date,
                'to_date'   => $to_date,
            ) );
            ?>
            <a href="<?php echo esc_url( $export_url ); ?>" class="page-title-action">
                <span class="dashicons dashicons-media-spreadsheet" style="vertical-align: middle;"></span>
                <?php esc_html_e( 'Xuất file Excel / CSV', 'wp-spin-wheel' ); ?>
            </a>

            <!-- Thống kê nhanh Hộp quà -->
            <div class="wp-spin-wheel-dashboard" style="margin-bottom: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
                <div class="stat-item" style="background: #fff; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; border-left: 4px solid #ef4444;">
                    <strong style="font-size: 24px; color: #ef4444; display: block;"><?php echo esc_html( number_format_i18n( $stats['total_claims'] ?? 0 ) ); ?></strong>
                    <span style="color: #64748b; font-size: 13px;"><?php esc_html_e( 'Tổng lượt nhận quà', 'wp-spin-wheel' ); ?></span>
                </div>
                <div class="stat-item" style="background: #fff; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; border-left: 4px solid #f59e0b;">
                    <strong style="font-size: 24px; color: #f59e0b; display: block;"><?php echo esc_html( number_format_i18n( $stats['today_claims'] ?? 0 ) ); ?></strong>
                    <span style="color: #64748b; font-size: 13px;"><?php esc_html_e( 'Lượt nhận hôm nay', 'wp-spin-wheel' ); ?></span>
                </div>
                <div class="stat-item" style="background: #fff; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; border-left: 4px solid #10b981;">
                    <strong style="font-size: 24px; color: #10b981; display: block;"><?php echo esc_html( number_format_i18n( $stats['total_players'] ?? 0 ) ); ?></strong>
                    <span style="color: #64748b; font-size: 13px;"><?php esc_html_e( 'Tổng khách nhận quà', 'wp-spin-wheel' ); ?></span>
                </div>
                <div class="stat-item" style="background: #fff; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0; border-left: 4px solid #6366f1;">
                    <strong style="font-size: 20px; color: #6366f1; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo esc_html( $stats['top_gift'] ?? 'N/A' ); ?></strong>
                    <span style="color: #64748b; font-size: 13px;"><?php esc_html_e( 'Quà nhận nhiều nhất', 'wp-spin-wheel' ); ?></span>
                </div>
            </div>

            <!-- Form Bộ lọc -->
            <form method="get" action="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>" style="margin-bottom: 15px; background: #fff; padding: 12px 16px; border: 1px solid #c3c4c7; border-radius: 6px;">
                <input type="hidden" name="post_type" value="spin_box" />
                <input type="hidden" name="page" value="spin-box-history" />

                <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                    <div>
                        <label for="filter-box-id" style="font-weight: 600; margin-right: 4px;"><?php esc_html_e( 'Hộp quà:', 'wp-spin-wheel' ); ?></label>
                        <select name="box_id" id="filter-box-id" style="max-width: 220px;">
                            <option value="0"><?php esc_html_e( '-- Tất cả hộp quà --', 'wp-spin-wheel' ); ?></option>
                            <?php foreach ( $boxes as $b ) : ?>
                                <option value="<?php echo esc_attr( $b->ID ); ?>" <?php selected( $box_id, $b->ID ); ?>>
                                    <?php echo esc_html( $b->post_title ); ?> (ID: <?php echo esc_html( $b->ID ); ?>)
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
                        <input type="text" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Tìm theo tên, email, sđt, quà, mã...', 'wp-spin-wheel' ); ?>" style="min-width: 220px;" />
                    </div>

                    <div>
                        <button type="submit" class="button button-primary"><?php esc_html_e( 'Lọc danh sách', 'wp-spin-wheel' ); ?></button>
                        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=spin_box&page=spin-box-history' ) ); ?>" class="button"><?php esc_html_e( 'Đặt lại', 'wp-spin-wheel' ); ?></a>
                    </div>
                </div>
            </form>

            <!-- Form thao tác hàng loạt & bảng danh sách -->
            <form method="post" id="spin-box-history-table-form" onsubmit="return handleBoxHistoryFormSubmit(this);">
                <?php wp_nonce_field( 'spin_box_bulk_history' ); ?>

                <div class="tablenav top" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div class="alignleft actions bulkactions">
                        <select name="action" id="bulk-box-action-selector-top">
                            <option value="-1"><?php esc_html_e( 'Hành động hàng loạt', 'wp-spin-wheel' ); ?></option>
                            <option value="bulk_delete"><?php esc_html_e( 'Xóa các mục đã chọn', 'wp-spin-wheel' ); ?></option>
                        </select>
                        <input type="submit" class="button action" value="<?php esc_attr_e( 'Áp dụng', 'wp-spin-wheel' ); ?>" />
                    </div>

                    <div class="alignright actions">
                        <button type="button" class="button button-link-delete" onclick="confirmClearBoxHistory();" style="color: #b32d2e; text-decoration: none;">
                            <span class="dashicons dashicons-trash" style="vertical-align: text-top; font-size: 16px;"></span>
                            <?php esc_html_e( 'Xóa tất cả lịch sử hộp quà', 'wp-spin-wheel' ); ?>
                        </button>
                    </div>
                </div>

                <table class="wp-list-table widefat fixed striped table-view-list">
                    <thead>
                        <tr>
                            <td class="manage-column column-cb check-column" style="width: 32px;"><input type="checkbox" id="cb-select-all-box" /></td>
                            <th scope="col" style="width: 60px;"><?php esc_html_e( 'ID', 'wp-spin-wheel' ); ?></th>
                            <th scope="col" style="width: 180px;"><?php esc_html_e( 'Hộp quà', 'wp-spin-wheel' ); ?></th>
                            <th scope="col" style="width: 160px;"><?php esc_html_e( 'Khách hàng', 'wp-spin-wheel' ); ?></th>
                            <th scope="col" style="width: 180px;"><?php esc_html_e( 'Email / SĐT', 'wp-spin-wheel' ); ?></th>
                            <th scope="col" style="width: 180px;"><?php esc_html_e( 'Phần quà đã nhận', 'wp-spin-wheel' ); ?></th>
                            <th scope="col" style="width: 130px;"><?php esc_html_e( 'Mã nhận thưởng', 'wp-spin-wheel' ); ?></th>
                            <th scope="col" style="width: 100px;"><?php esc_html_e( 'IP', 'wp-spin-wheel' ); ?></th>
                            <th scope="col" style="width: 150px;"><?php esc_html_e( 'Thời gian nhận', 'wp-spin-wheel' ); ?></th>
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
                                            <span class="view"><a href="<?php echo esc_url( get_permalink( $item['wheel_id'] ) ); ?>" target="_blank"><?php esc_html_e( 'Xem hộp quà', 'wp-spin-wheel' ); ?></a></span>
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
                                        <span class="badge" style="display: inline-block; padding: 4px 10px; border-radius: 6px; background: #fee2e2; color: #dc2626; font-weight: 700; font-size: 13px;">
                                            🎁 <?php echo esc_html( $item['prize_title'] ); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ( ! empty( $item['reward_code'] ) ) : ?>
                                            <code style="background: #f1f5f9; color: #0284c7; padding: 3px 6px; border-radius: 4px; font-weight: bold;"><?php echo esc_html( $item['reward_code'] ); ?></code>
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
                                            'spin_box_delete_history_' . $item['id']
                                        );
                                        ?>
                                        <a href="<?php echo esc_url( $del_url ); ?>" class="button button-small button-link-delete" onclick="return confirm('<?php esc_attr_e( 'Bạn có chắc chắn muốn xóa bản ghi nhận quà này không?', 'wp-spin-wheel' ); ?>');" title="<?php esc_attr_e( 'Xóa', 'wp-spin-wheel' ); ?>">
                                            <span class="dashicons dashicons-trash" style="font-size: 15px; width: 15px; height: 15px; vertical-align: middle;"></span>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 24px; color: #64748b;">
                                    <?php esc_html_e( 'Chưa có khách hàng nào nhận thưởng từ hộp quà.', 'wp-spin-wheel' ); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Phân trang -->
                <?php if ( $total_pages > 1 ) : ?>
                    <div class="tablenav bottom" style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                        <div class="alignleft">
                            <span class="displaying-num"><?php echo sprintf( esc_html__( 'Tổng cộng %s lượt nhận quà', 'wp-spin-wheel' ), number_format_i18n( $total_items ) ); ?></span>
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

            <!-- Form ẩn để Xóa toàn bộ lịch sử hộp quà -->
            <form method="post" id="form-clear-all-box-history" style="display: none;">
                <?php wp_nonce_field( 'spin_box_clear_history' ); ?>
                <input type="hidden" name="action" value="clear_all_box" />
                <input type="hidden" name="box_id" value="<?php echo esc_attr( $box_id ); ?>" />
            </form>

            <script>
            function confirmClearBoxHistory() {
                var msg = "<?php echo $box_id ? esc_js( __( 'Bạn có chắc chắn muốn xóa toàn bộ lịch sử nhận quà của hộp quà này?', 'wp-spin-wheel' ) ) : esc_js( __( 'CẢNH BÁO: Hành động này sẽ xóa vĩnh viễn toàn bộ lịch sử nhận quà của tất cả Hộp quà. Bạn có chắc không?', 'wp-spin-wheel' ) ); ?>";
                if (confirm(msg)) {
                    document.getElementById('form-clear-all-box-history').submit();
                }
            }

            function handleBoxHistoryFormSubmit(form) {
                var action = document.getElementById('bulk-box-action-selector-top').value;
                if (action === 'bulk_delete') {
                    var checked = form.querySelectorAll('input[name="bulk_ids[]"]:checked');
                    if (checked.length === 0) {
                        alert("<?php echo esc_js( __( 'Vui lòng chọn ít nhất 1 mục để xóa.', 'wp-spin-wheel' ) ); ?>");
                        return false;
                    }
                    return confirm("<?php echo esc_js( __( 'Bạn có chắc chắn muốn xóa các mục nhận quà đã chọn?', 'wp-spin-wheel' ) ); ?>");
                }
                return true;
            }

            // Select all checkboxes for Box
            document.addEventListener('DOMContentLoaded', function() {
                var selectAll = document.getElementById('cb-select-all-box');
                if (selectAll) {
                    selectAll.addEventListener('change', function() {
                        var cbs = document.querySelectorAll('#spin-box-history-table-form input[name="bulk_ids[]"]');
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

    /**
     * Import Dữ liệu Demo Vòng quay — đồng bộ TOÀN BỘ theme từ assets/data/themes.json
     * Mỗi theme trong thư viện sẽ tạo 1 vòng quay với màu viền/kim, ảnh nền, âm thanh
     * và giải thưởng lấy màu từ palette của theme đó.
     */
    public function ajax_import_wheel_demo() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Bạn không có quyền thực hiện thao tác này.', 'wp-spin-wheel' ) ) );
        }
        check_ajax_referer( 'spin_wheel_admin', 'nonce' );

        $file = WP_SPIN_WHEEL_PATH . 'assets/data/themes.json';
        if ( ! file_exists( $file ) ) {
            wp_send_json_error( array( 'message' => __( 'Không tìm thấy file thư viện themes.json.', 'wp-spin-wheel' ) ) );
        }

        $raw  = file_get_contents( $file );
        $data = json_decode( $raw, true );
        if ( empty( $data['categories'] ) || ! is_array( $data['categories'] ) ) {
            wp_send_json_error( array( 'message' => __( 'File themes.json không hợp lệ.', 'wp-spin-wheel' ) ) );
        }

        $user_id   = get_current_user_id();
        $imported  = 0;
        $duplicate = 0;

        foreach ( $data['categories'] as $category ) {
            $cat_name = isset( $category['name'] ) ? $category['name'] : '';
            if ( empty( $category['items'] ) || ! is_array( $category['items'] ) ) {
                continue;
            }

            foreach ( $category['items'] as $theme ) {
                $theme_title = isset( $theme['title'] ) ? trim( $theme['title'] ) : '';
                if ( empty( $theme_title ) ) {
                    continue;
                }

                $title = sprintf( __( 'Vòng quay %s', 'wp-spin-wheel' ), $theme_title );

                // Tránh tạo trùng tên nếu đã import trước đó
                $exists = get_posts( array(
                    'post_type'      => 'spin_wheel',
                    'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
                    'author'         => $user_id,
                    'title'          => $title,
                    'posts_per_page' => 1,
                    'fields'         => 'ids',
                ) );

                if ( ! empty( $exists ) ) {
                    $duplicate++;
                    continue;
                }

                // Map theme → settings của plugin
                $border  = isset( $theme['border'] ) && is_array( $theme['border'] ) ? $theme['border'] : array();
                $colors  = isset( $theme['colors'] ) && is_array( $theme['colors'] ) ? $theme['colors'] : array();

                $settings = array(
                    'duration'                => '6',
                    'show_confetti'           => 1,
                    'auto_remove'             => 0,
                    'show_popup'              => 1,
                    'popup_label'             => __( 'Bạn đã quay vào ô', 'wp-spin-wheel' ),
                    'show_remove_button'      => 1,
                    'start_sound'             => isset( $theme['start_sound'] ) ? sanitize_text_field( $theme['start_sound'] ) : '',
                    'end_sound'               => isset( $theme['end_sound'] ) ? sanitize_text_field( $theme['end_sound'] ) : '',
                    'wheel_background_image'  => isset( $theme['bg_img'] ) ? $theme['bg_img'] : '',
                    'wheel_border_color'      => ! empty( $border[0] ) ? $border[0] : '#ff4d00',
                    'wheel_diamond_color'     => ! empty( $border[1] ) ? $border[1] : '#f6fa00',
                    'wheel_button_text'       => isset( $theme['spin_label'] ) ? $theme['spin_label'] : 'Quay',
                    'wheel_button_color'      => ! empty( $colors[0] ) ? $colors[0] : '#d6392e',
                    'wheel_button_text_color' => '#ffffff',
                );

                // Tạo 6 giải thưởng lấy màu từ palette colors của theme
                $prize_titles = array( '10%', '50k', 'Bút Montblanc', 'Chúc bạn may mắn', '200k', 'Ví da 500k' );
                $prize_weights = array( 15, 12, 5, 30, 8, 3 );
                $prizes = array();
                for ( $i = 0; $i < 6; $i++ ) {
                    $prizes[] = array(
                        'title' => $prize_titles[ $i ],
                        'color' => ! empty( $colors ) ? $colors[ $i % count( $colors ) ] : '#d6392e',
                        'weight' => $prize_weights[ $i ],
                        'stock' => 9999,
                    );
                }

                $wheel_id = WP_Spin_Wheel_Wheel::create_user_wheel( $user_id, $title, $settings, $prizes );
                if ( $wheel_id ) {
                    update_post_meta( $wheel_id, '_spin_wheel_views', wp_rand( 10, 120 ) );
                    $imported++;
                }
            }
        }

        wp_send_json_success( array(
            'imported'  => $imported,
            'duplicate' => $duplicate,
        ) );
    }

    /**
     * Import Dữ liệu Demo Hộp quà từ file assets/data/box-demo.json
     */
    public function ajax_import_box_demo() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Bạn không có quyền thực hiện thao tác này.', 'wp-spin-wheel' ) ) );
        }
        check_ajax_referer( 'spin_wheel_admin', 'nonce' );

        $file = WP_SPIN_WHEEL_PATH . 'assets/data/box-demo.json';
        if ( ! file_exists( $file ) ) {
            wp_send_json_error( array( 'message' => __( 'Không tìm thấy file dữ liệu demo Hộp quà.', 'wp-spin-wheel' ) ) );
        }

        $raw  = file_get_contents( $file );
        $data = json_decode( $raw, true );
        if ( ! is_array( $data ) || empty( $data ) ) {
            wp_send_json_error( array( 'message' => __( 'File dữ liệu demo Hộp quà không hợp lệ.', 'wp-spin-wheel' ) ) );
        }

        $user_id   = get_current_user_id();
        $imported  = 0;
        $duplicate = 0;

        foreach ( $data as $box ) {
            $title = isset( $box['title'] ) ? sanitize_text_field( $box['title'] ) : '';
            if ( empty( $title ) ) {
                continue;
            }

            // Tránh tạo trùng tên nếu đã import trước đó
            $exists = get_posts( array(
                'post_type'      => 'spin_box',
                'post_status'    => array( 'publish', 'draft', 'private', 'pending' ),
                'author'         => $user_id,
                'title'          => $title,
                'posts_per_page' => 1,
                'fields'         => 'ids',
            ) );

            if ( ! empty( $exists ) ) {
                $duplicate++;
                continue;
            }

            $settings = isset( $box['settings'] ) && is_array( $box['settings'] ) ? $box['settings'] : array();
            $gifts    = isset( $box['gifts'] ) && is_array( $box['gifts'] ) ? $box['gifts'] : array();

            $box_id = WP_Spin_Wheel_Box::create_user_box( $user_id, $title, $settings, $gifts );
            if ( $box_id ) {
                update_post_meta( $box_id, '_spin_box_views', wp_rand( 10, 120 ) );
                $imported++;
            }
        }

        wp_send_json_success( array(
            'imported'  => $imported,
            'duplicate' => $duplicate,
        ) );
    }
}
