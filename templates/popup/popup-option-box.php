<?php if (! defined('ABSPATH')) {
    exit;
} ?>
<!-- Option cho box -->
<div class="modal" id="settingsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="settingsModal" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-settings" style="width: 24px;height: 24px;">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path
                            d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
                        </path>
                    </svg> Cài đặt</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="general-tab" data-bs-toggle="tab"
                            data-bs-target="#general-tab-pane" type="button" role="tab" aria-controls="general-tab-pane"
                            aria-selected="false" tabindex="-1">Chung</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="style-tab" data-bs-toggle="tab"
                            data-bs-target="#style-tab-pane" type="button" role="tab" aria-controls="style-tab-pane"
                            aria-selected="true">Giao diện</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="media-tab" data-bs-toggle="tab" data-bs-target="#media-tab-pane"
                            type="button" role="tab" aria-controls="media-tab-pane" aria-selected="false"
                            tabindex="-1">Thư viện</button>
                    </li>
                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane pt-3 fade" id="general-tab-pane" role="tabpanel" aria-labelledby="general-tab"
                        tabindex="0">
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">Tiêu đề</div>
                            <div class="col-md-9">
                                <div class="mb-2"><input type="text" id="hqmm-title" class="form-control"
                                        value="HỘP QUÀ MAY MẮN ONLINE" placeholder="HỘP QUÀ MAY MẮN ONLINE"></div>
                                <div class="text-muted small">➤ Vào <a
                                        href="https://kitudep.pro/?utm_source=vongquaymayman.co&amp;utm_medium=HQMM"
                                        class="link-primary" target="_blank">kitudep.pro</a> để tạo tiêu đề đẹp hơn.
                                </div>
                            </div>
                            <div class="col-md-3">Phần thưởng</div>
                            <div class="col-md-9">
                                <textarea id="section-list" class="form-control" cols="30" rows="8"
                                    placeholder="Mỗi dòng tương ứng với một phần thưởng">100k
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
Hộp đựng cơm
</textarea>
                            </div>

                            <div class="col-md-3">Lượt chơi</div>
                            <div class="col-md-9">
                                <select class="form-select" aria-label="Số lần chơi tối đa" id="luotchoi">
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3" selected="">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                    <option value="11">11</option>
                                    <option value="12">12</option>
                                </select>
                            </div>

                            <div class="col-md-3">Âm thanh</div>
                            <div class="col-md-9">
                                <div class="group-or">
                                    <span class="or">Hoặc</span>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text">♪ Khi mở</span>
                                        <select class="form-select" id="sound">
                                            <optgroup label="Hiệu ứng âm thanh">
                                                <option value="0" selected="">Tắt tiếng</option>
                                                <option value="random">Ngẫu nhiên</option>
                                                <option value="read">Đọc kết quả khi kết thúc</option>
                                                <option value="slot_end">Slot end</option>
                                            </optgroup>
                                            <optgroup label="Chúc mừng">
                                                <option value="congratulation">Chúc mừng</option>
                                                <option value="votay">Vỗ tay</option>
                                                <option value="phaohoano">Pháo hoa nổ</option>
                                                <option value="chuc-tet">Chúc Tết</option>
                                                <option value="winner" selected="">Winner</option>
                                                <option value="complete">Complete</option>
                                                <option value="start">Start</option>
                                                <option value="olympia-trao-giai-1">Olympia trao giải 1</option>
                                                <option value="olympia-trao-giai-2">Olympia trao giải 2</option>
                                                <option value="olympia-trao-giai-3">Olympia trao giải 3</option>
                                                <option value="olympia-trao-giai-4">Olympia trao giải 4</option>
                                                <option value="ailatrieuphu_dung">Ai là triệu phú (Đúng)</option>
                                                <option value="male-carnival-shout-1">Hò reo kiểu phê</option>
                                                <option value="children-loud-hooray">Children hooray</option>
                                                <option value="hooray">Hooray</option>
                                                <option value="military-trumpet">Kèn Quân đội</option>
                                                <option value="fanfare">Fanfare</option>
                                            </optgroup>
                                            <optgroup label="Trả lời đúng">
                                                <option value="correct-answer-ding-4">Trả lời đúng</option>
                                                <option value="10diem">10 điểm</option>
                                                <option value="ghechua">Ghê chưa, ghê chưa</option>
                                                <option value="hetnuoccham">Hết nước chấm</option>
                                                <option value="votayPewPew">Vỗ tay Pewpew</option>
                                                <option value="de-che-chien-thang">Đế chế chiến thắng</option>
                                            </optgroup>
                                            <optgroup label="Trả lời sai">
                                                <option value="oh_thatvong">Ôh thất vọng</option>
                                                <option value="thatvong">Thất vọng</option>
                                                <option value="traloisai">Trả lời sai</option>
                                                <option value="concainit">Còn cái nịt</option>
                                                <option value="troioi_baotapmuaxa">Trời ơi, bão táp mưa xa</option>
                                                <option value="troioi_bayoi">Trời ơi, bay ơi bay</option>
                                                <option value="cai_gi_vay_banoi">Cái gì vậy bà nội</option>
                                                <option value="chetmeroi">Chết mẹ, dĩnh bẫy rồi</option>
                                                <option value="cay-the-nho">Cay thế nhỉ</option>
                                                <option value="conginuadau">Còn gì nữa đâu mà khóc…</option>
                                                <option value="cartoon-failure-3">Sad trombone</option>
                                                <option value="game-over-losin">Game over losin</option>
                                                <option value="huge-thunder">Sấm sét</option>
                                                <option value="ailatrieuphu_sai">Ai là triệu phú (Sai)</option>
                                                <option value="saomadoduoc">Sao mà đỡ được</option>
                                                <option value="deohieuduoc">Đ..éo thể hiểu được</option>
                                                <option value="xaochoPewPew">Xạo chó PewPew</option>
                                                <option value="deotin">Đ..éo tin</option>
                                                <option value="oi-doi-oi">Ối dồi ôi</option>
                                                <option value="do-ngu">Đồ ngu, đồ ăn hại, cút</option>
                                                <option value="ngu-thi-chet">Ngu thi chết, Huấn Hoa Hồng</option>
                                                <option value="hon-70-tuoi">Tôi năm nay hơn 70 tuổi</option>
                                                <option value="bo-may-nhin">Bố nhịn mày lâu lắm rồi</option>
                                                <option value="troi-oi-cai-quan-que">Trời ơi, cái quần què gì đây
                                                </option>
                                                <option value="oh-nooo">Oh nooo!</option>
                                                <option value="Oh-no-no-no">Oh-no-no-no</option>
                                                <option value="de-che-thua">Đế chế thua cuộc</option>
                                                <option value="western-pan-flute">Pan flute</option>
                                                <option value="crash-hit-soft">Cymbals</option>
                                            </optgroup>
                                            <optgroup label="Cười &amp; hét">
                                                <option value="cuoi">Cười 1</option>
                                                <option value="cuoi2">Cười 2</option>
                                                <option value="cuoi3">Cười 3</option>
                                                <option value="ma_cuoi">Ma cười</option>
                                                <option value="evil-man-laugh-1">Nụ cười của Quỷ</option>
                                                <option value="horror-scream">Tiếng kêu kinh dị</option>
                                            </optgroup>
                                            <optgroup label="Tiếng chuông">
                                                <option value="bell">Tiếng chuông</option>
                                                <option value="star-ding-04">Twinkling star</option>
                                                <option value="swoosh-ui-success-ding-complet">Swoosh ding</option>
                                                <option value="synth-bell-announcement-01">Synth bell</option>
                                                <option value="mystery-bell">Mystery bell</option>
                                            </optgroup>
                                            <optgroup label="Khác">
                                                <option value="hon_di">Hôn đi, hôn đi</option>
                                                <option value="ketthuchaihuoc">Kết thúc hài hước</option>
                                            </optgroup>
                                        </select>
                                        <button class="btn btn-outline-secondary" id="btn-sound-play"><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-play">
                                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                            </svg></button>
                                    </div>

                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><a class="text-decoration-none" target="_blank"
                                                href="/huong-dan-lay-file_id-tren-nhactik-com/">♪ nhactik.com</a></span>
                                        <input type="text" class="form-control" id="sound_file" value=""
                                            placeholder="File ID">
                                        <button class="btn btn-outline-secondary" id="btn-sound-file-play"><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-play">
                                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                            </svg></button>
                                    </div>
                                </div> <!-- group-or -->
                                <hr class="my-4">
                                <div class="group-or">
                                    <span class="or">Hoặc</span>
                                    <div class="input-group mb-2">
                                        <span class="input-group-text">♪ Khi hết</span>
                                        <select class="form-select" id="noti_sound">
                                            <optgroup label="Hiệu ứng âm thanh">
                                                <option value="0" selected="">Tắt tiếng</option>
                                                <option value="random">Ngẫu nhiên</option>
                                                <option value="read" disabled="">Đọc kết quả khi kết thúc</option>
                                                <option value="slot_end">Slot end</option>
                                            </optgroup>
                                            <optgroup label="Chúc mừng">
                                                <option value="congratulation">Chúc mừng</option>
                                                <option value="votay">Vỗ tay</option>
                                                <option value="phaohoano">Pháo hoa nổ</option>
                                                <option value="chuc-tet">Chúc Tết</option>
                                                <option value="winner">Winner</option>
                                                <option value="complete">Complete</option>
                                                <option value="start">Start</option>
                                                <option value="olympia-trao-giai-1">Olympia trao giải 1</option>
                                                <option value="olympia-trao-giai-2">Olympia trao giải 2</option>
                                                <option value="olympia-trao-giai-3">Olympia trao giải 3</option>
                                                <option value="olympia-trao-giai-4">Olympia trao giải 4</option>
                                                <option value="ailatrieuphu_dung">Ai là triệu phú (Đúng)</option>
                                                <option value="male-carnival-shout-1">Hò reo kiểu phê</option>
                                                <option value="children-loud-hooray">Children hooray</option>
                                                <option value="hooray">Hooray</option>
                                                <option value="military-trumpet">Kèn Quân đội</option>
                                                <option value="fanfare">Fanfare</option>
                                            </optgroup>
                                            <optgroup label="Trả lời đúng">
                                                <option value="correct-answer-ding-4">Trả lời đúng</option>
                                                <option value="10diem">10 điểm</option>
                                                <option value="ghechua">Ghê chưa, ghê chưa</option>
                                                <option value="hetnuoccham">Hết nước chấm</option>
                                                <option value="votayPewPew">Vỗ tay Pewpew</option>
                                                <option value="de-che-chien-thang">Đế chế chiến thắng</option>
                                            </optgroup>
                                            <optgroup label="Trả lời sai">
                                                <option value="oh_thatvong">Ôh thất vọng</option>
                                                <option value="thatvong">Thất vọng</option>
                                                <option value="traloisai">Trả lời sai</option>
                                                <option value="concainit" selected="">Còn cái nịt</option>
                                                <option value="troioi_baotapmuaxa">Trời ơi, bão táp mưa xa</option>
                                                <option value="troioi_bayoi">Trời ơi, bay ơi bay</option>
                                                <option value="cai_gi_vay_banoi">Cái gì vậy bà nội</option>
                                                <option value="chetmeroi">Chết mẹ, dĩnh bẫy rồi</option>
                                                <option value="cay-the-nho">Cay thế nhỉ</option>
                                                <option value="conginuadau">Còn gì nữa đâu mà khóc…</option>
                                                <option value="cartoon-failure-3">Sad trombone</option>
                                                <option value="game-over-losin">Game over losin</option>
                                                <option value="huge-thunder">Sấm sét</option>
                                                <option value="ailatrieuphu_sai">Ai là triệu phú (Sai)</option>
                                                <option value="saomadoduoc">Sao mà đỡ được</option>
                                                <option value="deohieuduoc">Đ..éo thể hiểu được</option>
                                                <option value="xaochoPewPew">Xạo chó PewPew</option>
                                                <option value="deotin">Đ..éo tin</option>
                                                <option value="oi-doi-oi">Ối dồi ôi</option>
                                                <option value="do-ngu">Đồ ngu, đồ ăn hại, cút</option>
                                                <option value="ngu-thi-chet">Ngu thi chết, Huấn Hoa Hồng</option>
                                                <option value="hon-70-tuoi">Tôi năm nay hơn 70 tuổi</option>
                                                <option value="bo-may-nhin">Bố nhịn mày lâu lắm rồi</option>
                                                <option value="troi-oi-cai-quan-que">Trời ơi, cái quần què gì đây
                                                </option>
                                                <option value="oh-nooo">Oh nooo!</option>
                                                <option value="Oh-no-no-no">Oh-no-no-no</option>
                                                <option value="de-che-thua">Đế chế thua cuộc</option>
                                                <option value="western-pan-flute">Pan flute</option>
                                                <option value="crash-hit-soft">Cymbals</option>
                                            </optgroup>
                                            <optgroup label="Cười &amp; hét">
                                                <option value="cuoi">Cười 1</option>
                                                <option value="cuoi2">Cười 2</option>
                                                <option value="cuoi3">Cười 3</option>
                                                <option value="ma_cuoi">Ma cười</option>
                                                <option value="evil-man-laugh-1">Nụ cười của Quỷ</option>
                                                <option value="horror-scream">Tiếng kêu kinh dị</option>
                                            </optgroup>
                                            <optgroup label="Tiếng chuông">
                                                <option value="bell">Tiếng chuông</option>
                                                <option value="star-ding-04">Twinkling star</option>
                                                <option value="swoosh-ui-success-ding-complet">Swoosh ding</option>
                                                <option value="synth-bell-announcement-01">Synth bell</option>
                                                <option value="mystery-bell">Mystery bell</option>
                                            </optgroup>
                                            <optgroup label="Khác">
                                                <option value="hon_di">Hôn đi, hôn đi</option>
                                                <option value="ketthuchaihuoc">Kết thúc hài hước</option>
                                            </optgroup>
                                        </select>
                                        <button class="btn btn-outline-secondary" id="btn-noti_sound-play"><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-play">
                                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                            </svg></button>
                                    </div>

                                    <div class="input-group mb-3">
                                        <span class="input-group-text"><a class="text-decoration-none" target="_blank"
                                                href="/huong-dan-lay-file_id-tren-nhactik-com/">♪ nhactik.com</a></span>
                                        <input type="text" class="form-control" id="noti_sound_file" value=""
                                            placeholder="File ID">
                                        <button class="btn btn-outline-secondary" id="btn-noti_sound-file-play"><svg
                                                xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="feather feather-play">
                                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                            </svg></button>
                                    </div>
                                </div> <!-- group-or -->

                            </div>

                            <div class="col-md-3">Tiêu đề popup</div>
                            <div class="col-md-9">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="popup_title" value="Hộp quà có"
                                        placeholder="Hộp quà có">
                                </div>
                            </div>

                            <div class="col-md-3">Tùy chọn khác</div>
                            <div class="col-md-9">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="confetti" checked="">
                                    <label class="form-check-label" for="confetti">Bắn hoa giấy khi mở hộp quà</label>
                                </div>
                            </div>

                        </div> <!-- End Row -->
                    </div> <!-- //general-tab-pane -->

                    <div class="tab-pane fade pt-3 active show" id="style-tab-pane" role="tabpanel"
                        aria-labelledby="style-tab" tabindex="0">
                        <fieldset class="border border-2 px-2 mb-3">
                            <legend class="float-none w-auto p-2 fs-6 fw-bold">Mẫu <svg
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-chevron-down">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg></legend>
                            <div class="row g-3 mb-3">
                                <input type="hidden" id="template" value="tpl-jib">
                                <div class="col-md-3">
                                    Chọn mẫu
                                </div>
                                <div class="col-md-9">
                                    <div class="dropdown" id="myDropdown">
                                        <button class="btn btn-secondary d-flex align-items-center dropdown-toggle"
                                            id="btn-dropdown-select-tpl" type="button" data-bs-toggle="dropdown"
                                            aria-expanded="false" data-bs-auto-close="outside">
                                            <div class="d-flex justify-content-between item">
                                                <div class="item-title me-2">Ball in box</div>
                                                <div class="item-img tpl-jib me-1">
                                                    <div class="box box-cfg giftbox"></div>
                                                </div>
                                            </div>
                                        </button>
                                        <div class="dropdown-menu" id="btn-select-tpl">
                                            <div class="dropdown-item" data-content="tpl-default" data-title="Mặc định">
                                                <div class="d-flex justify-content-between item">
                                                    <div class="item-title">Mặc định</div>
                                                    <div class="item-img tpl-default">
                                                        <div class="box box-cfg giftbox"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="dropdown-item" data-content="tpl-christmas"
                                                data-title="Christmas">
                                                <div class="d-flex justify-content-between item">
                                                    <div class="item-title">Christmas</div>
                                                    <div class="item-img tpl-christmas">
                                                        <div class="box box-cfg giftbox"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="dropdown-item" data-content="tpl-money-bag"
                                                data-title="Túi tiền">
                                                <div class="d-flex justify-content-between item">
                                                    <div class="item-title">Túi tiền</div>
                                                    <div class="item-img tpl-money-bag">
                                                        <div class="box box-cfg giftbox"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="dropdown-item" data-content="tpl-blind-bag" data-title="Túi mù">
                                                <div class="d-flex justify-content-between item">
                                                    <div class="item-title">Túi mù</div>
                                                    <div class="item-img tpl-blind-bag">
                                                        <div class="box box-cfg giftbox"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="dropdown-item" data-content="tpl-money" data-title="Lì xì">
                                                <div class="d-flex justify-content-between item">
                                                    <div class="item-title">Lì xì</div>
                                                    <div class="item-img tpl-money">
                                                        <div class="box box-cfg giftbox"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="dropdown-item" data-content="tpl-egg" data-title="Đập trứng">
                                                <div class="d-flex justify-content-between item">
                                                    <div class="item-title">Đập trứng</div>
                                                    <div class="item-img tpl-egg">
                                                        <div class="box box-cfg giftbox"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="dropdown-item" data-content="tpl-jar" data-title="Đập lu">
                                                <div class="d-flex justify-content-between item">
                                                    <div class="item-title">Đập lu</div>
                                                    <div class="item-img tpl-jar">
                                                        <div class="box box-cfg giftbox"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="dropdown-item" data-content="tpl-rat" data-title="Đập chuột">
                                                <div class="d-flex justify-content-between item">
                                                    <div class="item-title">Đập chuột</div>
                                                    <div class="item-img tpl-rat">
                                                        <div class="box box-cfg giftbox"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="dropdown-item" data-content="tpl-ghost" data-title="Diệt ma">
                                                <div class="d-flex justify-content-between item">
                                                    <div class="item-title">Diệt ma</div>
                                                    <div class="item-img tpl-ghost">
                                                        <div class="box box-cfg giftbox"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="dropdown-item" data-content="tpl-jib" data-title="Ball in box">
                                                <div class="d-flex justify-content-between item">
                                                    <div class="item-title">Ball in box</div>
                                                    <div class="item-img tpl-jib">
                                                        <div class="box box-cfg giftbox"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div> <!-- //col-md-9 -->
                            </div> <!-- //row -->
                        </fieldset>

                        <fieldset class="border border-2 px-2 mb-3">
                            <legend class="float-none w-auto p-2 fs-6 fw-bold">Nút <svg
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-chevron-down">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg></legend>
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">Màu nền</div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="btn_bg_color" value="#dc3545"
                                                maxlength="7" placeholder="#dc3545" aria-label="background color">
                                            <span class="input-group-text">
                                                <input type="color" id="btn_bg_color_picker" value="#dc3545">
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">Màu chữ</div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="btn_color" value="#ffffff"
                                                maxlength="7" placeholder="#ffffff" aria-label="color">
                                            <span class="input-group-text">
                                                <input type="color" id="btn_color_picker" value="#ffffff">
                                            </span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </fieldset>

                        <fieldset class="border border-2 px-2 mb-3">
                            <legend class="float-none w-auto p-2 fs-6 fw-bold">Body <svg
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-chevron-down">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg></legend>
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">Màu chữ</div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="color" value="#ffffff"
                                                maxlength="7" placeholder="#ffffff" aria-label="color">
                                            <span class="input-group-text">
                                                <input type="color" id="color_picker" value="#ffffff">
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">Màu nền</div>
                                <div class="col-md-9">
                                    <div class="input-group">
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" id="bg_color" value="#dc3545"
                                                maxlength="7" placeholder="#dc3545" aria-label="bg color">
                                            <span class="input-group-text">
                                                <input type="color" id="bg_color_picker" value="#dc3545">
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">Ảnh nền</div>
                                <div class="col-md-9">
                                    <ul class="nav nav-tabs" id="deviceTab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="desktop-tab" data-bs-toggle="tab"
                                                data-bs-target="#desktop-tab-pane" type="button" role="tab"
                                                aria-controls="desktop-tab-pane" aria-selected="true"><span
                                                    class="btn-desktop">Desktop</span></button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="tablet-tab" data-bs-toggle="tab"
                                                data-bs-target="#tablet-tab-pane" type="button" role="tab"
                                                aria-controls="tablet-tab-pane" aria-selected="false"
                                                tabindex="-1"><span class="btn-tablet">Tablet</span></button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="mobile-tab" data-bs-toggle="tab"
                                                data-bs-target="#mobile-tab-pane" type="button" role="tab"
                                                aria-controls="mobile-tab-pane" aria-selected="false"
                                                tabindex="-1"><span class="btn-mobile">Mobile</span></button>
                                        </li>
                                    </ul>
                                    <div class="tab-content pt-1" id="deviceTabContent">
                                        <div class="tab-pane fade show active" id="desktop-tab-pane" role="tabpanel"
                                            aria-labelledby="desktop-tab" tabindex="0">
                                            <div class="input-group mb-3">
                                                <span class="input-group-text">Nền</span>
                                                <input type="text" class="form-control" id="bg_img"
                                                    value="/wp-content/themes/twentytwentythree-child/assets/background/christmas-2.jpg"
                                                    placeholder="https://example.com/bg.jpg">
                                                <button class="btn btn-secondary" id="btn-select-bg-img">Chọn<svg
                                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="feather feather-chevron-down">
                                                        <polyline points="6 9 12 15 18 9"></polyline>
                                                    </svg></button>
                                                <span class="input-group-text">
                                                    <label for="upload_bg_img" id="btn_upload_bg_img"
                                                        data-bs-toggle="tooltip"
                                                        aria-label="Kích thước (16:9): 1920 x 1080 (px)"
                                                        data-bs-original-title="Kích thước (16:9): 1920 x 1080 (px)"><svg
                                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" class="feather feather-camera">
                                                            <path
                                                                d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z">
                                                            </path>
                                                            <circle cx="12" cy="13" r="4"></circle>
                                                        </svg></label>
                                                </span>
                                                <input type="file" id="upload_bg_img" data-maxsize="5" class="d-none"
                                                    accept="image/*">
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="tablet-tab-pane" role="tabpanel"
                                            aria-labelledby="tablet-tab" tabindex="0">
                                            <div class="input-group mb-3">
                                                <span class="input-group-text">Nền</span>
                                                <input type="text" class="form-control" id="bg_img_tablet"
                                                    placeholder="https://example.com/bg-tablet.jpg">
                                                <button class="btn btn-secondary" id="btn-select-bg-tablet">Chọn<svg
                                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="feather feather-chevron-down">
                                                        <polyline points="6 9 12 15 18 9"></polyline>
                                                    </svg></button>
                                                <span class="input-group-text">
                                                    <label for="upload_bg_img_tablet" id="btn_upload_bg_img_tablet"
                                                        data-bs-toggle="tooltip"
                                                        aria-label="Kích thước (4:3): 1536 x 2048 (px)"
                                                        data-bs-original-title="Kích thước (4:3): 1536 x 2048 (px)"><svg
                                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" class="feather feather-camera">
                                                            <path
                                                                d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z">
                                                            </path>
                                                            <circle cx="12" cy="13" r="4"></circle>
                                                        </svg></label>
                                                </span>
                                                <input type="file" id="upload_bg_img_tablet" data-maxsize="5"
                                                    class="d-none" accept="image/*">
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="mobile-tab-pane" role="tabpanel"
                                            aria-labelledby="mobile-tab" tabindex="0">
                                            <div class="input-group mb-3">
                                                <span class="input-group-text">Nền</span>
                                                <input type="text" class="form-control" id="bg_img_mobile"
                                                    placeholder="https://example.com/bg-mobile.jpg">
                                                <button class="btn btn-secondary" id="btn-select-bg-mobile">Chọn<svg
                                                        xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="feather feather-chevron-down">
                                                        <polyline points="6 9 12 15 18 9"></polyline>
                                                    </svg></button>
                                                <span class="input-group-text">
                                                    <label for="upload_bg_img_mobile" id="btn_upload_bg_img_mobile"
                                                        data-bs-toggle="tooltip"
                                                        aria-label="Kích thước (9:16): 1080 x 1920 (px)"
                                                        data-bs-original-title="Kích thước (9:16): 1080 x 1920 (px)"><svg
                                                            xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" class="feather feather-camera">
                                                            <path
                                                                d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z">
                                                            </path>
                                                            <circle cx="12" cy="13" r="4"></circle>
                                                        </svg></label>
                                                </span>
                                                <input type="file" id="upload_bg_img_mobile" data-maxsize="5"
                                                    class="d-none" accept="image/*">
                                            </div>
                                        </div>
                                    </div> <!-- //tab-content -->
                                    <p class="text-muted">➥ Màu và ảnh nền chỉ áp dụng khi không có Nền Gradient</p>

                                </div> <!-- //col-md-9 -->


                            </div> <!-- //row -->

                            <div class="mb-3">
                                <label for="bg-gradient" class="form-label">
                                    <span class="dropdown mt-3">
                                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown">
                                            Nền Gradient
                                        </button> – <span class="text-success"><strong>Đẹp, nhẹ</strong> mà ko cần
                                            ảnh</span>
                                        <ul class="dropdown-menu" id="gradientList"
                                            style="max-height: 300px; overflow-y: auto; min-width: 250px;">
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 135deg, rgb(255, 255, 196) 0deg, rgb(255, 255, 196) 27.692deg, rgb(255, 255, 181) 27.692deg, rgb(255, 255, 181) 55.385deg, rgb(255, 237, 165) 55.385deg, rgb(255, 237, 165) 83.077deg, rgb(255, 205, 149) 83.077deg, rgb(255, 205, 149) 110.769deg, rgb(255, 170, 133) 110.769deg, rgb(255, 170, 133) 138.462deg, rgb(255, 134, 116) 138.462deg, rgb(255, 134, 116) 166.154deg, rgb(255, 97, 100) 166.154deg, rgb(255, 97, 100) 193.846deg, rgb(245, 61, 85) 193.846deg, rgb(245, 61, 85) 221.538deg, rgb(233, 28, 69) 221.538deg, rgb(233, 28, 69) 249.231deg, rgb(220, 0, 55) 249.231deg, rgb(220, 0, 55) 276.923deg, rgb(206, 0, 42) 276.923deg, rgb(206, 0, 42) 304.615deg, rgb(192, 0, 29) 304.615deg, rgb(192, 0, 29) 332.308deg, rgb(176, 0, 18) 332.308deg, rgb(176, 0, 18) 360deg);"></span>
                                                    <span>Mẫu 0</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 90deg, rgb(164, 116, 81) 0deg, rgb(164, 116, 81) 27.692deg, rgb(164, 137, 107) 27.692deg, rgb(164, 137, 107) 55.385deg, rgb(156, 152, 129) 55.385deg, rgb(156, 152, 129) 83.077deg, rgb(139, 160, 147) 83.077deg, rgb(139, 160, 147) 110.769deg, rgb(115, 160, 157) 110.769deg, rgb(115, 160, 157) 138.462deg, rgb(88, 152, 160) 138.462deg, rgb(88, 152, 160) 166.154deg, rgb(59, 137, 154) 166.154deg, rgb(59, 137, 154) 193.846deg, rgb(32, 116, 141) 193.846deg, rgb(32, 116, 141) 221.538deg, rgb(9, 91, 121) 221.538deg, rgb(9, 91, 121) 249.231deg, rgb(0, 65, 97) 249.231deg, rgb(0, 65, 97) 276.923deg, rgb(0, 40, 71) 276.923deg, rgb(0, 40, 71) 304.615deg, rgb(0, 18, 45) 304.615deg, rgb(0, 18, 45) 332.308deg, rgb(0, 1, 22) 332.308deg, rgb(0, 1, 22) 360deg);"></span>
                                                    <span>Mẫu 1</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 45deg, rgb(250, 218, 97) 0deg, rgb(250, 218, 97) 27.692deg, rgb(248, 210, 86) 27.692deg, rgb(248, 210, 86) 55.385deg, rgb(248, 199, 82) 55.385deg, rgb(248, 199, 82) 83.077deg, rgb(250, 187, 86) 83.077deg, rgb(250, 187, 86) 110.769deg, rgb(253, 173, 98) 110.769deg, rgb(253, 173, 98) 138.462deg, rgb(255, 159, 115) 138.462deg, rgb(255, 159, 115) 166.154deg, rgb(255, 145, 136) 166.154deg, rgb(255, 145, 136) 193.846deg, rgb(255, 131, 158) 193.846deg, rgb(255, 131, 158) 221.538deg, rgb(255, 118, 179) 221.538deg, rgb(255, 118, 179) 249.231deg, rgb(255, 108, 196) 249.231deg, rgb(255, 108, 196) 276.923deg, rgb(255, 99, 206) 276.923deg, rgb(255, 99, 206) 304.615deg, rgb(255, 93, 210) 304.615deg, rgb(255, 93, 210) 332.308deg, rgb(255, 90, 205) 332.308deg, rgb(255, 90, 205) 360deg);"></span>
                                                    <span>Mẫu 2</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 210deg, rgb(197, 187, 184) 0deg, rgb(197, 187, 184) 27.692deg, rgb(181, 180, 184) 27.692deg, rgb(181, 180, 184) 55.385deg, rgb(164, 173, 182) 55.385deg, rgb(164, 173, 182) 83.077deg, rgb(146, 164, 180) 83.077deg, rgb(146, 164, 180) 110.769deg, rgb(130, 155, 176) 110.769deg, rgb(130, 155, 176) 138.462deg, rgb(114, 145, 172) 138.462deg, rgb(114, 145, 172) 166.154deg, rgb(101, 135, 166) 166.154deg, rgb(101, 135, 166) 193.846deg, rgb(91, 124, 160) 193.846deg, rgb(91, 124, 160) 221.538deg, rgb(83, 114, 153) 221.538deg, rgb(83, 114, 153) 249.231deg, rgb(80, 103, 145) 249.231deg, rgb(80, 103, 145) 276.923deg, rgb(80, 93, 136) 276.923deg, rgb(80, 93, 136) 304.615deg, rgb(83, 84, 126) 304.615deg, rgb(83, 84, 126) 332.308deg, rgb(91, 75, 116) 332.308deg, rgb(91, 75, 116) 360deg);"></span>
                                                    <span>Mẫu 3</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 90deg, rgb(218, 197, 116) 0deg, rgb(218, 197, 116) 27.692deg, rgb(217, 182, 86) 27.692deg, rgb(217, 182, 86) 55.385deg, rgb(213, 163, 55) 55.385deg, rgb(213, 163, 55) 83.077deg, rgb(208, 143, 23) 83.077deg, rgb(208, 143, 23) 110.769deg, rgb(200, 121, 0) 110.769deg, rgb(200, 121, 0) 138.462deg, rgb(190, 97, 0) 138.462deg, rgb(190, 97, 0) 166.154deg, rgb(178, 72, 0) 166.154deg, rgb(178, 72, 0) 193.846deg, rgb(165, 47, 0) 193.846deg, rgb(165, 47, 0) 221.538deg, rgb(150, 22, 0) 221.538deg, rgb(150, 22, 0) 249.231deg, rgb(134, 0, 0) 249.231deg, rgb(134, 0, 0) 276.923deg, rgb(116, 0, 0) 276.923deg, rgb(116, 0, 0) 304.615deg, rgb(98, 0, 0) 304.615deg, rgb(98, 0, 0) 332.308deg, rgb(80, 0, 0) 332.308deg, rgb(80, 0, 0) 360deg);"></span>
                                                    <span>Mẫu 4</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 45deg, rgb(142, 197, 252) 0deg, rgb(142, 197, 252) 27.692deg, rgb(139, 202, 255) 27.692deg, rgb(139, 202, 255) 55.385deg, rgb(139, 207, 255) 55.385deg, rgb(139, 207, 255) 83.077deg, rgb(141, 211, 255) 83.077deg, rgb(141, 211, 255) 110.769deg, rgb(146, 214, 255) 110.769deg, rgb(146, 214, 255) 138.462deg, rgb(153, 215, 255) 138.462deg, rgb(153, 215, 255) 166.154deg, rgb(161, 216, 255) 166.154deg, rgb(161, 216, 255) 193.846deg, rgb(171, 215, 255) 193.846deg, rgb(171, 215, 255) 221.538deg, rgb(182, 213, 255) 221.538deg, rgb(182, 213, 255) 249.231deg, rgb(193, 210, 255) 249.231deg, rgb(193, 210, 255) 276.923deg, rgb(205, 205, 255) 276.923deg, rgb(205, 205, 255) 304.615deg, rgb(215, 201, 255) 304.615deg, rgb(215, 201, 255) 332.308deg, rgb(224, 195, 255) 332.308deg, rgb(224, 195, 255) 360deg);"></span>
                                                    <span>Mẫu 5</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 45deg, rgb(252, 142, 197) 0deg, rgb(252, 142, 197) 27.692deg, rgb(255, 139, 202) 27.692deg, rgb(255, 139, 202) 55.385deg, rgb(255, 139, 207) 55.385deg, rgb(255, 139, 207) 83.077deg, rgb(255, 141, 211) 83.077deg, rgb(255, 141, 211) 110.769deg, rgb(255, 146, 214) 110.769deg, rgb(255, 146, 214) 138.462deg, rgb(255, 153, 215) 138.462deg, rgb(255, 153, 215) 166.154deg, rgb(255, 161, 216) 166.154deg, rgb(255, 161, 216) 193.846deg, rgb(255, 171, 215) 193.846deg, rgb(255, 171, 215) 221.538deg, rgb(255, 182, 213) 221.538deg, rgb(255, 182, 213) 249.231deg, rgb(255, 193, 210) 249.231deg, rgb(255, 193, 210) 276.923deg, rgb(255, 205, 205) 276.923deg, rgb(255, 205, 205) 304.615deg, rgb(255, 215, 201) 304.615deg, rgb(255, 215, 201) 332.308deg, rgb(255, 224, 195) 332.308deg, rgb(255, 224, 195) 360deg);"></span>
                                                    <span>Mẫu 6</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 45deg, rgb(65, 89, 208) 0deg, rgb(65, 89, 208) 27.692deg, rgb(68, 56, 225) 27.692deg, rgb(68, 56, 225) 55.385deg, rgb(81, 34, 236) 55.385deg, rgb(81, 34, 236) 83.077deg, rgb(104, 24, 237) 83.077deg, rgb(104, 24, 237) 110.769deg, rgb(133, 29, 229) 110.769deg, rgb(133, 29, 229) 138.462deg, rgb(167, 49, 213) 138.462deg, rgb(167, 49, 213) 166.154deg, rgb(200, 79, 192) 166.154deg, rgb(200, 79, 192) 193.846deg, rgb(231, 116, 168) 193.846deg, rgb(231, 116, 168) 221.538deg, rgb(255, 152, 144) 221.538deg, rgb(255, 152, 144) 249.231deg, rgb(255, 184, 125) 249.231deg, rgb(255, 184, 125) 276.923deg, rgb(255, 205, 112) 276.923deg, rgb(255, 205, 112) 304.615deg, rgb(255, 212, 107) 304.615deg, rgb(255, 212, 107) 332.308deg, rgb(255, 205, 112) 332.308deg, rgb(255, 205, 112) 360deg);"></span>
                                                    <span>Mẫu 7</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 45deg, rgb(10, 74, 89) 0deg, rgb(10, 74, 89) 27.692deg, rgb(11, 82, 124) 27.692deg, rgb(11, 82, 124) 55.385deg, rgb(13, 92, 159) 55.385deg, rgb(13, 92, 159) 83.077deg, rgb(16, 104, 194) 83.077deg, rgb(16, 104, 194) 110.769deg, rgb(18, 118, 227) 110.769deg, rgb(18, 118, 227) 138.462deg, rgb(21, 133, 255) 138.462deg, rgb(21, 133, 255) 166.154deg, rgb(24, 150, 255) 166.154deg, rgb(24, 150, 255) 193.846deg, rgb(28, 169, 255) 193.846deg, rgb(28, 169, 255) 221.538deg, rgb(31, 188, 255) 221.538deg, rgb(31, 188, 255) 249.231deg, rgb(35, 209, 255) 249.231deg, rgb(35, 209, 255) 276.923deg, rgb(39, 230, 255) 276.923deg, rgb(39, 230, 255) 304.615deg, rgb(43, 251, 255) 304.615deg, rgb(43, 251, 255) 332.308deg, rgb(47, 255, 255) 332.308deg, rgb(47, 255, 255) 360deg);"></span>
                                                    <span>Mẫu 8</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 180deg, rgb(169, 202, 255) 0deg, rgb(169, 202, 255) 27.692deg, rgb(174, 203, 255) 27.692deg, rgb(174, 203, 255) 55.385deg, rgb(184, 203, 255) 55.385deg, rgb(184, 203, 255) 83.077deg, rgb(196, 203, 255) 83.077deg, rgb(196, 203, 255) 110.769deg, rgb(211, 203, 255) 110.769deg, rgb(211, 203, 255) 138.462deg, rgb(226, 202, 253) 138.462deg, rgb(226, 202, 253) 166.154deg, rgb(240, 200, 249) 166.154deg, rgb(240, 200, 249) 193.846deg, rgb(252, 199, 244) 193.846deg, rgb(252, 199, 244) 221.538deg, rgb(255, 197, 241) 221.538deg, rgb(255, 197, 241) 249.231deg, rgb(255, 195, 238) 249.231deg, rgb(255, 195, 238) 276.923deg, rgb(255, 192, 236) 276.923deg, rgb(255, 192, 236) 304.615deg, rgb(255, 189, 236) 304.615deg, rgb(255, 189, 236) 332.308deg, rgb(255, 186, 236) 332.308deg, rgb(255, 186, 236) 360deg);"></span>
                                                    <span>Mẫu 9</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 90deg, rgb(7, 174, 234) 0deg, rgb(7, 174, 234) 27.692deg, rgb(12, 179, 238) 27.692deg, rgb(12, 179, 238) 55.385deg, rgb(18, 189, 239) 55.385deg, rgb(18, 189, 239) 83.077deg, rgb(24, 201, 238) 83.077deg, rgb(24, 201, 238) 110.769deg, rgb(31, 215, 234) 110.769deg, rgb(31, 215, 234) 138.462deg, rgb(37, 230, 229) 138.462deg, rgb(37, 230, 229) 166.154deg, rgb(42, 243, 221) 166.154deg, rgb(42, 243, 221) 193.846deg, rgb(46, 253, 211) 193.846deg, rgb(46, 253, 211) 221.538deg, rgb(49, 255, 200) 221.538deg, rgb(49, 255, 200) 249.231deg, rgb(50, 255, 188) 249.231deg, rgb(50, 255, 188) 276.923deg, rgb(49, 255, 176) 276.923deg, rgb(49, 255, 176) 304.615deg, rgb(47, 255, 164) 304.615deg, rgb(47, 255, 164) 332.308deg, rgb(43, 245, 152) 332.308deg, rgb(43, 245, 152) 360deg);"></span>
                                                    <span>Mẫu 10</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 225deg, rgb(130, 205, 224) 0deg, rgb(130, 205, 224) 27.692deg, rgb(139, 202, 224) 27.692deg, rgb(139, 202, 224) 55.385deg, rgb(149, 201, 222) 55.385deg, rgb(149, 201, 222) 83.077deg, rgb(161, 200, 219) 83.077deg, rgb(161, 200, 219) 110.769deg, rgb(174, 200, 216) 110.769deg, rgb(174, 200, 216) 138.462deg, rgb(187, 200, 212) 138.462deg, rgb(187, 200, 212) 166.154deg, rgb(202, 201, 208) 166.154deg, rgb(202, 201, 208) 193.846deg, rgb(217, 203, 203) 193.846deg, rgb(217, 203, 203) 221.538deg, rgb(232, 206, 199) 221.538deg, rgb(232, 206, 199) 249.231deg, rgb(246, 209, 196) 249.231deg, rgb(246, 209, 196) 276.923deg, rgb(255, 213, 194) 276.923deg, rgb(255, 213, 194) 304.615deg, rgb(255, 217, 192) 304.615deg, rgb(255, 217, 192) 332.308deg, rgb(255, 221, 192) 332.308deg, rgb(255, 221, 192) 360deg);"></span>
                                                    <span>Mẫu 11</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 135deg, rgb(49, 44, 0) 0deg, rgb(49, 44, 0) 27.692deg, rgb(51, 45, 12) 27.692deg, rgb(51, 45, 12) 55.385deg, rgb(51, 46, 30) 55.385deg, rgb(51, 46, 30) 83.077deg, rgb(51, 47, 50) 83.077deg, rgb(51, 47, 50) 110.769deg, rgb(50, 49, 73) 110.769deg, rgb(50, 49, 73) 138.462deg, rgb(47, 50, 96) 138.462deg, rgb(47, 50, 96) 166.154deg, rgb(44, 51, 119) 166.154deg, rgb(44, 51, 119) 193.846deg, rgb(40, 53, 142) 193.846deg, rgb(40, 53, 142) 221.538deg, rgb(36, 54, 162) 221.538deg, rgb(36, 54, 162) 249.231deg, rgb(31, 56, 178) 249.231deg, rgb(31, 56, 178) 276.923deg, rgb(26, 57, 192) 276.923deg, rgb(26, 57, 192) 304.615deg, rgb(21, 59, 200) 304.615deg, rgb(21, 59, 200) 332.308deg, rgb(17, 60, 204) 332.308deg, rgb(17, 60, 204) 360deg);"></span>
                                                    <span>Mẫu 12</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 90deg, rgb(18, 18, 22) 0deg, rgb(18, 18, 22) 27.692deg, rgb(22, 23, 26) 27.692deg, rgb(22, 23, 26) 55.385deg, rgb(26, 27, 31) 55.385deg, rgb(26, 27, 31) 83.077deg, rgb(31, 32, 35) 83.077deg, rgb(31, 32, 35) 110.769deg, rgb(35, 36, 39) 110.769deg, rgb(35, 36, 39) 138.462deg, rgb(39, 40, 43) 138.462deg, rgb(39, 40, 43) 166.154deg, rgb(43, 44, 47) 166.154deg, rgb(43, 44, 47) 193.846deg, rgb(47, 48, 51) 193.846deg, rgb(47, 48, 51) 221.538deg, rgb(50, 51, 55) 221.538deg, rgb(50, 51, 55) 249.231deg, rgb(54, 55, 58) 249.231deg, rgb(54, 55, 58) 276.923deg, rgb(56, 57, 61) 276.923deg, rgb(56, 57, 61) 304.615deg, rgb(59, 60, 64) 304.615deg, rgb(59, 60, 64) 332.308deg, rgb(61, 62, 66) 332.308deg, rgb(61, 62, 66) 360deg);"></span>
                                                    <span>Mẫu 13</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 90deg, rgb(255, 255, 209) 0deg, rgb(255, 255, 209) 27.692deg, rgb(255, 255, 172) 27.692deg, rgb(255, 255, 172) 55.385deg, rgb(255, 224, 126) 55.385deg, rgb(255, 224, 126) 83.077deg, rgb(255, 156, 77) 83.077deg, rgb(255, 156, 77) 110.769deg, rgb(255, 83, 30) 110.769deg, rgb(255, 83, 30) 138.462deg, rgb(206, 17, 0) 138.462deg, rgb(206, 17, 0) 166.154deg, rgb(115, 0, 0) 166.154deg, rgb(115, 0, 0) 193.846deg, rgb(29, 0, 0) 193.846deg, rgb(29, 0, 0) 221.538deg, rgb(0, 0, 0) 221.538deg, rgb(0, 0, 0) 249.231deg, rgb(0, 0, 6) 249.231deg, rgb(0, 0, 6) 276.923deg, rgb(0, 45, 47) 276.923deg, rgb(0, 45, 47) 304.615deg, rgb(0, 115, 96) 304.615deg, rgb(0, 115, 96) 332.308deg, rgb(11, 188, 145) 332.308deg, rgb(11, 188, 145) 360deg);"></span>
                                                    <span>Mẫu 14</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 90deg, rgb(0, 0, 4) 0deg, rgb(0, 0, 4) 27.692deg, rgb(0, 0, 41) 27.692deg, rgb(0, 0, 41) 55.385deg, rgb(0, 12, 79) 55.385deg, rgb(0, 12, 79) 83.077deg, rgb(12, 70, 110) 83.077deg, rgb(12, 70, 110) 110.769deg, rgb(91, 129, 131) 110.769deg, rgb(91, 129, 131) 138.462deg, rgb(170, 178, 136) 138.462deg, rgb(170, 178, 136) 166.154deg, rgb(232, 205, 126) 166.154deg, rgb(232, 205, 126) 193.846deg, rgb(255, 205, 102) 193.846deg, rgb(255, 205, 102) 221.538deg, rgb(255, 178, 68) 221.538deg, rgb(255, 178, 68) 249.231deg, rgb(223, 130, 29) 249.231deg, rgb(223, 130, 29) 276.923deg, rgb(158, 71, 0) 276.923deg, rgb(158, 71, 0) 304.615deg, rgb(78, 13, 0) 304.615deg, rgb(78, 13, 0) 332.308deg, rgb(0, 0, 0) 332.308deg, rgb(0, 0, 0) 360deg);"></span>
                                                    <span>Mẫu 15</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 90deg, rgb(223, 48, 0) 0deg, rgb(223, 48, 0) 27.692deg, rgb(254, 96, 0) 27.692deg, rgb(254, 96, 0) 55.385deg, rgb(255, 145, 37) 55.385deg, rgb(255, 145, 37) 83.077deg, rgb(251, 187, 95) 83.077deg, rgb(251, 187, 95) 110.769deg, rgb(218, 217, 154) 110.769deg, rgb(218, 217, 154) 138.462deg, rgb(169, 230, 202) 138.462deg, rgb(169, 230, 202) 166.154deg, rgb(114, 224, 232) 166.154deg, rgb(114, 224, 232) 193.846deg, rgb(62, 201, 236) 193.846deg, rgb(62, 201, 236) 221.538deg, rgb(20, 163, 214) 221.538deg, rgb(20, 163, 214) 249.231deg, rgb(0, 116, 171) 249.231deg, rgb(0, 116, 171) 276.923deg, rgb(0, 67, 115) 276.923deg, rgb(0, 67, 115) 304.615deg, rgb(18, 22, 55) 304.615deg, rgb(18, 22, 55) 332.308deg, rgb(58, 0, 5) 332.308deg, rgb(58, 0, 5) 360deg);"></span>
                                                    <span>Mẫu 16</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 90deg, rgb(5, 5, 10) 0deg, rgb(5, 5, 10) 27.692deg, rgb(9, 10, 14) 27.692deg, rgb(9, 10, 14) 55.385deg, rgb(13, 14, 18) 55.385deg, rgb(13, 14, 18) 83.077deg, rgb(18, 19, 22) 83.077deg, rgb(18, 19, 22) 110.769deg, rgb(22, 23, 26) 110.769deg, rgb(22, 23, 26) 138.462deg, rgb(26, 27, 30) 138.462deg, rgb(26, 27, 30) 166.154deg, rgb(30, 31, 34) 166.154deg, rgb(30, 31, 34) 193.846deg, rgb(34, 35, 38) 193.846deg, rgb(34, 35, 38) 221.538deg, rgb(38, 39, 42) 221.538deg, rgb(38, 39, 42) 249.231deg, rgb(41, 42, 45) 249.231deg, rgb(41, 42, 45) 276.923deg, rgb(44, 45, 48) 276.923deg, rgb(44, 45, 48) 304.615deg, rgb(46, 47, 51) 304.615deg, rgb(46, 47, 51) 332.308deg, rgb(48, 49, 53) 332.308deg, rgb(48, 49, 53) 360deg);"></span>
                                                    <span>Mẫu 17</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 90deg, rgb(47, 17, 54) 0deg, rgb(47, 17, 54) 27.692deg, rgb(52, 18, 52) 27.692deg, rgb(52, 18, 52) 55.385deg, rgb(58, 22, 52) 55.385deg, rgb(58, 22, 52) 83.077deg, rgb(66, 28, 56) 83.077deg, rgb(66, 28, 56) 110.769deg, rgb(74, 35, 62) 110.769deg, rgb(74, 35, 62) 138.462deg, rgb(83, 45, 70) 138.462deg, rgb(83, 45, 70) 166.154deg, rgb(91, 55, 81) 166.154deg, rgb(91, 55, 81) 193.846deg, rgb(99, 67, 93) 193.846deg, rgb(99, 67, 93) 221.538deg, rgb(105, 80, 107) 221.538deg, rgb(105, 80, 107) 249.231deg, rgb(110, 92, 122) 249.231deg, rgb(110, 92, 122) 276.923deg, rgb(112, 104, 138) 276.923deg, rgb(112, 104, 138) 304.615deg, rgb(112, 116, 153) 304.615deg, rgb(112, 116, 153) 332.308deg, rgb(110, 126, 169) 332.308deg, rgb(110, 126, 169) 360deg);"></span>
                                                    <span>Mẫu 18</span>
                                                </div>
                                            </li>
                                            <li class="dropdown-item">
                                                <div
                                                    class="d-flex align-items-center justify-content-betweenx px-2 py-1">
                                                    <span
                                                        style="width:40px; height:30px; display:inline-block; border-radius:4px;margin-right:5px; background:conic-gradient(from 90deg, rgb(20, 25, 22) 0deg, rgb(20, 25, 22) 27.692deg, rgb(51, 43, 34) 27.692deg, rgb(51, 43, 34) 55.385deg, rgb(83, 60, 47) 55.385deg, rgb(83, 60, 47) 83.077deg, rgb(110, 76, 59) 83.077deg, rgb(110, 76, 59) 110.769deg, rgb(130, 89, 70) 110.769deg, rgb(130, 89, 70) 138.462deg, rgb(139, 98, 79) 138.462deg, rgb(139, 98, 79) 166.154deg, rgb(138, 102, 84) 166.154deg, rgb(138, 102, 84) 193.846deg, rgb(126, 102, 87) 193.846deg, rgb(126, 102, 87) 221.538deg, rgb(104, 98, 86) 221.538deg, rgb(104, 98, 86) 249.231deg, rgb(76, 88, 82) 249.231deg, rgb(76, 88, 82) 276.923deg, rgb(44, 75, 75) 276.923deg, rgb(44, 75, 75) 304.615deg, rgb(12, 59, 65) 304.615deg, rgb(12, 59, 65) 332.308deg, rgb(0, 42, 54) 332.308deg, rgb(0, 42, 54) 360deg);"></span>
                                                    <span>Mẫu 19</span>
                                                </div>
                                            </li>
                                        </ul>
                                    </span>
                                </label>
                                <div class="d-flex">
                                    <div id="previewBox"
                                        style="width: 150px; height: 150px; border: 2px solid red; border-radius: 6px; margin-right: 5px; transition: 0.2s; background: transparent;">
                                    </div>
                                    <textarea class="form-control" id="bg-gradient" rows="2"
                                        placeholder="Nhập CSS gradient (vd: conic-gradient(...))"></textarea>
                                </div>
                                <div class="small text-muted mt-2">➥ Gradient sẽ thay thế nền màu và ảnh đã thiết lập ở
                                    trên</div>
                            </div>

                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" checked="" id="show_particle">
                                <label class="form-check-label" for="show_particle">
                                    Nền chuyển động đẹp <sup class="text-danger">Mới</sup>
                                </label>
                            </div>
                            <div class="small text-secondary mb-3">➥ Nền phải khác màu trắng mới nhìn thấy</div>

                        </fieldset>


                    </div> <!-- //style-tab-pane -->

                    <div class="tab-pane py-4 fade" id="media-tab-pane" role="tabpanel" aria-labelledby="media-tab"
                        tabindex="0">
                        <!-- <div class="h5 mb-3">Ảnh đã tải lên</div> -->
                        <div id="uploaded-list" class="mb-3">
                            <div class="alert alert-warning">Bạn chưa tải lên ảnh nào</div>
                        </div>
                    </div> <!-- media-tab-pane -->

                </div>

            </div> <!-- modal-body -->
            <div class="modal-footer">
                <a class="text-decoration-none text-secondary" id="btn-reset" href="javascript:void(0);">Đặt lại</a>
                <button type="button" class="btn btn-primary" id="btn-save">Lưu lại</button>
            </div>
        </div>
    </div>
</div>