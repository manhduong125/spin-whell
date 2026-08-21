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

        // Hiển thị Popup Trúng Thưởng sau khi nắp hộp mở (400ms)
        setTimeout(function () {
            var customPopupTitle = $.trim($('#popup_title').val() || '') || 'Hộp quà có';
            $('#modal-box-result-label').text(customPopupTitle);
            $('#modal-box-result-title').text(selectedPrize);

            if (turnsLeft > 0) {
                $('#modal-box-result-turns').text('Bạn còn ' + turnsLeft + ' lượt mở').removeClass('bg-danger-subtle text-danger').addClass('bg-secondary-subtle text-secondary-emphasis');
                $('#btn-box-continue').removeClass('d-none').show().text('Mở tiếp (' + turnsLeft + ' lượt) ➔');
                $('#btn-box-view-all').text('Xem tất cả kết quả');
            } else {
                $('#modal-box-result-turns').text('Bạn đã hết lượt mở!').removeClass('bg-secondary-subtle text-secondary-emphasis').addClass('bg-danger-subtle text-danger');
                $('#btn-box-continue').addClass('d-none').hide();
                $('#btn-box-view-all').text('Tổng kết & Nhận quà 🎁');

                // Phát âm thanh kết thúc
                var notiSoundVal = $('#noti_sound').val() || 'concainit';
                var notiSoundFileVal = $.trim($('#noti_sound_file').val() || '');
                var notiSoundUrl = resolveAudioUrl(notiSoundVal, notiSoundFileVal, 'noti_sound');
                playSound(notiSoundUrl);
            }

            openModal('#modalBoxResult');
        }, 400);
    }

    // Tự động sinh danh sách box tương ứng với số lượng phần quà và mẫu được chọn
    function renderBoxes() {
        var gifts = parseGiftsFromTextarea();
        var template = $('#template').val() || 'tpl-default';
        var $content = $('#lucky-box-content');
        if (!$content.length) return;

        $content.empty();

        var springSvg = '<svg version="1.0" xmlns="http://www.w3.org/2000/svg" width="512.000000pt" height="512.000000pt" viewBox="0 0 512.000000 512.000000" preserveAspectRatio="xMidYMid meet"><g transform="translate(0.000000,512.000000) scale(0.100000,-0.100000)" stroke="none"><path d="M2810 4553 c-457 -32 -823 -110 -1054 -224 -192 -96 -307 -207 -359 -347 -17 -46 -21 -79 -21 -172 0 -100 4 -124 27 -182 36 -93 86 -168 163 -244 l66 -64 -30 -34 c-16 -18 -43 -57 -60 -87 -100 -176 -100 -374 -2 -569 55 -109 175 -243 280 -311 l24 -16 -56 -62 c-30 -34 -71 -92 -89 -129 -32 -63 -34 -73 -33 -167 0 -84 4 -110 26 -162 31 -72 75 -140 135 -204 l44 -47 -41 -58 c-56 -80 -74 -147 -67 -248 23 -333 317 -571 802 -651 50 -8 117 -15 151 -15 73 0 104 24 104 81 0 59 -30 78 -141 90 -278 31 -497 119 -625 251 -62 64 -87 106 -108 177 -30 102 -13 193 47 249 l23 21 84 -43 c255 -132 535 -199 830 -199 182 0 273 17 379 70 185 94 213 327 56 472 -152 140 -486 178 -869 99 -129 -26 -311 -89 -405 -139 -89 -48 -98 -45 -166 38 -66 81 -90 137 -90 213 0 84 21 127 102 208 l63 62 38 -16 c292 -128 597 -194 901 -194 492 0 744 187 615 457 -44 91 -141 159 -284 199 -101 29 -433 27 -590 -3 -219 -42 -472 -128 -619 -210 l-63 -35 -68 46 c-279 191 -371 478 -221 688 64 91 49 87 152 38 202 -93 426 -160 664 -196 169 -26 561 -27 690 -1 271 54 434 147 502 285 38 77 40 200 5 277 -60 131 -210 221 -435 262 -136 24 -402 22 -585 -6 -333 -50 -647 -162 -861 -305 l-83 -55 -42 34 c-158 129 -218 333 -143 481 119 235 629 400 1326 430 86 4 164 11 174 16 24 12 45 63 39 94 -9 50 -44 64 -156 62 -55 -1 -121 -3 -146 -5z m489 -923 c144 -29 259 -102 277 -176 11 -42 -3 -103 -31 -140 -82 -107 -286 -170 -587 -181 -338 -12 -704 54 -974 177 l-69 31 25 20 c50 38 267 139 385 178 179 58 380 97 595 115 85 7 293 -6 379 -24z m-39 -1148 c82 -27 136 -70 151 -123 11 -35 10 -43 -7 -67 -26 -36 -113 -78 -199 -97 -100 -22 -328 -30 -460 -16 -145 16 -320 53 -445 96 -56 18 -105 36 -108 39 -13 14 220 101 378 140 173 43 279 55 460 52 135 -3 177 -7 230 -24z m-218 -797 c137 -24 221 -69 248 -135 79 -192 -350 -260 -816 -129 -92 25 -276 97 -292 114 -9 9 13 21 101 55 243 95 545 133 759 95z"></path></g></svg>';

        gifts.forEach(function (gift, index) {
            var boxItemHtml = '';
            if (template === 'tpl-jib') {
                boxItemHtml = '<div class="col-4 col-md-3 col-lg-2 box-col-item">' +
                    '<div class="box-jack" id="box-' + index + '" data-index="' + index + '">' +
                        '<div class="lid"></div>' +
                        '<div class="top"></div>' +
                        '<div class="front"></div>' +
                        '<div class="right"></div>' +
                        '<div class="jack">' +
                            '<div class="gift">' +
                                '<div class="ball">' +
                                    '<div class="txt">🎀</div>' +
                                    '<div class="layer moving">' +
                                        '<div class="layer gridplane xline"></div>' +
                                        '<div class="layer gridplane xline2"></div>' +
                                        '<div class="layer gridplane yline"></div>' +
                                        '<div class="layer gridplane zline"></div>' +
                                        '<div class="layer gridplane laser"></div>' +
                                        '<div class="layer gridplane laser2"></div>' +
                                    '</div>' +
                                    '<div class="layer clip">' +
                                        '<div class="shade"></div>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="spring">' + springSvg + '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            } else {
                boxItemHtml = '<div class="col-4 col-md-3 col-lg-2 box-col-item">' +
                    '<div class="box giftbox" id="box-' + index + '" data-index="' + index + '">BOX ' + (index + 1) + '</div>' +
                '</div>';
            }
            $content.append(boxItemHtml);
        });
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

    var defaultPluginUrl = (typeof wp_spin_wheel_params !== 'undefined' && wp_spin_wheel_params.plugin_url)
        ? wp_spin_wheel_params.plugin_url
        : ((typeof wp_spin_box_params !== 'undefined' && wp_spin_box_params.plugin_url) ? wp_spin_box_params.plugin_url : '/wp-content/plugins/spin-whell/');
    var DEFAULT_BOX_BG_IMG = defaultPluginUrl + 'assets/img/christmas-2.jpg';
    var DEFAULT_BOX_BG_GRADIENT = 'conic-gradient(from 45deg, rgb(10, 74, 89) 0deg, rgb(10, 74, 89) 45deg, rgb(13, 89, 149) 45deg, rgb(13, 89, 149) 90deg, rgb(17, 109, 208) 90deg, rgb(17, 109, 208) 135deg, rgb(22, 136, 255) 135deg, rgb(22, 136, 255) 180deg, rgb(27, 166, 255) 180deg, rgb(27, 166, 255) 225deg, rgb(33, 200, 255) 225deg, rgb(33, 200, 255) 270deg, rgb(40, 236, 255) 270deg, rgb(40, 236, 255) 315deg, rgb(47, 255, 255) 315deg, rgb(47, 255, 255) 360deg)';

    // ── Hiệu ứng Particles cho Hộp quà (tương tự Wheel) ──
    var BOX_PARTICLE_CONFIGS = {
        default: {
            particles: {
                number: { value: 60, density: { enable: true, value_area: 800 } },
                color: { value: ['#ff6b6b', '#feca57', '#48dbfb', '#ff9ff3', '#54a0ff', '#5f27cd'] },
                shape: { type: 'circle' },
                opacity: { value: 0.6, random: true, anim: { enable: true, speed: 0.5, opacity_min: 0.1 } },
                size: { value: 4, random: true },
                line_linked: { enable: true, distance: 120, color: '#ffffff', opacity: 0.2, width: 1 },
                move: { enable: true, speed: 2, direction: 'none', random: true, out_mode: 'out' }
            },
            interactivity: {
                detect_on: 'canvas',
                events: { onhover: { enable: false }, onclick: { enable: false } }
            },
            retina_detect: true
        },
        snow: {
            particles: {
                number: { value: 120, density: { enable: true, value_area: 800 } },
                color: { value: '#ffffff' },
                shape: { type: 'circle' },
                opacity: { value: 0.8, random: true },
                size: { value: 5, random: true },
                line_linked: { enable: false },
                move: { enable: true, speed: 2, direction: 'bottom', random: true, straight: false, out_mode: 'out', bounce: false }
            },
            interactivity: {
                detect_on: 'canvas',
                events: { onhover: { enable: false }, onclick: { enable: false } }
            },
            retina_detect: true
        },
        bubble: {
            particles: {
                number: { value: 40, density: { enable: true, value_area: 800 } },
                color: { value: ['#a8edea', '#fed6e3', '#96fbc4', '#f9f7d9', '#ffecd2'] },
                shape: { type: 'circle', stroke: { width: 2, color: '#ffffff' } },
                opacity: { value: 0.4, random: true, anim: { enable: true, speed: 1, opacity_min: 0.1, sync: false } },
                size: { value: 18, random: true, anim: { enable: true, speed: 3, size_min: 4, sync: false } },
                line_linked: { enable: false },
                move: { enable: true, speed: 1.5, direction: 'top', random: true, straight: false, out_mode: 'out' }
            },
            interactivity: {
                detect_on: 'canvas',
                events: { onhover: { enable: false }, onclick: { enable: false } }
            },
            retina_detect: true
        },
        heart: {
            particles: {
                number: { value: 30, density: { enable: true, value_area: 800 } },
                color: { value: ['#ff6b6b', '#ff4757', '#ff6b81', '#ff4500', '#ffa502'] },
                shape: {
                    type: 'char',
                    stroke: { width: 0 },
                    character: { value: ['❤', '💕', '💖', '💗'], font: 'Verdana', style: '', weight: '400' }
                },
                opacity: { value: 0.8, random: true, anim: { enable: true, speed: 0.5, opacity_min: 0.2 } },
                size: { value: 14, random: true },
                line_linked: { enable: false },
                move: { enable: true, speed: 2, direction: 'top', random: true, straight: false, out_mode: 'out' }
            },
            interactivity: {
                detect_on: 'canvas',
                events: { onhover: { enable: false }, onclick: { enable: false } }
            },
            retina_detect: true
        }
    };

    function initBoxParticles() {
        if (typeof window.particlesJS === 'undefined') return;
        var enabled = $('#box_show_particle').length ? $('#box_show_particle').is(':checked') : ($('#show_particle').length ? $('#show_particle').is(':checked') : true);
        var type = $('#box_particle_type').length ? $('#box_particle_type').val() : ($('#particle_type').length ? $('#particle_type').val() : 'default');
        var el = document.getElementById('box-particles-js') || document.getElementById('particles-js');
        if (!el) return;

        el.innerHTML = '';
        if (!enabled) return;

        var config = BOX_PARTICLE_CONFIGS[type] || BOX_PARTICLE_CONFIGS['default'];
        window.particlesJS(el.id, config);
    }

    // Cài đặt giao diện & style
    function applyBoxSettings() {
        var title = $.trim($('#hqmm-title').val() || '') || 'HỘP QUÀ MAY MẮN ONLINE';
        $('#heading-title').text(title);

        var templateClass = $('#template').val() || 'tpl-default';
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
                this.style.background = 'url("' + DEFAULT_BOX_BG_IMG + '") center center / cover no-repeat';
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

        // Tự động sinh lại các box theo số lượng quà và mẫu
        renderBoxes();

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
                show_particle: $('#box_show_particle').length ? $('#box_show_particle').is(':checked') : ($('#show_particle').length ? $('#show_particle').is(':checked') : true),
                particle_type: $('#box_particle_type').length ? $('#box_particle_type').val() : ($('#particle_type').length ? $('#particle_type').val() : 'default')
            };
            localStorage.setItem(getBoxStorageKey(), JSON.stringify(boxPayload));
            localStorage.setItem('wp_spin_box_settings_guest', JSON.stringify(boxPayload));
        } catch (e) {}

        initBoxParticles();

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
            bg_img: DEFAULT_BOX_BG_IMG,
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
            if (finalSettings.particle_type) {
                $('#box_particle_type, #particle_type').val(finalSettings.particle_type);
            }
        }

        // Luôn áp dụng settings khi trang tải
        applyBoxSettings();
        setTimeout(initBoxParticles, 300);

        // Live update hiệu ứng khi thay đổi trong popup
        $(document).on('change', '#box_show_particle, #show_particle, #box_particle_type, #particle_type', function () {
            initBoxParticles();
        });

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
            var tplCode = $(this).data('content') || 'tpl-default';
            var tplTitle = $(this).data('title') || 'Hộp quà truyền thống';
            $('#template').val(tplCode);
            $('#btn-dropdown-select-tpl .item-title').text(tplTitle);
            $('#lucky-box').attr('class', 'lucky-box no-ads ' + tplCode);
            renderBoxes();
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
            $('#bg_img').val(DEFAULT_BOX_BG_IMG);
            $('#bg_gradient').val('');
            $('#box-bgr-preview').attr('src', DEFAULT_BOX_BG_IMG);
            $('#box-bgr-preview-wrap').show();
            $('#box_show_particle, #show_particle').prop('checked', true);
            $('#box_particle_type, #particle_type').val('default');

            applyBoxSettings();
            initBoxParticles();
            resetGame();
            closeModal('#settingsModal');
        });

        // 11. Nhận quà trong modal kết quả -> Ghi nhận vào DB & Mở popup xác nhận (#modalBoxClaim)
        $(document).on('click', '.btn-claim-gift', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var giftName = $btn.data('gift') || 'Phần quà may mắn';
            var randomCode = 'HQ-' + Math.floor(100000 + Math.random() * 900000);

            $('#modal-box-claim-gift-name').text(giftName);
            $('#modal-box-claim-code').text(randomCode);

            // Ghi nhận lượt nhận quà lên Database
            var params = window.wp_spin_box_params || window.wp_spin_wheel_params || {};
            var activeBoxId = boxId || parseInt($('#lucky-box').attr('data-box-id'), 10) || (params.user_box_id || 0);

            if (params.rest_url) {
                $.ajax({
                    url: params.rest_url + 'boxes/' + activeBoxId + '/claim',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        box_id: activeBoxId,
                        gift: giftName,
                        code: randomCode
                    }),
                    success: function (res) {
                        if (res && res.reward_code) {
                            $('#modal-box-claim-code').text(res.reward_code);
                        }
                    }
                });
            }

            closeModal('#modalKetqua');
            setTimeout(function() {
                openModal('#modalBoxClaim');
            }, 300);
        });

        // 12. Click nút "Xem tất cả kết quả & Nhận quà" từ Popup trúng thưởng
        $(document).on('click', '#btn-box-view-all', function (e) {
            e.preventDefault();
            closeModal('#modalBoxResult');
            renderKetquaTable();
            setTimeout(function() {
                openModal('#modalKetqua');
            }, 300);
        });

        // 13. Click nút "Chơi lại lượt mới" trong popup kết quả
        $(document).on('click', '#btn-modal-reload-box', function (e) {
            e.preventDefault();
            closeModal('#modalKetqua');
            resetGame();
        });

        // 14. Preview âm thanh trong popup cài đặt
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
