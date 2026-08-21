<?php
if (! defined('ABSPATH')) {
    exit;
}

// Lấy option nhạc từ global settings và danh sách audio items
$_global_opts     = WP_Spin_Wheel_Helper::get_box_global_settings();
$_sound           = $_global_opts['sound'] ?? 'winner';
$_sound_file      = $_global_opts['sound_file'] ?? '';
$_noti_sound      = $_global_opts['noti_sound'] ?? 'concainit';
$_noti_sound_file = $_global_opts['noti_sound_file'] ?? '';
$_audios_open     = WP_Spin_Wheel_Helper::get_audio_library('start') ?: array();
$_audios_end      = WP_Spin_Wheel_Helper::get_audio_library('end') ?: array();
?>

<!-- Option cho Box -->
<div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2" id="settingsModalLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="feather feather-settings text-primary">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path
                            d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
                        </path>
                    </svg>
                    <?php esc_html_e('Cài đặt Hộp quà may mắn', 'wp-spin-wheel'); ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <ul class="nav nav-tabs" id="boxTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="box-gen-setting-tab" data-bs-toggle="tab"
                            data-bs-target="#box-gen-setting-tab-pane" type="button" role="tab"
                            aria-controls="box-gen-setting-tab-pane" aria-selected="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path
                                    d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
                                </path>
                            </svg>
                            <span><?php esc_html_e('Chung', 'wp-spin-wheel'); ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="box-appearance-tab" data-bs-toggle="tab"
                            data-bs-target="#box-appearance-tab-pane" type="button" role="tab"
                            aria-controls="box-appearance-tab-pane" aria-selected="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
                            </svg>
                            <span><?php esc_html_e('Giao diện', 'wp-spin-wheel'); ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="box-media-tab" data-bs-toggle="tab"
                            data-bs-target="#box-media-tab-pane" type="button" role="tab"
                            aria-controls="box-media-tab-pane" aria-selected="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z">
                                </path>
                            </svg>
                            <span><?php esc_html_e('Thư viện', 'wp-spin-wheel'); ?></span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="boxTabContent">
                    <!-- TAB 1: CHUNG -->
                    <div class="tab-pane fade pt-3 show active" id="box-gen-setting-tab-pane" role="tabpanel"
                        aria-labelledby="box-gen-setting-tab" tabindex="0">
                        <!-- Tiêu đề -->
                        <div class="mb-3">
                            <label for="hqmm-title"
                                class="form-label fw-bold"><?php esc_html_e('Tiêu đề hộp quà', 'wp-spin-wheel'); ?></label>
                            <input type="text" id="hqmm-title" class="form-control" value="HỘP QUÀ MAY MẮN ONLINE"
                                placeholder="HỘP QUÀ MAY MẮN ONLINE">
                        </div>

                        <!-- Danh sách phần thưởng -->
                        <div class="mb-3">
                            <label for="section-list"
                                class="form-label fw-bold"><?php esc_html_e('Danh sách phần thưởng (mỗi dòng 1 phần thưởng)', 'wp-spin-wheel'); ?></label>
                            <textarea id="section-list" class="form-control" rows="6"
                                placeholder="Mỗi dòng tương ứng với một phần thưởng" style="min-height: 200px">100k
