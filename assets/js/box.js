/**
 * WP Spin Wheel - Mystery Box / Hộp quà may mắn Engine
 */
(function ($) {
    'use strict';

    if (!$('#lucky-box, .lucky-box-page').length) {
        return;
    }

    var container = $('#lucky-box, .lucky-box-page').first();
    var boxId = parseInt(container.data('box-id'), 10) || 0;
    var maxTurns = parseInt($('#luotchoi').val(), 10) || parseInt($('#conlai').text(), 10) || 3;
    var turnsLeft = maxTurns;
    var openedBoxes = 0;
    var sessionResults = [];
    var giftList = [];

    // Key lưu settings vào localStorage (phân biệt theo từng box_id và user_id)
    function getBoxStorageKey() {
        var activeId = boxId || $('#lucky-box').attr('data-box-id') || '0';
        var params = window.wp_spin_box_params || window.wp_spin_wheel_params || {};
        var userId = params.user_id ? params.user_id : 'guest';
        return 'wp_spin_box_ui_settings_box_' + activeId + '_user_' + userId;
    }

    // Parse JSON an toàn
    function parseJson(val, fallback) {
        if (!val) return fallback;
        try {
            var res = JSON.parse(val);
            return res !== null ? res : fallback;
        } catch (e) {
            return fallback;
        }
    }

    // Lấy danh sách quà tặng từ textarea #section-list
    function parseGiftsFromTextarea() {
        var raw = $.trim($('#section-list').val() || '');
        if (!raw) {
            return ['100k', 'Ốp lưng iphone', '50k', 'Chúc bạn may mắn', '200k', 'Bút Montblanc', 'Ví da 500k', 'Sổ tay', 'Gối tựa lưng', 'Bình giữ nhiệt', 'Ly sứ', 'Hộp đựng cơm'];
        }
        var lines = raw.split('\n').map(function (s) { return $.trim(s); }).filter(function (s) { return s.length > 0; });
        return lines.length ? lines : ['100k', '50k', '200k', 'Chúc bạn may mắn'];
    }

    // Audio Player
    var audioPlayer = null;
    function getAudioPlayer() {
        if (!audioPlayer) {
            audioPlayer = document.createElement('audio');
            audioPlayer.preload = 'auto';
        }
        return audioPlayer;
    }

    function resolveAudioUrl(soundVal, soundFileVal, selectId) {
        if (!soundVal || soundVal === '0') return null;
        if (soundFileVal) {
            return 'https://nhactik.com/play/' + soundFileVal + '.mp3';
        }
        if (soundVal === 'random' && selectId) {
            var urls = [];
            $('#' + selectId + ' option[data-url]').each(function () {
                var u = $(this).data('url');
                if (u) urls.push(u);
            });
            if (urls.length) return urls[Math.floor(Math.random() * urls.length)];
        }
        var pluginUrl = (typeof wp_spin_wheel_params !== 'undefined' && wp_spin_wheel_params.plugin_url)
            ? wp_spin_wheel_params.plugin_url
            : '/wp-content/plugins/spin-whell/';
        if (!pluginUrl.endsWith('/')) pluginUrl += '/';
        return pluginUrl + 'assets/sound/' + soundVal + '.mp3';
    }

    function playSound(url) {
        if (!url) return;
        try {
            var p = getAudioPlayer();
            p.src = url;
            p.currentTime = 0;
            p.play().catch(function () {});
        } catch (e) {}
    }

    // Render bảng danh sách quà tặng trong #modalQuatang
    function renderQuatangTable() {
        var $tbody = $('#modalQuatang tbody');
        if (!$tbody.length) return;
        $tbody.empty();
        var gifts = parseGiftsFromTextarea();
        gifts.forEach(function (g, idx) {
            $tbody.append('<tr><td>' + (idx + 1) + '</td><td>' + g + '</td></tr>');
        });
    }

    // Render bảng kết quả mở box trong #modalKetqua
    function renderKetquaTable() {
        var $tbody = $('#modalKetqua tbody');
        if (!$tbody.length) return;
        $tbody.empty();
        if (!sessionResults.length) {
            $tbody.append('<tr><td colspan="3" class="text-center text-muted">Chưa có lượt mở hộp quà nào.</td></tr>');
            return;
        }
        sessionResults.forEach(function (item, idx) {
            var tr = $('<tr>' +
                '<td>' + (idx + 1) + '</td>' +
                '<td class="fw-bold text-primary">' + item.prize + '</td>' +
                '<td><button type="button" class="btn btn-sm btn-danger btn-claim-gift" data-gift="' + item.prize + '">Nhận quà</button></td>' +
                '</tr>');
            $tbody.append(tr);
        });
    }

    // Mở modal tương thích
    function openModal(selector) {
        var el = document.querySelector(selector);
        if (!el) return;
        if (window.bootstrap && window.bootstrap.Modal) {
            try {
                var inst = window.bootstrap.Modal.getOrCreateInstance(el);
                if (inst) { inst.show(); return; }
            } catch (e) {}
        }
        if (window.jQuery && $(el).modal) {
            try { $(el).modal('show'); return; } catch (e) {}
        }
        $(el).addClass('show').css('display', 'block').removeAttr('aria-hidden').attr('aria-modal', 'true');
    }

    // Đóng modal tương thích
    function closeModal(selector) {
        var el = typeof selector === 'string' ? document.querySelector(selector) : selector;
        if (!el) return;
        if (window.bootstrap && window.bootstrap.Modal) {
            try {
                var inst = window.bootstrap.Modal.getInstance(el);
                if (inst) { inst.hide(); return; }
            } catch (e) {}
        }
        if (window.jQuery && $(el).modal) {
            try { $(el).modal('hide'); return; } catch (e) {}
        }
        $(el).removeClass('show').css('display', 'none').attr('aria-hidden', 'true');
        $('.modal-backdrop').remove();
    }

    // Cập nhật lượt còn lại
    function updateTurnsDisplay() {
        $('#conlai').text(turnsLeft);
        if (turnsLeft <= 0) {
            $('#btn-reload').removeClass('d-none').show();
        } else {
            $('#btn-reload').addClass('d-none').hide();
        }
    }

    // Xử lý mở một hộp quà
    function openBox(boxEl) {
        var $box = $(boxEl);
        if ($box.hasClass('opened') || $box.hasClass('active')) {
            return; // Hộp đã mở
        }
        if (turnsLeft <= 0) {
            // Đã hết lượt mở
            alert('Bạn đã hết lượt mở hộp quà! Nhấn nút "Chơi lại" để bắt đầu lượt mới.');
            return;
        }

        turnsLeft--;
        openedBoxes++;
        updateTurnsDisplay();

        // Lấy danh sách quà
        var gifts = parseGiftsFromTextarea();
        var selectedPrize = gifts[Math.floor(Math.random() * gifts.length)] || 'Phần quà bí mật';

        // Ghi nhận kết quả
        sessionResults.push({
            box_index: $box.attr('id') || openedBoxes,
            prize: selectedPrize,
            time: new Date().toLocaleTimeString('vi-VN')
        });

        // Cập nhật text phần thưởng vào quả bóng / nội dung hộp
        $box.find('.gift .ball, .gift .txt').text(selectedPrize);
        $box.addClass('opened active');

        // Bắn hoa giấy nếu có bật option
        if ($('#confetti').is(':checked') && typeof window.confetti === 'function') {
            window.confetti({
                particleCount: 80,
                spread: 70,
                origin: { y: 0.6 }
            });
        }

        // Phát âm thanh khi mở hộp
        var soundVal = $('#sound').val() || 'winner';
        var soundFileVal = $.trim($('#sound_file').val() || '');
        var openSoundUrl = resolveAudioUrl(soundVal, soundFileVal, 'sound');
        playSound(openSoundUrl);

        // Nếu hết lượt mở -> phát âm thanh kết thúc và mở modal kết quả
        if (turnsLeft <= 0) {
            setTimeout(function () {
                var notiSoundVal = $('#noti_sound').val() || 'concainit';
                var notiSoundFileVal = $.trim($('#noti_sound_file').val() || '');
                var notiSoundUrl = resolveAudioUrl(notiSoundVal, notiSoundFileVal, 'noti_sound');
                playSound(notiSoundUrl);

                renderKetquaTable();
                openModal('#modalKetqua');
            }, 1200);
        }
    }

    // Reset lại toàn bộ trò chơi
    function resetGame() {
        maxTurns = parseInt($('#luotchoi').val(), 10) || 3;
        turnsLeft = maxTurns;
        openedBoxes = 0;
        sessionResults = [];
        updateTurnsDisplay();

        $('.box-jack, .box').removeClass('opened active');
        $('.box-jack .gift .ball, .box-jack .gift .txt').text('🎀');
        $('#btn-reload').addClass('d-none').hide();
    }

    var DEFAULT_BOX_BG_GRADIENT = 'conic-gradient(from 45deg, rgb(10, 74, 89) 0deg, rgb(10, 74, 89) 45deg, rgb(13, 89, 149) 45deg, rgb(13, 89, 149) 90deg, rgb(17, 109, 208) 90deg, rgb(17, 109, 208) 135deg, rgb(22, 136, 255) 135deg, rgb(22, 136, 255) 180deg, rgb(27, 166, 255) 180deg, rgb(27, 166, 255) 225deg, rgb(33, 200, 255) 225deg, rgb(33, 200, 255) 270deg, rgb(40, 236, 255) 270deg, rgb(40, 236, 255) 315deg, rgb(47, 255, 255) 315deg, rgb(47, 255, 255) 360deg)';

    // Cài đặt giao diện & style
    function applyBoxSettings() {
        var title = $.trim($('#hqmm-title').val() || '') || 'HỘP QUÀ MAY MẮN ONLINE';
        $('#heading-title').text(title);

        var templateClass = $('#template').val() || 'tpl-jib';
        $('#lucky-box').attr('class', 'lucky-box no-ads ' + templateClass);

        var bgImg = $.trim($('#bg_img').val() || '');
        var bgGradient = $.trim($('#bg_gradient').val() || '');
        var bgColor = $('#bg_color').val() || '';
        var color = $('#color').val() || '#ffffff';
        var btnBgColor = $('#btn_bg_color').val() || '#dc3545';
        var btnColor = $('#btn_color').val() || '#ffffff';

        var $page = $('.lucky-box-page');
        if (!$page.length) {
            $page = $('#lucky-box').closest('.lucky-box-page');
        }
        if (!$page.length) {
            $page = $('#lucky-box');
        }

        $page.each(function () {
            this.style.removeProperty('background');
            this.style.removeProperty('background-image');
            this.style.removeProperty('background-color');
            this.style.removeProperty('background-size');
            this.style.removeProperty('background-position');
            this.style.removeProperty('background-repeat');

            if (bgGradient) {
                this.style.background = bgGradient;
            } else if (bgImg) {
                this.style.background = 'url("' + bgImg + '") center center / cover no-repeat';
            } else if (bgColor) {
                this.style.backgroundColor = bgColor;
            } else {
                this.style.background = DEFAULT_BOX_BG_GRADIENT;
            }
        });

        $('#lucky-box').css({
            'background': 'transparent',
            'background-color': 'transparent',
            'background-image': 'none',
            'color': color
        });

        $page.css({ 'color': color });
        $('.button-group .btn, .btn-brand').css({ 'background-color': btnBgColor, 'color': btnColor });
        $('.box-info .card').css({ 'background-color': btnBgColor, 'color': btnColor });

        maxTurns = parseInt($('#luotchoi').val(), 10) || 3;
        turnsLeft = maxTurns;
        updateTurnsDisplay();

        // Lưu vào localStorage cho khách & user
        try {
            var boxPayload = {
                title: title,
                gifts: parseGiftsFromTextarea(),
                luotchoi: maxTurns,
                template: templateClass,
                sound: $('#sound').val(),
                sound_file: $('#sound_file').val(),
                noti_sound: $('#noti_sound').val(),
                noti_sound_file: $('#noti_sound_file').val(),
                popup_title: $('#popup_title').val(),
                confetti: $('#confetti').is(':checked'),
                bg_img: bgImg,
                bg_gradient: bgGradient,
                bg_color: bgColor,
                color: color,
                btn_bg_color: btnBgColor,
                btn_color: btnColor,
                show_particle: $('#box_show_particle').length ? $('#box_show_particle').is(':checked') : ($('#show_particle').length ? $('#show_particle').is(':checked') : true)
            };
            localStorage.setItem(getBoxStorageKey(), JSON.stringify(boxPayload));
            localStorage.setItem('wp_spin_box_settings_guest', JSON.stringify(boxPayload));
        } catch (e) {}

        // Nếu đã đăng nhập -> Lưu lên server
        var params = window.wp_spin_box_params || window.wp_spin_wheel_params;
        if (params && params.is_logged_in) {
            syncBoxToServer(boxPayload);
        }
    }

    // Đồng bộ lên server nếu đăng nhập (chỉ lưu vào Box, tuyệt đối không ảnh hưởng đến Wheel)
    function syncBoxToServer(payload) {
        var params = window.wp_spin_box_params || window.wp_spin_wheel_params;
        if (!params || !params.is_logged_in) return;
        var activeId = boxId || parseInt($('#lucky-box').attr('data-box-id'), 10) || (params.user_box_id || 0);

        $.ajax({
            url: params.rest_url + 'user-box',
            method: 'POST',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', params.nonce);
            },
            contentType: 'application/json',
            data: JSON.stringify({
                box_id: activeId,
                id: activeId,
                title: payload.title,
                settings: payload,
                gifts: payload.gifts
            }),
            success: function (res) {
                if (res && res.box_id) {
                    boxId = parseInt(res.box_id, 10);
                    $('#lucky-box').attr('data-box-id', res.box_id);
                }
            }
        });
    }

    // Khởi tạo các sự kiện khi DOM sẵn sàng
    $(document).ready(function () {
        // 1. Đọc cài đặt từ data attribute PHP
        var containerEl = $('#lucky-box, #lucky-box-page').first();
        var phpSettings = parseJson(containerEl.attr('data-box-settings'), null);
        var phpGifts = parseJson(containerEl.attr('data-box-gifts'), null);

        // 2. Đọc cài đặt của user đăng nhập từ server params (nếu có)
        var params = window.wp_spin_box_params || window.wp_spin_wheel_params || {};
        var serverSettings = null;
        var serverGifts = null;
        if (params.user_box_data) {
            if (params.user_box_data.settings) serverSettings = params.user_box_data.settings;
            if (params.user_box_data.gifts) serverGifts = params.user_box_data.gifts;
        }

        // 3. Đọc cài đặt đã lưu trong localStorage (nếu có)
        var storedSettings = parseJson(localStorage.getItem(getBoxStorageKey()), null);
        var guestSettings = parseJson(localStorage.getItem('wp_spin_box_settings_guest'), null);

        var finalSettings = $.extend({}, {
            title: 'HỘP QUÀ MAY MẮN ONLINE',
            template: 'tpl-jib',
            luotchoi: 3,
            sound: 'winner',
            sound_file: '',
            noti_sound: 'concainit',
            noti_sound_file: '',
            popup_title: 'Hộp quà có',
            confetti: true,
            bg_color: '#dc3545',
            color: '#ffffff',
            bg_img: '',
            bg_gradient: '',
            btn_bg_color: '#dc3545',
            btn_color: '#ffffff',
            show_particle: true
        }, phpSettings || {}, serverSettings || {}, storedSettings || {}, guestSettings || {});

        if (finalSettings) {
            if (finalSettings.title) {
                $('#hqmm-title').val(finalSettings.title);
                $('#heading-title').text(finalSettings.title);
            }
            if (finalSettings.gifts && Array.isArray(finalSettings.gifts) && finalSettings.gifts.length) {
                $('#section-list').val(finalSettings.gifts.join('\n'));
            } else if (phpGifts && Array.isArray(phpGifts) && phpGifts.length) {
                $('#section-list').val(phpGifts.join('\n'));
            }
            if (finalSettings.luotchoi) {
                $('#luotchoi').val(finalSettings.luotchoi);
                maxTurns = parseInt(finalSettings.luotchoi, 10) || 3;
                turnsLeft = maxTurns;
                $('#conlai').text(turnsLeft);
            }
            if (finalSettings.template) {
                $('#template').val(finalSettings.template);
                var tplItem = $('#btn-select-tpl .dropdown-item[data-content="' + finalSettings.template + '"]');
                if (tplItem.length) {
                    $('#btn-dropdown-select-tpl .item-title').text(tplItem.data('title'));
                }
            }
            if (finalSettings.sound) $('#sound').val(finalSettings.sound);
            if (finalSettings.sound_file) $('#sound_file').val(finalSettings.sound_file);
            if (finalSettings.noti_sound) $('#noti_sound').val(finalSettings.noti_sound);
            if (finalSettings.noti_sound_file) $('#noti_sound_file').val(finalSettings.noti_sound_file);
            if (finalSettings.bg_img) {
                $('#bg_img').val(finalSettings.bg_img);
                $('#box-bgr-preview').attr('src', finalSettings.bg_img);
                $('#box-bgr-preview-wrap').show();
            }
            if (finalSettings.bg_gradient) {
                $('#bg_gradient').val(finalSettings.bg_gradient);
                $('#box-gradient-preview-box').css('background', finalSettings.bg_gradient);
            }
            if (finalSettings.bg_color) $('#bg_color').val(finalSettings.bg_color);
            if (finalSettings.color) $('#color').val(finalSettings.color);
            if (finalSettings.btn_bg_color) $('#btn_bg_color').val(finalSettings.btn_bg_color);
            if (finalSettings.btn_color) $('#btn_color').val(finalSettings.btn_color);
            if (finalSettings.show_particle !== undefined) {
                $('#box_show_particle, #show_particle').prop('checked', !!finalSettings.show_particle);
            }
        }

        // Luôn áp dụng settings khi trang tải
        applyBoxSettings();

        // Click mở hộp quà
        $(document).on('click', '.box-jack, .box', function (e) {
            e.preventDefault();
            openBox(this);
        });

        // ══════════════════════════════════════════════════════════
        // GÁN CÁC NÚT VỚI MODAL / POPUP THEO YÊU CẦU
        // ══════════════════════════════════════════════════════════

        // 1. btn-share trỏ đến popup share (#modalShare)
        $(document).on('click', '#btn-share', function (e) {
            e.preventDefault();
            openModal('#modalShare');
        });

        // 2. btn-manage trỏ đến popup-option-box (#settingsModal)
        $(document).on('click', '#btn-manage', function (e) {
            e.preventDefault();
            openModal('#settingsModal');
        });

        // 3. btn-settings trỏ đến popup-option box (#settingsModal)
        $(document).on('click', '#btn-settings, .btn-settings', function (e) {
            e.preventDefault();
            openModal('#settingsModal');
        });

        // 4. btn-quatang trỏ đến popup-box-item (#modalQuatang)
        $(document).on('click', '#btn-quatang', function (e) {
            e.preventDefault();
            renderQuatangTable();
            openModal('#modalQuatang');
        });

        // 5. btn-ketqua trỏ đến popup-result-box (#modalKetqua)
        $(document).on('click', '#btn-ketqua', function (e) {
            e.preventDefault();
            renderKetquaTable();
            openModal('#modalKetqua');
        });

        // 6. btn-reload: Chơi lại lượt mới
        $(document).on('click', '#btn-reload', function (e) {
            e.preventDefault();
            resetGame();
        });

        // 7. btn-user: Tài khoản (#modalUserInfo hoặc #modalNoneUser)
        $(document).on('click', '#btn-user', function (e) {
            e.preventDefault();
            var targetModal = (typeof wp_spin_wheel_params !== 'undefined' && wp_spin_wheel_params.is_logged_in)
                ? '#modalUserInfo'
                : '#modalNoneUser';
            openModal(targetModal);
        });

        // 8. Đóng modal khi click data-bs-dismiss hoặc nút đóng
        $(document).on('click', '[data-bs-dismiss="modal"], [data-dismiss="modal"], .btn-close', function (e) {
            var $modal = $(this).closest('.modal');
            if ($modal.length) {
                closeModal($modal[0]);
            }
        });

        // 9. Nút Lưu trong #settingsModal (#btn-save-box-settings, #btn_box_setting)
        $(document).on('click', '#btn-save-box-settings, #btn_box_setting, #btn_setting', function (e) {
            e.preventDefault();
            applyBoxSettings();
            var $btn = $(this);
            var origText = $btn.text();
            $btn.text('✓ Đã lưu').addClass('btn-success').removeClass('btn-primary');
            setTimeout(function () {
                $btn.text(origText).removeClass('btn-success').addClass('btn-primary');
                closeModal('#settingsModal');
            }, 800);
        });

        // 10. Chọn template giao diện trong dropdown #btn-select-tpl
        $(document).on('click', '#btn-select-tpl .dropdown-item', function (e) {
            e.preventDefault();
            var tplCode = $(this).data('content') || 'tpl-jib';
            var tplTitle = $(this).data('title') || 'Ball in box';
            $('#template').val(tplCode);
            $('#btn-dropdown-select-tpl .item-title').text(tplTitle);
            $('#lucky-box').attr('class', 'lucky-box no-ads ' + tplCode);
        });

        // ══════════════════════════════════════════════════════════
        // THƯ VIỆN ẢNH NỀN VÀ GRADIENT (GIỐNG WHEEL)
        // ══════════════════════════════════════════════════════════

        // Áp dụng từ bảng Thư viện (.sw-box-media-apply)
        $(document).on('click', '.sw-box-media-apply', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var type = $btn.data('type');
            var origText = $btn.text();

            if (type === 'bgr') {
                var url = $btn.data('url') || '';
                if (url) {
                    $('#bg_img').val(url);
                    $('#bg_gradient').val('');
                    $('#box-bgr-preview').attr('src', url);
                    $('#box-bgr-preview-wrap').show();
                    applyBoxSettings();
                }
            } else if (type === 'grd') {
                var gradientVal = $btn.data('gradient') || $btn.closest('tr').find('.sw-gradient-preview').data('gradient') || '';
                if (gradientVal) {
                    $('#bg_gradient').val(gradientVal);
                    $('#bg_img').val('');
                    $('#box-bgr-preview-wrap').hide();
                    $('#box-gradient-preview-box').css('background', gradientVal);
                    applyBoxSettings();
                }
            }

            $btn.text('✓ Đã chọn').addClass('btn-success').removeClass('btn-secondary');
            setTimeout(function () {
                $btn.text(origText).removeClass('btn-success').addClass('btn-secondary');
            }, 1200);
        });

        // Click các vòng tròn mẫu swatch gradient trong Tab 2
        $(document).on('click', '.sw-box-gradient-swatch', function (e) {
            e.preventDefault();
            var gradientVal = $(this).data('gradient') || '';
            if (gradientVal) {
                $('#bg_gradient').val(gradientVal);
                $('#bg_img').val('');
                $('#box-bgr-preview-wrap').hide();
                $('#box-gradient-preview-box').css('background', gradientVal);
                applyBoxSettings();
            }
        });

        // Nút Áp dụng Gradient thủ công
        $(document).on('click', '#btn-apply-box-gradient', function (e) {
            e.preventDefault();
            var gradientVal = $.trim($('#bg_gradient').val() || '');
            if (gradientVal) {
                $('#bg_img').val('');
                $('#box-bgr-preview-wrap').hide();
                $('#box-gradient-preview-box').css('background', gradientVal);
                applyBoxSettings();
            }
        });

        // Nút Áp dụng ảnh URL
        $(document).on('click', '#btn-apply-box-img', function (e) {
            e.preventDefault();
            var url = $.trim($('#bg_img').val() || '');
            if (url) {
                $('#bg_gradient').val('');
                $('#box-bgr-preview').attr('src', url);
                $('#box-bgr-preview-wrap').show();
                applyBoxSettings();
            }
        });

        // Nút Xoá ảnh nền
        $(document).on('click', '#btn-clear-box-img', function (e) {
            e.preventDefault();
            $('#bg_img').val('');
            $('#box-bgr-preview-wrap').hide();
            $('#box-bgr-preview').attr('src', '');
            applyBoxSettings();
        });

        // Xử lý Upload ảnh nền
        $(document).on('change', '#upload_box_bgr', function () {
            var file = this.files && this.files[0];
            if (!file) return;
            if (file.size > 5 * 1024 * 1024) {
                alert('Dung lượng ảnh tối đa 5MB.');
                return;
            }
            var reader = new FileReader();
            reader.onload = function (e) {
                var dataUrl = e.target.result;
                $('#bg_img').val(dataUrl);
                $('#bg_gradient').val('');
                $('#box-bgr-preview').attr('src', dataUrl);
                $('#box-bgr-preview-wrap').show();
                applyBoxSettings();
            };
            reader.readAsDataURL(file);
        });

        // Chọn mẫu ảnh nền từ thư viện
        $(document).on('click', '.sw-bg-sample', function (e) {
            e.preventDefault();
            var bgUrl = $(this).data('url') || '';
            if (bgUrl) {
                $('#bg_img').val(bgUrl);
                $('#bg_gradient').val('');
                $('#box-bgr-preview').attr('src', bgUrl);
                $('#box-bgr-preview-wrap').show();
                applyBoxSettings();
            }
        });

        // Nút Áp dụng ảnh URL
        $(document).on('click', '#btn-apply-box-img', function (e) {
            e.preventDefault();
            var url = $.trim($('#bg_img').val() || '');
            if (url) {
                $('#bg_gradient').val('');
                $('#box-bgr-preview').attr('src', url);
                $('#box-bgr-preview-wrap').show();
                $('#lucky-box, .lucky-box-page').css({
                    'background-image': 'url("' + url + '")',
                    'background-size': 'cover',
                    'background-position': 'center'
                });
            }
        });

        // Nút Xoá ảnh nền
        $(document).on('click', '#btn-clear-box-img', function (e) {
            e.preventDefault();
            $('#bg_img').val('');
            $('#box-bgr-preview-wrap').hide();
            $('#box-bgr-preview').attr('src', '');
            $('#lucky-box, .lucky-box-page').css({ 'background-image': 'none' });
        });

        // Xử lý Upload ảnh nền
        $(document).on('change', '#upload_box_bgr', function () {
            var file = this.files && this.files[0];
            if (!file) return;
            if (file.size > 5 * 1024 * 1024) {
                alert('Dung lượng ảnh tối đa 5MB.');
                return;
            }
            var reader = new FileReader();
            reader.onload = function (e) {
                var dataUrl = e.target.result;
                $('#bg_img').val(dataUrl);
                $('#bg_gradient').val('');
                $('#box-bgr-preview').attr('src', dataUrl);
                $('#box-bgr-preview-wrap').show();
                $('#lucky-box, .lucky-box-page').css({
                    'background-image': 'url("' + dataUrl + '")',
                    'background-size': 'cover',
                    'background-position': 'center'
                });
            };
            reader.readAsDataURL(file);
        });

        // Nút đặt lại cài đặt Box về mặc định (#btn-reset-box)
        $(document).on('click', '#btn-reset-box', function (e) {
            e.preventDefault();
            localStorage.removeItem(getBoxStorageKey());
            localStorage.removeItem('wp_spin_box_settings_guest');

            $('#hqmm-title').val('HỘP QUÀ MAY MẮN ONLINE');
            $('#heading-title').text('HỘP QUÀ MAY MẮN ONLINE');
            $('#section-list').val("100k\nỐp lưng iphone\n50k\nChúc bạn may mắn\n200k\nBút Montblanc\nVí da 500k\nSổ tay\nGối tựa lưng\nBình giữ nhiệt\nLy sứ\nHộp đựng cơm");
            $('#luotchoi').val('3');
            $('#sound').val('winner');
            $('#sound_file').val('');
            $('#noti_sound').val('concainit');
            $('#noti_sound_file').val('');
            $('#popup_title').val('Hộp quà có');
            $('#confetti').prop('checked', true);
            $('#template').val('tpl-jib');
            $('#btn-dropdown-select-tpl .item-title').text('Ball in box (Mặc định)');
            $('#bg_color').val('#dc3545');
            $('#color').val('#ffffff');
            $('#btn_bg_color').val('#dc3545');
            $('#btn_color').val('#ffffff');
            $('#bg_img').val('');
            $('#bg_gradient').val('');
            $('#box_show_particle, #show_particle').prop('checked', true);

            applyBoxSettings();
            resetGame();
            closeModal('#settingsModal');
        });

        // 11. Nhận quà trong modal kết quả
        $(document).on('click', '.btn-claim-gift', function (e) {
            e.preventDefault();
            var giftName = $(this).data('gift') || '';
            alert('Chúc mừng bạn đã chọn nhận phần quà: ' + giftName + '!\nVui lòng liên hệ ban tổ chức để hoàn tất.');
        });

        // 12. Preview âm thanh trong popup cài đặt
        $(document).on('click', '#btn-sound-play', function () {
            var val = $('#sound').val();
            var url = resolveAudioUrl(val, '', 'sound');
            playSound(url);
        });

        $(document).on('click', '#btn-sound-file-play', function () {
            var fId = $.trim($('#sound_file').val());
            if (fId) playSound('https://nhactik.com/play/' + fId + '.mp3');
        });

        $(document).on('click', '#btn-noti_sound-play', function () {
            var val = $('#noti_sound').val();
            var url = resolveAudioUrl(val, '', 'noti_sound');
            playSound(url);
        });

        $(document).on('click', '#btn-noti_sound-file-play', function () {
            var fId = $.trim($('#noti_sound_file').val());
            if (fId) playSound('https://nhactik.com/play/' + fId + '.mp3');
        });

        // Đóng modal khi click vào vùng backdrop ngoài
        $(document).on('click', '.modal', function (e) {
            if (e.target === this) {
                closeModal(this);
            }
        });

        // Khởi tạo hiển thị ban đầu
        renderQuatangTable();
        updateTurnsDisplay();
    });

    // Expose toàn cục
    window.openBox = openBox;
    window.resetGame = resetGame;
    window.applyBoxSettings = applyBoxSettings;

})(jQuery);
