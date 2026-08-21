<?php
if (! defined('ABSPATH')) {
    exit;
}

// Lấy cài đặt box
$box_id = ! empty($box_id) ? absint($box_id) : (! empty($_GET['box_id']) ? absint($_GET['box_id']) : 0);
$default_box_title = 'HỘP QUÀ MAY MẮN ONLINE';
$default_turns = 3;
$default_gifts = array(
    '100k', 'Ốp lưng iphone', '50k', 'Chúc bạn may mắn',
    '200k', 'Bút Montblanc', 'Ví da 500k', 'Sổ tay',
    'Gối tựa lưng', 'Bình giữ nhiệt', 'Ly sứ', 'Hộp đựng cơm'
);
$default_box_settings = WP_Spin_Wheel_Box::get_default_settings();

if ($box_id > 0) {
    $box_post = get_post($box_id);
    if ($box_post) {
        $default_box_title = $box_post->post_title ?: $default_box_title;
        $saved_settings = WP_Spin_Wheel_Box::get_box_settings($box_id);
        $saved_prizes = WP_Spin_Wheel_Box::get_box_gifts($box_id);
        if (! empty($saved_prizes)) {
            $default_gifts = $saved_prizes;
        }
        if (! empty($saved_settings)) {
            $default_box_settings = array_replace_recursive($default_box_settings, $saved_settings);
        }
        if (! empty($saved_settings['luotchoi'])) {
            $default_turns = absint($saved_settings['luotchoi']);
        }
    }
}

