<?php
if (! defined('ABSPATH')) {
    exit;
}

$history_obj = new WP_Spin_Wheel_History();
$current_wid = ! empty($wheel_id) ? absint($wheel_id) : (! empty($post_id) ? absint($post_id) : get_the_ID());

// Tăng lượt xem vòng quay
if ($current_wid > 0 && ! is_admin()) {
    $history_obj->increment_views($current_wid);
}

$wheel_stats = $history_obj->get_wheel_stats($current_wid);
$history_res = $history_obj->get_history(array(
    'wheel_id' => $current_wid,
    'per_page' => 20,
));
$recent_history = $history_res['items'] ?? array();

$current_user   = wp_get_current_user();
$player_display = is_user_logged_in() ? ($current_user->display_name ?: $current_user->user_login) : __('Khách ẩn danh', 'wp-spin-wheel');
$player_email   = is_user_logged_in() ? $current_user->user_email : '';
?>

<!-- Dropdown Menu góc trên bên trái -->

<div class="position-absolute top-0 start-0">
    <div class="dropdown me-2" style="display: inline-block;">
        <button class="btn dropdown-togglex" style="color:#000000" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
        </button>
        <ul class="dropdown-menu shadow-lg" aria-labelledby="sw-menu-dropdown-btn" style="border-radius: 8px; border: none; padding: 6px 0;">
            <li><a class="dropdown-item" href="javascript:void(0);" id="btn-zoomin"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-maximize">
                        <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                    </svg> <?php esc_html_e('Phóng to', 'wp-spin-wheel'); ?></a></li>
            <li><a class="dropdown-item d-none" href="javascript:void(0);" id="btn-zoomout"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-minimize">
                        <path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path>
                    </svg> <?php esc_html_e('Thu nhỏ', 'wp-spin-wheel'); ?></a></li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item" href="javascript:void(0);" id="menu-btn-info" data-bs-toggle="modal" data-bs-target="#modalInfo" data-toggle="modal" data-target="#modalInfo"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg> <?php esc_html_e('Thông tin', 'wp-spin-wheel'); ?></a></li>
            <li><a class="dropdown-item" href="javascript:void(0);" id="menu-btn-rs" data-bs-toggle="modal" data-bs-target="#modalRs" data-toggle="modal" data-target="#modalRs"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-book-open">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg> <?php esc_html_e('Kết quả quay', 'wp-spin-wheel'); ?></a></li>
            <li><a class="dropdown-item" href="javascript:void(0);" id="menu-btn-displaystats" data-bs-toggle="modal" data-bs-target="#modalDisplaystats" data-toggle="modal" data-target="#modalDisplaystats"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg> <?php esc_html_e('Lịch sử quay', 'wp-spin-wheel'); ?></a></li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item btn-create-new-user-wheel-top" href="javascript:void(0);"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg> <?php esc_html_e('Tạo vòng quay mới', 'wp-spin-wheel'); ?></a></li>
            <li><a class="dropdown-item" href="javascript:void(0);" id="btn-copy-wheel"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-copy">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg> <?php esc_html_e('Tạo bản sao', 'wp-spin-wheel'); ?></a></li>
            <li><a class="dropdown-item" href="javascript:void(0);" id="menu-btn-embed" data-bs-toggle="modal" data-bs-target="#modalEmbed" data-toggle="modal" data-target="#modalEmbed"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-code">
                        <polyline points="16 18 22 12 16 6"></polyline>
                        <polyline points="8 6 2 12 8 18"></polyline>
                    </svg> <?php esc_html_e('Mã nhúng', 'wp-spin-wheel'); ?></a></li>
            <li><a class="dropdown-item" href="javascript:void(0);" id="btn-download-code"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg> <?php esc_html_e('Tải code', 'wp-spin-wheel'); ?></a></li>
        </ul>
    </div>
</div>


