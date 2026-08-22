<?php if (! defined('ABSPATH')) {
    exit;
} ?>
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
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                            </svg>
                            <span>Chung</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="appearance-tab" data-bs-toggle="tab" data-bs-target="#appearance-tab-pane" type="button" role="tab" aria-controls="appearance-tab-pane" aria-selected="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
                            </svg>
                            <span>Giao diện</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="media-tab" data-bs-toggle="tab" data-bs-target="#media-tab-pane" type="button" role="tab" aria-controls="media-tab-pane" aria-selected="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                            </svg>
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
                                            <!-- chỗ này trả ra danh sách tên nhạc và file nhạc -->
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
                            <button class="btn btn-outline-primary btn-sm w-100 mb-3" id="btn-apply-body-gradient">Áp dụng gradient</button>

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
                                                $_btn_files = glob($_btn_dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
                                                if (! empty($_btn_files)) :
                                                    $_btn_i = 0;
                                                    foreach ($_btn_files as $_btn_file) :
                                                        $_btn_i++;
                                                        $_btn_name = pathinfo($_btn_file, PATHINFO_FILENAME);
                                                        $_btn_file_url = $_btn_url . rawurlencode(basename($_btn_file));
                                                ?>
                                                        <tr>
                                                            <td><span class="badge bg-secondary"><?php echo esc_html($_btn_i); ?></span></td>
                                                            <td class="small"><?php echo esc_html($_btn_name); ?></td>
                                                            <td><img
                                                                    src="<?php echo esc_url($_btn_file_url); ?>"
                                                                    width="50" height="50"
                                                                    class="border border-1 rounded-1"></td>
                                                            <td><button class="btn btn-sm btn-secondary sw-media-apply"
                                                                    data-type="btn"
                                                                    data-url="<?php echo esc_url($_btn_file_url); ?>">Nút
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
                                                $_bgr_files = glob($_bgr_dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
                                                if (! empty($_bgr_files)) :
                                                    $_bgr_i = 0;
                                                    foreach ($_bgr_files as $_bgr_file) :
                                                        $_bgr_i++;
                                                        $_bgr_name = pathinfo($_bgr_file, PATHINFO_FILENAME);
                                                        $_bgr_file_url = $_bgr_url . rawurlencode(basename($_bgr_file));
                                                ?>
                                                        <tr>
                                                            <td><span class="badge bg-secondary"><?php echo esc_html($_bgr_i); ?></span></td>
                                                            <td class="small"><?php echo esc_html($_bgr_name); ?></td>
                                                            <td><img
                                                                    src="<?php echo esc_url($_bgr_file_url); ?>"
                                                                    width="50" height="50"
                                                                    class="border border-1 rounded-1"></td>
                                                            <td><button class="btn btn-sm btn-secondary sw-media-apply"
                                                                    data-type="bgr"
                                                                    data-url="<?php echo esc_url($_bgr_file_url); ?>">Nền</button>
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
                                                if (file_exists($_grd_file)) {
                                                    $_grd_lines = file($_grd_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                                                    if (is_array($_grd_lines)) {
                                                        $_grd_section = '';
                                                        foreach ($_grd_lines as $_grd_line) {
                                                            $_grd_line = trim($_grd_line);
                                                            if ('' === $_grd_line) {
                                                                continue;
                                                            }
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
                                                            <td><span class="badge bg-secondary"><?php echo esc_html($_grd_i); ?></span></td>
                                                            <td class="small"><?php echo esc_html($_grd_name); ?></td>
                                                            <td>
                                                                <div class="sw-gradient-preview"
                                                                    data-gradient="<?php echo esc_attr($_grd_item['value']); ?>"
                                                                    style="width:50px;height:50px;border:1px solid #ccc;background:<?php echo esc_attr($_grd_item['value']); ?>">
                                                                </div>
                                                            </td>
                                                            <td><button class="btn btn-sm btn-secondary sw-media-apply"
                                                                    data-type="grd"
                                                                    data-gradient="<?php echo esc_attr($_grd_item['value']); ?>">Nền</button>
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