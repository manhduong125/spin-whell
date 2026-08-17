<?php
if (! defined('ABSPATH')) {
    exit;
}

// Lấy option nhạc từ global settings và danh sách audio items
$_global_opts     = WP_Spin_Wheel_Helper::get_global_settings();
$_start_sound     = $_global_opts['start_sound'] ?? '';
$_start_sound_file = $_global_opts['start_sound_file'] ?? '';
$_end_sound       = $_global_opts['end_sound'] ?? '';
$_end_sound_file  = $_global_opts['end_sound_file'] ?? '';
$_audios_start    = WP_Spin_Wheel_Helper::get_audio_library('start');
$_audios_end      = WP_Spin_Wheel_Helper::get_audio_library('end');

$default_settings = array(
    'background' => array(
        'type'  => 'color',
        'value' => '',
    ),
    'wheel' => array(
        'size'         => 600,
        'border'       => 12,
        'border_color' => '#ff4d00',
        'shadow'       => true,
    ),
    'button' => array(
        'text'       => 'Quay',
        'color'      => '#000',
        'text_color' => '',
        'radius'     => 100,
    ),
    'animation' => array(
        'duration' => 6,
        'confetti' => false,
    ),
    'audio' => array(
        'start_sound'      => $_start_sound,
        'start_sound_file' => $_start_sound_file,
        'end_sound'        => $_end_sound,
        'end_sound_file'   => $_end_sound_file,
    ),
);
$default_prizes = array();
$default_title = '';
$default_description = '';

// Nếu chưa có wheel_id, kiểm tra tham số URL hoặc lấy theo user đăng nhập
if (empty($wheel_id)) {
    if (! empty($_GET['wheel_id'])) {
        $wheel_id = absint($_GET['wheel_id']);
    } elseif (is_user_logged_in()) {
        $current_user_id = get_current_user_id();
        if ($current_user_id) {
            $wheel_id = WP_Spin_Wheel_Wheel::get_or_create_user_wheel($current_user_id);
        }
    }
}

// Nạp đúng cấu hình và giải thưởng của riêng wheel_id này
if (! empty($wheel_id)) {
    $wheel_saved_settings = WP_Spin_Wheel_Helper::get_wheel_overrides($wheel_id);
    if (! empty($wheel_saved_settings) && is_array($wheel_saved_settings)) {
        $default_settings = array_replace_recursive($default_settings, $wheel_saved_settings);
    }
    $wheel_db_prizes = WP_Spin_Wheel_Prize::get_prizes($wheel_id);
    if (! empty($wheel_db_prizes) && is_array($wheel_db_prizes)) {
        $default_prizes = $wheel_db_prizes;
    }
    $default_title = get_the_title($wheel_id);
    $default_description = get_post_field('post_content', $wheel_id);
}
?>
<div id="particles-js"></div>
<div class="container-fluid noads wp-spin-wheel-wrapper" id="wheel-wrapper" data-wheel-id="<?php echo esc_attr($wheel_id); ?>"
    data-wheel-settings="<?php echo esc_attr(wp_json_encode($default_settings)); ?>"
    data-wheel-prizes="<?php echo esc_attr(wp_json_encode($default_prizes)); ?>">
    <div class="row" id="row-wheel">
        <div class="col-xl-3 toggle-show-hide" id="wheel-left">

        </div>
        <div class="col-xl-6 text-center mb-3" id="wheel-center">
            <div id="wheel-container">
                <div id="wheelOfFortune">
                    <canvas id="wheel" width="700" height="700"></canvas>
                    <div id="spin"><?php esc_html_e('Quay', 'wp-spin-wheel'); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3" id="wheel-right">

        </div>
    </div>
</div>

<?php
require_once WP_SPIN_WHEEL_PATH . 'templates/popup/popup-user.php';
require_once WP_SPIN_WHEEL_PATH . 'templates/popup/popup-result.php';
?>

