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
$default_prizes = array(
    array('title' => 'Tuấn', 'color' => '#D6392E'),
    array('title' => 'Thông', 'color' => '#3369E8'),
    array('title' => 'Sơn', 'color' => '#4F9A29'),
    array('title' => 'Dũng', 'color' => '#EEB331'),
    array('title' => 'Phong', 'color' => '#D6392E'),
    array('title' => 'Lan', 'color' => '#3369E8'),
    array('title' => 'Hương', 'color' => '#4F9A29'),
    array('title' => 'Hoa', 'color' => '#EEB331'),
    array('title' => 'Mai', 'color' => '#D6392E'),
    array('title' => 'Ngọc', 'color' => '#3369E8'),
    array('title' => 'Hà', 'color' => '#4F9A29'),
    array('title' => 'Thành', 'color' => '#EEB331'),
    array('title' => 'Trang', 'color' => '#D6392E'),
    array('title' => 'Giang', 'color' => '#3369E8'),
    array('title' => 'Tuyền', 'color' => '#4F9A29'),
    array('title' => 'Linh', 'color' => '#EEB331'),
);
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
        <div class="col-xl-12 text-center mb-3" id="wheel-center">
            <div id="wheel-container">
                <div id="wheelOfFortune">
                    <canvas id="wheel" width="700" height="700"></canvas>
                    <div id="spin"><?php esc_html_e('Quay', 'wp-spin-wheel'); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once WP_SPIN_WHEEL_PATH . 'templates/popup/popup-user.php';
require_once WP_SPIN_WHEEL_PATH . 'templates/popup/popup-result.php';
require_once WP_SPIN_WHEEL_PATH . 'templates/popup/popup-info.php';
require_once WP_SPIN_WHEEL_PATH . 'templates/popup/popup-share.php';
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