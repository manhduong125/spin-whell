<?php
/**
 * Template for rendering User Wheels List / Collection
 * Shortcode: [spin_wheel_user_wheels]
 *
 * @package WP_Spin_Wheel
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$unique_id = 'sw-wheels-' . wp_rand( 1000, 9999 );
?>
<div class="container-fluid sw-user-wheels-container py-3" id="<?php echo esc_attr( $unique_id ); ?>">
    <!-- Header & Search Toolbar -->
    <div class="sw-user-wheels-header mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <h2 class="h3 mb-0 text-dark fw-bold"><?php echo esc_html( $title ); ?></h2>
            <?php if ( is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px;">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <?php esc_html_e( 'Tạo vòng quay mới', 'wp-spin-wheel' ); ?>
                </a>
            <?php endif; ?>
        </div>

        <?php if ( $show_search || $show_sort ) : ?>
            <div class="row g-2 align-items-center bg-light p-3 rounded-3 border mb-3">
                <?php if ( $show_search ) : ?>
                    <div class="col-md-6 col-lg-5">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control sw-search-kw" placeholder="<?php esc_attr_e( 'Nhập từ khoá tìm kiếm vòng quay...', 'wp-spin-wheel' ); ?>" value="<?php echo esc_attr( $keyword ); ?>">
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
                    <div class="col-md-6 col-lg-7 d-flex align-items-center justify-content-md-end flex-wrap gap-3">
                        <span class="small fw-semibold text-secondary"><?php esc_html_e( 'Sắp xếp:', 'wp-spin-wheel' ); ?></span>
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input sw-sort-radio" type="radio" name="sw_sort_<?php echo esc_attr( $unique_id ); ?>" id="sw_sort_date_<?php echo esc_attr( $unique_id ); ?>" value="date" <?php checked( $orderby, 'date' ); ?>>
                            <label class="form-check-label small" for="sw_sort_date_<?php echo esc_attr( $unique_id ); ?>"><?php esc_html_e( 'Mới nhất', 'wp-spin-wheel' ); ?></label>
                        </div>
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input sw-sort-radio" type="radio" name="sw_sort_<?php echo esc_attr( $unique_id ); ?>" id="sw_sort_views_<?php echo esc_attr( $unique_id ); ?>" value="views" <?php checked( $orderby, 'views' ); ?>>
                            <label class="form-check-label small" for="sw_sort_views_<?php echo esc_attr( $unique_id ); ?>"><?php esc_html_e( 'Xem nhiều', 'wp-spin-wheel' ); ?></label>
                        </div>
                        <div class="form-check form-check-inline m-0">
                            <input class="form-check-input sw-sort-radio" type="radio" name="sw_sort_<?php echo esc_attr( $unique_id ); ?>" id="sw_sort_title_<?php echo esc_attr( $unique_id ); ?>" value="title" <?php checked( $orderby, 'title' ); ?>>
                            <label class="form-check-label small" for="sw_sort_title_<?php echo esc_attr( $unique_id ); ?>"><?php esc_html_e( 'Tên A-Z', 'wp-spin-wheel' ); ?></label>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <!-- Grid Danh Sách Vòng Quay -->
    <?php if ( ! empty( $wheels ) ) : ?>
        <div class="row g-3 sw-user-wheels-grid" id="list-sharelink">
            <?php foreach ( $wheels as $w ) :
                $wid           = $w['id'];
                $wcode         = sprintf( 'SW-%05d', $wid );
                $wtitle        = $w['title'];
                $permalink     = $w['permalink'];
                $views         = $w['views'];
                $author_name   = $w['author_name'];
                $time_ago      = $w['time_ago'];
                $prizes        = $w['prizes'];
                $prizes_count  = count( $prizes );
                $border_color  = ! empty( $w['border_color'] ) ? $w['border_color'] : '#ff4d00';
                $diamond_color = ! empty( $w['diamond_color'] ) ? $w['diamond_color'] : '#f6fa00';
                $button_label  = ! empty( $w['button_label'] ) ? $w['button_label'] : 'Quay';
                $button_img    = $w['button_img'] ?? '';
                $button_color  = ! empty( $w['button_color'] ) ? $w['button_color'] : '#d6392e';
            ?>
                <div class="col-sm-6 col-lg-4 col-xl-<?php echo 12 / max( 1, min( 6, absint( $columns ) ) ); ?> link-item" id="row-<?php echo esc_attr( $wid ); ?>">
                    <div class="card mb-3 h-100 shadow-sm border-0 rounded-3 sw-wheel-card">
                        <div class="card-body d-flex flex-column p-3">
                            <!-- Link Header -->
                            <div class="d-flex justify-content-between align-items-start link-header mb-2">
                                <div class="card-title mb-0">
                                    <div class="fw-bold text-primary mb-1"><?php echo esc_html( $wcode ); ?></div>
                                    <div class="mb-1">
                                        <span class="badge bg-light text-dark border">
                                            <img src="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/icons/tripod.png" width="14" height="14" alt="tripod" style="vertical-align:text-bottom;margin-right:2px;" onerror="this.style.display='none'">
                                            <?php echo esc_html( $prizes_count ); ?> mục
                                        </span>
                                    </div>
                                </div>
                                <div class="card-text text-muted small text-end">
                                    <div class="view" title="<?php esc_attr_e( 'Lượt quay / lượt xem', 'wp-spin-wheel' ); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye" style="vertical-align:text-bottom;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        <?php echo number_format_i18n( $views ); ?> (lần)
                                    </div>
                                    <div class="user text-truncate" style="max-width: 120px;" title="<?php echo esc_attr( $author_name ); ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user" style="vertical-align:text-bottom;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        <span><?php echo esc_html( $author_name ); ?></span>
                                    </div>
                                    <div class="time text-muted">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock" style="vertical-align:text-bottom;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        <?php echo esc_html( $time_ago ); ?>
                                    </div>
                                </div>
                            </div>
                            <!-- Mini Wheel Canvas Preview -->
                            <div class="link-wheel text-center my-3 cursor-pointer" onclick="window.open('<?php echo esc_url( $permalink ); ?>','_blank').focus();" id="link-wheel-<?php echo esc_attr( $wid ); ?>" data-code="<?php echo esc_attr( $wid ); ?>">
                                <div class="wheel-wrap position-relative mx-auto" style="width:180px;height:180px;">
                                    <canvas class="sw-mini-wheel-canvas" id="wheel-mini-<?php echo esc_attr( $wid ); ?>" width="360" height="360"
                                        data-prizes="<?php echo esc_attr( wp_json_encode( $prizes ) ); ?>"
                                        data-border-color="<?php echo esc_attr( $border_color ); ?>"
                                        data-diamond-color="<?php echo esc_attr( $diamond_color ); ?>"
                                        style="width: 100%; height: 100%; transform: rotate(-90deg);"></canvas>
                                    <div class="spin sw-mini-spin-center" style="<?php echo ! empty( $button_img ) ? 'background-image:url(' . esc_url( $button_img ) . ');background-size:cover;background-position:center;' : 'background-color:' . esc_attr( $button_color ) . ';color:#ffffff;'; ?>">
                                        <?php echo empty( $button_img ) ? esc_html( $button_label ) : ''; ?>
                                    </div>
                                </div>
                                <div class="tripod mt-1">
                                    <div class="tripod-top" style="height: 35px; width: 10px; background-color: <?php echo esc_attr( $border_color ); ?>; margin: 0 auto;"></div>
                                    <div class="tripod-bottom" style="width: 110px; height: 8px; background-color: <?php echo esc_attr( $border_color ); ?>; border-radius: 4px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>
                                </div>
                            </div>

                            <!-- Wheel Title -->
                            <div class="h5 mb-3 link-title text-center text-truncate fw-bold" title="<?php echo esc_attr( $wtitle ); ?>">
                                <a href="<?php echo esc_url( $permalink ); ?>" target="_blank" class="text-decoration-none text-dark">
                                    <?php echo esc_html( $wtitle ); ?>
                                </a>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-auto d-flex justify-content-between align-items-center gap-2 pt-2 border-top">
                                <button type="button" class="btn btn-outline-primary btn-sm flex-fill sw-btn-copy-wheel" data-url="<?php echo esc_url( $permalink ); ?>" data-shortcode='[spin_wheel id="<?php echo esc_attr( $wid ); ?>"]' title="<?php esc_attr_e( 'Sao chép liên kết vòng quay', 'wp-spin-wheel' ); ?>">
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
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-secondary mb-3"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <h5 class="fw-bold mb-2"><?php esc_html_e( 'Chưa tìm thấy vòng quay nào', 'wp-spin-wheel' ); ?></h5>
            <p class="text-muted mb-3"><?php esc_html_e( 'Bạn chưa tạo vòng quay nào hoặc không tìm thấy kết quả phù hợp.', 'wp-spin-wheel' ); ?></p>
            <?php if ( is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary btn-sm px-4 rounded-pill">
                    <?php esc_html_e( 'Tạo vòng quay đầu tiên ngay', 'wp-spin-wheel' ); ?>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.sw-user-wheels-container .sw-wheel-card {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    border: 1px solid #edf2f7;
}
.sw-user-wheels-container .sw-wheel-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.08) !important;
}
.sw-user-wheels-container .cursor-pointer {
    cursor: pointer;
}
.sw-user-wheels-container .wheel-wrap {
    position: relative;
    border-radius: 50%;
    overflow: visible;
}
.sw-user-wheels-container .sw-mini-spin-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    z-index: 2;
    border: 2px solid #ffffff;
}
.sw-user-wheels-container .tripod {
    display: flex;
    flex-direction: column;
    align-items: center;
}
</style>

<script>
(function() {
    // Render tất cả canvas mini wheel khi trang sẵn sàng
    function renderMiniWheels() {
        var canvases = document.querySelectorAll('.sw-mini-wheel-canvas');
        canvases.forEach(function(canvas) {
            if (canvas.dataset.rendered === 'true') return;
            var ctx = canvas.getContext('2d');
            if (!ctx) return;

            var rawPrizes = canvas.dataset.prizes || '[]';
            var prizes = [];
            try {
                prizes = JSON.parse(rawPrizes);
            } catch(e) {
                prizes = [];
            }
            if (!prizes || !prizes.length) {
                prizes = [
                    { title: '1', color: '#D6392E' },
                    { title: '2', color: '#3369E8' },
                    { title: '3', color: '#4F9A29' },
                    { title: '4', color: '#EEB331' }
                ];
            }

            var size = canvas.width || 360;
            var center = size / 2;
            var numSlices = prizes.length;
            var step = (Math.PI * 2) / numSlices;
            var borderColor = canvas.dataset.borderColor || '#ff4d00';
            var diamondColor = canvas.dataset.diamondColor || '#f6fa00';
            var borderWidth = 10;
            var radius = center - borderWidth;

            ctx.clearRect(0, 0, size, size);

            // Vẽ các lát ô thưởng
            for (var i = 0; i < numSlices; i++) {
                var startAngle = i * step;
                var endAngle = startAngle + step;
                var color = prizes[i].color || '#D6392E';

                ctx.beginPath();
                ctx.moveTo(center, center);
                ctx.arc(center, center, radius, startAngle, endAngle);
                ctx.closePath();
                ctx.fillStyle = color;
                ctx.fill();

                // Vẽ text rút gọn
                ctx.save();
                ctx.translate(center, center);
                ctx.rotate(startAngle + step / 2);
                ctx.textAlign = 'right';
                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold 12px sans-serif';
                ctx.shadowColor = 'rgba(0,0,0,0.5)';
                ctx.shadowBlur = 3;
                var txt = prizes[i].title || '';
                if (txt.length > 10) txt = txt.substring(0, 8) + '..';
                ctx.fillText(txt, radius - 14, 4);
                ctx.restore();
            }

            // Vẽ viền kim cương bên ngoài
            ctx.beginPath();
            ctx.arc(center, center, center - borderWidth / 2, 0, Math.PI * 2);
            ctx.strokeStyle = borderColor;
            ctx.lineWidth = borderWidth;
            ctx.stroke();

            // Vẽ các chấm kim cương
            var dotCount = Math.min(24, Math.max(12, numSlices * 2));
            for (var d = 0; d < dotCount; d++) {
                var dotAngle = (d / dotCount) * Math.PI * 2;
                var dx = center + (center - borderWidth / 2) * Math.cos(dotAngle);
                var dy = center + (center - borderWidth / 2) * Math.sin(dotAngle);
                ctx.beginPath();
                ctx.arc(dx, dy, 2.5, 0, Math.PI * 2);
                ctx.fillStyle = diamondColor;
                ctx.fill();
            }

            canvas.dataset.rendered = 'true';
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', renderMiniWheels);
    } else {
        renderMiniWheels();
    }

    // Xử lý sao chép link / shortcode
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.sw-btn-copy-wheel');
        if (!btn) return;
        var url = btn.dataset.url || '';
        var shortcode = btn.dataset.shortcode || '';
        var textToCopy = url || shortcode;
        if (!textToCopy) return;

        navigator.clipboard.writeText(textToCopy).then(function() {
            var origHtml = btn.innerHTML;
            btn.innerHTML = '✓ Đã sao chép';
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');
            setTimeout(function() {
                btn.innerHTML = origHtml;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 1800);
        }).catch(function() {
            prompt('Sao chép liên kết:', textToCopy);
        });
    });

    // Xử lý tìm kiếm và phân trang theo URL param
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
            var wrap = searchBtn.closest('.sw-user-wheels-container');
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
            var wrap = e.target.closest('.sw-user-wheels-container');
            var kw = e.target.value.trim();
            updateListUrl({ sw_keyword: kw, sw_page: 1 });
        }
    });
})();
</script>
