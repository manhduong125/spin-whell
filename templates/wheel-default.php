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
?>
<div id="particles-js"></div>
<div class="container-fluid noads wp-spin-wheel-wrapper" id="wheel-wrapper" data-wheel-id="0"
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

<div class="modal" id="modal-edit" tabindex="-1" style="display: none;" aria-modal="true" aria-hidden="true"
    role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" id="modal-dialog">
        <div class="modal-content" id="modal-content">
            <div class="modal-header" id="modal-header">
                <h5 class="modal-title" id="modal-title"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-edit"
                        style="width: 30px; height: 30px; vertical-align: middle;">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg> Sửa tiêu đề và mô tả</h5>
                <button type="button" id="modal-edit-close" class="btn-close" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div class="mb-2"><label for="editTitle" class="form-label">Tiêu đề</label><input type="text"
                        class="form-control" value="<?php echo esc_attr($default_title); ?>" data-maxlength="60"
                        id="editTitle" placeholder="Tiêu đề vòng quay"></div>
                <div class="mb-3"><label for="editDesc" class="form-label">Mô tả</label><textarea class="form-control"
                        id="editDesc" placeholder="Mô tả vòng quay" data-maxlength="160"
                        rows="3"><?php echo esc_textarea($default_description); ?></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="modal-edit-cancel">Đóng</button>
                <button type="button" id="saveTitleDesc" class="btn btn-primary">Lưu lại</button>
            </div>
        </div>
    </div>
</div>

