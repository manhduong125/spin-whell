jQuery(document).ready(function ($) {
    var container = $('#wheel-wrapper');
    var wheelId = parseInt(container.data('wheel-id'), 10) || 0;
    var nonce = container.data('nonce');
    var rawSettings = container.attr('data-wheel-settings');
    var rawPrizes = container.attr('data-wheel-prizes');
    var wheelSettings = {};
    var wheelPrizes = [];
    var spinning = false;
    var pendingRequest = false;
    var currentRotation = 0;
    var idleAnimationId = null;
    var IDLE_SPEED = 0.001; // radians per frame (~0.17°/frame)

    // Key lưu settings vào localStorage (phân biệt theo từng wheel_id và user_id)
    function getSettingsStorageKey() {
        var activeId = wheelId || $('#wheel-wrapper').attr('data-wheel-id') || '0';
        var userId = (typeof wp_spin_wheel_params !== 'undefined' && wp_spin_wheel_params.user_id) ? wp_spin_wheel_params.user_id : 'guest';
        return 'wp_spin_wheel_ui_settings_wheel_' + activeId + '_user_' + userId;
    }

    var syncTimeout = null;
    // Đồng bộ lên server theo đúng ID vòng quay nếu user đã đăng nhập
    function syncUserWheelToServer(customData) {
        if (typeof wp_spin_wheel_params === 'undefined' || !wp_spin_wheel_params.is_logged_in) {
            return;
        }
        clearTimeout(syncTimeout);
        syncTimeout = setTimeout(function () {
            var activeId = (customData && customData.wheel_id) ? customData.wheel_id : (wheelId || $('#wheel-wrapper').attr('data-wheel-id') || 0);
            var data = {
                wheel_id: activeId,
                id: activeId,
                settings: collectSettings(),
                prizes: wheelPrizes,
                title: $.trim($('#txt-title').text() || $('#vqmm-title').text() || ''),
                description: $.trim($('#txt-desc').text() || $('#vqmm-desc').text() || '')
            };
            if (customData) {
                $.extend(data, customData);
            }
            $.ajax({
                url: wp_spin_wheel_params.rest_url + 'user-wheel',
                method: 'POST',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', wp_spin_wheel_params.nonce);
                },
                contentType: 'application/json',
                data: JSON.stringify(data),
                success: function (res) {
                    if (res && res.wheel_id) {
                        wheelId = parseInt(res.wheel_id, 10);
                        $('#wheel-wrapper').attr('data-wheel-id', res.wheel_id);
                    }
                },
                error: function (err) {
                    console.warn('Lỗi lưu vòng quay lên server:', err);
                }
            });
        }, 300);
    }

    // Đọc tất cả option từ modal settings thành object
    function collectSettings() {
        var spinBgIsImage = $('#switch_spin_bg_type').is(':checked');
        // background_image có thể là URL hoặc dataURL từ upload — luôn lấy từ wheelSettings
        var currentBgImage = (wheelSettings.button && wheelSettings.button.background_image) || '';
        return {
            animation: {
                duration: parseFloat($('#duration').val()) || 6,
                confetti: $('#show_confetti').is(':checked'),
            },
            audio: {
                start_sound: $('#start_sound').val() || '0',
                start_sound_file: $.trim($('#start_sound_file').val()),
                end_sound: $('#end_sound').val() || '0',
                end_sound_file: $.trim($('#end_sound_file').val()),
            },
            ui: {
                auto_remove: $('#auto_remove').is(':checked'),
                show_popup: $('#show_popup').is(':checked'),
                popup_label: $.trim($('#popup_label').val()),
                show_remove_button: $('#show_remove_button').is(':checked'),
            },
            button: {
                text: $.trim($('#btn-spin-label').val()),
                bg_type: currentBgImage ? 'image' : 'color',
                color: currentBgImage ? '' : ($('#btn-spin-color').val() || '#ff0000'),
                text_color: currentBgImage ? '' : ($('#btn-spin-text-color').val() || '#ffffff'),
                background_image: currentBgImage,
            },
            // background: luôn đồng bộ từ wheelSettings
            background: $.extend({}, wheelSettings.background || {}),
            wheel: {
                size: (wheelSettings.wheel && wheelSettings.wheel.size) || 600,
                border: (wheelSettings.wheel && wheelSettings.wheel.border) || 14,
                border_color: $('#border_color').length ? $('#border_color').val() : ((wheelSettings.wheel && wheelSettings.wheel.border_color) || '#FF4D00'),
                diamond_color: $('#diamond_color').length ? $('#diamond_color').val() : ((wheelSettings.wheel && wheelSettings.wheel.diamond_color) || '#F6FA00'),
                show_border: $('#show_border').length ? $('#show_border').is(':checked') : (wheelSettings.wheel ? wheelSettings.wheel.show_border !== false : true),
                shadow: (wheelSettings.wheel && wheelSettings.wheel.shadow) !== undefined ? wheelSettings.wheel.shadow : true,
            },
            sector_colors: getSelectedSectorColors(),
        };
    }
    // Áp settings đã lưu vào các control trong modal
    function applySettingsToUI(s) {
        if (!s) return;
        if (s.animation) {
            if (s.animation.duration !== undefined) {
                $('#duration').val(s.animation.duration);
            }
            if (s.animation.confetti !== undefined) {
                $('#show_confetti').prop('checked', !!s.animation.confetti);
            }
        }
        if (s.audio) {
            if (s.audio.start_sound !== undefined) {
                $('#start_sound').val(s.audio.start_sound);
            }
            if (s.audio.start_sound_file !== undefined) {
                $('#start_sound_file').val(s.audio.start_sound_file);
            }
            if (s.audio.end_sound !== undefined) {
                $('#end_sound').val(s.audio.end_sound);
            }
            if (s.audio.end_sound_file !== undefined) {
                $('#end_sound_file').val(s.audio.end_sound_file);
            }
        }
        if (s.ui) {
            if (s.ui.auto_remove !== undefined) {
                $('#auto_remove').prop('checked', !!s.ui.auto_remove);
            }
            if (s.ui.show_popup !== undefined) {
                $('#show_popup').prop('checked', !!s.ui.show_popup);
            }
            if (s.ui.popup_label !== undefined) {
                $('#popup_label').val(s.ui.popup_label);
                $('#modal-result-popup-label').text(s.ui.popup_label || 'Bạn đã quay vào ô');
            }
            if (s.ui.show_remove_button !== undefined) {
                $('#show_remove_button').prop('checked', !!s.ui.show_remove_button);
            }
        }
        if (s.button) {
            var b = s.button;
            if (b.text !== undefined) {
                $('#btn-spin-label').val(b.text);
            }
            var isImage = b.bg_type === 'image' || (!b.bg_type && !!b.background_image);
            $('#switch_spin_bg_type').prop('checked', isImage);
            if (isImage) {
                $('#form_spin_bg_color').addClass('d-none');
                $('#form_spin_bg_image').removeClass('d-none');
                if (b.background_image !== undefined) {
                    $('#btn-spin-img').val(b.background_image);
                    updateSpinImgPreview(b.background_image);
                }
            } else {
                $('#form_spin_bg_image').addClass('d-none');
                $('#form_spin_bg_color').removeClass('d-none');
                if (b.color) {
                    $('#btn-spin-color').val(b.color);
                }
                if (b.text_color) {
                    $('#btn-spin-text-color').val(b.text_color);
                }
            }
        }
        if (s.background) {
            if (s.background.image) {
                $('#custom-bg-img').val(s.background.image);
                $('#bgr-preview').attr('src', s.background.image);
                $('#bgr-preview-wrap').show();
                $('#bg-gradient').val('');
                $('#gradient-preview-box').css('background', 'transparent');
            } else if (s.background.gradient) {
                $('#bg-gradient').val(s.background.gradient);
                $('#gradient-preview-box').css('background', s.background.gradient);
                $('#custom-bg-img').val('');
                $('#bgr-preview-wrap').hide();
                $('#bgr-preview').attr('src', '');
            }
            if (s.background.color) {
                $('#custom-bg-color').val(s.background.color);
            }
        }
        if (s.wheel) {
            if (s.wheel.border_color && $('#border_color').length) {
                $('#border_color').val(s.wheel.border_color);
            }
            if (s.wheel.diamond_color && $('#diamond_color').length) {
                $('#diamond_color').val(s.wheel.diamond_color);
            }
            if (s.wheel.show_border !== undefined && $('#show_border').length) {
                $('#show_border').prop('checked', !!s.wheel.show_border);
                if (s.wheel.show_border) {
                    $('#custom_border_color').show();
                } else {
                    $('#custom_border_color').hide();
                }
            }
        }
    }
    // Merge saved settings vào wheelSettings
    function mergeUserSettings(saved) {
        if (!saved) return;
        if (saved.animation) {
            wheelSettings.animation = $.extend({}, wheelSettings.animation, saved.animation);
        }
        if (saved.audio) {
            wheelSettings.audio = $.extend({}, wheelSettings.audio, saved.audio);
        }
        if (saved.ui) {
            wheelSettings.ui = $.extend({}, wheelSettings.ui, saved.ui);
        }
        if (saved.button) {
            wheelSettings.button = $.extend({}, wheelSettings.button, saved.button);
        }
        if (saved.background) {
            wheelSettings.background = $.extend({}, wheelSettings.background, saved.background);
        }
        if (saved.wheel) {
            wheelSettings.wheel = $.extend({}, wheelSettings.wheel, saved.wheel);
        }
        if (saved.sector_colors) {
            wheelSettings.sector_colors = saved.sector_colors;
        }
    }
    // Lưu settings + apply vào wheelSettings + re-render
    function saveWheelSettings() {
        var s = collectSettings();
        // 1. Lưu localStorage (cả 2 trường hợp)
        localStorage.setItem(getSettingsStorageKey(), JSON.stringify(s));
        // 2. Merge vào wheelSettings đang dùng
        mergeUserSettings(s);
        // 3. Nếu user đã đăng nhập: lưu vào post spin_wheel của user trên server (Trường hợp 2)
        syncUserWheelToServer({ settings: s });
        // Re-render vòng quay để áp dụng thay đổi giao diện
        renderWheel();
        // Feedback trên nút
        var $btn = $('#btn_wheel_setting');
        $btn.text('Đã lưu \u2713').addClass('btn-success').removeClass('btn-primary');
        setTimeout(function () {
            $btn.text('Lưu lại').removeClass('btn-success').addClass('btn-primary');
        }, 1500);
    }
    // Reset về default
    function resetWheelSettings() {
        localStorage.removeItem(getSettingsStorageKey());
        // Reset wheelSettings về default từ data attribute
        wheelSettings = normalizeSettings(parseJson(rawSettings));
        // Reset UI controls về giá trị mặc định
        applySettingsToUI({
            animation: { duration: 6, confetti: false },
            audio: { start_sound: '0', start_sound_file: '', end_sound: '0', end_sound_file: '' },
            ui: { auto_remove: false, show_popup: false, popup_label: '', show_remove_button: false },
            button: { text: 'Quay', bg_type: 'color', color: '#ff0000', text_color: '#ffffff', background_image: '' },
            background: { gradient: DEFAULT_BG_GRADIENT, image: '', color: '' },
            wheel: { border_color: '#FF4D00', diamond_color: '#F6FA00', show_border: true, border: 14 },
        });
        renderWheel();
    }

    // Cập nhật preview ảnh nút quay
    function updateSpinImgPreview(url) {
        var $wrap = $('#spin-img-preview-wrap');
        var $img = $('#spin-img-preview');
        if (url) {
            $img.attr('src', url);
            $wrap.show();
        } else {
            $wrap.hide();
            $img.attr('src', '');
        }
    }

    function parseJson(value, fallback) {
        if (!value) {
            return fallback;
        }
        try {
            var parsed = JSON.parse(value);
            return parsed !== null ? parsed : fallback;
        } catch (e) {
            return typeof value === 'object' ? value : fallback;
        }
    }

    // Gradient mặc định cho #wheel-wrapper (phải khớp với CSS fallback)
    var DEFAULT_BG_GRADIENT = 'conic-gradient(from 90deg, rgb(223, 48, 0) 0deg, rgb(223, 48, 0) 27.692deg, rgb(254, 96, 0) 27.692deg, rgb(254, 96, 0) 55.385deg, rgb(255, 145, 37) 55.385deg, rgb(255, 145, 37) 83.077deg, rgb(251, 187, 95) 83.077deg, rgb(251, 187, 95) 110.769deg, rgb(218, 217, 154) 110.769deg, rgb(218, 217, 154) 138.462deg, rgb(169, 230, 202) 138.462deg, rgb(169, 230, 202) 166.154deg, rgb(114, 224, 232) 166.154deg, rgb(114, 224, 232) 193.846deg, rgb(62, 201, 236) 193.846deg, rgb(62, 201, 236) 221.538deg, rgb(20, 163, 214) 221.538deg, rgb(20, 163, 214) 249.231deg, rgb(0, 116, 171) 249.231deg, rgb(0, 116, 171) 276.923deg, rgb(0, 67, 115) 276.923deg, rgb(0, 67, 115) 304.615deg, rgb(18, 22, 55) 304.615deg, rgb(18, 22, 55) 332.308deg, rgb(58, 0, 5) 332.308deg, rgb(58, 0, 5) 360deg)';

    function normalizeSettings(settings) {
        settings = settings || {};
        var background = settings.background || {};
        var backgroundColor = '';
        var backgroundImage = '';
        var backgroundType = 'color';

        if (typeof background === 'string') {
            backgroundColor = background;
        } else if (typeof background === 'object' && background !== null) {
            backgroundType = background.type || 'color';
            backgroundColor = background.value || background.color || '';
            backgroundImage = background.image || '';
        }

        return {
            background: {
                type: backgroundType,
                color: backgroundColor,
                image: backgroundImage,
                // nếu không có gì → dùng gradient mặc định
                gradient: background.gradient || DEFAULT_BG_GRADIENT,
            },
            wheel: {
                size: (settings.wheel && settings.wheel.size) || 600,
                border: (settings.wheel && settings.wheel.border) !== undefined ? settings.wheel.border : 14,
                border_color: (settings.wheel && settings.wheel.border_color) || ($('#border_color').length ? $('#border_color').val() : '#FF4D00'),
                diamond_color: (settings.wheel && settings.wheel.diamond_color) || ($('#diamond_color').length ? $('#diamond_color').val() : '#F6FA00'),
                show_border: (settings.wheel && settings.wheel.show_border) !== undefined ? !!settings.wheel.show_border : ($('#show_border').length ? $('#show_border').is(':checked') : true),
                shadow: (settings.wheel && settings.wheel.shadow) !== undefined ? settings.wheel.shadow : true,
            },
            button: settings.button || {
                text: 'QUAY',
                color: '#ff0000',
                text_color: '#ffffff',
                radius: 50,
                background_image: '',
            },
            animation: settings.animation || {
                duration: 6,
                confetti: true,
            },
            audio: settings.audio || {
                spin: '',
                win: '',
            },
            custom_css: settings.custom_css || '',
        };
    }
    function openTitleDescriptionEditor() {
        var title = $.trim($('#vqmm-title').text());
        var description = $.trim($('#vqmm-desc').text());

        $('#editTitle').val(title || 'Vòng quay may mắn');
        $('#editDesc').val(description || 'Nhấn vào nút quay để bắt đầu.');
        $('#edit-mode-txt').text('Đang chỉnh sửa...');
        $('#modal-edit').show().attr('aria-hidden', 'false');
    }

    function closeTitleDescriptionEditor() {
        $('#modal-edit').hide().attr('aria-hidden', 'true');
    }

    function saveTitleDesc() {
        var title = $.trim($('#editTitle').val());
        var description = $.trim($('#editDesc').val());

        if (!title) {
            title = 'Vòng quay may mắn';
        }

        $('#vqmm-title').text(title);
        if (description) {
            $('#vqmm-desc').html(description.replace(/\n/g, '<br>'));
        } else {
            $('#vqmm-desc').empty();
        }

        if (wheelId && nonce) {
            $.ajax({
                url: wp_spin_wheel_params.rest_url + 'wheels/' + wheelId,
                type: 'PUT',
                dataType: 'json',
                contentType: 'application/json',
                headers: {
                    'x_wp_nonce': nonce,
                },
                data: JSON.stringify({
                    title: title,
                    content: description,
                    settings: {},
                    prizes: wheelPrizes,
                }),
                success: function () {
                    $('#edit-mode-txt').text('Đã lưu');
                },
                error: function () {
                    $('#edit-mode-txt').text('Lưu thất bại');
                },
            });
        } else {
            $('#edit-mode-txt').text('Đã cập nhật');
        }

        closeTitleDescriptionEditor();
    }

    // Bảng 6 màu mặc định cho các ô vòng quay
    var DEFAULT_SECTOR_COLORS = ['#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899'];

    // ── Hội màu sắc vòng quay ──
    var SECTOR_COLOR_SLOTS = 6;

    // Lấy danh sách màu đang chọn từ các checkbox
    function getSelectedSectorColors() {
        var colors = [];
        for (var i = 1; i <= SECTOR_COLOR_SLOTS; i++) {
            var $chk = $('#chkcolor-' + i);
            if ($chk.length && $chk.is(':checked')) {
                var val = $('#color-' + i).val() || '';
                if (val) colors.push(val);
            }
        }
        return colors;
    }

    // Sync màu trong `wheelPrizes` với hội màu đã chọn
    function syncSectorColors() {
        if (!wheelPrizes.length) return;
        var colors = getSelectedSectorColors();
        wheelPrizes.forEach(function (prize, index) {
            prize.color = colors.length ? colors[index % colors.length] : DEFAULT_SECTOR_COLORS[index % DEFAULT_SECTOR_COLORS.length];
        });
        savePrizes();
        renderPrizeList();
        renderWheel();
    }

    // Render các pill màu đã chọn
    function renderSectorColorPills() {
        var $list = $('#sector-colors-list');
        if (!$list.length) return;

        $list.empty();
        var colors = getSelectedSectorColors();

        if (!colors.length) {
            $list.html('<span class="text-muted small">Chưa chọn màu nào</span>');
            return;
        }

        colors.forEach(function (color, index) {
            var $pill = $('<span/>', {
                'class': 'badge rounded-pill sw-sector-color-pill',
                css: {
                    backgroundColor: color,
                    color: '#ffffff',
                    cursor: 'pointer',
                    fontSize: '12px',
                },
                'data-color-index': index,
                'data-color-value': color,
                'data-slot': index + 1,
            });
            $pill.text(color + ' ');
            $('<span/>', {
                'class': 'sw-remove-color',
                css: { marginLeft: '4px', cursor: 'pointer' },
                text: '×',
            }).appendTo($pill);
            $list.append($pill);
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function escapeAttr(str) {
        if (!str) return '';
        return String(str).replace(/"/g, '&quot;');
    }

    // Tự động chuẩn hóa đường dẫn ảnh (hỗ trợ cả URL online lẫn đường dẫn tương đối trong plugin)
    function resolveAssetUrl(url) {
        if (!url) return '';
        url = $.trim(url);
        // Nếu là data URI hoặc URL đầy đủ (http/https/protocol-relative) -> giữ nguyên
        if (/^(https?:|\/\/|data:)/i.test(url)) {
            return url;
        }
        var pluginUrl = (typeof wp_spin_wheel_params !== 'undefined' && wp_spin_wheel_params.plugin_url)
            ? wp_spin_wheel_params.plugin_url
            : '/wp-content/plugins/spin-whell/';
        if (!pluginUrl.endsWith('/')) pluginUrl += '/';
        // Bỏ dấu / ở đầu url nếu có (ví dụ "/assets/..." -> "assets/...")
        var cleanPath = url.replace(/^\/+/, '');
        return pluginUrl + cleanPath;
    }

    function loadAndRenderThemeDropdown() {
        var $dropdown = $('#myDropdown .dropdown-menu');
        if (!$dropdown.length) return;

        var themesUrl = (typeof wp_spin_wheel_params !== 'undefined' && wp_spin_wheel_params.themes_json_url)
            ? wp_spin_wheel_params.themes_json_url
            : ((typeof wp_spin_wheel_params !== 'undefined' && wp_spin_wheel_params.plugin_url)
                ? wp_spin_wheel_params.plugin_url + 'assets/data/themes.json'
                : '/wp-content/plugins/spin-whell/assets/data/themes.json');

        $.getJSON(themesUrl, function (data) {
            if (data && data.categories) {
                renderThemeDropdown(data.categories);
            }
        }).fail(function (err) {
            console.error('Không thể tải file themes.json:', err);
        });
    }

    function renderThemeDropdown(categories) {
        var $dropdown = $('#myDropdown .dropdown-menu');
        if (!$dropdown.length || !categories || !categories.length) return;

        var html = '';
        categories.forEach(function (cat) {
            html += '<div class="item-header border-top border-bottom pt-3 pb-2 m-2">' + escapeHtml(cat.name) + '</div>';
            (cat.items || []).forEach(function (item) {
                var colors = item.colors || [];
                var contentAttr = colors.join(',');
                var borderAttr = (item.border || []).join(',');
                var spinLabel = item.spin_label || '';
                var spinImg = resolveAssetUrl(item.spin_img || '');
                var isStroke = item.is_stroke || 'no';
                var bgImg = resolveAssetUrl(item.bg_img || '');
                var bgGradient = item.bg_gradient || '';
                var title = item.title || '';
                var tcsw = item.tcsw || '';

                var svgIcons = '';
                colors.forEach(function (color) {
                    svgIcons += '<svg width="12" height="12" style="margin-left: 5px;">' +
                        '<rect width="12" height="12" style="fill:' + color + '; stroke-width: 1; stroke: rgb(0, 0, 0);"></rect>' +
                        '</svg>';
                });

                html += '<div class="dropdown-item ms-1"' +
                    ' data-content="' + escapeAttr(contentAttr) + '"' +
                    ' data-border="' + escapeAttr(borderAttr) + '"' +
                    ' data-spin_label="' + escapeAttr(spinLabel) + '"' +
                    ' data-spin_img="' + escapeAttr(spinImg) + '"' +
                    ' data-is_stroke="' + escapeAttr(isStroke) + '"' +
                    ' data-bg_img="' + escapeAttr(bgImg) + '"' +
                    ' data-bg_gradient="' + escapeAttr(bgGradient) + '"' +
                    ' data-title="' + escapeAttr(title) + '"' +
                    ' data-tcsw="' + escapeAttr(tcsw) + '">' +
                    '<div class="d-flex justify-content-between item">' +
                    '<div class="item-title">' + escapeHtml(title) + '</div>' +
                    '<div class="item-icon">' + svgIcons + '</div>' +
                    '</div>' +
                    '</div>';
            });
        });

        $dropdown.html(html);
    }

    function normalizePrize(prize, index) {
        prize = prize || {};
        return {
            id: prize.id || prize.title + '-' + index,
            title: prize.title || prize.name || '',
            color: prize.color || prize.background || DEFAULT_SECTOR_COLORS[index % DEFAULT_SECTOR_COLORS.length],
            description: prize.description || '',
        };
    }

    function getStorageKey() {
        var activeId = wheelId || $('#wheel-wrapper').attr('data-wheel-id') || '0';
        var userId = (typeof wp_spin_wheel_params !== 'undefined' && wp_spin_wheel_params.user_id) ? wp_spin_wheel_params.user_id : 'guest';
        return 'wp_spin_wheel_prizes_wheel_' + activeId + '_user_' + userId;
    }
    function loadPrizes() {
        var prizes = parseJson(rawPrizes, []);
        // Trường hợp 2: Nếu user đăng nhập và có dữ liệu từ server
        if (typeof wp_spin_wheel_params !== 'undefined' && wp_spin_wheel_params.is_logged_in && wp_spin_wheel_params.user_wheel_data && Array.isArray(wp_spin_wheel_params.user_wheel_data.prizes) && wp_spin_wheel_params.user_wheel_data.prizes.length) {
            prizes = wp_spin_wheel_params.user_wheel_data.prizes;
        } else {
            // Trường hợp 1: Tải từ localStorage
            var stored = parseJson(localStorage.getItem(getStorageKey()), null);
            if (Array.isArray(stored) && stored.length) {
                prizes = stored;
            }
        }
        return prizes.map(normalizePrize);
    }
    function savePrizes() {
        localStorage.setItem(getStorageKey(), JSON.stringify(wheelPrizes));
        // Trường hợp 2: Đồng bộ lên server bài viết spin_wheel của user
        syncUserWheelToServer({ prizes: wheelPrizes });
    }

    function renderPrizeList() {
        var list = $('#sector_list');
        list.empty();

        if (!wheelPrizes.length) {
            list.removeClass('has-prizes');
            updateEntriesCount();
            return;
        }

        wheelPrizes.forEach(function (prize, index) {
            var item = $('<div/>', {
                'class': 'wheel-prize-item d-flex justify-content-between align-items-center',
                'data-index': index,
                'data-id': prize.id,
            });
            $('<span/>', {
                'class': 'prize-title flex-grow-1',
                text: prize.title,
            }).appendTo(item);
            $('<span/>', {
                'class': 'badge rounded-pill',
                css: {
                    backgroundColor: prize.color,
                    color: '#ffffff',
                },
                text: prize.color,
            }).appendTo(item);
            $('<button/>', {
                type: 'button',
                'class': 'btn btn-sm btn-outline-danger btn-remove-prize ms-2',
                text: '×',
                'data-index': index,
                title: 'Xóa',
            }).appendTo(item);
            list.append(item);
        });

        list.addClass('has-prizes');
        updateEntriesCount();
    }

    function updateEntriesCount() {
        $('#entries_count').text(wheelPrizes.length);
    }

    function sortPrizes(ascending) {
        wheelPrizes.sort(function (a, b) {
            var titleA = (a.title || '').toLowerCase();
            var titleB = (b.title || '').toLowerCase();
            if (titleA < titleB) {
                return ascending ? -1 : 1;
            }
            if (titleA > titleB) {
                return ascending ? 1 : -1;
            }
            return 0;
        });
        savePrizes();
        renderPrizeList();
        renderWheel();
    }

    function addPrize(title, color) {
        title = $.trim(title);
        color = color || '#10b981';
        if (!title) {
            return;
        }
        wheelPrizes.push({
            id: title + '-' + Date.now(),
            title: title,
            color: color,
            description: '',
        });
        savePrizes();
        renderPrizeList();
        renderWheel();
    }

    function removePrize(index) {
        if (index < 0 || index >= wheelPrizes.length) {
            return;
        }
        wheelPrizes.splice(index, 1);
        savePrizes();
        renderPrizeList();
        renderWheel();
    }

    function clearPrizes() {
        wheelPrizes = [];
        savePrizes();
        renderPrizeList();
        renderWheel();
    }

    function restoreDefaultPrizes() {
        wheelPrizes = parseJson(rawPrizes, []).map(normalizePrize);
        savePrizes();
        renderPrizeList();
        renderWheel();
    }

    function updateResultCount() {
        var count = $('#wheel_result').children().length;
        $('#result_count').text(count);
    }

    function getPrizeIndex(prize) {
        return wheelPrizes.findIndex(function (item) {
            return item.id === prize.id || item.title === prize.title;
        });
    }

    function drawWheelCanvas(rotation) {
        var canvas = document.getElementById('wheel');
        if (!canvas || !wheelPrizes.length) {
            return;
        }

        var size = wheelSettings.wheel.size;
        var ctx = canvas.getContext('2d');
        var centerX = size / 2;
        var centerY = size / 2;

        var showBorder = (wheelSettings.wheel && wheelSettings.wheel.show_border) !== undefined ? wheelSettings.wheel.show_border : true;
        if ($('#show_border').length) {
            showBorder = $('#show_border').is(':checked');
        }
        var borderColor = (wheelSettings.wheel && wheelSettings.wheel.border_color) || ($('#border_color').length ? $('#border_color').val() : '#FF4D00');
        var diamondColor = (wheelSettings.wheel && wheelSettings.wheel.diamond_color) || ($('#diamond_color').length ? $('#diamond_color').val() : '#F6FA00');

        var borderWidth = showBorder ? Math.max(14, (wheelSettings.wheel && wheelSettings.wheel.border) || 14) : 0;
        var sliceRadius = centerX - borderWidth;
        var borderRingRadius = centerX - borderWidth / 2;
        var segmentCount = wheelPrizes.length;
        var angleStep = (Math.PI * 2) / segmentCount;

        canvas.width = size;
        canvas.height = size;
        canvas.style.width = size + 'px';
        canvas.style.height = size + 'px';

        ctx.clearRect(0, 0, size, size);
        ctx.save();
        ctx.translate(centerX, centerY);
        ctx.rotate(rotation);
        ctx.translate(-centerX, -centerY);

        if (wheelSettings.wheel.shadow) {
            ctx.shadowColor = 'rgba(0,0,0,0.15)';
            ctx.shadowBlur = 20;
        }

        // Nền vòng tròn phía sau
        ctx.beginPath();
        ctx.arc(centerX, centerY, centerX - 1, 0, Math.PI * 2);
        ctx.fillStyle = wheelSettings.background.color || '#ffffff';
        ctx.fill();

        ctx.shadowColor = 'transparent';

        // Vẽ từng ô phần thưởng (sector slices)
        wheelPrizes.forEach(function (prize, index) {
            var startAngle = index * angleStep;
            var endAngle = startAngle + angleStep;
            var fillColor = prize.color || DEFAULT_SECTOR_COLORS[index % DEFAULT_SECTOR_COLORS.length];

            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, sliceRadius, startAngle, endAngle);
            ctx.closePath();
            ctx.fillStyle = fillColor;
            ctx.fill();
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = 2;
            ctx.stroke();

            ctx.save();
            var textAngle = startAngle + angleStep / 2;
            var textRadius = sliceRadius * 0.65;
            ctx.translate(centerX + Math.cos(textAngle) * textRadius, centerY + Math.sin(textAngle) * textRadius);
            ctx.rotate(textAngle);
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 20px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            var text = prize.title || prize.name || 'Prize';
            var lines = text.split('\n');
            lines.forEach(function (line, lineIndex) {
                ctx.fillText(line, 0, (lineIndex - (lines.length - 1) / 2) * 22);
            });
            ctx.restore();
        });

        // Vẽ viền ngoài và các nút kim cương nếu bật showBorder
        if (showBorder && borderWidth > 0) {
            // 1. Viền ngoài cùng (Màu viền: borderColor)
            ctx.beginPath();
            ctx.arc(centerX, centerY, borderRingRadius, 0, Math.PI * 2);
            ctx.lineWidth = borderWidth;
            ctx.strokeStyle = borderColor;
            ctx.stroke();

            // 2. Các nút / chấm tròn kim cương (Màu kim cương: diamondColor)
            var dotRadius = Math.max(3.5, Math.min(borderWidth * 0.3, 5.5));
            for (var i = 0; i < segmentCount; i++) {
                var pinAngle = (i + 0.5) * angleStep;
                var pinX = centerX + Math.cos(pinAngle) * borderRingRadius;
                var pinY = centerY + Math.sin(pinAngle) * borderRingRadius;

                ctx.beginPath();
                ctx.arc(pinX, pinY, dotRadius, 0, Math.PI * 2);
                ctx.fillStyle = diamondColor;
                ctx.fill();
                ctx.lineWidth = 1;
                ctx.strokeStyle = 'rgba(0, 0, 0, 0.25)';
                ctx.stroke();
            }
        }

        ctx.restore();
    }

    function startIdleAnimation() {
        if (idleAnimationId) {
            return;
        }
        function idleStep() {
            if (spinning) {
                idleAnimationId = null;
                return;
            }
            currentRotation = (currentRotation + IDLE_SPEED) % (Math.PI * 2);
            drawWheelCanvas(currentRotation);
            idleAnimationId = requestAnimationFrame(idleStep);
        }
        idleAnimationId = requestAnimationFrame(idleStep);
    }

    function stopIdleAnimation() {
        if (idleAnimationId) {
            cancelAnimationFrame(idleAnimationId);
            idleAnimationId = null;
        }
    }

    function renderWheel() {
        var wrapper = $('#wheel-wrapper');
        var bg = wheelSettings.background || {};

        if (bg.image) {
            // Ảnh URL hoặc Upload
            wrapper.css({
                background: 'url("' + bg.image + '") center center / cover no-repeat',
            });
        } else if (bg.gradient) {
            // Gradient
            wrapper.css({
                background: bg.gradient,
            });
        } else if (bg.color) {
            // Màu nền thuần
            wrapper.css({
                background: bg.color,
            });
        } else {
            // Mặc định
            wrapper.css({
                background: DEFAULT_BG_GRADIENT,
            });
        }

        var btnText = wheelSettings.button.text !== undefined ? wheelSettings.button.text : 'QUAY NGAY';
        $('#spin').text(btnText);
        $('#spin').css({
            backgroundColor: wheelSettings.button.background_image ? 'transparent' : (wheelSettings.button.color || '#ff0000'),
            backgroundImage: wheelSettings.button.background_image ? 'url(' + wheelSettings.button.background_image + ')' : 'none',
            color: wheelSettings.button.background_image ? 'transparent' : (wheelSettings.button.text_color || '#ffffff'),
            borderRadius: (wheelSettings.button.radius || 50) + 'px',
            backgroundSize: wheelSettings.button.background_image ? 'cover' : '',
            backgroundRepeat: wheelSettings.button.background_image ? 'no-repeat' : '',
            backgroundPosition: wheelSettings.button.background_image ? 'center center' : '',
        });

        if (wheelSettings.custom_css) {
            var styleId = 'wp-spin-wheel-custom-css-' + wheelId;
            var styleTag = document.getElementById(styleId);
            if (!styleTag) {
                styleTag = document.createElement('style');
                styleTag.id = styleId;
                document.head.appendChild(styleTag);
            }
            styleTag.textContent = wheelSettings.custom_css;
        }

        drawWheelCanvas(currentRotation);
    }

    function animateSpin(targetIndex, callback) {
        if (spinning || !wheelPrizes.length) {
            return;
        }

        stopIdleAnimation();
        spinning = true;

        // Phát nhạc bắt đầu
        playSpinAudio();
        var segmentCount = wheelPrizes.length;
        var angleStep = (Math.PI * 2) / segmentCount;
        var normalizedRotation = currentRotation % (Math.PI * 2);
        if (normalizedRotation < 0) {
            normalizedRotation += Math.PI * 2;
        }

        var pointerAngle = -Math.PI / 2;
        var targetAngle = pointerAngle - (targetIndex + 0.5) * angleStep;
        var rotations = 4;
        var totalRotation = rotations * Math.PI * 2 + targetAngle - normalizedRotation;
        while (totalRotation < 0) {
            totalRotation += Math.PI * 2;
        }

        var duration = (wheelSettings.animation.duration || 6) * 1000;
        var startRotation = currentRotation;
        var startTime = null;

        function step(timestamp) {
            if (!startTime) {
                startTime = timestamp;
            }
            var elapsed = timestamp - startTime;
            var progress = Math.min(elapsed / duration, 1);
            var ease = 1 - Math.pow(1 - progress, 3);
            var rotation = startRotation + totalRotation * ease;
            currentRotation = rotation;
            drawWheelCanvas(currentRotation);

            if (progress < 1) {
                requestAnimationFrame(step);
                return;
            }

            spinning = false;
            currentRotation = (startRotation + totalRotation) % (Math.PI * 2);
            drawWheelCanvas(currentRotation);

            // Dừng nhạc quay, phát nhạc kết thúc
            stopSpinAudio();
            playEndAudio();

            if (wheelSettings.animation.confetti && typeof window.confetti === 'function') {
                window.confetti();
            }

            if (typeof callback === 'function') {
                callback();
            }

            startIdleAnimation();
        }

        requestAnimationFrame(step);
    }

    var autoRemoveTimer = null;

    // ── Audio engine cho vòng quay ──
    var spinAudioEl = null;  // nhạc đang chạy khi quay
    var endAudioEl = null;  // nhạc kết thúc

    function createAudio() {
        var el = document.createElement('audio');
        el.preload = 'auto';
        return el;
    }

    // Lấy URL file nhạc từ value của select
    // value có thể là: '0' (tắt) | 'random' | 'slot_start' | 'conquay' | ... | ID thư viện
    function resolveAudioUrl(soundVal, soundFileVal, selectId) {
        if (!soundVal || soundVal === '0') return null;

        // Nhactik file ID ưu tiên nếu có
        if (soundFileVal) {
            return 'https://nhactik.com/play/' + soundFileVal + '.mp3';
        }

        if (soundVal === 'random') {
            // Lấy toàn bộ option có data-url trong select tương ứng
            var urls = [];
            $('#' + selectId + ' option[data-url]').each(function () {
                var u = $(this).data('url');
                if (u) urls.push(u);
            });
            if (!urls.length) return null;
            return urls[Math.floor(Math.random() * urls.length)];
        }

        // Các âm thanh built-in — trỏ vào thư mục assets/sound/
        var pluginUrl = (wp_spin_wheel_params && wp_spin_wheel_params.plugin_url)
            ? wp_spin_wheel_params.plugin_url.replace(/\/$/, '')
            : '';
        var builtIn = {
            slot_start: pluginUrl ? pluginUrl + '/assets/sound/slot_start.mp3' : null,
            conquay: pluginUrl ? pluginUrl + '/assets/sound/conquay.mp3' : null,
            slot_end: pluginUrl ? pluginUrl + '/assets/sound/slot_end.mp3' : null,
            read: null,
        };
        if (builtIn[soundVal] !== undefined) {
            return builtIn[soundVal] || null;
        }

        // Là ID item trong thư viện — lấy data-url từ option
        var $opt = $('#' + selectId + ' option[value="' + soundVal + '"]');
        return $opt.data('url') || null;
    }

    function playSpinAudio() {
        var audio = wheelSettings.audio || {};
        var url = resolveAudioUrl(
            audio.start_sound || $('#start_sound').val(),
            audio.start_sound_file || $('#start_sound_file').val(),
            'start_sound'
        );
        if (!url) return;

        if (spinAudioEl) { spinAudioEl.pause(); }
        spinAudioEl = createAudio();
        spinAudioEl.src = url;
        spinAudioEl.loop = true;   // nhạc bắt đầu lặp lại trong khi quay
        spinAudioEl.volume = 0.8;
        spinAudioEl.play().catch(function () { });
    }

    function stopSpinAudio() {
        if (spinAudioEl) {
            spinAudioEl.pause();
            spinAudioEl.currentTime = 0;
            spinAudioEl = null;
        }
    }

    function playEndAudio() {
        var audio = wheelSettings.audio || {};
        var url = resolveAudioUrl(
            audio.end_sound || $('#end_sound').val(),
            audio.end_sound_file || $('#end_sound_file').val(),
            'end_sound'
        );
        if (!url) return;

        if (endAudioEl) { endAudioEl.pause(); }
        endAudioEl = createAudio();
        endAudioEl.src = url;
        endAudioEl.loop = false;
        endAudioEl.volume = 0.9;
        endAudioEl.play().catch(function () { });
    }

    function showResultPopup(prize) {
        // Nếu show_popup bị tắt thì không hiện popup
        if (wheelSettings.ui && wheelSettings.ui.show_popup === false) {
            return;
        }

        // Cập nhật tiêu đề popup theo settings
        var label = (wheelSettings.ui && wheelSettings.ui.popup_label)
            ? wheelSettings.ui.popup_label
            : 'Bạn đã quay vào ô';
        $('#modal-result-popup-label').text(label);

        $('#modal-result-title').text(prize.title || '');
        $('#modal-result-desc').text(prize.description || '');
        $('#modal-result').show().attr('aria-hidden', 'false');

        // Auto-close sau 5 giây nếu checkbox auto_remove được bật
        if (autoRemoveTimer) {
            clearTimeout(autoRemoveTimer);
            autoRemoveTimer = null;
        }
        if ($('#auto_remove').is(':checked')) {
            autoRemoveTimer = setTimeout(function () {
                closeResultPopup();   // auto-close → giữ kết quả, đóng popup
                autoRemoveTimer = null;
            }, 5000);
        }
    }

    // Đóng popup — luôn GIỮ kết quả (đã append rồi)
    function closeResultPopup() {
        if (autoRemoveTimer) {
            clearTimeout(autoRemoveTimer);
            autoRemoveTimer = null;
        }
        $('#modal-result').hide().attr('aria-hidden', 'true');
    }

    // Biến lưu jQuery element entry vừa append, để nút "Xóa ô này" dùng
    var lastResultEntry = null;

    function handleSpinSuccess(prize) {
        var index = getPrizeIndex(prize);
        if (index === -1) {
            index = 0;
        }
        animateSpin(index, function () {
            // 1. Append kết quả vào bảng ngay
            var $entry = $('<div/>', { 'class': 'wheel-result-item' })
                .append($('<strong/>').text(prize.title || ''))
                .append($('<div/>').text(prize.description || ''));
            $('#wheel_result').append($entry);
            lastResultEntry = $entry;
            updateResultCount();
            $('#tab-result').trigger('click');

            // 2. Nếu show_popup tắt → không hiện popup, dừng tại đây
            if (wheelSettings.ui && wheelSettings.ui.show_popup === false) {
                return;
            }

            // Hiện/ẩn nút "Xóa ô này":
            // Mặc định luôn hiện, trừ khi user tắt option show_remove_button
            var showRemove = !wheelSettings.ui || wheelSettings.ui.show_remove_button !== false;
            $('#btn-remove-result-item').toggle(showRemove);

            // 4. Hiện popup
            showResultPopup(prize);
        });
    }

    wheelSettings = normalizeSettings(parseJson(rawSettings));

    // Trường hợp 2: Nếu user đã đăng nhập và có dữ liệu từ server bài viết spin_wheel
    if (typeof wp_spin_wheel_params !== 'undefined' && wp_spin_wheel_params.is_logged_in && wp_spin_wheel_params.user_wheel_data) {
        var uwd = wp_spin_wheel_params.user_wheel_data;
        if (uwd.id) {
            wheelId = uwd.id;
            $('#wheel-wrapper').attr('data-wheel-id', uwd.id);
        }
        if (uwd.settings && typeof uwd.settings === 'object') {
            mergeUserSettings(uwd.settings);
            applySettingsToUI(uwd.settings);
        }
    } else {
        // Trường hợp 1: User chưa đăng nhập -> Tải từ localStorage
        var savedSettings = parseJson(localStorage.getItem(getSettingsStorageKey()), null);
        if (savedSettings) {
            mergeUserSettings(savedSettings);
            applySettingsToUI(savedSettings);
        }
    }

    wheelPrizes = loadPrizes();
    renderPrizeList();

    // Khởi tạo hội màu sắc
    renderSectorColorPills();
    loadAndRenderThemeDropdown();
    if (savedSettings && savedSettings.sector_colors) {
        wheelPrizes.forEach(function (prize, index) {
            prize.color = savedSettings.sector_colors[index % savedSettings.sector_colors.length];
        });
        savePrizes();
        renderPrizeList();
    }

    renderWheel();
    startIdleAnimation();

    $('#tab-entries').on('click', function () {
        $('#tab-entries').addClass('active');
        $('#tab-result').removeClass('active');
        $('#tab-content-entries').addClass('show active');
        $('#tab-content-result').removeClass('show active');
    });

    $('#tab-result').on('click', function () {
        $('#tab-result').addClass('active');
        $('#tab-entries').removeClass('active');
        $('#tab-content-result').addClass('show active');
        $('#tab-content-entries').removeClass('show active');
    });

    $('#spin').on('click', function () {
        if (spinning || pendingRequest) {
            return;
        }

        if (!wheelId) {
            if (!wheelPrizes.length) {
                alert(wp_spin_wheel_params.text_error);
                return;
            }

            var prizeIndex = Math.floor(Math.random() * wheelPrizes.length);
            var prize = wheelPrizes[prizeIndex] || { title: 'Thưởng' };
            handleSpinSuccess(prize);
            return;
        }

        var form = {};
        container.find('.spin-wheel-form input').each(function () {
            var name = $(this).attr('name');
            if (name) {
                form[name] = $(this).val();
            }
        });

        // Trường hợp khách chưa đăng nhập và không có wheelId -> Quay mượt mà ngay trên client
        if (!wheelId && (typeof wp_spin_wheel_params === 'undefined' || !wp_spin_wheel_params.is_logged_in)) {
            var randomIndex = Math.floor(Math.random() * wheelPrizes.length);
            var localWinner = wheelPrizes[randomIndex] || { title: 'Chúc mừng bạn!', id: 'win-1' };
            handleSpinSuccess(localWinner);
            return;
        }

        // Trường hợp user có wheelId -> Gọi REST API máy chủ
        pendingRequest = true;
        var restBase = (wp_spin_wheel_params && wp_spin_wheel_params.rest_url) ? wp_spin_wheel_params.rest_url.replace(/\/$/, '') : (window.location.origin + '/wp-json/spin-wheel/v1');
        var endpoint = restBase + '/wheels/' + (wheelId || 0) + '/spin';
        fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': (wp_spin_wheel_params && wp_spin_wheel_params.nonce) ? wp_spin_wheel_params.nonce : ''
            },
            body: JSON.stringify({
                form: form,
                prizes: wheelPrizes
            })
        }).then(function (res) {
            pendingRequest = false;
            if (!res.ok) {
                return res.json().then(function (err) { throw err; }).catch(function () { throw { message: wp_spin_wheel_params.text_error }; });
            }
            return res.json();
        }).then(function (data) {
            if (data && data.prize) {
                handleSpinSuccess(data.prize);
            } else if (data && data.message) {
                alert(data.message || wp_spin_wheel_params.text_error);
            } else {
                alert(wp_spin_wheel_params.text_error);
            }
        }).catch(function (err) {
            var msg = (err && err.message) ? err.message : (err && err.data && err.data.message) ? err.data.message : wp_spin_wheel_params.text_error;
            alert(msg);
            pendingRequest = false;
        });
    });

    $('#edit-content').on('click', function (e) {
        e.preventDefault();
        openTitleDescriptionEditor();
    });

    $('#saveTitleDesc').on('click', function (e) {
        e.preventDefault();
        saveTitleDesc();
    });

    $('#modal-edit-close, #modal-edit-cancel').on('click', function (e) {
        e.preventDefault();
        closeTitleDescriptionEditor();
    });

    $('#modal-edit').on('click', function (e) {
        if ($(e.target).is('#modal-edit')) {
            closeTitleDescriptionEditor();
        }
    });

    // "Đóng lại" hoặc nút X → giữ kết quả, đóng popup
    $('#modal-result-close, #modal-result-close-btn').on('click', function (e) {
        e.preventDefault();
        closeResultPopup();
    });

    // Click backdrop → đóng, giữ kết quả
    $('#modal-result').on('click', function (e) {
        if ($(e.target).is('#modal-result')) {
            closeResultPopup();
        }
    });

    // "Xóa ô này" → xóa entry vừa append, đóng popup
    $(document).on('click', '#btn-remove-result-item', function () {
        if (lastResultEntry) {
            lastResultEntry.remove();
            lastResultEntry = null;
            updateResultCount();
        }
        closeResultPopup();
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            closeTitleDescriptionEditor();
            closeResultPopup();
        }
    });

    $('#btn-clear-result').on('click', function () {
        $('#wheel_result').empty();
        updateResultCount();
    });

    $('#btn-sort-wheel-az').on('click', function () {
        sortPrizes(true);
        $('#btn-sort-wheel-az').addClass('d-none');
        $('#btn-sort-wheel-za').removeClass('d-none');
    });

    $('#btn-sort-wheel-za').on('click', function () {
        sortPrizes(false);
        $('#btn-sort-wheel-za').addClass('d-none');
        $('#btn-sort-wheel-az').removeClass('d-none');
    });

    $('#btn-clear-entry').on('click', function () {
        clearPrizes();
    });

    $('#btn-add-prize').on('click', function () {
        var title = $('#new_prize_title').val();
        var color = $('#new_prize_color').val();
        addPrize(title, color);
        $('#new_prize_title').val('');
    });

    $('#sector_list').on('click', '.btn-remove-prize', function () {
        var index = parseInt($(this).data('index'), 10);
        removePrize(index);
    });

    $('#btn-restore-defaults').on('click', function () {
        restoreDefaultPrizes();
        $('#btn-sort-wheel-za').addClass('d-none');
        $('#btn-sort-wheel-az').removeClass('d-none');
    });

    $('#btn-shuffle-wheel').on('click', function () {
        var items = $('#sector_list .wheel-prize-item').toArray();
        items.sort(function () { return 0.5 - Math.random(); });
        $('#sector_list').empty().append(items);

        var newOrder = [];
        items.forEach(function (item) {
            var prizeId = $(item).data('id');
            var prize = wheelPrizes.find(function (entry) {
                return entry.id === prizeId;
            });
            if (prize) {
                newOrder.push(prize);
            }
        });

        if (newOrder.length) {
            wheelPrizes = newOrder;
            savePrizes();
            renderPrizeList();
            renderWheel();
        }
    });

    // ── popup_label: cập nhật span tiêu đề real-time khi user gõ ──
    $('#popup_label').on('input', function () {
        var val = $.trim($(this).val()) || 'Bạn đã quay vào ô';
        if (!wheelSettings.ui) wheelSettings.ui = {};
        wheelSettings.ui.popup_label = val;
        $('#modal-result-popup-label').text(val);
    });

    // ── Nút "Chủ đề" — mở modalSettings và switch sang tab Giao diện ──
    $('#btn-open-theme').on('click', function () {
        // Trigger Bootstrap modal
        var $modal = $('#modalSettings');
        $modal.modal ? $modal.modal('show') : $modal.show();

        // Switch sang tab "Giao diện"
        var $tab = $('#appearance-tab');
        if ($tab.length) {
            $tab.tab ? $tab.tab('show') : $tab.trigger('click');
        }
    });

    // ── Lưu settings từ modal ──
    $('#btn_wheel_setting').on('click', function () {
        saveWheelSettings();
    });

    // ── Xử lý chuyển tab trong modal cài đặt (#myTab) ──
    $(document).on('click', '#myTab .nav-link', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var target = $btn.attr('data-bs-target') || $btn.attr('href');
        if (!target) return;

        $('#myTab .nav-link').removeClass('active').attr('aria-selected', 'false');
        $btn.addClass('active').attr('aria-selected', 'true');

        $('#myTabContent > .tab-pane').removeClass('show active');
        $(target).addClass('show active');

        if (target === '#media-tab-pane' && typeof window.swRenderMediaActiveTab === 'function') {
            window.swRenderMediaActiveTab();
        }
    });

    // ── Nút "Chủ đề" → mở modalSettings và nhảy sang tab Giao diện ──
    $('#btn-open-theme').on('click', function () {
        var $modal = $('#modalSettings');
        if ($modal.length) {
            if ($modal.modal) {
                $modal.modal('show');
            } else {
                $modal.show();
            }
        }
        $('#appearance-tab').trigger('click');
    });

    // Cũng bắt sự kiện khi modal vừa hiện để đảm bảo tab đúng
    $('#modalSettings').on('show.bs.modal shown.bs.modal', function (e) {
        var relatedBtn = e ? e.relatedTarget : null;
        if (relatedBtn && $(relatedBtn).data('tab')) {
            var tabId = $(relatedBtn).data('tab');
            $('#' + tabId).trigger('click');
        }
    });

    // ── Reset về mặc định ──
    $('#btn-reset-wheel').on('click', function () {
        if (confirm('Reset tất cả cài đặt về mặc định?')) {
            resetWheelSettings();
        }
    });

    // ── Áp dụng một chủ đề: chọn màu từ data-content vào hội màu, viền và kim cương ──
    $('#myDropdown').on('click', '.dropdown-item', function (e) {
        e.preventDefault();
        var content = $(this).attr('data-content') || '';
        if (content) {
            var themeColors = content.split(',').map(function (c) { return $.trim(c); }).filter(function (c) { return c !== ''; });
            if (themeColors.length) {
                // Bỏ check tất cả checkbox màu trước
                $('input[name="chkcolor"]').prop('checked', false);
                // Checks riêng từng màu
                themeColors.forEach(function (color, index) {
                    var slot = index + 1;
                    if (slot > SECTOR_COLOR_SLOTS) return;
                    $('#chkcolor-' + slot).prop('checked', true);
                    $('#color-' + slot).val(color);
                });
            }
        }

        // Áp dụng data-border từ theme nếu có (màu viền, màu kim cương)
        var borderData = $(this).attr('data-border') || '';
        if (borderData) {
            var borderParts = borderData.split(',').map(function (c) { return $.trim(c); });
            if (borderParts[0]) {
                $('#border_color').val(borderParts[0]);
                if (!wheelSettings.wheel) wheelSettings.wheel = {};
                wheelSettings.wheel.border_color = borderParts[0];
            }
            if (borderParts[1]) {
                $('#diamond_color').val(borderParts[1]);
                if (!wheelSettings.wheel) wheelSettings.wheel = {};
                wheelSettings.wheel.diamond_color = borderParts[1];
            }
        }

        // Áp dụng data-spin_img nếu có
        var spinImg = $(this).attr('data-spin_img') || '';
        if (spinImg) {
            $('#btn-spin-img').val(spinImg);
            $('#switch_spin_bg_type').prop('checked', true).trigger('change');
            if (!wheelSettings.button) wheelSettings.button = {};
            wheelSettings.button.bg_type = 'image';
            wheelSettings.button.background_image = spinImg;
            updateSpinImgPreview(spinImg);
        }

        // Áp dụng gradient nền hoặc ảnh nền từ theme nếu có
        var bgGradient = $(this).attr('data-bg_gradient') || '';
        var bgImg = $(this).attr('data-bg_img') || '';
        if (bgGradient) {
            if (!wheelSettings.background) wheelSettings.background = {};
            wheelSettings.background.gradient = bgGradient;
            wheelSettings.background.image = '';
            wheelSettings.background.color = '';
            $('#bg-gradient').val(bgGradient);
            $('#gradient-preview-box').css('background', bgGradient);
            $('#custom-bg-img').val('');
            $('#bgr-preview-wrap').hide();
            $('#bgr-preview').attr('src', '');
        } else if (bgImg) {
            if (!wheelSettings.background) wheelSettings.background = {};
            wheelSettings.background.image = bgImg;
            wheelSettings.background.gradient = '';
            wheelSettings.background.color = '';
            $('#custom-bg-img').val(bgImg);
            $('#bgr-preview').attr('src', bgImg);
            $('#bgr-preview-wrap').show();
            $('#bg-gradient').val('');
            $('#gradient-preview-box').css('background', 'transparent');
        }

        // Sync hội màu
        syncSectorColors();
        drawWheelCanvas(currentRotation);
        renderWheel();
        // Lưu vào localStorage ngay
        localStorage.setItem(getSettingsStorageKey(), JSON.stringify(collectSettings()));
    });

    // ── Hội màu sắc: checkbox thay đổi ──
    $(document).on('change', 'input[name="chkcolor"]', function () {
        renderSectorColorPills();
        syncSectorColors();
    });

    // ── Hội màu sắc: đổi giá trị màu ──
    $(document).on('input', '.sw-sector-color-input, [id^="color-"]', function () {
        renderSectorColorPills();
        syncSectorColors();
    });

    // ── Hội màu sắc: click pill để đổi màu ──
    $(document).on('click', '.sw-sector-color-pill', function (e) {
        if ($(e.target).hasClass('sw-remove-color')) return;
        var index = parseInt($(this).data('slot'), 10);
        var $input = $('#color-' + index);
        if ($input.length) {
            $input.trigger('click');
        }
    });

    // ── Hội màu sắc: click × để xóa màu khỏi hội ──
    $(document).on('click', '.sw-remove-color', function (e) {
        e.stopPropagation();
        var $pill = $(this).closest('.sw-sector-color-pill');
        var slot = parseInt($pill.attr('data-slot'), 10);
        var $chk = $('#chkcolor-' + slot);
        var $input = $('#color-' + slot);
        if ($chk.length) $chk.prop('checked', false);
        if ($input.length) $input.val('');
        renderSectorColorPills();
        syncSectorColors();
    });

    // Shuffle mảng màu để mỗi lần fill ra màu khác nhau
    function shuffleColors() {
        var colors = DEFAULT_SECTOR_COLORS.slice();
        for (var i = colors.length - 1; i > 0; i--) {
            var j = Math.floor(Math.random() * (i + 1));
            var tmp = colors[i]; colors[i] = colors[j]; colors[j] = tmp;
        }
        return colors;
    }

    // ── Hàm fill prizes từ data-content (dùng chung) ──
    function fillPrizesFromContent(content) {
        if (!content) return;
        var titles = content.split('||').map(function (t) { return $.trim(t); }).filter(function (t) { return t !== ''; });
        if (!titles.length) return;
        var colors = shuffleColors();
        wheelPrizes = titles.map(function (title, index) {
            return normalizePrize({ title: title, color: colors[index % colors.length] }, index);
        });
        savePrizes();
        renderPrizeList();
        renderWheel();
    }

    function fillPrizesFromRange(from, to) {
        from = parseInt(from, 10) || 1;
        to = parseInt(to, 10) || 10;
        if (from > to) { var tmp = from; from = to; to = tmp; }
        var titles = [];
        for (var i = from; i <= to; i++) titles.push(String(i));
        var colors = shuffleColors();
        wheelPrizes = titles.map(function (title, index) {
            return normalizePrize({ title: title, color: colors[index % colors.length] }, index);
        });
        savePrizes();
        renderPrizeList();
        renderWheel();
    }

    // ── Click nút chủ đề text ──
    $(document).on('click', '.btn-fill', function () {
        var content = $(this).data('content');
        if (!content) return;
        fillPrizesFromContent(content);
        // Đánh dấu nút đang active
        $('.btn-fill, .btn-fill-number').removeClass('active');
        $(this).addClass('active');
    });

    // ── Click nút chủ đề số ──
    $(document).on('click', '.btn-fill-number', function () {
        var from = $(this).data('from');
        var to = $(this).data('to');
        fillPrizesFromRange(from, to);
        $('.btn-fill, .btn-fill-number').removeClass('active');
        $(this).addClass('active');
    });

    // ── Mặc định: nếu wheelPrizes rỗng và không có localStorage → load nút đầu tiên ──
    if (!wheelPrizes.length) {
        var $firstBtn = $('.btn-fill').first();
        if ($firstBtn.length) {
            fillPrizesFromContent($firstBtn.data('content'));
            $firstBtn.addClass('active');
        }
    }

    // ── Toggle hiển thị ô nhập popup label khi bật/tắt show_popup ──
    $('#show_popup').on('change', function () {
        var $extra = $('#show_popup').closest('.form-check').next('.mb-3');
        $extra.toggle($(this).is(':checked'));
    }).trigger('change');

    // ── Particles effect ──────────────────────────────────────────────────────

    var PARTICLE_CONFIGS = {

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
                events: { onhover: { enable: false }, onclick: { enable: false } },
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

    function initParticles() {
        if (typeof window.particlesJS === 'undefined') return;

        var enabled = $('#show_particle').is(':checked');
        var type = $('#particle_type').val() || 'default';
        var el = document.getElementById('particles-js');
        if (!el) return;

        // Dừng instance cũ nếu có
        if (window.pJSDom && window.pJSDom.length) {
            try { window.pJSDom[window.pJSDom.length - 1].pJS.fn.vendors.destroypJS(); } catch (e) { }
            window.pJSDom = [];
        }

        // Xoá canvas cũ để tránh stacking
        el.innerHTML = '';

        if (!enabled) return;

        var config = PARTICLE_CONFIGS[type] || PARTICLE_CONFIGS['default'];
        window.particlesJS('particles-js', config);
    }

    // Khởi tạo khi trang load (delay nhỏ chờ particles.js sẵn)
    setTimeout(initParticles, 300);

    // Live update khi user thay đổi checkbox hoặc select
    $('#show_particle, #particle_type').on('change', function () {
        initParticles();
    });

    // ── End Particles ─────────────────────────────────────────────────────────
    // Nếu popup đang mở mà user thay đổi checkbox → cập nhật ngay.
    $('#show_remove_button').on('change', function () {
        if ($('#modal-result').is(':visible')) {
            $('#btn-remove-result-item').toggle($(this).is(':checked'));
        }
        // Lưu vào wheelSettings ngay để lần quay tiếp áp dụng đúng
        if (!wheelSettings.ui) wheelSettings.ui = {};
        wheelSettings.ui.show_remove_button = $(this).is(':checked');
    });

    // ── Thư viện media: event delegation cho nút .sw-media-apply ──
    // data-type="btn"  → đặt làm ảnh nút quay
    // data-type="bgr"  → đặt làm ảnh nền #wheel-wrapper
    // data-type="grd"  → đặt làm gradient nền #wheel-wrapper (đọc từ .sw-gradient-preview cùng hàng)
    $(document).on('click', '.sw-media-apply', function () {
        var type = $(this).data('type');
        var url = $(this).data('url') || '';

        if (type === 'btn') {
            // --- Nút quay: ảnh từ thư viện → dùng chung applySpinImage() ---
            $('#btn-spin-img').val(url);   // đồng bộ URL field
            applySpinImage(url);

        } else if (type === 'bgr') {
            // --- Nền: ảnh URL ---
            if (!wheelSettings.background) wheelSettings.background = {};
            wheelSettings.background.image = url;
            wheelSettings.background.gradient = '';   // xóa gradient nếu có
            wheelSettings.background.color = '';

            // Đồng bộ UI trong modal
            $('#custom-bg-img').val(url);
            if ($('#bg-gradient').length) $('#bg-gradient').val('');

        } else if (type === 'grd') {
            // --- Nền: gradient — đọc từ data-gradient attribute trên .sw-gradient-preview cùng hàng ---
            var gradientValue = $(this).closest('tr').find('.sw-gradient-preview').data('gradient') || '';
            if (!gradientValue) return;

            if (!wheelSettings.background) wheelSettings.background = {};
            wheelSettings.background.gradient = gradientValue;
            wheelSettings.background.image = '';   // xóa ảnh URL nếu có

            // Đồng bộ UI trong modal
            $('#custom-bg-img').val('');
            if ($('#bg-gradient').length) $('#bg-gradient').val(gradientValue);
        }

        // Lưu vào localStorage và re-render ngay, không cần bấm "Lưu lại"
        localStorage.setItem(getSettingsStorageKey(), JSON.stringify(collectSettings()));
        renderWheel();

        // Feedback: đổi nút thành "\u2713" rồi trả về
        var $btn = $(this);
        var origText = $btn.text();
        $btn.text('\u2713').addClass('btn-success').removeClass('btn-secondary');
        setTimeout(function () {
            $btn.text(origText).removeClass('btn-success').addClass('btn-secondary');
        }, 1200);
    });

    // ── Body: Áp dụng màu nền + màu chữ ──
    $('#btn-apply-body-color').on('click', function () {
        var bg = $('#custom-bg-color').val() || '#ffffff';
        var color = $('#custom-color').val() || '#000000';
        if (!wheelSettings.background) wheelSettings.background = {};
        wheelSettings.background.color = bg;
        wheelSettings.background.image = '';
        wheelSettings.background.gradient = '';
        $('#wheel-wrapper').css({ color: color });
        renderWheel();
        localStorage.setItem(getSettingsStorageKey(), JSON.stringify(collectSettings()));
        feedbackBtn($(this), 'Đã áp dụng \u2713');
    });

    // ── Body: Upload ảnh nền ──
    $('#upload_bgr').on('change', function () {
        var file = this.files && this.files[0];
        if (!file) return;
        var maxMB = parseFloat($(this).data('maxsize')) || 5;
        if (file.size > maxMB * 1024 * 1024) {
            $('#bgr-upload-info').text('Quá ' + maxMB + 'MB!').addClass('text-danger');
            return;
        }
        $('#bgr-upload-info').text('Đang xử lý...').removeClass('text-danger');
        var reader = new FileReader();
        reader.onload = function (e) {
            applyBodyImage(e.target.result, 'Ảnh từ máy của bạn');
            $('#bgr-upload-info').text(file.name);
            document.getElementById('upload_bgr').value = '';
        };
        reader.readAsDataURL(file);
    });

    // ── Body: Áp dụng URL ảnh nền ──
    $('#btn-apply-body-img').on('click', function () {
        var url = $.trim($('#custom-bg-img').val());
        if (!url) return;
        applyBodyImage(url, 'URL ảnh nền');
        feedbackBtn($(this), 'Đã áp dụng \u2713');
    });

    // Nhấn Enter trong ô URL ảnh nền
    $('#custom-bg-img').on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $('#btn-apply-body-img').trigger('click');
        }
    });

    // ── Body: Xoá ảnh nền ──
    $('#btn-clear-body-img').on('click', function () {
        $('#custom-bg-img').val('');
        applyBodyImage('', '');
        $('#bgr-upload-info').text('JPG/PNG/WebP ≤ 5MB').removeClass('text-danger');
    });

    // Helper: apply ảnh vào background #wheel-wrapper
    function applyBodyImage(src, label) {
        if (!wheelSettings.background) wheelSettings.background = {};
        if (src) {
            wheelSettings.background.image = src;
            wheelSettings.background.gradient = '';
            wheelSettings.background.color = '';
            $('#custom-bg-img').val(src);
            $('#bgr-preview').attr('src', src);
            $('#bgr-preview-wrap').show();
            $('#bg-gradient').val('');
            $('#gradient-preview-box').css('background', 'transparent');
            $('.sw-gradient-swatch').css('outline', '');
        } else {
            wheelSettings.background.image = '';
            wheelSettings.background.gradient = DEFAULT_BG_GRADIENT;
            wheelSettings.background.color = '';
            $('#custom-bg-img').val('');
            $('#bgr-preview-wrap').hide();
            $('#bgr-preview').attr('src', '');
            $('#bg-gradient').val(DEFAULT_BG_GRADIENT);
            $('#gradient-preview-box').css('background', DEFAULT_BG_GRADIENT);
        }
        renderWheel();
        localStorage.setItem(getSettingsStorageKey(), JSON.stringify(collectSettings()));
    }

    // ── Body: Swatches gradient nhanh (Click chọn là áp dụng ngay) ──
    $(document).on('click', '.sw-gradient-swatch', function () {
        var gradient = $(this).data('gradient') || '';
        if (!gradient) return;
        if (!wheelSettings.background) wheelSettings.background = {};
        wheelSettings.background.gradient = gradient;
        wheelSettings.background.image = '';
        wheelSettings.background.color = '';

        $('#bg-gradient').val(gradient);
        $('#gradient-preview-box').css('background', gradient);
        $('#custom-bg-img').val('');
        $('#bgr-preview-wrap').hide();
        $('#bgr-preview').attr('src', '');

        // Đánh dấu swatch đang chọn
        $('.sw-gradient-swatch').css('outline', '');
        $(this).css('outline', '2px solid #0d6efd');

        renderWheel();
        localStorage.setItem(getSettingsStorageKey(), JSON.stringify(collectSettings()));
    });

    // ── Body: Preview live khi gõ gradient textarea ──
    $('#bg-gradient').on('input', function () {
        var val = $.trim($(this).val());
        if (val) $('#gradient-preview-box').css('background', val);
    });

    // ── Body: Áp dụng gradient ──
    $('#btn-apply-body-gradient').on('click', function () {
        var gradient = $.trim($('#bg-gradient').val()) || DEFAULT_BG_GRADIENT;
        if (!wheelSettings.background) wheelSettings.background = {};
        wheelSettings.background.gradient = gradient;
        wheelSettings.background.image = '';
        wheelSettings.background.color = '';

        $('#gradient-preview-box').css('background', gradient);
        $('#custom-bg-img').val('');
        $('#bgr-preview-wrap').hide();
        $('#bgr-preview').attr('src', '');

        renderWheel();
        localStorage.setItem(getSettingsStorageKey(), JSON.stringify(collectSettings()));
        feedbackBtn($(this), 'Đã áp dụng \u2713');
    });

    // Utility: feedback ngắn trên button
    function feedbackBtn($btn, msg) {
        var orig = $btn.text();
        $btn.text(msg).addClass('btn-success').removeClass('btn-primary btn-outline-secondary');
        setTimeout(function () {
            $btn.text(orig).removeClass('btn-success').addClass('btn-primary');
        }, 1400);
    }

    // ── Switch nút quay: toggle giữa bg color và bg image ──
    $('#switch_spin_bg_type').on('change', function () {
        var isImage = $(this).is(':checked');
        if (isImage) {
            $('#form_spin_bg_color').addClass('d-none');
            $('#form_spin_bg_image').removeClass('d-none');
        } else {
            $('#form_spin_bg_image').addClass('d-none');
            $('#form_spin_bg_color').removeClass('d-none');
        }
    });

    // ── Preview ảnh khi user nhập URL — chỉ khi user thực sự gõ, không trigger từ JS ──
    $('#btn-spin-img').on('input', function () {
        var url = $.trim($(this).val());
        updateSpinImgPreview(url);
    });

    // ── Nút "Áp dụng text" nút quay ──
    $('#btn-apply-spin-label').on('click', function () {
        var text = $.trim($('#btn-spin-label').val());
        if (!wheelSettings.button) wheelSettings.button = {};
        wheelSettings.button.text = text;
        renderWheel();
        localStorage.setItem(getSettingsStorageKey(), JSON.stringify(collectSettings()));
        var $b = $(this);
        $b.text('\u2713').addClass('btn-success').removeClass('btn-outline-secondary');
        setTimeout(function () { $b.text('Áp dụng').removeClass('btn-success').addClass('btn-outline-secondary'); }, 1200);
    });

    // Enter trong input text cũng apply
    $('#btn-spin-label').on('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); $('#btn-apply-spin-label').trigger('click'); }
    });

    // ── Nút "Áp dụng" URL ảnh nút quay ──
    $('#btn-apply-spin-img').on('click', function () {
        var url = $.trim($('#btn-spin-img').val());
        applySpinImage(url);
    });

    // ── Upload ảnh nút quay từ máy ──
    $('#upload_spin_bg').on('change', function () {
        var file = this.files && this.files[0];
        if (!file) return;

        var maxMB = parseFloat($(this).data('maxsize')) || 2;
        if (file.size > maxMB * 1024 * 1024) {
            $('#spin-upload-info').text('Ảnh quá lớn! Tối đa ' + maxMB + 'MB').addClass('text-danger');
            return;
        }
        $('#spin-upload-info').text('Đang xử lý...').removeClass('text-danger');

        var reader = new FileReader();
        reader.onload = function (e) {
            var dataUrl = e.target.result;
            $('#spin-upload-info').text(file.name);
            $('#btn-spin-img').val('');   // clear URL field vì đang dùng upload
            applySpinImage(dataUrl);
            // reset input SAU khi đọc xong để không cancel FileReader
            document.getElementById('upload_spin_bg').value = '';
        };
        reader.onerror = function () {
            $('#spin-upload-info').text('Lỗi đọc file!').addClass('text-danger');
        };
        reader.readAsDataURL(file);
    });

    // ── Xoá ảnh nút quay ──
    $('#btn-clear-spin-img').on('click', function () {
        $('#btn-spin-img').val('');
        applySpinImage('');
        $('#spin-upload-info').text('JPG/PNG/WebP, tối đa 2MB').removeClass('text-danger');
    });

    // Helper trung tâm: apply ảnh vào nút quay từ mọi nguồn (upload/URL/thư viện)
    function applySpinImage(src) {
        if (!wheelSettings.button) wheelSettings.button = {};
        wheelSettings.button.background_image = src;
        if (src) {
            // Có ảnh: ẩn text
            wheelSettings.button.text = '';
            wheelSettings.button.text_color = 'transparent';
            // Hiện preview
            $('#spin-img-preview').attr('src', src);
            $('#spin-img-preview-wrap').show();
        } else {
            // Xoá ảnh: trả text về
            wheelSettings.button.text = $.trim($('#btn-spin-label').val()) || 'Quay';
            wheelSettings.button.text_color = '#ffffff';
            $('#spin-img-preview-wrap').hide();
            $('#spin-img-preview').attr('src', '');
        }
        renderWheel();
        localStorage.setItem(getSettingsStorageKey(), JSON.stringify(collectSettings()));
    }

    // ── Viền kim cương: Đổi màu viền canvas ──
    $('#border_color').on('input change', function () {
        var color = $(this).val();
        if (!wheelSettings.wheel) wheelSettings.wheel = {};
        wheelSettings.wheel.border_color = color;
        drawWheelCanvas(currentRotation);
        localStorage.setItem(getSettingsStorageKey(), JSON.stringify(collectSettings()));
    });

    // ── Viền kim cương: Đổi màu kim cương / nút trên viền ──
    $('#diamond_color').on('input change', function () {
        var color = $(this).val();
        if (!wheelSettings.wheel) wheelSettings.wheel = {};
        wheelSettings.wheel.diamond_color = color;
        drawWheelCanvas(currentRotation);
        localStorage.setItem(getSettingsStorageKey(), JSON.stringify(collectSettings()));
    });

    // ── Viền kim cương: Bật / Tắt viền ──
    $('#show_border').on('change', function () {
        var isChecked = $(this).is(':checked');
        $('#custom_border_color').toggle(isChecked);
        if (!wheelSettings.wheel) wheelSettings.wheel = {};
        wheelSettings.wheel.show_border = isChecked;
        drawWheelCanvas(currentRotation);
        localStorage.setItem(getSettingsStorageKey(), JSON.stringify(collectSettings()));
    });

    // ══════════════════════════════════════════════════════════
    // QUẢN LÝ DANH SÁCH VÒNG QUAY CỦA USER (#modalUserWheels)
    // ══════════════════════════════════════════════════════════

    function loadUserWheelsList() {
        var $wrap = $('#user-wheels-container');
        var $loading = $('#user-wheels-loading');
        var $tableWrap = $('#user-wheels-table-wrap');
        var $tbody = $('#user-wheels-list-tbody');

        if (typeof wp_spin_wheel_params === 'undefined' || !wp_spin_wheel_params.is_logged_in) {
            $loading.hide();
            $tableWrap.addClass('d-none');
            $wrap.html('<div class="alert alert-info py-3 text-center mb-0">Vui lòng đăng nhập tài khoản để lưu trữ và quản lý nhiều vòng quay của riêng bạn.</div>');
            return;
        }

        $loading.show();
        $tableWrap.addClass('d-none');

        $.ajax({
            url: wp_spin_wheel_params.rest_url + 'user/wheels',
            method: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wp_spin_wheel_params.nonce);
            },
            success: function (list) {
                $loading.hide();
                if (!Array.isArray(list) || !list.length) {
                    $tableWrap.addClass('d-none');
                    $wrap.html('<div class="text-center py-4 text-muted">Bạn chưa có vòng quay nào. Nhấn <strong>Tạo vòng quay mới</strong> để bắt đầu!</div>');
                    return;
                }

                var html = '';
                list.forEach(function (w) {
                    var isCurrent = (parseInt(w.id, 10) === parseInt(wheelId, 10));
                    html += '<tr class="' + (isCurrent ? 'table-primary bg-opacity-25' : '') + '">' +
                        '<td>' +
                        '<div class="fw-bold">' + escapeHtml(w.title) + ' ' +
                        (isCurrent ? '<span class="badge bg-success ms-1">Đang dùng</span>' : '') +
                        '</div>' +
                        '<div class="small text-muted"></div>' +
                        '</td>' +
                        '<td class="text-center"><span class="badge bg-secondary rounded-pill">' + (w.prizes_count || 0) + ' giải</span></td>' +
                        '<td class="text-center small text-muted">' + escapeHtml((w.created_at || '').split(' ')[0]) + '</td>' +
                        '<td class="text-end">' +
                        '<div class="btn-group btn-group-sm">' +
                        (!isCurrent ? '<button type="button" class="btn btn-outline-primary btn-switch-user-wheel" data-id="' + w.id + '">Dùng</button>' : '') +
                        (w.permalink ? '<a href="' + escapeAttr(w.permalink) + '" target="_blank" class="btn btn-outline-info" title="Xem trang vòng quay"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg></a>' : '') +
                        '<button type="button" class="btn btn-outline-secondary btn-edit-user-wheel" data-id="' + w.id + '" data-title="' + escapeAttr(w.title) + '" title="Sửa tiêu đề"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></button>' +
                        '<button type="button" class="btn btn-outline-secondary btn-dup-user-wheel" data-id="' + w.id + '" title="Nhân bản"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg></button>' +
                        '<button type="button" class="btn btn-outline-danger btn-del-user-wheel" data-id="' + w.id + '" title="Xóa"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>' +
                        '</div>' +
                        '</td>' +
                        '</tr>';
                });

                $tbody.html(html);
                $tableWrap.removeClass('d-none');
            },
            error: function (err) {
                $loading.hide();
                $wrap.html('<div class="alert alert-danger py-2 text-center mb-0">Không thể tải danh sách vòng quay.</div>');
            }
        });
    }

    // Mở modal danh sách vòng quay
    $('#modalUserWheels').on('show.bs.modal', function () {
        loadUserWheelsList();
    });

    // Chuyển sang dùng vòng quay khác
    $(document).on('click', '.btn-switch-user-wheel', function () {
        var id = $(this).data('id');
        if (!id) return;
        var $btn = $(this);
        $btn.prop('disabled', true).text('Đang nạp...');
        $.ajax({
            url: wp_spin_wheel_params.rest_url + 'user/wheels/' + id,
            method: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wp_spin_wheel_params.nonce);
            },
            success: function (data) {
                if (data && data.id) {
                    wheelId = parseInt(data.id, 10);
                    $('#wheel-wrapper').attr('data-wheel-id', data.id);
                    if (data.title) {
                        $('#txt-title, #vqmm-title').text(data.title);
                    }
                    if (data.description) {
                        $('#txt-desc, #vqmm-desc').text(data.description);
                    }
                    if (data.settings && typeof data.settings === 'object') {
                        wheelSettings = normalizeSettings(data.settings);
                        applySettingsToUI(data.settings);
                    }
                    if (Array.isArray(data.prizes) && data.prizes.length) {
                        wheelPrizes = data.prizes.map(normalizePrize);
                    }

                    localStorage.setItem(getSettingsStorageKey(), JSON.stringify(collectSettings()));
                    localStorage.setItem(getStorageKey(), JSON.stringify(wheelPrizes));

                    renderPrizeList();
                    syncSectorColors();
                    renderWheel();
                    drawWheelCanvas(0);

                    // Đóng modal
                    var modalEl = document.getElementById('modalUserWheels');
                    if (modalEl) {
                        var modalInst = bootstrap.Modal.getInstance(modalEl);
                        if (modalInst) modalInst.hide();
                    }
                }
            },
            complete: function () {
                $btn.prop('disabled', false).text('Dùng');
            }
        });
    });

    // Sửa / Đổi tên tiêu đề vòng quay
    $(document).on('click', '.btn-edit-user-wheel', function () {
        var id = $(this).data('id');
        var currentTitle = $(this).data('title') || '';
        if (!id) return;

        var newTitle = prompt('Nhập tiêu đề mới cho vòng quay #' + id + ':', currentTitle);
        if (newTitle === null) return;
        newTitle = $.trim(newTitle);
        if (!newTitle) {
            alert('Tiêu đề không được để trống!');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: wp_spin_wheel_params.rest_url + 'user/wheels/' + id,
            method: 'POST',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wp_spin_wheel_params.nonce);
            },
            contentType: 'application/json',
            data: JSON.stringify({
                title: newTitle
            }),
            success: function (res) {
                if (res && res.success) {
                    if (parseInt(id, 10) === parseInt(wheelId, 10)) {
                        $('#txt-title, #vqmm-title').text(res.title || newTitle);
                    }
                    loadUserWheelsList();
                }
            },
            error: function () {
                alert('Không thể cập nhật tiêu đề vòng quay.');
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });

    // Khi sửa trực tiếp tiêu đề hoặc mô tả trên màn hình -> tự động đồng bộ lên server
    $('#txt-title, #vqmm-title, #txt-desc, #vqmm-desc').on('blur', function () {
        if (typeof wp_spin_wheel_params !== 'undefined' && wp_spin_wheel_params.is_logged_in) {
            syncUserWheelToServer();
        }
    });

    // Tạo mới vòng quay (dùng chung cho nút trong modal và nút trên dropdown)
    function createNewUserWheel() {
        var title = prompt('Nhập tên cho vòng quay mới:', 'Vòng quay ' + new Date().toLocaleDateString('vi-VN'));
        if (title === null) return;
        title = $.trim(title) || 'Vòng quay mới';

        var $btn = $('#btn-create-new-user-wheel');
        $btn.prop('disabled', true);

        $.ajax({
            url: wp_spin_wheel_params.rest_url + 'user/wheels',
            method: 'POST',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wp_spin_wheel_params.nonce);
            },
            contentType: 'application/json',
            data: JSON.stringify({
                title: title,
                settings: collectSettings(),
                prizes: wheelPrizes
            }),
            success: function (res) {
                if (res && res.wheel_id) {
                    wheelId = res.wheel_id;
                    $('#wheel-wrapper').attr('data-wheel-id', res.wheel_id);
                    $('#txt-title, #vqmm-title').text(res.title || title);
                    loadUserWheelsList();

                    // Đóng modal
                    var modalEl = document.getElementById('modalUserWheels');
                    if (modalEl) {
                        var modalInst = bootstrap.Modal.getInstance(modalEl);
                        if (modalInst) modalInst.hide();
                    }
                }
            },
            error: function (err) {
                var msg = (err && err.responseJSON && err.responseJSON.message) ? err.responseJSON.message : 'Lỗi tạo vòng quay mới!';
                alert(msg);
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    }

    $('#btn-create-new-user-wheel').on('click', createNewUserWheel);
    // Nút "Tạo vòng quay mới" trên dropdown của template wheel-user.php
    $(document).on('click', '.btn-create-new-user-wheel-top', createNewUserWheel);

    // Nhân bản vòng quay
    $(document).on('click', '.btn-dup-user-wheel', function () {
        var id = $(this).data('id');
        if (!id) return;

        var $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: wp_spin_wheel_params.rest_url + 'user/wheels/' + id + '/duplicate',
            method: 'POST',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wp_spin_wheel_params.nonce);
            },
            success: function (res) {
                loadUserWheelsList();
            },
            error: function () {
                alert('Không thể nhân bản vòng quay.');
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });

    // Xóa vòng quay
    $(document).on('click', '.btn-del-user-wheel', function () {
        var id = $(this).data('id');
        if (!id) return;
        if (!confirm('Bạn có chắc chắn muốn xóa vòng quay này không?')) return;

        var $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: wp_spin_wheel_params.rest_url + 'user/wheels/' + id,
            method: 'DELETE',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', wp_spin_wheel_params.nonce);
            },
            success: function (res) {
                loadUserWheelsList();
            },
            error: function () {
                alert('Không thể xóa vòng quay này.');
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });
});