<div class="position-absolute top-0 start-0">
    <div class="p-1">
        <div class="dropdown">
            <button class="btn dropdown-togglex" style="color:#000000" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-info">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
            </button>
            <ul class="dropdown-menu" style="">
                <li class="d-none d-sm-block"><a class="dropdown-item" id="btn-zoomin" href="javascript:void(0);"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-maximize">
                            <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                        </svg> Phóng to</a></li>
                <li class="d-none d-sm-block"><a class="dropdown-item d-none" id="btn-zoomout" href="javascript:void(0);"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-minimize">
                            <path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path>
                        </svg> Thu nhỏ</a></li>
                <li><a class="dropdown-item" id="btn-info" href="javascript:void(0);"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg> Thông tin</a></li>
                <li><a class="dropdown-item" id="btn-rs" href="javascript:void(0);"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-book-open">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                        </svg> Kết quả quay</a></li>
                <li><a class="dropdown-item btn-displaystats" id="btn-displaystats" href="javascript:void(0);"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg> Lịch sử quay <sup>Chỉ bạn</sup></a></li>
                <li><a class="dropdown-item btn-create-new-user-wheel-top" href="javascript:void(0);"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="16"></line>
                            <line x1="8" y1="12" x2="16" y2="12"></line>
                        </svg> Tạo vòng quay mới</a></li>
                <li><a class="dropdown-item" href="javascript:void(0);" id="btn-copy-wheel"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-copy">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg> Tạo bản sao</a></li>

                <li><a class="dropdown-item" id="btn-embed-form" href="javascript:void(0);"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-code">
                            <polyline points="16 18 22 12 16 6"></polyline>
                            <polyline points="8 6 2 12 8 18"></polyline>
                        </svg> Mã nhúng</a></li>
                <li><a class="dropdown-item" id="btn-download-code" href="javascript:void(0);"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg> Tải code</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Thông tin user -->
<div class="modal" id="btn-info" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" id="modal-dialog">
        <div class="modal-content" id="modal-content">
            <div class="modal-header" id="modal-header">
                <h5 class="modal-title" id="modal-title"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle" style="width: 30px;heigh:30px;vertical-align: middle;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg> Thông tin</h5>
                <button type="button" id="modal-close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3" id="modal-body">
                <ul>
                    <li>Chọn số ngẫu nhiên</li>
                    <li>Số lần quay tối đa: ∞</li>
                    <li>Lượt xem: 11 (lần)</li>
                    <li>Tạo bởi: Phan Duong</li>
                </ul>
            </div>
            <div class="modal-footer justify-content-center" id="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng lại</button></div>
        </div>
    </div>
</div>

<!-- Kết quả quay -->
<div class="modal show" id="btn-rs" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" id="modal-dialog">
        <div class="modal-content" id="modal-content">
            <div class="modal-header" id="modal-header">
                <h5 class="modal-title" id="modal-title"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clipboard" style="width: 30px;heigh:30px;vertical-align: middle;">
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                    </svg> Kết quả đã quay</h5>
                <button type="button" id="modal-close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3" id="modal-body">
                <div class="list-result">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Kết quả</th>
                                <th>Mô tả</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Hoa</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Tuấn</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-center" id="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng lại</button></div>
        </div>
    </div>
</div>

