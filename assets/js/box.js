/**
 * WP Spin Wheel - Mystery Box / Hộp quà may mắn Engine
 */
(function ($) {
    'use strict';

    var container = $('#lucky-box');
    var boxId = parseInt(container.data('box-id'), 10) || 0;
    var maxTurns = parseInt($('#luotchoi').val(), 10) || parseInt($('#conlai').text(), 10) || 3;
    var turnsLeft = maxTurns;
    var openedBoxes = 0;
    var sessionResults = [];
    var giftList = [];

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

        var $wrap = $('#lucky-box, .lucky-box-page');
        if (bgImg) {
            $wrap.css({ 'background-image': 'url("' + bgImg + '")', 'background-size': 'cover', 'background-position': 'center' });
        } else if (bgGradient) {
            $wrap.css({ 'background': bgGradient });
        } else if (bgColor) {
            $wrap.css({ 'background-color': bgColor, 'background-image': 'none' });
        }

        $wrap.css({ 'color': color });
        $('.button-group .btn, .btn-brand').css({ 'background-color': btnBgColor, 'color': btnColor });
        $('.box-info .card').css({ 'background-color': btnBgColor, 'color': btnColor });

        maxTurns = parseInt($('#luotchoi').val(), 10) || 3;
        turnsLeft = maxTurns;
        updateTurnsDisplay();

        // Lưu vào localStorage cho khách
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
                btn_color: btnColor
            };
            localStorage.setItem('wp_spin_box_settings_guest', JSON.stringify(boxPayload));
        } catch (e) {}

        // Nếu đã đăng nhập -> Lưu lên server
        if (typeof wp_spin_wheel_params !== 'undefined' && wp_spin_wheel_params.is_logged_in) {
            syncBoxToServer(boxPayload);
        }
    }

    // Đồng bộ lên server nếu đăng nhập
    function syncBoxToServer(payload) {
        if (typeof wp_spin_wheel_params === 'undefined' || !wp_spin_wheel_params.is_logged_in) return;
        $.ajax({
            url: wp_spin_wheel_params.rest_url + 'user-wheel',
            method: 'POST',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wp_spin_wheel_params.nonce);
            },
            contentType: 'application/json',
            data: JSON.stringify({
                wheel_id: boxId || 0,
                type: 'box',
                title: payload.title,
                settings: payload,
                prizes: payload.gifts.map(function(g) { return { title: g, color: '#dc3545' }; })
            })
        });
    }

    // Khởi tạo các sự kiện khi DOM sẵn sàng
    $(document).ready(function () {
        // Nạp cấu hình từ localStorage nếu có
        try {
            var saved = parseJson(localStorage.getItem('wp_spin_box_settings_guest'), null);
            if (saved) {
                if (saved.title) {
                    $('#hqmm-title').val(saved.title);
                    $('#heading-title').text(saved.title);
                }
                if (saved.gifts && Array.isArray(saved.gifts)) {
                    $('#section-list').val(saved.gifts.join('\n'));
                }
                if (saved.luotchoi) {
                    $('#luotchoi').val(saved.luotchoi);
                    maxTurns = saved.luotchoi;
                    turnsLeft = maxTurns;
                    $('#conlai').text(turnsLeft);
                }
                if (saved.template) $('#template').val(saved.template);
                if (saved.sound) $('#sound').val(saved.sound);
                if (saved.sound_file) $('#sound_file').val(saved.sound_file);
                if (saved.noti_sound) $('#noti_sound').val(saved.noti_sound);
                if (saved.noti_sound_file) $('#noti_sound_file').val(saved.noti_sound_file);
                if (saved.bg_img) $('#bg_img').val(saved.bg_img);
                if (saved.bg_gradient) $('#bg_gradient').val(saved.bg_gradient);
                if (saved.bg_color) $('#bg_color').val(saved.bg_color);
                if (saved.color) $('#color').val(saved.color);
                if (saved.btn_bg_color) $('#btn_bg_color').val(saved.btn_bg_color);
                if (saved.btn_color) $('#btn_color').val(saved.btn_color);
                if (saved.confetti !== undefined) $('#confetti').prop('checked', !!saved.confetti);

                applyBoxSettings();
            }
        } catch (e) {}

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
            $('#btn-dropdown-select-tpl .item-img').attr('class', 'item-img ' + tplCode + ' me-1');
            $('#lucky-box').attr('class', 'lucky-box no-ads ' + tplCode);
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