<!-- Modal 1: Thông tin vòng quay -->
<div class="modal fade" id="modalInfo" tabindex="-1" aria-labelledby="modalInfoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold" id="modalInfoLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle text-primary me-2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <?php esc_html_e('Thông tin vòng quay', 'wp-spin-wheel'); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div class="card border-0 bg-light p-3 mb-3">
                    <h6 class="text-primary fw-bold mb-3 sw-stat-title"><?php echo esc_html($wheel_stats['title']); ?></h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-2 border bg-white rounded">
                                <span class="text-muted d-block small"><?php esc_html_e('Số lần quay tối đa:', 'wp-spin-wheel'); ?></span>
                                <strong class="sw-stat-max-spins text-dark"><?php echo esc_html($wheel_stats['max_spins']); ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2 border bg-white rounded">
                                <span class="text-muted d-block small"><?php esc_html_e('Lượt xem vòng quay:', 'wp-spin-wheel'); ?></span>
                                <strong class="sw-stat-views text-success"><?php echo esc_html(number_format_i18n($wheel_stats['views'])); ?></strong> <small class="text-muted">(lần)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2 border bg-white rounded">
                                <span class="text-muted d-block small"><?php esc_html_e('Tổng số lượt đã quay:', 'wp-spin-wheel'); ?></span>
                                <strong class="sw-stat-total-spins text-info"><?php echo esc_html(number_format_i18n($wheel_stats['total_spins'])); ?></strong> <small class="text-muted">(lượt)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2 border bg-white rounded">
                                <span class="text-muted d-block small"><?php esc_html_e('Lượt quay hôm nay:', 'wp-spin-wheel'); ?></span>
                                <strong class="sw-stat-today-spins text-warning"><?php echo esc_html(number_format_i18n($wheel_stats['today_spins'])); ?></strong> <small class="text-muted">(lượt)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2 border bg-white rounded">
                                <span class="text-muted d-block small"><?php esc_html_e('Tạo bởi:', 'wp-spin-wheel'); ?></span>
                                <strong class="sw-stat-creator text-dark"><?php echo esc_html($wheel_stats['creator']); ?></strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-2 border bg-white rounded">
                                <span class="text-muted d-block small"><?php esc_html_e('Ngày tạo:', 'wp-spin-wheel'); ?></span>
                                <strong class="sw-stat-created-at text-dark"><?php echo esc_html( ( $current_wid ? get_the_date( 'd/m/Y', $current_wid ) : ( ! empty( $wheel_stats['created_at'] ) ? explode( ' ', $wheel_stats['created_at'] )[0] : '' ) ) ?: __( 'Chưa cập nhật', 'wp-spin-wheel' ) ); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><?php esc_html_e('Đóng lại', 'wp-spin-wheel'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Kết quả đã quay -->
<div class="modal fade" id="modalRs" tabindex="-1" aria-labelledby="modalRsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold" id="modalRsLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-book-open text-primary me-2">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                    </svg>
                    <?php esc_html_e('Kết quả đã quay', 'wp-spin-wheel'); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;" class="text-center">STT</th>
                                <th><?php esc_html_e('Kết quả trúng', 'wp-spin-wheel'); ?></th>
                                <th style="width: 130px;" class="text-end"><?php esc_html_e('Thời gian', 'wp-spin-wheel'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="sw-session-results-tbody">
                            <!-- Dữ liệu kết quả được populate tự động khi quay -->
                        </tbody>
                    </table>
                    <div id="sw-session-results-empty" class="text-center py-4 text-muted">
                        <span class="d-block mb-1" style="font-size: 24px;">🎰</span>
                        <?php esc_html_e('Chưa có lượt quay nào trong phiên này.', 'wp-spin-wheel'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-danger btn-sm" id="btn-clear-session-results" onclick="clearSessionResults();">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2 me-1">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    <?php esc_html_e('Xóa danh sách', 'wp-spin-wheel'); ?>
                </button>
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><?php esc_html_e('Đóng lại', 'wp-spin-wheel'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Lịch sử quay -->
<div class="modal fade" id="modalDisplaystats" tabindex="-1" aria-labelledby="modalDisplaystatsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold" id="modalDisplaystatsLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock text-primary me-2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <?php esc_html_e('Lịch sử quay thưởng', 'wp-spin-wheel'); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div class="border rounded p-3 mb-3 bg-light">
                    <div class="fw-bold mb-2 text-dark d-flex align-items-center justify-content-between">
                        <span>👤 <span id="sw-player-header-title"><?php echo esc_html($player_display . ($player_email ? " ($player_email)" : '')); ?></span> <?php esc_html_e('đã quay:', 'wp-spin-wheel'); ?></span>
                        <button type="button" class="btn btn-sm btn-link text-decoration-none" onclick="loadRealHistoryData();" title="<?php esc_attr_e('Tải lại', 'wp-spin-wheel'); ?>">🔄 <?php esc_html_e('Làm mới', 'wp-spin-wheel'); ?></button>
                    </div>

                    <ul class="list-group small" id="sw-history-list-group" style="max-height: 350px; overflow-y: auto;">
                        <?php if (! empty($recent_history)) : ?>
                            <?php foreach ($recent_history as $h) : ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center" id="sw-hist-item-<?php echo esc_attr($h['id']); ?>">
                                    <div>
                                        <div class="fs-6 text-primary fw-bold">🎁 <?php echo esc_html($h['prize_title']); ?></div>
                                        <div class="text-muted small">
                                            <span>👤 <?php echo esc_html($h['name']); ?></span> &bull;
                                            <span>🎡 <?php echo esc_html($h['wheel_title']); ?></span> &bull;
                                            <span class="sw-hist-time">⏱️ <?php echo esc_html($h['time_ago']); ?></span>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-outline-danger btn-delstat" onclick="deleteStatItem(<?php echo esc_attr($h['id']); ?>, this);" title="<?php esc_attr_e('Xóa bản ghi này', 'wp-spin-wheel'); ?>" style="border: none; padding: 2px 6px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>

                    <div id="sw-history-empty-msg" class="text-center py-4 text-muted" style="<?php echo empty($recent_history) ? '' : 'display: none;'; ?>">
                        <span class="d-block mb-1" style="font-size: 24px;">📜</span>
                        <?php esc_html_e('Chưa có lượt quay nào được ghi nhận.', 'wp-spin-wheel'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between flex-wrap gap-2">
                <button type="button" class="btn btn-danger btn-sm px-3" id="btn-clear-all-stats" onclick="clearAllStats();">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash me-1">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    <?php esc_html_e('Xoá hết lịch sử', 'wp-spin-wheel'); ?>
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-sm px-3" id="btn-exportstats" onclick="exportStats();">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download me-1">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <?php esc_html_e('Xuất ra Excel', 'wp-spin-wheel'); ?>
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal"><?php esc_html_e('Đóng lại', 'wp-spin-wheel'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 4: Mã nhúng -->
<div class="modal fade" id="modalEmbed" tabindex="-1" aria-labelledby="modalEmbedLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title font-weight-bold" id="modalEmbedLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-code text-primary me-2">
                        <polyline points="16 18 22 12 16 6"></polyline>
                        <polyline points="8 6 2 12 8 18"></polyline>
                    </svg>
                    <?php esc_html_e('Mã nhúng vòng quay', 'wp-spin-wheel'); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div class="mb-3">
                    <label class="form-label fw-bold"><?php esc_html_e('Shortcode WordPress:', 'wp-spin-wheel'); ?></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="sw-embed-shortcode-val" value="[spin_wheel id=&quot;<?php echo esc_attr($current_wid); ?>&quot;]" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="copyEmbedCode('sw-embed-shortcode-val')"><?php esc_html_e('Sao chép', 'wp-spin-wheel'); ?></button>
                    </div>
                </div>
                <div>
                    <label class="form-label fw-bold"><?php esc_html_e('Mã Iframe HTML:', 'wp-spin-wheel'); ?></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="sw-embed-iframe-val" value="&lt;iframe src=&quot;<?php echo esc_url(get_permalink($current_wid)); ?>&quot; width=&quot;100%&quot; height=&quot;650&quot; frameborder=&quot;0&quot;&gt;&lt;/iframe&gt;" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="copyEmbedCode('sw-embed-iframe-val')"><?php esc_html_e('Sao chép', 'wp-spin-wheel'); ?></button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"><?php esc_html_e('Đóng', 'wp-spin-wheel'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Script điều khiển Modal & Dữ liệu cho popup-info.php -->
<script>
    (function() {
        // Hàm mở modal tương thích Bootstrap 5, Bootstrap 4 và Fallback CSS
        function openSwModal(modalSelector) {
            var modalEl = typeof modalSelector === 'string' ? document.querySelector(modalSelector) : modalSelector;
            if (!modalEl) return;

            // Đóng menu dropdown nếu đang mở
            jQuery('.dropdown-menu').removeClass('show');

            // 1. Thử dùng Bootstrap 5 JS
            if (window.bootstrap && window.bootstrap.Modal) {
                try {
                    var modalInst = window.bootstrap.Modal.getOrCreateInstance(modalEl);
                    if (modalInst) {
                        modalInst.show();
                        return;
                    }
                } catch (e) {}
            }

            // 2. Thử dùng jQuery Bootstrap plugin
            if (window.jQuery && jQuery(modalEl).modal) {
                try {
                    jQuery(modalEl).modal('show');
                    return;
                } catch (e) {}
            }

            // 3. Fallback CSS
            jQuery(modalEl).addClass('show').css('display', 'block').removeAttr('aria-hidden').attr('aria-modal', 'true');
            if (!jQuery('.sw-custom-backdrop').length) {
                jQuery('body').append('<div class="modal-backdrop fade show sw-custom-backdrop"></div>');
            }
        }

        // Hàm đóng modal tương thích
        function closeSwModal(modalSelector) {
            var modalEl = typeof modalSelector === 'string' ? document.querySelector(modalSelector) : modalSelector;
            if (!modalEl) return;

            if (window.bootstrap && window.bootstrap.Modal) {
                try {
                    var modalInst = window.bootstrap.Modal.getInstance(modalEl);
                    if (modalInst) {
                        modalInst.hide();
                        return;
                    }
                } catch (e) {}
            }

            if (window.jQuery && jQuery(modalEl).modal) {
                try {
                    jQuery(modalEl).modal('hide');
                    return;
                } catch (e) {}
            }

            jQuery(modalEl).removeClass('show').css('display', 'none').attr('aria-hidden', 'true');
            jQuery('.sw-custom-backdrop').remove();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Xử lý bật/tắt menu dropdown nếu Bootstrap JS không tự chạy
            jQuery(document).on('click', '#sw-menu-dropdown-btn', function(e) {
                if ((window.bootstrap && window.bootstrap.Dropdown) || (window.jQuery && jQuery.fn && jQuery.fn.dropdown)) {
                    return; // Để Bootstrap tự xử lý
                }
                e.preventDefault();
                var $menu = jQuery(this).next('.dropdown-menu');
                jQuery('.dropdown-menu').not($menu).removeClass('show');
                $menu.toggleClass('show');
            });

            // Đóng dropdown khi click ngoài
            jQuery(document).on('click', function(e) {
                if (!jQuery(e.target).closest('.dropdown').length) {
                    jQuery('.dropdown-menu').removeClass('show');
                }
            });

            // 1. Phóng to / Thu nhỏ Màn hình
            var btnZoomIn = document.getElementById('btn-zoomin');
            var btnZoomOut = document.getElementById('btn-zoomout');

            if (btnZoomIn && btnZoomOut) {
                btnZoomIn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var elem = document.documentElement;
                    if (elem.requestFullscreen) {
                        elem.requestFullscreen();
                    } else if (elem.webkitRequestFullscreen) {
                        elem.webkitRequestFullscreen();
                    } else if (elem.msRequestFullscreen) {
                        elem.msRequestFullscreen();
                    }
                    btnZoomIn.classList.add('d-none');
                    btnZoomOut.classList.remove('d-none');
                });

                btnZoomOut.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen();
                    } else if (document.msExitFullscreen) {
                        document.msExitFullscreen();
                    }
                    btnZoomOut.classList.add('d-none');
                    btnZoomIn.classList.remove('d-none');
                });

                document.addEventListener('fullscreenchange', function() {
                    if (!document.fullscreenElement && !document.webkitIsFullScreen && !document.mozFullScreen) {
                        btnZoomOut.classList.add('d-none');
                        btnZoomIn.classList.remove('d-none');
                    }
                });
            }

            // 2. Click các mục trong dropdown menu để mở popup tương ứng
            jQuery(document).on('click', '#menu-btn-info', function(e) {
                e.preventDefault();
                loadRealStatsData();
                openSwModal('#modalInfo');
            });

            jQuery(document).on('click', '#menu-btn-rs', function(e) {
                e.preventDefault();
                openSwModal('#modalRs');
            });

            jQuery(document).on('click', '#menu-btn-displaystats', function(e) {
                e.preventDefault();
                loadRealHistoryData();
                openSwModal('#modalDisplaystats');
            });

            jQuery(document).on('click', '#menu-btn-embed, #btn-download-code', function(e) {
                e.preventDefault();
                openSwModal('#modalEmbed');
            });

            jQuery(document).on('click', '#btn-copy-wheel', function(e) {
                e.preventDefault();
                duplicateCurrentWheel();
            });

            // Lắng nghe sự kiện đóng modal
            jQuery(document).on('click', '[data-bs-dismiss="modal"], [data-dismiss="modal"]', function(e) {
                var $modal = jQuery(this).closest('.modal');
                if ($modal.length) {
                    closeSwModal($modal[0]);
                }
            });

            jQuery(document).on('click', '.modal', function(e) {
                if (e.target === this) {
                    closeSwModal(this);
                }
            });

            var modalInfoEl = document.getElementById('modalInfo');
            var modalDisplaystatsEl = document.getElementById('modalDisplaystats');

            if (modalInfoEl) {
                modalInfoEl.addEventListener('show.bs.modal', function() {
                    loadRealStatsData();
                });
            }

            if (modalDisplaystatsEl) {
                modalDisplaystatsEl.addEventListener('show.bs.modal', function() {
                    loadRealHistoryData();
                });
            }
        });

        // Hàm tạo bản sao vòng quay hiện tại
        function duplicateCurrentWheel() {
            var wid = getCurrentWheelId();
            if (!wid || typeof wp_spin_wheel_params === 'undefined') {
                alert('Không thể tạo bản sao lúc này.');
                return;
            }

            jQuery.ajax({
                url: wp_spin_wheel_params.rest_url + 'user/wheels/' + wid + '/duplicate',
                method: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', wp_spin_wheel_params.nonce);
                },
                success: function(res) {
                    if (res && res.wheel_id) {
                        alert('Đã tạo bản sao vòng quay thành công!');
                        if (res.permalink) {
                            window.location.href = res.permalink;
                        } else {
                            window.location.reload();
                        }
                    } else {
                        window.location.reload();
                    }
                },
                error: function() {
                    alert('Không thể tạo bản sao vòng quay!');
                }
            });
        }

        window.openSwModal = openSwModal;
        window.closeSwModal = closeSwModal;
    })();

    // Hàm lấy ID vòng quay hiện tại
    function getCurrentWheelId() {
        var $wrapper = jQuery('#wheel-wrapper');
        if ($wrapper.length && $wrapper.attr('data-wheel-id')) {
            return parseInt($wrapper.attr('data-wheel-id')) || <?php echo (int) $current_wid; ?>;
        }
        return <?php echo (int) $current_wid; ?>;
    }

    // 3. Nạp Thống kê vòng quay thực tế
    function loadRealStatsData() {
        var wid = getCurrentWheelId();
        if (!wid || typeof wp_spin_wheel_params === 'undefined') return;

        jQuery.ajax({
            url: wp_spin_wheel_params.rest_url + 'wheels/' + wid + '/stats',
            method: 'GET',
            success: function(res) {
                if (res) {
                    if (res.title) jQuery('.sw-stat-title').text(res.title);
                    if (res.max_spins) jQuery('.sw-stat-max-spins').text(res.max_spins);
                    if (typeof res.views !== 'undefined') jQuery('.sw-stat-views').text(Number(res.views).toLocaleString('vi-VN'));
                    if (typeof res.total_spins !== 'undefined') jQuery('.sw-stat-total-spins').text(Number(res.total_spins).toLocaleString('vi-VN'));
                    if (typeof res.today_spins !== 'undefined') jQuery('.sw-stat-today-spins').text(Number(res.today_spins).toLocaleString('vi-VN'));
                    if (res.creator) jQuery('.sw-stat-creator').text(res.creator);
                    if (res.created_at) jQuery('.sw-stat-created-at').text(res.created_at);
                }
            }
        });
    }

    // 4. Nạp Lịch sử quay thực tế
    function loadRealHistoryData() {
        var wid = getCurrentWheelId();
        if (typeof wp_spin_wheel_params === 'undefined') return;

        var $list = jQuery('#sw-history-list-group');
        var $empty = jQuery('#sw-history-empty-msg');

        jQuery.ajax({
            url: wp_spin_wheel_params.rest_url + 'history',
            method: 'GET',
            data: {
                wheel_id: wid,
                per_page: 30
            },
            success: function(res) {
                if (res && res.items && res.items.length > 0) {
                    var html = '';
                    jQuery.each(res.items, function(idx, item) {
                        var nameStr = item.name || 'Khách ẩn danh';
                        var prizeStr = item.prize_title || 'Giải thưởng';
                        var timeAgo = item.time_ago || item.created_at_formatted || '';
                        var wheelTitle = item.wheel_title || '';

                        html += '<li class="list-group-item d-flex justify-content-between align-items-center" id="sw-hist-item-' + item.id + '">';
                        html += '  <div>';
                        html += '    <div class="fs-6 text-primary fw-bold">🎁 ' + escapeHtml(prizeStr) + '</div>';
                        html += '    <div class="text-muted small">';
                        html += '      <span>👤 ' + escapeHtml(nameStr) + '</span> &bull; ';
                        html += '      <span>🎡 ' + escapeHtml(wheelTitle) + '</span> &bull; ';
                        html += '      <span class="sw-hist-time">⏱️ ' + escapeHtml(timeAgo) + '</span>';
                        html += '    </div>';
                        html += '  </div>';
                        html += '  <button class="btn btn-sm btn-outline-danger btn-delstat" onclick="deleteStatItem(' + item.id + ', this);" title="Xóa bản ghi" style="border: none; padding: 2px 6px;">';
                        html += '    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>';
                        html += '  </button>';
                        html += '</li>';
                    });
                    $list.html(html);
                    $empty.hide();
                } else {
                    $list.empty();
                    $empty.show();
                }
            },
            error: function() {
                $list.empty();
                $empty.show();
            }
        });
    }

    // 5. Xóa 1 bản ghi lịch sử
    function deleteStatItem(id, btnEl) {
        if (!confirm('Bạn có chắc chắn muốn xóa lượt quay này không?')) return;

        if (typeof wp_spin_wheel_params === 'undefined') return;

        var $li = jQuery(btnEl).closest('li');
        $li.css('opacity', '0.5');

        jQuery.ajax({
            url: wp_spin_wheel_params.rest_url + 'history/' + id,
            method: 'DELETE',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wp_spin_wheel_params.nonce);
            },
            success: function(res) {
                $li.slideUp(200, function() {
                    jQuery(this).remove();
                    if (jQuery('#sw-history-list-group li').length === 0) {
                        jQuery('#sw-history-empty-msg').show();
                    }
                });
            },
            error: function(err) {
                $li.css('opacity', '1');
                alert('Lỗi khi xóa lượt quay!');
            }
        });
    }

    // 6. Xóa toàn bộ lịch sử
    function clearAllStats() {
        if (!confirm('CẢNH BÁO: Bạn có chắc chắn muốn xóa TOÀN BỘ lịch sử quay thưởng của vòng quay này không?')) return;

        var wid = getCurrentWheelId();
        if (typeof wp_spin_wheel_params === 'undefined') return;

        jQuery.ajax({
            url: wp_spin_wheel_params.rest_url + 'history?wheel_id=' + wid,
            method: 'DELETE',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wp_spin_wheel_params.nonce);
            },
            success: function(res) {
                jQuery('#sw-history-list-group').empty();
                jQuery('#sw-history-empty-msg').show();
                alert('Đã xóa toàn bộ lịch sử quay thưởng.');
            },
            error: function(err) {
                alert('Không thể xóa lịch sử.');
            }
        });
    }

    // 7. Xuất Lịch sử ra Excel / CSV
    function exportStats() {
        var wid = getCurrentWheelId();
        var exportUrl = window.location.protocol + '//' + window.location.host + '/wp-admin/edit.php?post_type=spin_wheel&page=spin-wheel-history&action=export_csv&wheel_id=' + wid + '&_wpnonce=' + encodeURIComponent(wp_spin_wheel_params.nonce || '');
        window.open(exportUrl, '_blank');
    }

    // 8. Xóa kết quả phiên hiện tại
    function clearSessionResults() {
        if (confirm('Bạn có muốn xóa danh sách kết quả đã quay trong phiên làm việc này?')) {
            jQuery('#sw-session-results-tbody').empty();
            jQuery('#sw-session-results-empty').show();
        }
    }

    // 9. Sao chép mã nhúng
    function copyEmbedCode(inputId) {
        var input = document.getElementById(inputId);
        if (!input) return;
        input.select();
        input.setSelectionRange(0, 99999);
        try {
            navigator.clipboard.writeText(input.value).then(function() {
                alert('Đã sao chép mã nhúng vào khay nhớ tạm!');
            });
        } catch (e) {
            document.execCommand('copy');
            alert('Đã sao chép mã nhúng!');
        }
    }

    // Hàm Escape HTML an toàn
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>

<script>
    // Cập nhật bảng "Kết quả đã quay" trong modal #modalRs khi quay trúng
    var sessionResultIndex = 0;

    function updateSessionResultModal(prizeTitle) {
        if (!prizeTitle) return;
        sessionResultIndex++;
        var $tbody = jQuery('#sw-session-results-tbody');
        var $empty = jQuery('#sw-session-results-empty');

        var now = new Date();
        var timeStr = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');

        var html = '<tr>' +
            '<td class="text-center fw-bold">' + sessionResultIndex + '</td>' +
            '<td><span class="badge bg-primary px-2 py-1 text-wrap" style="font-size: 13px;">🎁 ' + escapeHtml(prizeTitle) + '</span></td>' +
            '<td class="text-end text-muted small">' + timeStr + '</td>' +
            '</tr>';

        $tbody.append(html);
        $empty.hide();
    }
</script>