<?php
/**
 * Template Danh sách / Bộ sưu tập Hộp quà may mắn (Dynamic PHP)
 * Shortcode: [box_gallery] / [user_boxes] / [my_boxes]
 * Cấu trúc đồng bộ với templates/wheel/user-wheels-list.php
 *
 * @package WP_Spin_Wheel
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$unique_id = 'sw-boxes-' . wp_rand( 1000, 9999 );
?>
<div class="container-fluid sw-user-boxes-container py-3" id="<?php echo esc_attr( $unique_id ); ?>">
    <!-- Header & Search Toolbar -->
    <div class="sw-user-boxes-header mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h2 class="h2 mb-2 text-dark fw-bold"><?php echo esc_html( $title ); ?></h2>
            <?php if ( is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url( home_url( '/hop-qua-may-man/' ) ); ?>" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px;">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <?php esc_html_e( 'Tạo hộp quà mới', 'wp-spin-wheel' ); ?>
                </a>
            <?php endif; ?>
        </div>

        <?php if ( $show_search || $show_sort ) : ?>
            <div class="align-items-center bg-light p-3 rounded-3 border mb-3">
                <?php if ( $show_search ) : ?>
                    <div class="input-group mb-3">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control sw-search-kw" placeholder="<?php esc_attr_e( 'Nhập từ khoá tìm kiếm hộp quà...', 'wp-spin-wheel' ); ?>" value="<?php echo esc_attr( $keyword ); ?>">
                            <button class="btn btn-primary sw-btn-do-search" type="button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                <?php esc_html_e( 'Tìm kiếm', 'wp-spin-wheel' ); ?>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( $show_sort ) : ?>
                    <div class="d-flex mb-3">
                        <span class="small fw-semibold text-secondary"><?php esc_html_e( 'Sắp xếp: ', 'wp-spin-wheel' ); ?>&nbsp;</span>
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input sw-sort-radio" type="radio" name="sw_sort_<?php echo esc_attr( $unique_id ); ?>" id="sw_box_sort_date_<?php echo esc_attr( $unique_id ); ?>" value="date" <?php checked( $orderby, 'date' ); ?>>
                            <label class="form-check-label small" for="sw_box_sort_date_<?php echo esc_attr( $unique_id ); ?>"><?php esc_html_e( 'Mới nhất', 'wp-spin-wheel' ); ?>&nbsp;</label>
                        </div>
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input sw-sort-radio" type="radio" name="sw_sort_<?php echo esc_attr( $unique_id ); ?>" id="sw_box_sort_views_<?php echo esc_attr( $unique_id ); ?>" value="views" <?php checked( $orderby, 'views' ); ?>>
                            <label class="form-check-label small" for="sw_box_sort_views_<?php echo esc_attr( $unique_id ); ?>"><?php esc_html_e( 'Xem nhiều', 'wp-spin-wheel' ); ?>&nbsp;</label>
                        </div>
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input sw-sort-radio" type="radio" name="sw_sort_<?php echo esc_attr( $unique_id ); ?>" id="sw_box_sort_title_<?php echo esc_attr( $unique_id ); ?>" value="title" <?php checked( $orderby, 'title' ); ?>>
                            <label class="form-check-label small" for="sw_box_sort_title_<?php echo esc_attr( $unique_id ); ?>"><?php esc_html_e( 'Tên A-Z', 'wp-spin-wheel' ); ?>&nbsp;</label>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Grid Danh Sách Hộp Quà -->
    <?php if ( ! empty( $boxes ) ) : ?>
        <div class="row g-3 sw-user-boxes-grid" id="list-sharelink">
            <?php foreach ( $boxes as $b ) :
                $bid        = $b['id'];
                $bcode_raw  = (string) get_post_field( 'post_name', $bid );
                $bcode      = strtoupper( preg_replace( '/^hq/i', '', $bcode_raw ) );
                if ( empty( $bcode ) ) {
                    $bcode = sprintf( 'HQ-%05d', $bid );
                }
                $btitle     = $b['title'];
                $permalink  = $b['permalink'];
                $bviews     = $b['views'];
                $bauthor    = $b['author_name'];
                $btime_ago  = $b['time_ago'];
                $prizes     = is_array( $b['prizes'] ) ? $b['prizes'] : array();
                $settings   = is_array( $b['settings'] ) ? $b['settings'] : array();
                $template   = ! empty( $b['template'] ) ? $b['template'] : 'tpl-jib';
                $conlai     = isset( $b['conlai'] ) ? (int) $b['conlai'] : 3;
                $gift_count = count( $prizes );

                // Dữ liệu cài đặt hiển thị (fallback an toàn)
                $s_bg_color    = ! empty( $settings['bg_color'] ) ? $settings['bg_color'] : '#dc3545';
                $s_color       = ! empty( $settings['color'] ) ? $settings['color'] : '#ffffff';
                $s_btn_bg      = ! empty( $settings['btn_bg_color'] ) ? $settings['btn_bg_color'] : '#dc3545';
                $s_btn_color   = ! empty( $settings['btn_color'] ) ? $settings['btn_color'] : '#ffffff';
                $s_bg_gradient = isset( $settings['bg_gradient'] ) ? (string) $settings['bg_gradient'] : '';
                $s_bg_img      = isset( $settings['bg_img'] ) ? (string) $settings['bg_img'] : '';

                // Style nền cho khung preview hộp quà
                $lkb_style = '';
                if ( '' !== trim( $s_bg_gradient ) ) {
                    $lkb_style .= 'background:' . $s_bg_gradient . ';';
                } elseif ( ! empty( $s_bg_img ) ) {
                    $lkb_style .= 'background-image:url(' . esc_url( $s_bg_img ) . ');background-size:cover;background-position:center;';
                } else {
                    $lkb_style .= 'background-color:' . $s_bg_color . ';';
                }
                $lkb_style .= 'color:' . $s_color . ';';

                // Tối đa 6 ô quà trong preview
                $preview_gifts = array_slice( $prizes, 0, 6 );
            ?>
                <div class="col-sm-6 col-lg-4 col-xl-<?php echo 12 / max( 1, min( 6, absint( $columns ) ) ); ?> link-item" id="row-<?php echo esc_attr( $bid ); ?>">
                    <div class="card mb-3 h-100 shadow-sm border-0 rounded-3 sw-box-card">
                        <div class="card-body d-flex flex-column p-3">
                            <!-- Link Header -->
                            <div class="d-flex justify-content-between align-items-start link-header mb-2">
                                <div class="card-title mb-0">
                                    <div class="fw-bold text-primary mb-1"><?php echo esc_html( $bcode ); ?></div>
                                    <div class="mb-1">
                                        <span class="badge bg-light text-dark border">🎁 <?php echo esc_html( $gift_count ); ?> quà</span>
                                    </div>
                                </div>
                                <div class="card-text text-muted small text-end">
                                    <div class="view" title="<?php esc_attr_e( 'Lượt mở / lượt xem', 'wp-spin-wheel' ); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye" style="vertical-align:text-bottom;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        <?php echo number_format_i18n( $bviews ); ?> (lần)
                                    </div>
                                    <div class="user text-truncate" style="max-width: 120px;" title="<?php echo esc_attr( $bauthor ); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user" style="vertical-align:text-bottom;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        <span><?php echo esc_html( $bauthor ); ?></span>
                                    </div>
                                    <div class="time text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock" style="vertical-align:text-bottom;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        <?php echo esc_html( $btime_ago ); ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Dữ liệu ẩn phục vụ JS sao chép -->
                            <div class="d-none" id="link-data-<?php echo esc_attr( $bid ); ?>"
                                data-title="<?php echo esc_attr( $btitle ); ?>"
                                data-template="<?php echo esc_attr( $template ); ?>"
                                data-bg_color="<?php echo esc_attr( $s_bg_color ); ?>"
                                data-color="<?php echo esc_attr( $s_color ); ?>"
                                data-bg_img="<?php echo esc_attr( $s_bg_img ); ?>"
                                data-bg_gradient="<?php echo esc_attr( $s_bg_gradient ); ?>"
                                data-conlai="<?php echo esc_attr( $conlai ); ?>"
                                data-btn_bg_color="<?php echo esc_attr( $s_btn_bg ); ?>"
                                data-btn_color="<?php echo esc_attr( $s_btn_color ); ?>"><?php echo esc_html( wp_json_encode( array_values( $prizes ) ) ); ?></div>

                            <!-- Mini Lucky Box Preview -->
                            <div class="link-box text-center my-3 cursor-pointer"
                                onclick="window.open('<?php echo esc_url( $permalink ); ?>','_blank').focus();"
                                id="link-box-<?php echo esc_attr( $bid ); ?>" data-code="<?php echo esc_attr( $bcode ); ?>">
                                <div class="lkb-container rounded-3 p-2" id="lkb-container-<?php echo esc_attr( $bid ); ?>" style="<?php echo esc_attr( $lkb_style ); ?>">
                                    <div class="lucky-box no-ads <?php echo esc_attr( $template ); ?>" id="lucky-box-<?php echo esc_attr( $bid ); ?>">
                                        <div class="hqmm-meta mb-2">
                                            <div class="box-info mx-auto mb-2">
                                                <div class="card border-0" style="background-color:<?php echo esc_attr( $s_btn_bg ); ?>;color:<?php echo esc_attr( $s_btn_color ); ?>;">
                                                    <div class="card-body py-2 px-3">
                                                        <h3 class="h6 mb-1 text-truncate" id="heading-title-<?php echo esc_attr( $bid ); ?>" title="<?php echo esc_attr( $btitle ); ?>"><?php echo esc_html( $btitle ); ?></h3>
                                                        <div class="small"><?php echo sprintf( esc_html__( 'Bạn còn %d lượt mở', 'wp-spin-wheel' ), $conlai ); ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="box-container row g-2 justify-content-center align-items-center" id="box-container-<?php echo esc_attr( $bid ); ?>">
                                            <?php foreach ( $preview_gifts as $gi => $g ) :
                                                $g_title = is_array( $g ) ? ( isset( $g['title'] ) ? $g['title'] : '' ) : (string) $g;
                                            ?>
                                                <div class="col-4">
                                                    <div class="box giftbox" id="box-<?php echo esc_attr( $bid . '-' . $gi ); ?>" title="<?php echo esc_attr( $g_title ); ?>">BOX <?php echo esc_html( $gi ); ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                            <?php if ( empty( $preview_gifts ) ) : ?>
                                                <div class="col-12 small opacity-75"><?php esc_html_e( 'Chưa có quà trong hộp', 'wp-spin-wheel' ); ?></div>
                                            <?php endif; ?>
                                        </div> <!-- //box-container -->

                                        <div class="mt-2 text-center button-group">
                                            <button type="button" class="btn btn-sm btn-brand" style="background-color:<?php echo esc_attr( $s_btn_bg ); ?>;color:<?php echo esc_attr( $s_btn_color ); ?>;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;">
                                                    <polyline points="20 12 20 22 4 22 4 12"></polyline>
                                                    <rect x="2" y="7" width="20" height="5"></rect>
                                                    <line x1="12" y1="22" x2="12" y2="7"></line>
                                                    <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                                                    <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                                                </svg>
                                                <?php esc_html_e( 'Trúng thưởng', 'wp-spin-wheel' ); ?>
                                            </button>
                                        </div>
                                    </div> <!-- lucky-box -->
                                </div> <!-- lkb-container -->
                            </div>

                            <!-- Box Title -->
                            <div class="h5 mb-3 link-title text-center text-truncate fw-bold" title="<?php echo esc_attr( $btitle ); ?>">
                                <a href="<?php echo esc_url( $permalink ); ?>" target="_blank" class="text-decoration-none text-dark">
                                    <?php echo esc_html( $btitle ); ?>
                                </a>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-auto d-flex justify-content-between align-items-center gap-2 pt-2 border-top">
                                <button type="button" class="btn btn-outline-primary btn-sm flex-fill sw-btn-copy-box"
                                    data-id="<?php echo esc_attr( $bid ); ?>"
                                    data-title="<?php echo esc_attr( $btitle ); ?>"
                                    data-settings="<?php echo esc_attr( wp_json_encode( $settings ) ); ?>"
                                    data-prizes="<?php echo esc_attr( wp_json_encode( $prizes ) ); ?>"
                                    data-url="<?php echo esc_url( $permalink ); ?>"
                                    title="<?php esc_attr_e( 'Sao chép nội dung hộp quà và cài đặt lại', 'wp-spin-wheel' ); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:2px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                    <?php esc_html_e( 'Sao chép', 'wp-spin-wheel' ); ?>
                                </button>
                                <a href="<?php echo esc_url( $permalink ); ?>" target="_blank" class="btn btn-primary btn-sm flex-fill">
                                    <?php esc_html_e( 'Xem ngay ›', 'wp-spin-wheel' ); ?>
                                </a>
                            </div>
                        </div> <!-- //card-body -->
                    </div> <!-- //card -->
                </div> <!-- //col -->
            <?php endforeach; ?>
        </div> <!-- //row -->

        <!-- Pagination Navigation -->
        <?php if ( $total_pages > 1 ) : ?>
            <div class="navlink text-center mt-4 d-flex justify-content-center flex-wrap gap-1" id="navigationLink">
                <?php for ( $p = 1; $p <= $total_pages; $p++ ) : ?>
                    <button type="button" class="btn btn-sm <?php echo ( $p === $current_page ) ? 'btn-primary' : 'btn-outline-secondary'; ?> sw-btn-page" data-page="<?php echo esc_attr( $p ); ?>">
                        <?php echo esc_html( $p ); ?>
                    </button>
                <?php endfor; ?>
                <?php if ( $current_page < $total_pages ) : ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary sw-btn-page" data-page="<?php echo esc_attr( $current_page + 1 ); ?>">
                        <?php esc_html_e( 'Tiếp ›', 'wp-spin-wheel' ); ?>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <div class="alert alert-info text-center py-5 rounded-3 border">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-secondary mb-3"><polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path></svg>
            <h5 class="fw-bold mb-2"><?php esc_html_e( 'Chưa tìm thấy hộp quà nào', 'wp-spin-wheel' ); ?></h5>
            <p class="text-muted mb-3"><?php esc_html_e( 'Bạn chưa tạo hộp quà nào hoặc không tìm thấy kết quả phù hợp.', 'wp-spin-wheel' ); ?></p>
            <?php if ( is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url( home_url( '/hop-qua-may-man/' ) ); ?>" class="btn btn-primary btn-sm px-4 rounded-pill">
                    <?php esc_html_e( 'Tạo hộp quà đầu tiên ngay', 'wp-spin-wheel' ); ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.sw-user-boxes-container .sw-box-card {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    border: 1px solid #edf2f7;
}
.sw-user-boxes-container .sw-box-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.08) !important;
}
.sw-user-boxes-container .cursor-pointer {
    cursor: pointer;
}
.sw-user-boxes-container .lkb-container {
    min-height: 150px;
}
.sw-user-boxes-container .box.giftbox:hover {
    transform: translateY(-3px) scale(1.03);
}
</style>

<script>
(function() {
    // Xử lý sao chép hộp quà
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.sw-btn-copy-box');
        if (!btn) return;
        e.preventDefault();

        var bid = parseInt(btn.dataset.id, 10) || 0;
        var title = btn.dataset.title || '';
        var rawSettings = btn.dataset.settings || '{}';
        var rawPrizes = btn.dataset.prizes || '[]';
        var settings = {};
        var prizes = [];
        try { settings = JSON.parse(rawSettings); } catch(err) {}
        try { prizes = JSON.parse(rawPrizes); } catch(err) {}

        btn.disabled = true;
        btn.innerHTML = 'Đang sao chép...';

        var gifts = prizes.map(function(p) { return is_object(p) ? (p.title || '') : p; });
        var homeBase = (typeof wp_spin_wheel_params !== 'undefined' && wp_spin_wheel_params.home_url) ? wp_spin_wheel_params.home_url : '/';

        try {
            var boxPayload = {
                title: title ? (title.indexOf('(Bản sao)') >= 0 ? title : (title + ' (Bản sao)')) : 'Hộp quà (Bản sao)',
                gifts: gifts,
                luotchoi: settings.luotchoi || 3,
                template: settings.template || 'tpl-jib',
                sound: settings.sound || 'winner',
                sound_file: settings.sound_file || '',
                noti_sound: settings.noti_sound || 'concainit',
                noti_sound_file: settings.noti_sound_file || '',
                popup_title: settings.popup_title || 'Hộp quà có',
                confetti: settings.confetti !== false,
                bg_img: settings.bg_img || '',
                bg_gradient: settings.bg_gradient || '',
                bg_color: settings.bg_color || '#dc3545',
                color: settings.color || '#ffffff',
                btn_bg_color: settings.btn_bg_color || '#dc3545',
                btn_color: settings.btn_color || '#ffffff'
            };
            localStorage.setItem('wp_spin_box_settings_guest', JSON.stringify(boxPayload));
            sessionStorage.setItem('wp_spin_wheel_just_copied', '1');
        } catch(err) {}

        window.location.href = homeBase + (homeBase.indexOf('?') >= 0 ? '&' : '?') + 'type=box';
    });

    function is_object(val) {
        return val !== null && typeof val === 'object';
    }

    // Xử lý tìm kiếm và phân trang theo URL param (đồng bộ user-wheels-list.php)
    function updateListUrl(params) {
        var url = new URL(window.location.href);
        Object.keys(params).forEach(function(k) {
            if (params[k] !== null && params[k] !== '') {
                url.searchParams.set(k, params[k]);
            } else {
                url.searchParams.delete(k);
            }
        });
        window.location.href = url.toString();
    }

    document.addEventListener('click', function(e) {
        var searchBtn = e.target.closest('.sw-btn-do-search');
        if (searchBtn) {
            var wrap = searchBtn.closest('.sw-user-boxes-container');
            var kw = wrap ? wrap.querySelector('.sw-search-kw').value.trim() : '';
            updateListUrl({ sw_keyword: kw, sw_page: 1 });
        }

        var pageBtn = e.target.closest('.sw-btn-page');
        if (pageBtn) {
            var page = pageBtn.dataset.page;
            if (page) {
                updateListUrl({ sw_page: page });
            }
        }
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('sw-sort-radio')) {
            var val = e.target.value;
            updateListUrl({ sw_order_by: val, sw_page: 1 });
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && e.target.classList.contains('sw-search-kw')) {
            e.preventDefault();
            var wrap = e.target.closest('.sw-user-boxes-container');
            var kw = e.target.value.trim();
            updateListUrl({ sw_keyword: kw, sw_page: 1 });
        }
    });
})();
</script>