Ốp lưng iphone
50k
Chúc bạn may mắn
200k
Bút Montblanc
Ví da 500k
Sổ tay
Gối tựa lưng
Bình giữ nhiệt
Ly sứ
Hộp đựng cơm</textarea>
                            <div class="form-text">
                                <?php esc_html_e('Các hộp quà khi mở sẽ bốc ngẫu nhiên một trong các phần quà trên.', 'wp-spin-wheel'); ?>
                            </div>
                        </div>

                        <!-- Số lượt mở -->
                        <div class="mb-3">
                            <label for="luotchoi"
                                class="form-label fw-bold"><?php esc_html_e('Số lượt mở tối đa', 'wp-spin-wheel'); ?></label>
                            <select class="form-select" id="luotchoi">
                                <?php for ($i = 1; $i <= 12; $i++) : ?>
                                <option value="<?php echo esc_attr($i); ?>" <?php selected($i, 3); ?>>
                                    <?php echo sprintf(esc_html__('%d lượt mở', 'wp-spin-wheel'), $i); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- ♪ Âm thanh khi mở -->
                        <div class="mb-3">
                            <label
                                class="form-label fw-bold"><?php esc_html_e('Âm thanh khi mở hộp', 'wp-spin-wheel'); ?></label>
                            <div class="input-group mb-1">
                                <span class="input-group-text">♪ Khi mở</span>
                                <select class="form-select" id="sound">
                                    <option value="0"><?php esc_html_e('Tắt tiếng', 'wp-spin-wheel'); ?></option>
                                    <option value="random"><?php esc_html_e('Ngẫu nhiên', 'wp-spin-wheel'); ?></option>
                                    <option value="winner" selected>
                                        <?php esc_html_e('Winner (Mặc định)', 'wp-spin-wheel'); ?></option>
                                    <option value="congratulation"><?php esc_html_e('Chúc mừng', 'wp-spin-wheel'); ?>
                                    </option>
                                    <option value="bell"><?php esc_html_e('Tiếng chuông', 'wp-spin-wheel'); ?></option>
                                    <option value="votay"><?php esc_html_e('Vỗ tay', 'wp-spin-wheel'); ?></option>
                                    <option value="phaohoano"><?php esc_html_e('Pháo hoa nổ', 'wp-spin-wheel'); ?>
                                    </option>
                                    <?php if (! empty($_audios_open)) : ?>
                                    <optgroup label="── Thư viện nhạc ──">
                                        <?php foreach ($_audios_open as $_ao) : ?>
                                        <option value="<?php echo esc_attr($_ao['id'] ?? ''); ?>"
                                            data-url="<?php echo esc_url($_ao['config']['file'] ?? ''); ?>">
                                            <?php echo esc_html($_ao['name'] ?? ''); ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <?php endif; ?>
                                </select>
                                <button type="button" class="btn btn-outline-secondary" id="btn-sound-play"
                                    title="Nghe thử">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                    </svg>
                                </button>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><a class="text-decoration-none" target="_blank"
                                        href="https://nhactik.com">♪ nhactik.com</a></span>
                                <input type="text" class="form-control" id="sound_file"
                                    placeholder="File ID (vd: 12345)">
                                <button type="button" class="btn btn-outline-secondary" id="btn-sound-file-play"
                                    title="Nghe thử nhactik">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- ♪ Âm thanh khi hết lượt -->
                        <div class="mb-3">
                            <label
                                class="form-label fw-bold"><?php esc_html_e('Âm thanh khi hết lượt', 'wp-spin-wheel'); ?></label>
                            <div class="input-group mb-1">
                                <span class="input-group-text">♪ Khi hết</span>
                                <select class="form-select" id="noti_sound">
                                    <option value="0"><?php esc_html_e('Tắt tiếng', 'wp-spin-wheel'); ?></option>
                                    <option value="random"><?php esc_html_e('Ngẫu nhiên', 'wp-spin-wheel'); ?></option>
                                    <option value="concainit" selected>
                                        <?php esc_html_e('Còn cái nịt', 'wp-spin-wheel'); ?></option>
                                    <option value="complete"><?php esc_html_e('Hoàn thành', 'wp-spin-wheel'); ?>
                                    </option>
                                    <option value="slot_end"><?php esc_html_e('Slot End', 'wp-spin-wheel'); ?></option>
                                    <option value="fanfare"><?php esc_html_e('Fanfare', 'wp-spin-wheel'); ?></option>
                                    <?php if (! empty($_audios_end)) : ?>
                                    <optgroup label="── Thư viện nhạc ──">
                                        <?php foreach ($_audios_end as $_ae) : ?>
                                        <option value="<?php echo esc_attr($_ae['id'] ?? ''); ?>"
                                            data-url="<?php echo esc_url($_ae['config']['file'] ?? ''); ?>">
                                            <?php echo esc_html($_ae['name'] ?? ''); ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <?php endif; ?>
                                </select>
                                <button type="button" class="btn btn-outline-secondary" id="btn-noti_sound-play"
                                    title="Nghe thử">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                    </svg>
                                </button>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><a class="text-decoration-none" target="_blank"
                                        href="https://nhactik.com">♪ nhactik.com</a></span>
                                <input type="text" class="form-control" id="noti_sound_file" placeholder="File ID">
                                <button type="button" class="btn btn-outline-secondary" id="btn-noti_sound-file-play"
                                    title="Nghe thử nhactik">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Tiêu đề Popup & Hoa giấy -->
                        <div class="mb-3">
                            <label for="popup_title"
                                class="form-label fw-bold"><?php esc_html_e('Tiêu đề popup trúng thưởng', 'wp-spin-wheel'); ?></label>
                            <input type="text" class="form-control" id="popup_title" value="Hộp quà có"
                                placeholder="Hộp quà có">
                        </div>

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="confetti" checked>
                            <label class="form-check-label fw-semibold"
                                for="confetti"><?php esc_html_e('Bắn hoa giấy khi mở trúng hộp quà', 'wp-spin-wheel'); ?></label>
                        </div>
                    </div>

                    <!-- TAB 2: GIAO DIỆN -->
                    <div class="tab-pane fade pt-3" id="box-appearance-tab-pane" role="tabpanel"
                        aria-labelledby="box-appearance-tab" tabindex="0">
                        <!-- Chọn chủ đề mẫu -->
                        <fieldset class="border border-2 rounded-3 p-3 mb-3">
                            <legend class="float-none w-auto px-2 fs-6 fw-bold">
                                <?php esc_html_e('Chủ đề mẫu Hộp quà', 'wp-spin-wheel'); ?></legend>
                            <div class="mb-3">
                                <label
                                    class="form-label fw-bold"><?php esc_html_e('Chọn kiểu mẫu:', 'wp-spin-wheel'); ?></label>
                                <input type="hidden" id="template" value="tpl-default">
                                <div class="dropdown" id="boxTemplateDropdown">
                                    <button
                                        class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-between dropdown-toggle"
                                        id="btn-dropdown-select-tpl" type="button" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fs-5">🎁</span>
                                            <span class="item-title fw-bold">Hộp quà truyền thống (Mặc định)</span>
                                        </div>
                                    </button>
                                    <div class="dropdown-menu w-100 shadow-lg" id="btn-select-tpl"
                                        style="max-height: 280px; overflow-y: auto;">
                                        <div class="dropdown-item py-2 cursor-pointer" data-content="tpl-default"
                                            data-title="Hộp quà truyền thống"><span class="me-2">🎁</span> Hộp quà
                                            truyền thống (Mặc định)</div>
                                        <div class="dropdown-item py-2 cursor-pointer" data-content="tpl-jib"
                                            data-title="Ball in box"><span class="me-2">📦</span> Ball in box</div>
                                        <div class="dropdown-item py-2 cursor-pointer" data-content="tpl-christmas"
                                            data-title="Giáng sinh Christmas"><span class="me-2">🎄</span> Giáng sinh
                                            Christmas</div>
                                        <div class="dropdown-item py-2 cursor-pointer" data-content="tpl-money-bag"
                                            data-title="Túi tiền tài lộc"><span class="me-2">💰</span> Túi tiền tài lộc
                                        </div>
                                        <div class="dropdown-item py-2 cursor-pointer" data-content="tpl-blind-bag"
                                            data-title="Túi mù bí ẩn"><span class="me-2">🛍️</span> Túi mù bí ẩn</div>
                                        <div class="dropdown-item py-2 cursor-pointer" data-content="tpl-money"
                                            data-title="Bao lì xì Tết"><span class="me-2">🧧</span> Bao lì xì Tết</div>
                                        <div class="dropdown-item py-2 cursor-pointer" data-content="tpl-egg"
                                            data-title="Đập trứng vàng"><span class="me-2">🥚</span> Đập trứng vàng
                                        </div>
                                        <div class="dropdown-item py-2 cursor-pointer" data-content="tpl-jar"
                                            data-title="Đập lu đất"><span class="me-2">🏺</span> Đập lu đất</div>
                                        <div class="dropdown-item py-2 cursor-pointer" data-content="tpl-rat"
                                            data-title="Đập chuột may mắn"><span class="me-2">🐭</span> Đập chuột may
                                            mắn</div>
                                        <div class="dropdown-item py-2 cursor-pointer" data-content="tpl-ghost"
                                            data-title="Halloween Diệt ma"><span class="me-2">👻</span> Halloween Diệt
                                            ma</div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Màu sắc & Nút -->
                        <fieldset class="border border-2 rounded-3 p-3 mb-3">
                            <legend class="float-none w-auto px-2 fs-6 fw-bold">
                                <?php esc_html_e('Màu sắc giao diện', 'wp-spin-wheel'); ?></legend>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label
                                        class="form-label small fw-bold text-muted"><?php esc_html_e('Màu nền', 'wp-spin-wheel'); ?></label>
                                    <div class="input-group input-group-sm">
                                        <span
                                            class="input-group-text"><?php esc_html_e('Nền', 'wp-spin-wheel'); ?></span>
                                        <input type="color" class="form-control form-control-color" id="bg_color"
                                            value="#dc3545">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label
                                        class="form-label small fw-bold text-muted"><?php esc_html_e('Màu chữ', 'wp-spin-wheel'); ?></label>
                                    <div class="input-group input-group-sm">
                                        <span
                                            class="input-group-text"><?php esc_html_e('Chữ', 'wp-spin-wheel'); ?></span>
                                        <input type="color" class="form-control form-control-color" id="color"
                                            value="#ffffff">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label
                                        class="form-label small fw-bold text-muted"><?php esc_html_e('Màu nút bấm', 'wp-spin-wheel'); ?></label>
                                    <div class="input-group input-group-sm">
                                        <span
                                            class="input-group-text"><?php esc_html_e('Nút', 'wp-spin-wheel'); ?></span>
                                        <input type="color" class="form-control form-control-color" id="btn_bg_color"
                                            value="#dc3545">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label
                                        class="form-label small fw-bold text-muted"><?php esc_html_e('Màu chữ nút', 'wp-spin-wheel'); ?></label>
                                    <div class="input-group input-group-sm">
                                        <span
                                            class="input-group-text"><?php esc_html_e('Chữ nút', 'wp-spin-wheel'); ?></span>
                                        <input type="color" class="form-control form-control-color" id="btn_color"
                                            value="#ffffff">
                                    </div>
                                </div>
                            </div>

                            <hr class="my-2">

                            <!-- Ảnh nền -->
                            <div class="mb-1 small fw-semibold text-muted">
                                <?php esc_html_e('Ảnh nền', 'wp-spin-wheel'); ?></div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <label for="upload_box_bgr" class="btn btn-outline-secondary btn-sm mb-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" style="vertical-align:middle;margin-right:3px;">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    <?php esc_html_e('Upload ảnh', 'wp-spin-wheel'); ?>
                                </label>
                                <input type="file" id="upload_box_bgr" data-maxsize="5" class="d-none" accept="image/*">
                                <span class="small text-muted" id="box-bgr-upload-info">JPG/PNG/WebP &le; 5MB</span>
                            </div>
                            <div class="input-group input-group-sm mb-2">
                                <span class="input-group-text">URL</span>
                                <input type="url" class="form-control" id="bg_img"
                                    placeholder="https://example.com/bg.jpg" value="">
                                <button class="btn btn-outline-secondary" id="btn-apply-box-img"
                                    type="button"><?php esc_html_e('Áp dụng', 'wp-spin-wheel'); ?></button>
                            </div>
                            <div id="box-bgr-preview-wrap" style="display:none;"
                                class="mb-2 d-flex align-items-center gap-2">
                                <img id="box-bgr-preview" src="https://placehold.co/100x100" alt="" width="60"
                                    height="40"
                                    style="object-fit:cover;border-radius:4px;border:1px solid #ccc;flex-shrink:0;">
                                <button class="btn btn-outline-danger btn-sm py-0 px-2" id="btn-clear-box-img"
                                    type="button">✕ <?php esc_html_e('Xoá ảnh', 'wp-spin-wheel'); ?></button>
                            </div>

                            <hr class="my-2">

                            <!-- Gradient -->
                            <div class="mb-1 small fw-semibold text-muted">
                                <?php esc_html_e('Gradient', 'wp-spin-wheel'); ?></div>
                            <div class="d-flex flex-wrap gap-1 mb-2" id="box-gradient-swatches">
                                <div class="sw-box-gradient-swatch" title="Cam đỏ"
                                    data-gradient="conic-gradient(from 135deg, rgb(255, 255, 196) 0deg, rgb(255, 255, 196) 27.692deg, rgb(255, 255, 181) 27.692deg, rgb(255, 255, 181) 55.385deg, rgb(255, 237, 165) 55.385deg, rgb(255, 237, 165) 83.077deg, rgb(255, 205, 149) 83.077deg, rgb(255, 205, 149) 110.769deg, rgb(255, 170, 133) 110.769deg, rgb(255, 170, 133) 138.462deg, rgb(255, 134, 116) 138.462deg, rgb(255, 134, 116) 166.154deg, rgb(255, 97, 100) 166.154deg, rgb(255, 97, 100) 193.846deg, rgb(245, 61, 85) 193.846deg, rgb(245, 61, 85) 221.538deg, rgb(233, 28, 69) 221.538deg, rgb(233, 28, 69) 249.231deg, rgb(220, 0, 55) 249.231deg, rgb(220, 0, 55) 276.923deg, rgb(206, 0, 42) 276.923deg, rgb(206, 0, 42) 304.615deg, rgb(192, 0, 29) 304.615deg, rgb(192, 0, 29) 332.308deg, rgb(176, 0, 18) 332.308deg, rgb(176, 0, 18) 360deg)"
                                    style="width:28px;height:28px;border-radius:50%;cursor:pointer;border:2px solid #dee2e6;background:conic-gradient(from 135deg, rgb(255, 255, 196) 0deg, rgb(255, 255, 196) 27.692deg, rgb(255, 255, 181) 27.692deg, rgb(255, 255, 181) 55.385deg, rgb(255, 237, 165) 55.385deg, rgb(255, 237, 165) 83.077deg, rgb(255, 205, 149) 83.077deg, rgb(255, 205, 149) 110.769deg, rgb(255, 170, 133) 110.769deg, rgb(255, 170, 133) 138.462deg, rgb(255, 134, 116) 138.462deg, rgb(255, 134, 116) 166.154deg, rgb(255, 97, 100) 166.154deg, rgb(255, 97, 100) 193.846deg, rgb(245, 61, 85) 193.846deg, rgb(245, 61, 85) 221.538deg, rgb(233, 28, 69) 221.538deg, rgb(233, 28, 69) 249.231deg, rgb(220, 0, 55) 249.231deg, rgb(220, 0, 55) 276.923deg, rgb(206, 0, 42) 276.923deg, rgb(206, 0, 42) 304.615deg, rgb(192, 0, 29) 304.615deg, rgb(192, 0, 29) 332.308deg, rgb(176, 0, 18) 332.308deg, rgb(176, 0, 18) 360deg)">
                                </div>
                                <div class="sw-box-gradient-swatch" title="Xanh biển"
                                    data-gradient="conic-gradient(from 90deg, rgb(164, 116, 81) 0deg, rgb(164, 116, 81) 27.692deg, rgb(164, 137, 107) 27.692deg, rgb(164, 137, 107) 55.385deg, rgb(156, 152, 129) 55.385deg, rgb(156, 152, 129) 83.077deg, rgb(139, 160, 147) 83.077deg, rgb(139, 160, 147) 110.769deg, rgb(115, 160, 157) 110.769deg, rgb(115, 160, 157) 138.462deg, rgb(88, 152, 160) 138.462deg, rgb(88, 152, 160) 166.154deg, rgb(59, 137, 154) 166.154deg, rgb(59, 137, 154) 193.846deg, rgb(32, 116, 141) 193.846deg, rgb(32, 116, 141) 221.538deg, rgb(9, 91, 121) 221.538deg, rgb(9, 91, 121) 249.231deg, rgb(0, 65, 97) 249.231deg, rgb(0, 65, 97) 276.923deg, rgb(0, 40, 71) 276.923deg, rgb(0, 40, 71) 304.615deg, rgb(0, 18, 45) 304.615deg, rgb(0, 18, 45) 332.308deg, rgb(0, 1, 22) 332.308deg, rgb(0, 1, 22) 360deg)"
                                    style="width:28px;height:28px;border-radius:50%;cursor:pointer;border:2px solid #dee2e6;background:conic-gradient(from 90deg, rgb(164, 116, 81) 0deg, rgb(164, 116, 81) 27.692deg, rgb(164, 137, 107) 27.692deg, rgb(164, 137, 107) 55.385deg, rgb(156, 152, 129) 55.385deg, rgb(156, 152, 129) 83.077deg, rgb(139, 160, 147) 83.077deg, rgb(139, 160, 147) 110.769deg, rgb(115, 160, 157) 110.769deg, rgb(115, 160, 157) 138.462deg, rgb(88, 152, 160) 138.462deg, rgb(88, 152, 160) 166.154deg, rgb(59, 137, 154) 166.154deg, rgb(59, 137, 154) 193.846deg, rgb(32, 116, 141) 193.846deg, rgb(32, 116, 141) 221.538deg, rgb(9, 91, 121) 221.538deg, rgb(9, 91, 121) 249.231deg, rgb(0, 65, 97) 249.231deg, rgb(0, 65, 97) 276.923deg, rgb(0, 40, 71) 276.923deg, rgb(0, 40, 71) 304.615deg, rgb(0, 18, 45) 304.615deg, rgb(0, 18, 45) 332.308deg, rgb(0, 1, 22) 332.308deg, rgb(0, 1, 22) 360deg)">
                                </div>
                                <div class="sw-box-gradient-swatch" title="Tím hồng"
                                    data-gradient="conic-gradient(from 45deg, rgb(250, 218, 97) 0deg, rgb(250, 218, 97) 27.692deg, rgb(248, 210, 86) 27.692deg, rgb(248, 210, 86) 55.385deg, rgb(248, 199, 82) 55.385deg, rgb(248, 199, 82) 83.077deg, rgb(250, 187, 86) 83.077deg, rgb(250, 187, 86) 110.769deg, rgb(253, 173, 98) 110.769deg, rgb(253, 173, 98) 138.462deg, rgb(255, 159, 115) 138.462deg, rgb(255, 159, 115) 166.154deg, rgb(255, 145, 136) 166.154deg, rgb(255, 145, 136) 193.846deg, rgb(255, 131, 158) 193.846deg, rgb(255, 131, 158) 221.538deg, rgb(255, 118, 179) 221.538deg, rgb(255, 118, 179) 249.231deg, rgb(255, 108, 196) 249.231deg, rgb(255, 108, 196) 276.923deg, rgb(255, 99, 206) 276.923deg, rgb(255, 99, 206) 304.615deg, rgb(255, 93, 210) 304.615deg, rgb(255, 93, 210) 332.308deg, rgb(255, 90, 205) 332.308deg, rgb(255, 90, 205) 360deg)"
                                    style="width:28px;height:28px;border-radius:50%;cursor:pointer;border:2px solid #dee2e6;background:conic-gradient(from 45deg, rgb(250, 218, 97) 0deg, rgb(250, 218, 97) 27.692deg, rgb(248, 210, 86) 27.692deg, rgb(248, 210, 86) 55.385deg, rgb(248, 199, 82) 55.385deg, rgb(248, 199, 82) 83.077deg, rgb(250, 187, 86) 83.077deg, rgb(250, 187, 86) 110.769deg, rgb(253, 173, 98) 110.769deg, rgb(253, 173, 98) 138.462deg, rgb(255, 159, 115) 138.462deg, rgb(255, 159, 115) 166.154deg, rgb(255, 145, 136) 166.154deg, rgb(255, 145, 136) 193.846deg, rgb(255, 131, 158) 193.846deg, rgb(255, 131, 158) 221.538deg, rgb(255, 118, 179) 221.538deg, rgb(255, 118, 179) 249.231deg, rgb(255, 108, 196) 249.231deg, rgb(255, 108, 196) 276.923deg, rgb(255, 99, 206) 276.923deg, rgb(255, 99, 206) 304.615deg, rgb(255, 93, 210) 304.615deg, rgb(255, 93, 210) 332.308deg, rgb(255, 90, 205) 332.308deg, rgb(255, 90, 205) 360deg)">
                                </div>
                            </div>
                            <div class="d-flex gap-2 align-items-start mb-2">
                                <div id="box-gradient-preview-box"
                                    style="width:44px;height:44px;border-radius:6px;border:1px solid #ccc;flex-shrink:0;transition:.2s;background:conic-gradient(from 90deg,#df3000,#feb81a,#df3000);">
                                </div>
                                <textarea class="form-control form-control-sm" id="bg_gradient" rows="3"
                                    placeholder="Nhập CSS gradient (vd: conic-gradient(...) hoặc linear-gradient(...))"
                                    style="min-height: 100px"></textarea>
                            </div>
                            <button class="btn btn-outline-primary btn-sm w-100 mb-3"
                                id="btn-apply-box-gradient"><?php esc_html_e('Áp dụng gradient', 'wp-spin-wheel'); ?></button>

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="box_show_particle" checked>
                                <label class="form-check-label small"
                                    for="box_show_particle"><?php esc_html_e('Hiệu ứng hạt nền chuyển động', 'wp-spin-wheel'); ?></label>
                            </div>
                        </fieldset>
                    </div>

                    <!-- TAB 3: THƯ VIỆN -->
                    <div class="tab-pane fade pt-3" id="box-media-tab-pane" role="tabpanel"
                        aria-labelledby="box-media-tab" tabindex="0">
                        <div id="box-uploaded-list" class="mb-3">
                            <ul class="nav nav-tabs" id="boxMediaTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="box-background-tab" data-bs-toggle="tab"
                                        data-bs-target="#box-background-pane" type="button" role="tab"
                                        aria-controls="box-background-pane" aria-selected="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="me-1">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                            <polyline points="21 15 16 10 5 21"></polyline>
                                        </svg>
                                        <?php esc_html_e('Ảnh nền', 'wp-spin-wheel'); ?>
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="box-gradient-tab" data-bs-toggle="tab"
                                        data-bs-target="#box-gradient-pane" type="button" role="tab"
                                        aria-controls="box-gradient-pane" aria-selected="false">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="me-1">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <path d="M12 2a10 10 0 0 1 0 20z"></path>
                                        </svg>
                                        <?php esc_html_e('Gradient', 'wp-spin-wheel'); ?>
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="boxMediaTabsContent">
                                <!-- Subtab 1: Ảnh nền -->
                                <div class="tab-pane fade show active" id="box-background-pane" role="tabpanel"
                                    aria-labelledby="box-background-tab" tabindex="0">
                                    <div class="mt-3" style="max-height: 350px; overflow-y: auto; overflow-x: hidden;">
                                        <table class="table table-striped align-middle" id="media-box-background">
                                            <thead>
                                                <tr>
                                                    <th style="width: 50px;">STT</th>
                                                    <th>Tên</th>
                                                    <th style="width: 80px;">Preview</th>
                                                    <th style="width: 100px;">Đặt làm</th>
                                                    <th style="width: 30px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="mediaBoxBackgroundBody">
                                                <?php
                                                $_bgr_dir   = WP_SPIN_WHEEL_PATH . 'assets/background/';
                                                $_bgr_url   = WP_SPIN_WHEEL_URL . 'assets/background/';
                                                $_bgr_files = glob($_bgr_dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
                                                if (! empty($_bgr_files)) :
                                                    $_bgr_i = 0;
                                                    foreach ($_bgr_files as $_bgr_file) :
                                                        $_bgr_i++;
                                                        $_bgr_name     = pathinfo($_bgr_file, PATHINFO_FILENAME);
                                                        $_bgr_file_url = $_bgr_url . rawurlencode(basename($_bgr_file));
                                                ?>
                                                <tr>
                                                    <td><span
                                                            class="badge bg-secondary"><?php echo esc_html($_bgr_i); ?></span>
                                                    </td>
                                                    <td class="small fw-semibold"><?php echo esc_html($_bgr_name); ?>
                                                    </td>
                                                    <td><img src="<?php echo esc_url($_bgr_file_url); ?>" width="50"
                                                            height="50" class="border border-1 rounded-1"
                                                            style="object-fit:cover;"></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-secondary sw-box-media-apply"
                                                            data-type="bgr"
                                                            data-url="<?php echo esc_url($_bgr_file_url); ?>"><?php esc_html_e('Nền', 'wp-spin-wheel'); ?></button>
                                                    </td>
                                                    <td class="text-muted">∗</td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php else : ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted small">
                                                        <?php esc_html_e('Không có ảnh nền', 'wp-spin-wheel'); ?></td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Subtab 2: Gradient -->
                                <div class="tab-pane fade" id="box-gradient-pane" role="tabpanel"
                                    aria-labelledby="box-gradient-tab" tabindex="0">
                                    <div class="mt-3" style="max-height: 350px; overflow-y: auto; overflow-x: hidden;">
                                        <table class="table table-striped align-middle" id="media-box-gradient">
                                            <thead>
                                                <tr>
                                                    <th style="width: 50px;">STT</th>
                                                    <th>Tên</th>
                                                    <th style="width: 80px;">Preview</th>
                                                    <th style="width: 100px;">Đặt làm</th>
                                                    <th style="width: 30px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="mediaBoxGradientBody">
                                                <?php
                                                $_grd_file      = WP_SPIN_WHEEL_PATH . 'assets/gradients.txt';
                                                $_grd_gradients = array();
                                                if (file_exists($_grd_file)) {
                                                    $_grd_lines = file($_grd_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                                                    if (is_array($_grd_lines)) {
                                                        $_grd_section = '';
                                                        foreach ($_grd_lines as $_grd_line) {
                                                            $_grd_line = trim($_grd_line);
                                                            if ('' === $_grd_line) continue;
                                                            if (preg_match('/^\[(.+)\]$/', $_grd_line, $_grd_m)) {
                                                                $_grd_section = $_grd_m[1];
                                                                continue;
                                                            }
                                                            if (strpos($_grd_line, 'gradient(') !== false) {
                                                                $_grd_gradients[] = array(
                                                                    'section' => $_grd_section,
                                                                    'value'   => $_grd_line,
                                                                );
                                                            }
                                                        }
                                                    }
                                                }
                                                if (! empty($_grd_gradients)) :
                                                    $_grd_i = 0;
                                                    foreach ($_grd_gradients as $_grd_item) :
                                                        $_grd_i++;
                                                        $_grd_name = (! empty($_grd_item['section']) ? $_grd_item['section'] . '-' : '') . $_grd_i;
                                                ?>
                                                <tr>
                                                    <td><span
                                                            class="badge bg-secondary"><?php echo esc_html($_grd_i); ?></span>
                                                    </td>
                                                    <td class="small fw-semibold"><?php echo esc_html($_grd_name); ?>
                                                    </td>
                                                    <td>
                                                        <div class="sw-gradient-preview"
                                                            data-gradient="<?php echo esc_attr($_grd_item['value']); ?>"
                                                            style="width:50px;height:50px;border:1px solid #ccc;border-radius:4px;background:<?php echo esc_attr($_grd_item['value']); ?>;">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <button class="btn btn-sm btn-secondary sw-box-media-apply"
                                                            data-type="grd"
                                                            data-gradient="<?php echo esc_attr($_grd_item['value']); ?>"><?php esc_html_e('Nền', 'wp-spin-wheel'); ?></button>
                                                    </td>
                                                    <td class="text-muted">∗</td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php else : ?>
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted small">
                                                        <?php esc_html_e('Không có gradient', 'wp-spin-wheel'); ?></td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- modal-body -->

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm"
                    id="btn-reset-box"><?php esc_html_e('Đặt lại', 'wp-spin-wheel'); ?></button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary btn-sm"
                        data-bs-dismiss="modal"><?php esc_html_e('Đóng', 'wp-spin-wheel'); ?></button>
                    <button type="button" class="btn btn-primary btn-sm px-3"
                        id="btn_box_setting"><?php esc_html_e('Lưu lại', 'wp-spin-wheel'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>