$_box_bg_style = '';
if (! empty($default_box_settings['bg_gradient'])) {
    $_box_bg_style = 'background: ' . $default_box_settings['bg_gradient'] . ';';
} elseif (! empty($default_box_settings['bg_img'])) {
    $_box_bg_style = 'background-image: url(' . esc_url($default_box_settings['bg_img']) . '); background-size: cover; background-position: center;';
} elseif (! empty($default_box_settings['bg_color'])) {
    $_box_bg_style = 'background-color: ' . esc_attr($default_box_settings['bg_color']) . ';';
}
?>
<div class="lucky-box-page" id="lucky-box-page" data-box-id="<?php echo esc_attr($box_id); ?>"
    data-box-settings="<?php echo esc_attr(wp_json_encode($default_box_settings)); ?>"
    data-box-gifts="<?php echo esc_attr(wp_json_encode($default_gifts)); ?>"
    style="<?php echo esc_attr($_box_bg_style); ?>">
    <div class="position-absolute top-0 end-0 mt-1 actions" id="actions">
        <button class="btn d-none" id="btn-reload" style="color: rgb(255, 255, 255);">Chơi lại</button>

        <button class="btn btn-sm" id="btn-share" data-bs-toggle="tooltip" data-bs-title="Tạo link chia sẻ"
            data-bs-placement="bottom" style="color: rgb(255, 255, 255);"><svg xmlns="http://www.w3.org/2000/svg"
                width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" class="feather feather-share-2">
                <circle cx="18" cy="5" r="3"></circle>
                <circle cx="6" cy="12" r="3"></circle>
                <circle cx="18" cy="19" r="3"></circle>
                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
            </svg></button>
        <button class="btn btn-sm" id="btn-quatang" data-bs-toggle="tooltip" data-bs-title="Danh sách đã tạo"
            data-bs-placement="bottom" style="color: rgb(255, 255, 255);"><svg xmlns="http://www.w3.org/2000/svg"
                width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" class="feather feather-folder">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
        </button>
        <button type="button" class="btn btn-sm btn-settings" data-bs-toggle="tooltip" data-bs-title="Cài đặt"
            data-bs-placement="bottom" id="btn-settings" aria-label="Cài đặt" data-bs-original-title="Cài đặt"
            style="color: rgb(255, 255, 255);">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="feather feather-settings">
                <circle cx="12" cy="12" r="3"></circle>
                <path
                    d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
                </path>
            </svg>
        </button>
        <a href="/box-gallery" class="btn btn-sm" style="color: rgb(255, 255, 255);"><svg
                xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="feather feather-grid">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg></a>
        <button class="btn btn-sm" id="btn-info" data-bs-toggle="tooltip" data-bs-title="Hộp quà may mắn"
            data-bs-placement="bottom" style="color: rgb(255, 255, 255);"><svg xmlns="http://www.w3.org/2000/svg"
                width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" class="feather feather-alert-circle">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg></button>
        <button class="btn btn-sm" id="btn-user" data-bs-toggle="tooltip" data-bs-title="Tài khoản"
            style="color: rgb(255, 255, 255);"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" class="feather feather-user">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg></button>
    </div>

    <div class="lucky-box no-ads tpl-jib" id="lucky-box" data-box-id="<?php echo esc_attr($box_id); ?>"
        data-box-settings="<?php echo esc_attr(wp_json_encode($default_box_settings)); ?>"
        data-box-gifts="<?php echo esc_attr(wp_json_encode($default_gifts)); ?>">
        <div class="hqmm-meta mb-5" id="hqmm-meta">
            <span id="edit-mode-txt"></span>

            <div class="box-info mx-auto mb-3">
                <div class="card" style="background-color: rgb(220, 53, 69); color: rgb(255, 255, 255);">
                    <div class="card-body">
                        <h1 id="heading-title" style="color: #fff">HỘP QUÀ MAY MẮN ONLINE
                        </h1>
                        <div class="badgex bg-secondaryx">Bạn còn <span class="fw-bold" id="conlai">3</span> lượt mở
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-3 g-lg-4 g-xl-4 justify-content-center align-items-center" id="lucky-box-content">

            <?php foreach ($default_gifts as $idx => $gift) : ?>
                <div class="col-4 col-md-3 col-lg-2 box-col-item">
                    <?php if ($default_template === 'tpl-jib') : ?>
                        <div class="box-jack" id="box-<?php echo esc_attr($idx); ?>" data-index="<?php echo esc_attr($idx); ?>">
                            <div class="lid"></div>
                            <div class="top"></div>
                            <div class="front"></div>
                            <div class="right"></div>
                            <div class="jack">
                                <div class="gift">
                                    <div class="ball">
                                        <div class="txt">🎀</div>
                                        <span class="fix-ratio-before"></span>
                                        <div class="layer moving">
                                            <div class="layer gridplane xline"></div>
                                            <div class="layer gridplane xline2"></div>
                                            <div class="layer gridplane yline"></div>
                                            <div class="layer gridplane zline"></div>
                                            <div class="layer gridplane laser"></div>
                                            <div class="layer gridplane laser2"></div>
                                        </div>
                                        <div class="layer clip">
                                            <div class="shade"></div>
                                        </div>
                                        <span class="fix-ratio-after"></span>
                                    </div>
                                    <div class="spring"><svg version="1.0" xmlns="http://www.w3.org/2000/svg" width="512.000000pt" height="512.000000pt" viewBox="0 0 512.000000 512.000000" preserveAspectRatio="xMidYMid meet"><g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)" stroke="none"><path d="M2810 4553 c-457 -32 -823 -110 -1054 -224 -192 -96 -307 -207 -359 -347 -17 -46 -21 -79 -21 -172 0 -100 4 -124 27 -182 36 -93 86 -168 163 -244 l66 -64 -30 -34 c-16 -18 -43 -57 -60 -87 -100 -176 -100 -374 -2 -569 55 -109 175 -243 280 -311 l24 -16 -56 -62 c-30 -34 -71 -92 -89 -129 -32 -63 -34 -73 -33 -167 0 -84 4 -110 26 -162 31 -72 75 -140 135 -204 l44 -47 -41 -58 c-56 -80 -74 -147 -67 -248 23 -333 317 -571 802 -651 50 -8 117 -15 151 -15 73 0 104 24 104 81 0 59 -30 78 -141 90 -278 31 -497 119 -625 251 -62 64 -87 106 -108 177 -30 102 -13 193 47 249 l23 21 84 -43 c255 -132 535 -199 830 -199 182 0 273 17 379 70 185 94 213 327 56 472 -152 140 -486 178 -869 99 -129 -26 -311 -89 -405 -139 -89 -48 -98 -45 -166 38 -66 81 -90 137 -90 213 0 84 21 127 102 208 l63 62 38 -16 c292 -128 597 -194 901 -194 492 0 744 187 615 457 -44 91 -141 159 -284 199 -101 29 -433 27 -590 -3 -219 -42 -472 -128 -619 -210 l-63 -35 -68 46 c-279 191 -371 478 -221 688 64 91 49 87 152 38 202 -93 426 -160 664 -196 169 -26 561 -27 690 -1 271 54 434 147 502 285 38 77 40 200 5 277 -60 131 -210 221 -435 262 -136 24 -402 22 -585 -6 -333 -50 -647 -162 -861 -305 l-83 -55 -42 34 c-158 129 -218 333 -143 481 119 235 629 400 1326 430 86 4 164 11 174 16 24 12 45 63 39 94 -9 50 -44 64 -156 62 -55 -1 -121 -3 -146 -5z m489 -923 c144 -29 259 -102 277 -176 11 -42 -3 -103 -31 -140 -82 -107 -286 -170 -587 -181 -338 -12 -704 54 -974 177 l-69 31 25 20 c50 38 267 139 385 178 179 58 380 97 595 115 85 7 293 -6 379 -24z m-39 -1148 c82 -27 136 -70 151 -123 11 -35 10 -43 -7 -67 -26 -36 -113 -78 -199 -97 -100 -22 -328 -30 -460 -16 -145 16 -320 53 -445 96 -56 18 -105 36 -108 39 -13 14 220 101 378 140 173 43 279 55 460 52 135 -3 177 -7 230 -24z m-218 -797 c137 -24 221 -69 248 -135 79 -192 -350 -260 -816 -129 -92 25 -276 97 -292 114 -9 9 13 21 101 55 243 95 545 133 759 95z"></path></g></svg></div>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="box giftbox" id="box-<?php echo esc_attr($idx); ?>" data-index="<?php echo esc_attr($idx); ?>">BOX <?php echo esc_html($idx + 1); ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-5 text-center button-group" id="button-group">
            <button class="btn" id="btn-quatang"
                style="background-color: rgb(220, 53, 69); color: rgb(255, 255, 255);"><svg
                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-gift">
                    <polyline points="20 12 20 22 4 22 4 12"></polyline>
                    <rect x="2" y="7" width="20" height="5"></rect>
                    <line x1="12" y1="22" x2="12" y2="7"></line>
                    <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                    <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                </svg> Quà tặng</button>
            <button class="btn" id="btn-ketqua"
                style="background-color: rgb(220, 53, 69); color: rgb(255, 255, 255);"><svg
                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="feather feather-gift">
                    <polyline points="20 12 20 22 4 22 4 12"></polyline>
                    <rect x="2" y="7" width="20" height="5"></rect>
                    <line x1="12" y1="22" x2="12" y2="7"></line>
                    <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                    <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                </svg> Trúng thưởng</button>
        </div>

    </div>
</div>

<?php
require_once WP_SPIN_WHEEL_PATH . 'templates/box/popups/popup-option-box.php';
require_once WP_SPIN_WHEEL_PATH . 'templates/box/popups/popup-box-item.php';
require_once WP_SPIN_WHEEL_PATH . 'templates/box/popups/popup-result-box.php';
require_once WP_SPIN_WHEEL_PATH . 'templates/box/popups/popup-share.php';
require_once WP_SPIN_WHEEL_PATH . 'templates/box/popups/popup-user-info.php';
require_once WP_SPIN_WHEEL_PATH . 'templates/box/popups/popup-none-user.php';
?>