<!-- Lịch sử quay -->
<div class="modal show" id="btn-displaystats" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" id="modal-dialog">
        <div class="modal-content" id="modal-content">
            <div class="modal-header" id="modal-header">
                <h5 class="modal-title" id="modal-title"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg> Lịch sử quay</h5>
                <button type="button" id="modal-close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3" id="modal-body">
                <div class="border rounded p-2 mb-3 bg-light">
                    <div class="fw-bold mb-2">
                        Phan Duong (phanduong125@gmail.com) đã quay vào:
                    </div>
                    <ul class="list-group small">
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="fs-5 text-primary">Tuấn </div>
                                    <div class="text-muted"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock" style="width:16px;height:16px;">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg> 1 phút trước</div>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-danger btn-delstat" data-id="148978">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                            </button>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="fs-5 text-primary">Hoa </div>
                                    <div class="text-muted"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock" style="width:16px;height:16px;">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg> 13 phút trước</div>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-danger btn-delstat" data-id="148954">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer justify-content-center" id="modal-footer"><a class="btn btn-clearallstats" onclick="clearAllStats()">Xoá hết lịch sử</a> <button class="btn btn-primary" id="btn-exportstats" onclick="exportStats();">Xuất ra Excel</button></div>
        </div>
    </div>
</div>

<script>
    (function($) {
        /* ── Audio preview cho select nhạc bắt đầu / kết thúc ── */
        var $player = null;
        var playingBtn = null;

        function getPlayer() {
            if (!$player || !$player.length) $player = $('#sw-audio-preview');
            return $player;
        }

        // Dừng player và reset icon nút đang phát
        function stopPreview() {
            var p = getPlayer();
            if (p.length) {
                p[0].pause();
                p[0].currentTime = 0;
                p.attr('src', '');
            }
            if (playingBtn) {
                $(playingBtn).find('[data-feather]').attr('data-feather', 'play');
                if (typeof feather !== 'undefined') feather.replace();
                playingBtn = null;
            }
        }

        // Phát URL — click lần 2 vào cùng nút thì dừng
        function playUrl(url, btn) {
            if (!url) return;
            if (playingBtn === btn) {
                stopPreview();
                return;
            }
            stopPreview();
            var p = getPlayer();
            p.attr('src', url);
            p[0].play().catch(function() {});
            $(btn).find('[data-feather]').attr('data-feather', 'square');
            if (typeof feather !== 'undefined') feather.replace();
            playingBtn = btn;
            p[0].onended = function() {
                $(btn).find('[data-feather]').attr('data-feather', 'play');
                if (typeof feather !== 'undefined') feather.replace();
                playingBtn = null;
            };
        }

        // Lấy URL từ option đang chọn trong select
        function getUrlFromSelect($select, audios) {
            var val = $select.val();
            if (!val || val === '0') return null; // Tắt tiếng → không phát

            if (val === 'random') {
                // Ngẫu nhiên → pick 1 bài trong thư viện
                var list = (audios || []).filter(function(a) {
                    return !!a.url;
                });
                if (!list.length) return null;
                return list[Math.floor(Math.random() * list.length)].url;
            }

            // Option thư viện nhạc có data-url
            return $select.find('option:selected').data('url') || null;
        }

        // Nút play của select (class .sw-btn-preview, data-target = id của <select>)
        $(document).on('click', '.sw-btn-preview', function() {
            var btn = this;
            var audios = $(btn).data('audios') || [];
            var $sel = $('#' + $(btn).data('target'));
            var url = getUrlFromSelect($sel, audios);
            if (!url) {
                stopPreview();
                return;
            }
            playUrl(url, btn);
        });

        // Nút play nhactik.com — bắt đầu
        $(document).on('click', '#btn-start-sound-play-file', function() {
            var fileId = $.trim($('#start_sound_file').val());
            if (!fileId) {
                stopPreview();
                return;
            }
            playUrl('https://nhactik.com/play/' + fileId + '.mp3', this);
        });

        // Nút play nhactik.com — kết thúc
        $(document).on('click', '#btn-end-sound-play-file', function() {
            var fileId = $.trim($('#end_sound_file').val());
            if (!fileId) {
                stopPreview();
                return;
            }
            playUrl('https://nhactik.com/play/' + fileId + '.mp3', this);
        });

        // Dừng nhạc khi đóng modal settings
        $(document).on('hide.bs.modal', '#modalSettings', function() {
            stopPreview();
        });

    })(jQuery);
</script>