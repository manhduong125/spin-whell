<?php
/**
 * Template Danh sách / Bộ sưu tập Hộp quà may mắn (Dynamic PHP)
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$boxes        = $boxes ?? array();
$total_pages  = $total_pages ?? 1;
$current_page = $current_page ?? 1;
$title        = $title ?? __( 'Bộ sưu tập hộp quà may mắn', 'wp-spin-wheel' );
$show_search  = $show_search ?? true;
$show_sort    = $show_sort ?? true;
$keyword      = $keyword ?? '';
$orderby      = $orderby ?? 'date';
$columns      = $columns ?? 4;
$col_class    = 'col-12 col-md-6 col-lg-' . ( 12 / max( 1, min( 6, $columns ) ) );
?>

<div class="container-fluid sw-user-boxes-container py-3">
    <!-- Header Title & Tools -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <h2 class="h2 mb-2 text-dark fw-bold"><?php echo esc_html( $title ); ?></h2>
        <?php if ( is_user_logged_in() ) : ?>
            <a href="<?php echo esc_url( home_url( '/hop-qua-may-man/' ) ); ?>" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm mb-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <?php esc_html_e( 'Tạo hộp quà mới', 'wp-spin-wheel' ); ?>
            </a>
        <?php endif; ?>
    </div>

    <!-- Search & Sort Filter Bar -->
    <?php if ( $show_search || $show_sort ) : ?>
        <div class="align-items-center bg-light p-3 rounded-3 border mb-3">
            <form method="GET" class="row g-2 align-items-center" id="sw-box-filter-form">
                <?php if ( $show_search ) : ?>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            </span>
                            <input type="text" name="sw_keyword" class="form-control border-start-0" placeholder="<?php esc_attr_e( 'Tìm kiếm hộp quà...', 'wp-spin-wheel' ); ?>" value="<?php echo esc_attr( $keyword ); ?>">
                            <button type="submit" class="btn btn-primary"><?php esc_html_e( 'Tìm kiếm', 'wp-spin-wheel' ); ?></button>
                        </div>
                <?php endif; ?>

                <?php if ( $show_sort ) : ?>
                    <div class="d-flex mb-3">
                        <span class="small text-muted fw-semibold"><?php esc_html_e( 'Sắp xếp:', 'wp-spin-wheel' ); ?>&nbsp;</span>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input sw-box-order-radio" type="radio" name="sw_order_by" id="sw_box_order_date" value="date" <?php checked( $orderby, 'date' ); ?>>
                            <label class="form-check-label small" for="sw_box_order_date"><?php esc_html_e( 'Mới nhất', 'wp-spin-wheel' ); ?>&nbsp;</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input sw-box-order-radio" type="radio" name="sw_order_by" id="sw_box_order_views" value="views" <?php checked( $orderby, 'views' ); ?>>
                            <label class="form-check-label small" for="sw_box_order_views"><?php esc_html_e( 'Xem nhiều', 'wp-spin-wheel' ); ?>&nbsp;</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input sw-box-order-radio" type="radio" name="sw_order_by" id="sw_box_order_title" value="title" <?php checked( $orderby, 'title' ); ?>>
                            <label class="form-check-label small" for="sw_box_order_title"><?php esc_html_e( 'Tên A-Z', 'wp-spin-wheel' ); ?>&nbsp;</label>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    <?php endif; ?>

    <!-- Box Cards Grid -->
    <?php if ( ! empty( $boxes ) ) : ?>
        <div class="row g-3" id="sw-boxs-grid">
            <?php foreach ( $boxes as $b ) :
                $bid       = $b['id'];
                $btitle    = $b['title'];
                $permalink = $b['permalink'];
                $bviews    = $b['views'];
                $bauthor   = $b['author_name'];
                $bdate     = $b['created_date'];
                $btime_ago = $b['time_ago'];
                $prizes    = $b['prizes'];
                $settings  = $b['settings'];
                $template  = $b['template'];
                $conlai    = $b['conlai'];
                $gift_count = count( $prizes );
            ?>
                <div class="<?php echo esc_attr( $col_class ); ?>">
                    <div class="card h-100 shadow-sm border rounded-3 sw-box-card">
                        <div class="card-body d-flex flex-column p-3">
                            <!-- Card Header: Title & Meta -->
                            <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
                                <div>
                                    <span class="badge bg-danger rounded-pill px-2 py-1 small">#<?php echo esc_html( $bid ); ?></span>
                                    <span class="text-muted small ms-1">🎁 <?php echo esc_html( $gift_count ); ?> quà</span>
                                </div>
                                <div class="text-end text-muted small">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-secondary"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        <strong><?php echo esc_html( number_format_i18n( $bviews ) ); ?></strong>
                                    </div>
                                    <small class="text-secondary"><?php echo esc_html( $btime_ago ); ?></small>
                                </div>
                            </div>

                            <!-- Mini Box Preview Visual -->
                            <div class="text-center my-3 py-2 bg-light rounded-2 position-relative overflow-hidden" style="min-height: 120px;">
                                <div class="box-jack-preview d-inline-block mt-2">
                                    <div class="box-jack mini-preview <?php echo esc_attr($template); ?>" style="transform: scale(0.7); margin: -10px auto;">
                                        <div class="lid"></div>
                                        <div class="front"></div>
                                    </div>
                                </div>
                                <div class="mt-2 text-truncate small fw-bold text-dark px-2"><?php echo esc_html( $btitle ); ?></div>
                                <div class="small text-muted"><?php echo sprintf( esc_html__( 'Còn %d lượt mở', 'wp-spin-wheel' ), $conlai ); ?></div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-auto d-flex justify-content-between align-items-center gap-2 pt-2 border-top">
                                <button type="button" class="btn btn-outline-primary btn-sm flex-fill sw-btn-copy-box"
                                    data-id="<?php echo esc_attr( $bid ); ?>"
                                    data-title="<?php echo esc_attr( $btitle ); ?>"
                                    data-settings="<?php echo esc_attr( wp_json_encode( $settings ) ); ?>"
                                    data-prizes="<?php echo esc_attr( wp_json_encode( $prizes ) ); ?>"
                                    title="<?php esc_attr_e( 'Sao chép nội dung hộp quà và cài đặt lại', 'wp-spin-wheel' ); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:2px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                    <?php esc_html_e( 'Sao chép', 'wp-spin-wheel' ); ?>
                                </button>
                                <a href="<?php echo esc_url( $permalink ); ?>" target="_blank" class="btn btn-primary btn-sm flex-fill">
                                    <?php esc_html_e( 'Mở ngay ›', 'wp-spin-wheel' ); ?>
                                </a>
                            </div>
                        </div> <!-- //card-body -->
                    </div> <!-- //card -->
                </div> <!-- //col -->
            <?php endforeach; ?>
        </div> <!-- //row -->

        <!-- Pagination Navigation -->
        <?php if ( $total_pages > 1 ) : ?>
            <div class="navlink text-center mt-4 d-flex justify-content-center flex-wrap gap-1">
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
})();
</script>
