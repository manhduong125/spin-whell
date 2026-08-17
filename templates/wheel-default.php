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
$_audios_start    = WP_Spin_Wheel_Helper::get_setting_items('audios_start');
$_audios_end      = WP_Spin_Wheel_Helper::get_setting_items('audios_end');

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
            <div class="vqmm-entry mb-4"> <a href="javascript:void(0);" data-bs-toggle="tooltip"
                    data-bs-title="Chỉnh sửa tiêu đề và mô tả" class="btn-edit toggle-show-hide" id="edit-content"
                    aria-label="Chỉnh sửa tiêu đề và mô tả" data-bs-original-title="Chỉnh sửa tiêu đề và mô tả"><svg
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-edit">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg></a> <span id="edit-mode-txt"></span>
                <div id="vqmm-content" class="text-center text-xl-start vqmm-content">
                    <div class="spin-heading toggle-show-hide" id="vqmm-title"><?php echo esc_html($default_title); ?>
                    </div>
                    <p class="toggle-show-hide" id="vqmm-desc"><?php echo esc_html($default_description); ?></p>
                </div>
            </div>
        </div>
        <div class="col-xl-6 text-center mb-3" id="wheel-center">
            <div id="wheel-container">
                <div id="wheelOfFortune">
                    <canvas id="wheel" width="700" height="700"></canvas>
                    <div id="spin"><?php esc_html_e('Quay', 'wp-spin-wheel'); ?></div>
                </div>
            </div>
            <div class="mt-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#modalSettings" aria-label="Cài đặt vòng quay">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        style="vertical-align:middle;">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83
                            l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4
                            0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0
                            1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2
                            2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2
                            2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0
                            1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0
                            1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65
                            1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    Cài đặt
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#modalTemplate" aria-label="Chủ đề">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        style="vertical-align:middle;">
                        <circle cx="13.5" cy="6.5" r="1.5"></circle>
                        <circle cx="17.5" cy="10.5" r="1.5"></circle>
                        <circle cx="8.5" cy="7.5" r="1.5"></circle>
                        <circle cx="6.5" cy="12.5" r="1.5"></circle>
                        <path
                            d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z">
                        </path>
                    </svg>
                    Chủ đề
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-user-wheels" data-bs-toggle="modal"
                    data-bs-target="#modalUserWheels" aria-label="Danh sách vòng quay">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        style="vertical-align:middle;">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                    Vòng quay của tôi
                </button>
            </div>
        </div>
        <div class="col-xl-3" id="wheel-right">
            <div class="wrc bg-light" id="wrc">
                <nav>
                    <div class="nav nav-tabs" id="main-tab" role="tablist">
                        <button class="nav-link w-50 active" id="tab-entries" data-bs-toggle="tab"
                            data-bs-target="#tab-content-entries" type="button" role="tab"
                            aria-controls="tab-content-entries"
                            aria-selected="true"><?php esc_html_e('Mục', 'wp-spin-wheel'); ?> <span
                                class="badge bg-primary text-light rounded-pill"
                                id="entries_count"><?php echo esc_html(count($default_prizes)); ?></span></button>
                        <button class="nav-link w-50" id="tab-result" data-bs-toggle="tab"
                            data-bs-target="#tab-content-result" type="button" role="tab"
                            aria-controls="tab-content-result"
                            aria-selected="false"><?php esc_html_e('Kết quả', 'wp-spin-wheel'); ?> <span
                                class="badge bg-primary text-light rounded-pill" id="result_count">0</span></button>
                    </div>
                </nav>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane py-3 fade show active" id="tab-content-entries" role="tabpanel"
                        aria-labelledby="tab-entries">
                        <div class="d-flex flex-wrap w-100 justify-content-start align-items-center mb-3" id="top-tool">
                            <button type="button" class="btn btn-outline-secondary btn-sm me-1" id="btn-shuffle-wheel">
                                <img decoding="async"
                                    src="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/icons/shuffle.png"
                                    class="custom-icon" width="20" height="20" alt="shuffle" loading="lazy">
                                Trộn
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm me-1" id="btn-sort-wheel-az">⇣
                                AZ</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm me-1 d-none"
                                id="btn-sort-wheel-za">⇣ ZA</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                id="btn-restore-defaults"><?php esc_html_e('Khôi phục', 'wp-spin-wheel'); ?></button>
                            <button type="button" class="btn btn-outline-danger btn-sm me-1" id="btn-clear-entry">
                                <span data-feather="x" style="width:20px;height:20px;"></span>
                                Xoá
                            </button>
                            <div class="form-check ms-1 me-0" style="display: none;">
                                <label class="form-check-label" for="is_advance">Nâng cao</label>
                                <input class="form-check-input" type="checkbox" role="checkbox" id="is_advance"
                                    value="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="input-group input-group-sm mb-2">
                                <input type="text" id="new_prize_title" class="form-control"
                                    placeholder="<?php echo esc_attr__('Nhập mục mới', 'wp-spin-wheel'); ?>">
                                <input type="color" id="new_prize_color" class="form-control form-control-color"
                                    value="#10b981"
                                    title="<?php echo esc_attr__('Màu phần thưởng', 'wp-spin-wheel'); ?>">
                                <button class="btn btn-primary" type="button"
                                    id="btn-add-prize"><?php esc_html_e('Thêm', 'wp-spin-wheel'); ?></button>
                            </div>

                        </div>
                        <div class="mb-3">
                            <div id="sector_list" class="form-control section-list rounded-0" readonly
                                placeholder="<?php esc_attr_e('Danh sách phần thưởng', 'wp-spin-wheel'); ?>">
                                <?php foreach ($default_prizes as $prize) : ?>
                                    <div><?php echo esc_html($prize['title']); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane py-3 fade" id="tab-content-result" role="tabpanel"
                        aria-labelledby="tab-result">
                        <div class="d-flex justify-content-start mb-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm me-2"
                                id="btn-sort-result"><?php esc_html_e('Sắp xếp', 'wp-spin-wheel'); ?></button>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                id="btn-clear-result"><?php esc_html_e('Xóa kết quả', 'wp-spin-wheel'); ?></button>
                        </div>
                        <div id="wheel_result" class="form-control rounded-0 section-list" readonly
                            placeholder="<?php esc_attr_e('Kết quả quay', 'wp-spin-wheel'); ?>"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once WP_SPIN_WHEEL_PATH . 'templates/popup/popup-user.php';
require_once WP_SPIN_WHEEL_PATH . 'templates/popup/popup-edit-title.php';
require_once WP_SPIN_WHEEL_PATH . 'templates/popup/popup-result.php';
require_once WP_SPIN_WHEEL_PATH . 'templates/popup/popup-option.php';
require_once WP_SPIN_WHEEL_PATH . 'templates/popup/popup-json.php';
?>

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