<!-- Popup kết quả quay -->
<div class="modal" id="modal-result" tabindex="-1" style="display: none;" aria-modal="true" aria-hidden="true"
    role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="d-flex align-items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-clipboard me-1"
                            style="width:28px;height:28px;vertical-align:middle;">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        </svg>
                        <span
                            id="modal-result-popup-label"><?php esc_html_e('Bạn đã quay vào ô', 'wp-spin-wheel'); ?></span>
                    </span>
                </h5>
                <button type="button" class="btn-close" id="modal-result-close" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4 text-center">
                <div class="tick-btn active mb-3">
                    <svg viewBox="0 0 64 64" width="64" height="64">
                        <circle class="circ" cx="32" cy="32" r="28" style="stroke:#4f9a29;"></circle>
                        <path class="chk" d="M20 33 L28 41 L44 24" fill="none" stroke-linecap="round"
                            stroke-linejoin="round" style="stroke:#4f9a29;"></path>
                    </svg>
                </div>
                <div class="fs-2 fw-bold" id="modal-result-title" style="color:#4f9a29;word-break:break-word;"></div>
                <div class="mt-2 text-muted" id="modal-result-desc"></div>
            </div>
            <div class="modal-footer justify-content-center gap-2">
                <button type="button" class="btn btn-secondary" id="modal-result-close-btn">
                    <?php esc_html_e('Đóng lại', 'wp-spin-wheel'); ?>
                </button>
                <button type="button" class="btn btn-outline-danger" id="btn-remove-result-item" style="display:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        style="vertical-align:middle;margin-right:3px;">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6l-1 14H6L5 6"></path>
                        <path d="M10 11v6"></path>
                        <path d="M14 11v6"></path>
                        <path d="M9 6V4h6v2"></path>
                    </svg>
                    <?php esc_html_e('Xóa ô này', 'wp-spin-wheel'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- option json cho vòng quay -->
<div class="modal" id="modalTemplate" tabindex="-1" aria-modal="true" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-folder">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Chủ đề mẫu
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <button type="button" class="btn btn-success btn-sm mb-2 rounded-pill btn-fill"
                    data-content="Tuấn||Thông||Sơn||Dũng||Phong||Lan||Hương||Hoa||Mai||Ngọc||Hà||Thành||Trang||Giang||Tuyền||Linh">
                    Tên ngẫu nhiên
                </button><button type="button" class="btn btn-secondary btn-sm mb-2 rounded-pill btn-fill"
                    data-content="Mời người đối diện||Tự uống 1 ly||Tìm người uống cùng||Tất cả cùng uống||Tự uống 2 ly||Được ăn mồi||Quay lại||Chỉ ai đó uống||Bên phải uống 1 ly||Thoát nạn (khỏi uống)||Bên trái uống 1 ly||Tự uống 1/2 ly||Nhảy một điệu nhảy ngẫu nhiên||Uống hai ly nước lọc.||Hỏi một câu hỏi khó cho người khác trả lời||Giữ thăng bằng trên một chân trong 30 giây||Khen một người bất kỳ trong bàn nhậu||Kể một câu chuyện hài hước||Hát một bài hát">
                    Trên bàn nhậu
                </button>
                <button type="button" class="btn btn-primary btn-sm mb-2 rounded-pill btn-fill"
                    data-content="Ăn Phở||Ăn Bún||Gà KFC||Gà Lotteria||Gà 36||Tokyo Deli||Lẩu Gogi||Isushi||Sumo BBQ||Phốn gon 37||Kichi-Kichi||Ba con cừu||Shogun||MANWAH||HUTONG||DARUMA||Quay lại||Mỳ UDON">
                    Trưa nay ăn gì
                </button>
                <button type="button" class="btn btn-danger btn-sm mb-2 rounded-pill btn-fill"
                    data-content="Ôm rồi hôn||Đi nhà nghỉ||Về nhà ngủ||Quay lại||Ra công viên||Ăn rồi ngủ||Đi xem film||Mua 2 trà sữa||Mát xa cho em||Chơi đuổi nhau||Chơi chốn tìm||Chơi game 69">
                    Rủ dê gái
                </button>
                <button type="button" class="btn btn-warning btn-sm mb-2 rounded-pill btn-fill"
                    data-content="Chẵn||Lẻ||Chẵn||Lẻ||Chẵn||Lẻ||Chẵn||Lẻ||chẵn||Lẻ||Chẵn||Lẻ">
                    Chẵn lẻ
                </button>
                <button type="button" class="btn btn-danger btn-sm mb-2 rounded-pill btn-fill"
                    data-content="Bên phải||Tên Cường||Đeo kính||Đối diện||Bên trái||Quay lại||Nói nhiều||Thằng quay||Mồm to||Vừa đi vệ sinh">
                    Ai trả tiền
                </button>

                <button type="button" class="btn btn-info btn-sm mb-2 rounded-pill btn-fill"
                    data-content="A||B||C||D||E||F||G||H||I||J||K||L||M||N||O||P||Q||R||S||T||U||V||W||X||Y||Z">
                    Chữ cái A→Z
                </button>
                <button type="button" class="btn btn-dark btn-sm mb-2 rounded-pill btn-fill-number" data-from="1"
                    data-to="10" title="Số từ 1→10 (rất nhanh)">
                    Số (1→10)
                </button>
                <button type="button" class="btn btn-secondary btn-sm mb-2 rounded-pill btn-fill-number" data-from="1"
                    data-to="100" title="Số từ 1→100 (nhanh)">
                    Số (1→100)
                </button>
                <button type="button" class="btn btn-warning btn-sm mb-2 rounded-pill btn-fill-number" data-from="1"
                    data-to="500" title="Số từ 1→500 (trung bình)">
                    Số (1→500)
                </button>
                <button type="button" class="btn btn-danger btn-sm mb-2 rounded-pill btn-fill-number" data-from="1"
                    data-to="1000" title="Số từ 1→1000 (chậm)">
                    Số (1→1000)
                </button>
                <button type="button" class="btn btn-warning btn-sm mb-2 rounded-pill btn-fill"
                    data-content="🇻🇳 Việt Nam||🇹🇭 Thái Lan||🇲🇾 Malaysia||🇮🇩 Indonesia||🇸🇬 Singapore||🇱🇦 Lào||🇰🇭 Campuchia||🇵🇭 Philippines||🇲🇲 Myanmar||Quay lại">
                    Bóng đá
                </button>
                <button type="button" class="btn btn-info btn-sm mb-2 rounded-pill btn-fill"
                    data-content="5k||10k||20k||50k||30k||100k||Nhân đôi||200k||Chia đôi||500k">
                    Phần thưởng
                </button>
                <button type="button" class="btn btn-primary btn-sm mb-2 rounded-pill btn-fill"
                    data-content="💍Ring||📿Necklake||👙Bikini||👗Dress||👚blouse||👕T-shirt||👘Kimono||️🎽Runingshirt||👖Jean||👠Highheels||👢Boot||👞Man\sshoe||👒Hat||🎩Tophat">
                    Thời trang
                </button>
                <button type="button" class="btn btn-success btn-sm mb-2 rounded-pill btn-fill"
                    data-content="😽Cat||🐶Puppy||🐰Bunny||🐹Hamster||🦊Fox||🐻Bear||🐼Panda||🐨Koala||🐯Tiger||🦁Lion||🐮Cow||🐂Ox||🐷Pig||🐸Frog||🐵Monkey||🦍Gorilla||🐺Wolf||🐑Sheep||🐐Goat||🐏Ram||🦌Deer||🐪Camel||🐎Horse||🐊Croccodile||🐢Turtle||🐬Dolphin||🦈Shark||🐋Whale||🦐Shrimp||🦀Crab||🐙Octopus||🦑Squid||🐜Ant||🕷️Spider||🐞Ladybug||🦋Butterfly||🐝Bee||🐌Snail||🐲Dragon||🦉Owl||🐔Chicken||🐓Rooster||🐧Penguin||🦇Bat">
                    Động vật
                </button>
                <a href="https://vongquaymayman.co/gallery/" class="btn btn-light btn-sm mb-2 rounded-pill btn-fill"
                    data-content="" id="btn-more">Xem tiếp
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-chevrons-right">
                        <polyline points="13 17 18 12 13 7"></polyline>
                        <polyline points="6 17 11 12 6 7"></polyline>
                    </svg></a>
            </div>
        </div>
    </div>
</div>

<!-- Option cho theme -->
<div class="modal" id="modalSettings" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><span data-feather="settings"></span> Cài đặt</h5> <button type="button"
                    class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="gen-setting-tab" data-bs-toggle="tab" data-bs-target="#gen-setting-tab-pane" type="button" role="tab" aria-controls="gen-setting-tab-pane" aria-selected="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                            <span>Chung</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="appearance-tab" data-bs-toggle="tab" data-bs-target="#appearance-tab-pane" type="button" role="tab" aria-controls="appearance-tab-pane" aria-selected="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg>
                            <span>Giao diện</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="media-tab" data-bs-toggle="tab" data-bs-target="#media-tab-pane" type="button" role="tab" aria-controls="media-tab-pane" aria-selected="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                            <span>Thư viện</span>
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade pt-3 show active" id="gen-setting-tab-pane" role="tabpanel"
                        aria-labelledby="gen-setting-tab" tabindex="0">
                        <!-- audio player ẩn dùng chung cho preview -->
                        <audio id="sw-audio-preview" preload="none" style="display:none;"></audio>

                        <!-- ♪ Nhạc bắt đầu -->
                        <div class="mb-2">
                            <div class="input-group mb-1">
                                <span class="input-group-text">♪ Bắt đầu</span>
                                <select class="form-select" id="start_sound">
                                    <!-- dữ liệu nhạc bắt đầu -->
                                    <option value="0"
                                        <?php selected(in_array($_start_sound, array('0', ''), true), true); ?>>Tắt
                                        tiếng</option>
                                    <?php if (! empty($_audios_start)) : ?>
                                    <option value="random" <?php selected($_start_sound, 'random'); ?>>Ngẫu nhiên
                                    </option>
                                    <?php endif; ?>
                                    <?php if (! empty($_audios_start)) : ?>
                                    <optgroup label="── Thư viện nhạc ──">
                                        <?php foreach ($_audios_start as $_as) :
                                                $_as_id  = $_as['id'] ?? '';
                                                $_as_url = $_as['config']['file'] ?? '';
                                                $_as_lbl = $_as['name'] ?? '';
                                            ?>
                                        <option value="<?php echo esc_attr($_as_id); ?>"
                                            data-url="<?php echo esc_url($_as_url); ?>"
                                            <?php selected($_start_sound, $_as_id); ?>><?php echo esc_html($_as_lbl); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <?php endif; ?>
                                </select>
                                <button type="button" class="btn btn-outline-secondary sw-btn-preview"
                                    data-target="start_sound" data-audios="<?php
                                                    $_audios_start_map = array_values(array_map(
                                                        function ($a) {
                                                            return array(
                                                                'id'  => $a['id'] ?? '',
                                                                'url' => $a['config']['file'] ?? '',
                                                            );
                                                        },
                                                        $_audios_start
                                                    ));
                                                    echo esc_attr(wp_json_encode($_audios_start_map));
                                                    ?>" title="Nghe thử">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="feather feather-play">
                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                    </svg>
                                </button>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text">
                                    <a class="text-decoration-none" target="_blank"
                                        href="/huong-dan-lay-file_id-tren-nhactik-com/">♪ nhactik.com</a>
                                </span>
                                <input type="text" class="form-control" id="start_sound_file" placeholder="File ID"
                                    value="<?php echo esc_attr($_start_sound_file); ?>">
                                <button type="button" class="btn btn-outline-secondary" id="btn-start-sound-play-file"
                                    title="Nghe thử nhactik">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="feather feather-play">
                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- ♪ Nhạc kết thúc -->
                        <div class="mb-2">
                            <div class="input-group mb-1">
                                <span class="input-group-text">♪ Kết thúc</span>
                                <select class="form-select" id="end_sound">
                                    <!-- Dữ liệu nhạc kết thúc -->
                                    <option value="0"
                                        <?php selected(in_array($_end_sound, array('0', ''), true), true); ?>>Tắt tiếng
                                    </option>
                                    <?php if (! empty($_audios_end)) : ?>
                                    <option value="random" <?php selected($_end_sound, 'random'); ?>>Ngẫu nhiên</option>
                                    <?php endif; ?>
                                    <option value="read" <?php selected($_end_sound, 'read'); ?>>Đọc kết quả</option>
                                    <option value="slot_end" <?php selected($_end_sound, 'slot_end'); ?>>Slot end
                                    </option>
                                    <?php if (! empty($_audios_end)) : ?>
                                    <optgroup label="── Thư viện nhạc ──">
                                        <?php foreach ($_audios_end as $_ae) :
                                                $_ae_id  = $_ae['id'] ?? '';
                                                $_ae_url = $_ae['config']['file'] ?? '';
                                                $_ae_lbl = $_ae['name'] ?? '';
                                            ?>
                                        <option value="<?php echo esc_attr($_ae_id); ?>"
                                            data-url="<?php echo esc_url($_ae_url); ?>"
                                            <?php selected($_end_sound, $_ae_id); ?>><?php echo esc_html($_ae_lbl); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <?php endif; ?>
                                </select>
                                <button type="button" class="btn btn-outline-secondary sw-btn-preview"
                                    data-target="end_sound" data-audios="<?php
                                                    $_audios_end_map = array_values(array_map(
                                                        function ($a) {
                                                            return array(
                                                                'id'  => $a['id'] ?? '',
                                                                'url' => $a['config']['file'] ?? '',
                                                            );
                                                        },
                                                        $_audios_end
                                                    ));
                                                    echo esc_attr(wp_json_encode($_audios_end_map));
                                                    ?>" title="Nghe thử">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="feather feather-play">
                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                    </svg>
                                </button>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text">
                                    <a class="text-decoration-none" target="_blank"
                                        href="/huong-dan-lay-file_id-tren-nhactik-com/">♪ nhactik.com</a>
                                </span>
                                <input type="text" class="form-control" id="end_sound_file"
                                    value="<?php echo esc_attr($_end_sound_file); ?>" placeholder="File ID">
                                <button type="button" class="btn btn-outline-secondary" id="btn-end-sound-play-file"
                                    title="Nghe thử nhactik">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="feather feather-play">
                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="input-group mb-3"> <span class="input-group-text">Tốc độ quay</span>
                            <select class="form-select" id="duration">
                                <?php
                                $_dur = $default_settings['animation']['duration'];
                                $duration_options = array(
                                    4  => 'Nhanh',
                                    6  => 'Tiêu chuẩn',
                                    8  => 'Chậm hơn',
                                    12 => 'Chậm hơn nữa',
                                );
                                foreach ($duration_options as $_sec => $_label) :
                                ?>
                                <option value="<?php echo esc_attr($_sec); ?>" <?php selected((int) $_dur, $_sec); ?>>
                                    <?php echo esc_html($_label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-check mb-3"> <input class="form-check-input" type="checkbox" id="show_confetti"
                                checked> <label class="form-check-label" for="show_confetti"> Bắn hoa giấy khi kết thúc
                            </label> </div>
                        <div class="form-check mb-3"> <input class="form-check-input" type="checkbox" id="auto_remove">
                            <label class="form-check-label" for="auto_remove"> Tự động
                                xóa kết quả sau 5 giây </label>
                        </div>
                        <div class="form-check mb-3"> <input class="form-check-input" type="checkbox" id="show_popup"
                                checked> <label class="form-check-label" for="show_popup">
                                Popup kết quả với tiêu đề: </label> </div>
                        <div class="mb-3"> <input type="text" class="form-control ms-3"
                                style="width: calc(100% - 1rem);" id="popup_label" value="Bạn đã quay vào ô"
                                placeholder="Bạn đã quay vào ô"> </div>
                        <div class="form-check ms-3 mb-3"> <input class="form-check-input" type="checkbox"
                                id="show_remove_button" checked=""> <label class="form-check-label"
                                for="show_remove_button"> Hiển thị nút “Xóa ô này” </label> </div>
                    </div>

                    <div class="tab-pane fade pt-1" id="appearance-tab-pane" role="tabpanel"
                        aria-labelledby="appearance-tab" tabindex="0">
                        <fieldset class="border border-2 px-2">
                            <legend class="float-none w-auto p-2 fs-6 fw-bold">Vòng quay <svg
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-chevron-down">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg></legend>
                            <div class="mb-3" id="custom-basic-style"> <input type="hidden" id="type" value="color">
                                <div class="row justify-content-center align-items-center mb-3">
                                    <div class="col-5 text-end"> <button class="btn mb-1" id="btn_color_wheel"><img
                                                decoding="async"
                                                src="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/images/color-wheel.png"
                                                width="36" alt="Color wheel"></button> <label for="btn_color_wheel">Màu
                                            cho mỗi phần</label> </div>
                                    <div class="col-2 text-center">
                                        <div class="form-check form-switch p-0 d-inline-block mx-auto"> <input
                                                class="form-check-input ms-0"
                                                style="background-color: #0d6efd;border-color: #0d6efd;background-image: url('data:image/svg+xml,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'-4 -4 8 8\'%3e%3ccircle r=\'3\' fill=\'%23fff\'/%3e%3c/svg%3e');"
                                                type="checkbox" role="switch" id="switch_cover_img"> </div>
                                    </div>
                                    <div class="col-5 text-start"> <button class="btn mb-1" id="btn_cover_wheel"><img
                                                decoding="async"
                                                src="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/images/cover-wheel.jpg"
                                                width="36" class="rounded-pill" alt="Cover wheel"></button> <label
                                            for="btn_cover_wheel">Ảnh nền vòng quay</label> </div>
                                </div>
                                <div class="mb-3 d-none" id="form_cover_img">
                                    <div class="input-group justify-content-center mb-3"> <input type="text"
                                            class="form-control" id="cover_img"
                                            value="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/images/cover-wheel.jpg"
                                            placeholder="https://example.com/cover800.jpg"> <span
                                            class="input-group-text"> <img decoding="async"
                                                src="https://vongquaymayman.co/wp-content/themes/twentytwentythree-child/assets/images/cover-wheel.jpg"
                                                width="36" height="36" id="cover_img_display" class="rounded-pill"
                                                alt="default cover image"> </span> <button class="btn btn-secondary"
                                            id="btn-select-cover-img">Chọn <svg xmlns="http://www.w3.org/2000/svg"
                                                width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" class="feather feather-chevron-down">
                                                <polyline points="6 9 12 15 18 9"></polyline>
                                            </svg></button> <span class="input-group-text"> <label
                                                for="upload_cover_img" id="btn_upload_cover_img"
                                                data-bs-toggle="tooltip"
                                                aria-label="Kích thước khuyên dùng: 800 x 800 (px)"
                                                data-bs-original-title="Kích thước khuyên dùng: 800 x 800 (px)"><svg
                                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                    class="feather feather-image">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                    <polyline points="21 15 16 10 5 21"></polyline>
                                                </svg></label> </span> <input type="file" id="upload_cover_img"
                                            data-maxsize="2" class="d-none" accept="image/*"> </div>
                                </div>
                                <div id="form_theme_color">
                                    <div class="dropdown mb-3" id="myDropdown"> <button type="button"
                                            class="btn btn-primary dropdown-toggle" id="btn-mydropdown"
                                            data-bs-toggle="dropdown" aria-expanded="false"
                                            data-bs-auto-close="outside"> Áp dụng một chủ đề </button>
                                        <div class="dropdown-menu"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3"><span>Hội màu sắc vòng quay</span></div>
                                    <div class="row g-1 g-lg-1 mb-3">
                                        <div class="col-2 col-lg-2">
                                            <div class="d-flex">
                                                <div class="form-check"> <input class="form-check-input" type="checkbox"
                                                        value="1" name="chkcolor" id="chkcolor-1" checked=""> </div>
                                                <input type="color"
                                                    class="form-control form-control-sm form-control-color m-0 p-0 border-0"
                                                    value="#D6392E" id="color-1">
                                            </div>
                                        </div>
                                        <div class="col-2 col-lg-2">
                                            <div class="d-flex">
                                                <div class="form-check"> <input class="form-check-input" type="checkbox"
                                                        value="2" name="chkcolor" id="chkcolor-2" checked=""> </div>
                                                <input type="color"
                                                    class="form-control form-control-sm form-control-color m-0 p-0 border-0"
                                                    value="#3369E8" id="color-2">
                                            </div>
                                        </div>
                                        <div class="col-2 col-lg-2">
                                            <div class="d-flex">
                                                <div class="form-check"> <input class="form-check-input" type="checkbox"
                                                        value="3" name="chkcolor" id="chkcolor-3" checked=""> </div>
                                                <input type="color"
                                                    class="form-control form-control-sm form-control-color m-0 p-0 border-0"
                                                    value="#4F9A29" id="color-3">
                                            </div>
                                        </div>
                                        <div class="col-2 col-lg-2">
                                            <div class="d-flex">
                                                <div class="form-check"> <input class="form-check-input" type="checkbox"
                                                        value="4" name="chkcolor" id="chkcolor-4" checked=""> </div>
                                                <input type="color"
                                                    class="form-control form-control-sm form-control-color m-0 p-0 border-0"
                                                    value="#EEB331" id="color-4">
                                            </div>
                                        </div>
                                        <div class="col-2 col-lg-2">
                                            <div class="d-flex">
                                                <div class="form-check"> <input class="form-check-input" type="checkbox"
                                                        value="5" name="chkcolor" id="chkcolor-5"> </div> <input
                                                    type="color"
                                                    class="form-control form-control-sm form-control-color m-0 p-0 border-0"
                                                    value="#ADB2B0" id="color-5">
                                            </div>
                                        </div>
                                        <div class="col-2 col-lg-2">
                                            <div class="d-flex">
                                                <div class="form-check"> <input class="form-check-input" type="checkbox"
                                                        value="6" name="chkcolor" id="chkcolor-6"> </div> <input
                                                    type="color"
                                                    class="form-control form-control-sm form-control-color m-0 p-0 border-0"
                                                    value="#ADB2B0" id="color-6">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <hr>

                            <div class="form-check mb-3"> <input class="form-check-input" type="checkbox"
                                    id="show_border" checked=""> <label class="form-check-label" for="show_border"> Viền
                                    kim cương </label> </div>
                            <div class="row " id="custom_border_color">
                                <div class="col-6">
                                    <div class="input-group mb-3"> <span class="input-group-text">Màu viền</span> <input
                                            type="color" class="form-control form-control-color" id="border_color"
                                            value="#FF4D00"> </div>
                                </div>
                                <div class="col-6">
                                    <div class="input-group mb-3"> <span class="input-group-text">Màu kim cương</span>
                                        <input type="color" class="form-control form-control-color" id="diamond_color"
                                            value="#F6FA00">
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                        <!-- text nút quay và ảnh nút quay -->
                        <fieldset class="border border-2 px-2">
                            <legend class="float-none w-auto p-2 fs-6 fw-bold">Nút quay <svg
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-chevron-down">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg></legend>

                            <!-- Text nút quay -->
                            <div class="input-group mb-3">
                                <span class="input-group-text">Text</span>
                                <input type="text" class="form-control" id="btn-spin-label" placeholder="Quay"
                                    value="Quay">
                                <button class="btn btn-outline-secondary" id="btn-apply-spin-label" type="button">Áp
                                    dụng</button>
                            </div>

                            <!-- Ảnh nút quay: 2 tab tách biệt -->
                            <label class="form-label small text-muted mb-1">Ảnh nút quay</label>
                            <ul class="nav nav-tabs nav-sm mb-2" id="spinImgTabs">
                                <li class="nav-item">
                                    <button class="nav-link active py-1 px-2 small" data-bs-toggle="tab"
                                        data-bs-target="#spin-tab-upload" type="button">
                                        ↑ Upload
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button class="nav-link py-1 px-2 small" data-bs-toggle="tab"
                                        data-bs-target="#spin-tab-url" type="button">
                                        🔗 URL
                                    </button>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <!-- Tab 1: Upload từ máy -->
                                <div class="tab-pane fade show active" id="spin-tab-upload">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <label for="upload_spin_bg" class="btn btn-outline-primary btn-sm mb-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                style="vertical-align:middle;margin-right:3px;">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                <polyline points="17 8 12 3 7 8"></polyline>
                                                <line x1="12" y1="3" x2="12" y2="15"></line>
                                            </svg>
                                            Chọn ảnh từ máy
                                        </label>
                                        <input type="file" id="upload_spin_bg" data-maxsize="2" class="d-none"
                                            accept="image/*">
                                        <span class="small text-muted" id="spin-upload-info">JPG/PNG/WebP ≤ 2MB</span>
                                    </div>
                                </div>
                                <!-- Tab 2: URL -->
                                <div class="tab-pane fade" id="spin-tab-url">
                                    <div class="input-group mb-2">
                                        <input type="url" class="form-control form-control-sm" id="btn-spin-img"
                                            value="" placeholder="https://example.com/nut.jpg">
                                        <button class="btn btn-outline-secondary btn-sm" id="btn-apply-spin-img"
                                            type="button">Áp dụng</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview chung (hiện khi có ảnh từ bất kỳ nguồn nào) -->
                            <div id="spin-img-preview-wrap" style="display:none;"
                                class="mb-1 d-flex align-items-center gap-3">
                                <img id="spin-img-preview" src="https://placehold.co/100x100" alt="preview" width="72"
                                    height="72" class="rounded-circle border border-2 shadow-sm"
                                    style="object-fit:cover;flex-shrink:0;">
                                <div>
                                    <div class="small text-muted mb-1" id="spin-img-preview-label">Đang dùng ảnh này
                                    </div>
                                    <button class="btn btn-outline-danger btn-sm py-0 px-2" id="btn-clear-spin-img"
                                        type="button">
                                        ✕ Xoá ảnh
                                    </button>
                                </div>
                            </div>
                        </fieldset>
                        <fieldset class="border border-2 px-2 mb-3">
                            <legend class="float-none w-auto p-2 fs-6 fw-bold">Body (nền trang) <svg
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-chevron-down">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg></legend>

                            <!-- Màu nền + màu chữ -->
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Màu nền</span>
                                        <input type="color" class="form-control form-control-color" id="custom-bg-color"
                                            value="#ffffff">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">Màu chữ</span>
                                        <input type="color" class="form-control form-control-color" id="custom-color"
                                            value="#000000">
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-outline-primary btn-sm w-100 mb-3" id="btn-apply-body-color">Áp dụng
                                màu</button>

                            <hr class="my-2">

                            <!-- Ảnh nền: upload hoặc URL -->
                            <div class="mb-1 small fw-semibold text-muted">Ảnh nền</div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <label for="upload_bgr" class="btn btn-outline-secondary btn-sm mb-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" style="vertical-align:middle;margin-right:3px;">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    Upload ảnh
                                </label>
                                <input type="file" id="upload_bgr" data-maxsize="5" class="d-none" accept="image/*">
                                <span class="small text-muted" id="bgr-upload-info">JPG/PNG/WebP &le; 5MB</span>
                            </div>
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text">URL</span>
                                <input type="url" class="form-control" id="custom-bg-img"
                                    placeholder="https://example.com/bg.jpg" value="">
                                <button class="btn btn-outline-secondary" id="btn-apply-body-img" type="button">Áp
                                    dụng</button>
                            </div>
                            <div id="bgr-preview-wrap" style="display:none;"
                                class="mb-2 d-flex align-items-center gap-2">
                                <img id="bgr-preview" src="https://placehold.co/100x100" alt="" width="60" height="40"
                                    style="object-fit:cover;border-radius:4px;border:1px solid #ccc;flex-shrink:0;">
                                <button class="btn btn-outline-danger btn-sm py-0 px-2" id="btn-clear-body-img"
                                    type="button">x Xoa</button>
                            </div>

                            <hr class="my-2">

                            <!-- Gradient -->
                            <div class="mb-1 small fw-semibold text-muted">Gradient</div>
                            <div class="d-flex flex-wrap gap-1 mb-2" id="gradient-swatches">
                                <div class="sw-gradient-swatch" title="Cam do"
                                    data-gradient="conic-gradient(from 135deg, rgb(255, 255, 196) 0deg, rgb(255, 255, 196) 27.692deg, rgb(255, 255, 181) 27.692deg, rgb(255, 255, 181) 55.385deg, rgb(255, 237, 165) 55.385deg, rgb(255, 237, 165) 83.077deg, rgb(255, 205, 149) 83.077deg, rgb(255, 205, 149) 110.769deg, rgb(255, 170, 133) 110.769deg, rgb(255, 170, 133) 138.462deg, rgb(255, 134, 116) 138.462deg, rgb(255, 134, 116) 166.154deg, rgb(255, 97, 100) 166.154deg, rgb(255, 97, 100) 193.846deg, rgb(245, 61, 85) 193.846deg, rgb(245, 61, 85) 221.538deg, rgb(233, 28, 69) 221.538deg, rgb(233, 28, 69) 249.231deg, rgb(220, 0, 55) 249.231deg, rgb(220, 0, 55) 276.923deg, rgb(206, 0, 42) 276.923deg, rgb(206, 0, 42) 304.615deg, rgb(192, 0, 29) 304.615deg, rgb(192, 0, 29) 332.308deg, rgb(176, 0, 18) 332.308deg, rgb(176, 0, 18) 360deg)"
                                    style="width:28px;height:28px;border-radius:50%;cursor:pointer;border:2px solid #dee2e6;background:conic-gradient(from 135deg, rgb(255, 255, 196) 0deg, rgb(255, 255, 196) 27.692deg, rgb(255, 255, 181) 27.692deg, rgb(255, 255, 181) 55.385deg, rgb(255, 237, 165) 55.385deg, rgb(255, 237, 165) 83.077deg, rgb(255, 205, 149) 83.077deg, rgb(255, 205, 149) 110.769deg, rgb(255, 170, 133) 110.769deg, rgb(255, 170, 133) 138.462deg, rgb(255, 134, 116) 138.462deg, rgb(255, 134, 116) 166.154deg, rgb(255, 97, 100) 166.154deg, rgb(255, 97, 100) 193.846deg, rgb(245, 61, 85) 193.846deg, rgb(245, 61, 85) 221.538deg, rgb(233, 28, 69) 221.538deg, rgb(233, 28, 69) 249.231deg, rgb(220, 0, 55) 249.231deg, rgb(220, 0, 55) 276.923deg, rgb(206, 0, 42) 276.923deg, rgb(206, 0, 42) 304.615deg, rgb(192, 0, 29) 304.615deg, rgb(192, 0, 29) 332.308deg, rgb(176, 0, 18) 332.308deg, rgb(176, 0, 18) 360deg)">
                                </div>
                                <div class="sw-gradient-swatch" title="Xanh bien"
                                    data-gradient="conic-gradient(from 90deg, rgb(164, 116, 81) 0deg, rgb(164, 116, 81) 27.692deg, rgb(164, 137, 107) 27.692deg, rgb(164, 137, 107) 55.385deg, rgb(156, 152, 129) 55.385deg, rgb(156, 152, 129) 83.077deg, rgb(139, 160, 147) 83.077deg, rgb(139, 160, 147) 110.769deg, rgb(115, 160, 157) 110.769deg, rgb(115, 160, 157) 138.462deg, rgb(88, 152, 160) 138.462deg, rgb(88, 152, 160) 166.154deg, rgb(59, 137, 154) 166.154deg, rgb(59, 137, 154) 193.846deg, rgb(32, 116, 141) 193.846deg, rgb(32, 116, 141) 221.538deg, rgb(9, 91, 121) 221.538deg, rgb(9, 91, 121) 249.231deg, rgb(0, 65, 97) 249.231deg, rgb(0, 65, 97) 276.923deg, rgb(0, 40, 71) 276.923deg, rgb(0, 40, 71) 304.615deg, rgb(0, 18, 45) 304.615deg, rgb(0, 18, 45) 332.308deg, rgb(0, 1, 22) 332.308deg, rgb(0, 1, 22) 360deg)"
                                    style="width:28px;height:28px;border-radius:50%;cursor:pointer;border:2px solid #dee2e6;background:conic-gradient(from 90deg, rgb(164, 116, 81) 0deg, rgb(164, 116, 81) 27.692deg, rgb(164, 137, 107) 27.692deg, rgb(164, 137, 107) 55.385deg, rgb(156, 152, 129) 55.385deg, rgb(156, 152, 129) 83.077deg, rgb(139, 160, 147) 83.077deg, rgb(139, 160, 147) 110.769deg, rgb(115, 160, 157) 110.769deg, rgb(115, 160, 157) 138.462deg, rgb(88, 152, 160) 138.462deg, rgb(88, 152, 160) 166.154deg, rgb(59, 137, 154) 166.154deg, rgb(59, 137, 154) 193.846deg, rgb(32, 116, 141) 193.846deg, rgb(32, 116, 141) 221.538deg, rgb(9, 91, 121) 221.538deg, rgb(9, 91, 121) 249.231deg, rgb(0, 65, 97) 249.231deg, rgb(0, 65, 97) 276.923deg, rgb(0, 40, 71) 276.923deg, rgb(0, 40, 71) 304.615deg, rgb(0, 18, 45) 304.615deg, rgb(0, 18, 45) 332.308deg, rgb(0, 1, 22) 332.308deg, rgb(0, 1, 22) 360deg)">
                                </div>
                                <div class="sw-gradient-swatch" title="Tim"
                                    data-gradient="conic-gradient(from 45deg, rgb(250, 218, 97) 0deg, rgb(250, 218, 97) 27.692deg, rgb(248, 210, 86) 27.692deg, rgb(248, 210, 86) 55.385deg, rgb(248, 199, 82) 55.385deg, rgb(248, 199, 82) 83.077deg, rgb(250, 187, 86) 83.077deg, rgb(250, 187, 86) 110.769deg, rgb(253, 173, 98) 110.769deg, rgb(253, 173, 98) 138.462deg, rgb(255, 159, 115) 138.462deg, rgb(255, 159, 115) 166.154deg, rgb(255, 145, 136) 166.154deg, rgb(255, 145, 136) 193.846deg, rgb(255, 131, 158) 193.846deg, rgb(255, 131, 158) 221.538deg, rgb(255, 118, 179) 221.538deg, rgb(255, 118, 179) 249.231deg, rgb(255, 108, 196) 249.231deg, rgb(255, 108, 196) 276.923deg, rgb(255, 99, 206) 276.923deg, rgb(255, 99, 206) 304.615deg, rgb(255, 93, 210) 304.615deg, rgb(255, 93, 210) 332.308deg, rgb(255, 90, 205) 332.308deg, rgb(255, 90, 205) 360deg)"
                                    style="width:28px;height:28px;border-radius:50%;cursor:pointer;border:2px solid #dee2e6;background:conic-gradient(from 45deg, rgb(250, 218, 97) 0deg, rgb(250, 218, 97) 27.692deg, rgb(248, 210, 86) 27.692deg, rgb(248, 210, 86) 55.385deg, rgb(248, 199, 82) 55.385deg, rgb(248, 199, 82) 83.077deg, rgb(250, 187, 86) 83.077deg, rgb(250, 187, 86) 110.769deg, rgb(253, 173, 98) 110.769deg, rgb(253, 173, 98) 138.462deg, rgb(255, 159, 115) 138.462deg, rgb(255, 159, 115) 166.154deg, rgb(255, 145, 136) 166.154deg, rgb(255, 145, 136) 193.846deg, rgb(255, 131, 158) 193.846deg, rgb(255, 131, 158) 221.538deg, rgb(255, 118, 179) 221.538deg, rgb(255, 118, 179) 249.231deg, rgb(255, 108, 196) 249.231deg, rgb(255, 108, 196) 276.923deg, rgb(255, 99, 206) 276.923deg, rgb(255, 99, 206) 304.615deg, rgb(255, 93, 210) 304.615deg, rgb(255, 93, 210) 332.308deg, rgb(255, 90, 205) 332.308deg, rgb(255, 90, 205) 360deg)">
                                </div>
                            </div>
                            <div class="d-flex gap-2 align-items-start mb-2">
                                <div id="gradient-preview-box"
                                    style="width:44px;height:44px;border-radius:6px;border:1px solid #ccc;flex-shrink:0;transition:.2s;background:conic-gradient(from 90deg,#df3000,#feb81a,#df3000);">
                                </div>
                                <textarea class="form-control form-control-sm" id="bg-gradient" rows="4"
                                    placeholder="Nhap CSS gradient (vd: conic-gradient(...))">conic-gradient(from 90deg,#df3000 0deg,#feb81a 180deg,#df3000 360deg)</textarea>
                            </div>
                            <button class="btn btn-outline-primary btn-sm w-100 mb-3" id="btn-apply-body-gradient">Ap
                                dung gradient</button>

                            <hr class="my-2">

                            <!-- Hieu ung -->
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" checked id="show_particle">
                                <label class="form-check-label" for="show_particle">Bat hieu ung <sup
                                        class="text-danger">Moi</sup></label>
                            </div>
                            <select class="form-select form-select-sm mb-1" id="particle_type">
                                <option value="default">Mac dinh</option>
                                <option value="snow">Tuyet roi</option>
                                <option value="bubble">Bong bong</option>
                                <option value="heart">Trai tim</option>
                            </select>
                            <div class="small text-muted mb-2">Nen phai khac mau trang moi thay hieu ung</div>
                        </fieldset>
                    </div>

                    <div class="tab-pane py-4 fade" id="media-tab-pane" role="tabpanel" aria-labelledby="media-tab"
                        tabindex="0">
                        <div id="uploaded-list" class="mb-3">
                            <ul class="nav nav-tabs" id="mediaTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="button-tab" data-bs-toggle="tab"
                                        data-bs-target="#button-pane" type="button" role="tab"
                                        aria-controls="button-pane" aria-selected="true">
                                        Nút quay
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="background-tab" data-bs-toggle="tab"
                                        data-bs-target="#background-pane" type="button" role="tab"
                                        aria-controls="background-pane" aria-selected="false">
                                        Nền
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="gradient-tab" data-bs-toggle="tab"
                                        data-bs-target="#gradient-pane" type="button" role="tab"
                                        aria-controls="gradient-pane" aria-selected="false">
                                        Gradient
                                    </button>
                                </li>
                            </ul>
                            <div class="tab-content" id="mediaTabsContent">
                                <div class="tab-pane fade show active" id="button-pane" role="tabpanel"
                                    aria-labelledby="button-tab" tabindex="0">
                                    <div class="mt-3" style="max-height: 350px; overflow-y: auto; overflow-x: hidden;">
                                        <table class="table table-striped" id="media-button">
                                            <thead>
                                                <tr>
                                                    <th>STT</th>
                                                    <th>Tên</th>
                                                    <th>Link / Preview</th>
                                                    <th>Đặt làm</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="mediaButtonBody">
                                                <?php
                                                $_btn_dir  = WP_SPIN_WHEEL_PATH . 'assets/buttons/';
                                                $_btn_url  = WP_SPIN_WHEEL_URL . 'assets/buttons/';
                                                $_btn_files = glob( $_btn_dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE );
                                                if ( ! empty( $_btn_files ) ) :
                                                    $_btn_i = 0;
                                                    foreach ( $_btn_files as $_btn_file ) :
                                                        $_btn_i++;
                                                        $_btn_name = pathinfo( $_btn_file, PATHINFO_FILENAME );
                                                        $_btn_file_url = $_btn_url . rawurlencode( basename( $_btn_file ) );
                                                ?>
                                                <tr>
                                                    <td><span class="badge bg-secondary"><?php echo esc_html( $_btn_i ); ?></span></td>
                                                    <td class="small"><?php echo esc_html( $_btn_name ); ?></td>
                                                    <td><img
                                                                src="<?php echo esc_url( $_btn_file_url ); ?>"
                                                                width="50" height="50"
                                                                class="border border-1 rounded-1"></td>
                                                    <td><button class="btn btn-sm btn-secondary sw-media-apply"
                                                            data-type="btn"
                                                            data-url="<?php echo esc_url( $_btn_file_url ); ?>">Nút
                                                            Quay</button></td>
                                                    <td>∗</td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php else : ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted small">Không có ảnh nút quay</td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="background-pane" role="tabpanel"
                                    aria-labelledby="background-tab" tabindex="0">

                                    <div class="mt-3" style="max-height: 350px; overflow-y: auto; overflow-x: hidden;">
                                        <table class="table table-striped" id="media-background">
                                            <thead>
                                                <tr>
                                                    <th>STT</th>
                                                    <th>Tên</th>
                                                    <th>Link / Preview</th>
                                                    <th>Đặt làm</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="mediaBackgroundBody">
                                                <?php
                                                $_bgr_dir  = WP_SPIN_WHEEL_PATH . 'assets/background/';
                                                $_bgr_url  = WP_SPIN_WHEEL_URL . 'assets/background/';
                                                $_bgr_files = glob( $_bgr_dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE );
                                                if ( ! empty( $_bgr_files ) ) :
                                                    $_bgr_i = 0;
                                                    foreach ( $_bgr_files as $_bgr_file ) :
                                                        $_bgr_i++;
                                                        $_bgr_name = pathinfo( $_bgr_file, PATHINFO_FILENAME );
                                                        $_bgr_file_url = $_bgr_url . rawurlencode( basename( $_bgr_file ) );
                                                ?>
                                                <tr>
                                                    <td><span class="badge bg-secondary"><?php echo esc_html( $_bgr_i ); ?></span></td>
                                                    <td class="small"><?php echo esc_html( $_bgr_name ); ?></td>
                                                    <td><img
                                                                src="<?php echo esc_url( $_bgr_file_url ); ?>"
                                                                width="50" height="50"
                                                                class="border border-1 rounded-1"></td>
                                                    <td><button class="btn btn-sm btn-secondary sw-media-apply"
                                                            data-type="bgr"
                                                            data-url="<?php echo esc_url( $_bgr_file_url ); ?>">Nền</button>
                                                    </td>
                                                    <td>∗</td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php else : ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted small">Không có ảnh nền</td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="gradient-pane" role="tabpanel"
                                    aria-labelledby="gradient-tab" tabindex="0">
                                    <div class="mt-3" style="max-height: 350px; overflow-y: auto; overflow-x: hidden;">
                                        <table class="table table-striped" id="media-gradient">
                                            <thead>
                                                <tr>
                                                    <th>STT</th>
                                                    <th>Tên</th>
                                                    <th>Link / Preview</th>
                                                    <th>Đặt làm</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="mediaGradientBody">
                                                <?php
                                                $_grd_file = WP_SPIN_WHEEL_PATH . 'assets/gradients.txt';
                                                $_grd_gradients = array();
                                                if ( file_exists( $_grd_file ) ) {
                                                    $_grd_lines = file( $_grd_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
                                                    if ( is_array( $_grd_lines ) ) {
                                                        $_grd_section = '';
                                                        foreach ( $_grd_lines as $_grd_line ) {
                                                            $_grd_line = trim( $_grd_line );
                                                            if ( '' === $_grd_line ) {
                                                                continue;
                                                            }
                                                            if ( preg_match( '/^\[(.+)\]$/', $_grd_line, $_grd_m ) ) {
                                                                $_grd_section = $_grd_m[1];
                                                                continue;
                                                            }
                                                            if ( strpos( $_grd_line, 'gradient(' ) !== false ) {
                                                                $_grd_gradients[] = array(
                                                                    'section' => $_grd_section,
                                                                    'value'   => $_grd_line,
                                                                );
                                                            }
                                                        }
                                                    }
                                                }
                                                if ( ! empty( $_grd_gradients ) ) :
                                                    $_grd_i = 0;
                                                    foreach ( $_grd_gradients as $_grd_item ) :
                                                        $_grd_i++;
                                                        $_grd_name = ( ! empty( $_grd_item['section'] ) ? $_grd_item['section'] . '-' : '' ) . $_grd_i;
                                                ?>
                                                <tr>
                                                    <td><span class="badge bg-secondary"><?php echo esc_html( $_grd_i ); ?></span></td>
                                                    <td class="small"><?php echo esc_html( $_grd_name ); ?></td>
                                                    <td>
                                                        <div class="sw-gradient-preview"
                                                            data-gradient="<?php echo esc_attr( $_grd_item['value'] ); ?>"
                                                            style="width:50px;height:50px;border:1px solid #ccc;background:<?php echo esc_attr( $_grd_item['value'] ); ?>">
                                                        </div>
                                                    </td>
                                                    <td><button class="btn btn-sm btn-secondary sw-media-apply"
                                                            data-type="grd"
                                                            data-gradient="<?php echo esc_attr( $_grd_item['value'] ); ?>">Nền</button>
                                                    </td>
                                                    <td>∗</td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php else : ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted small">Không có gradient</td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- option thay màu, bg -->
                </div>
                <div class="mb-3">
                    <button class="btn btn-primary w-100" id="btn_wheel_setting">Lưu lại</button>
                </div>
                <p class="text-center"><a href="javascript:void(0);" id="btn-reset-wheel"
                        class="link-secondary text-decoration-none">Reset về mặc định</a></p>
            </div>

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