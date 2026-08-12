jQuery(document).ready(function($) {
    var container = $('#wheel-wrapper');
    var wheelId = parseInt( container.data('wheel-id'), 10 ) || 0;
    var nonce = container.data('nonce');
    var rawSettings = container.attr('data-wheel-settings');
    var rawPrizes = container.attr('data-wheel-prizes');
    var wheelSettings = {};
    var wheelPrizes = [];
    var spinning = false;
    var pendingRequest = false;
    var currentRotation = 0;
    var pointerImageCache = {};
    var idleAnimationId = null;
    var IDLE_SPEED = 0.001; // radians per frame (~0.17°/frame)

    function parseJson(value, fallback) {
        if ( ! value ) {
            return fallback;
        }
        try {
            var parsed = JSON.parse(value);
            return parsed !== null ? parsed : fallback;
        } catch (e) {
            return typeof value === 'object' ? value : fallback;
        }
    }

    function normalizeSettings(settings) {
        settings = settings || {};
        var background = settings.background || {};
        var backgroundColor = '#ffffff';
        var backgroundImage = '';
        var backgroundType = 'color';

        if ( typeof background === 'string' ) {
            backgroundColor = background;
        } else if ( typeof background === 'object' && background !== null ) {
            backgroundType = background.type || 'color';
            backgroundColor = background.value || background.color || '#ffffff';
            backgroundImage = background.image || '';
        }

        return {
            background: {
                type: backgroundType,
                color: backgroundColor,
                image: backgroundImage,
            },
            wheel: settings.wheel || {
                size: 600,
                border: 8,
                border_color: '#ffffff',
                shadow: true,
            },
            button: settings.button || {
                text: 'QUAY',
                color: '#ff0000',
                text_color: '#ffffff',
                radius: 50,
                background_image: '',
            },
            pointer: settings.pointer || {
                image: '',
                size: 80,
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

    function getStorageKey() {
        return 'wp_spin_wheel_default_prizes';
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

        if ( ! title ) {
            title = 'Vòng quay may mắn';
        }

        $('#vqmm-title').text(title);
        if ( description ) {
            $('#vqmm-desc').html(description.replace(/\n/g, '<br>'));
        } else {
            $('#vqmm-desc').empty();
        }

        if ( wheelId && nonce ) {
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
                success: function() {
                    $('#edit-mode-txt').text('Đã lưu');
                },
                error: function() {
                    $('#edit-mode-txt').text('Lưu thất bại');
                },
            });
        } else {
            $('#edit-mode-txt').text('Đã cập nhật');
        }

        closeTitleDescriptionEditor();
    }

    function normalizePrize(prize, index) {
        prize = prize || {};
        return {
            id: prize.id || prize.title + '-' + index,
            title: prize.title || prize.name || '',
            color: prize.color || prize.background || (index % 2 === 0 ? '#ef4444' : '#f59e0b'),
            description: prize.description || '',
        };
    }

    function loadPrizes() {
        var prizes = parseJson(rawPrizes, []);
        if ( ! wheelId ) {
            var stored = parseJson(localStorage.getItem(getStorageKey()), null);
            if ( Array.isArray(stored) && stored.length ) {
                prizes = stored;
            }
        }
        return prizes.map(normalizePrize);
    }

    function savePrizes() {
        if ( ! wheelId ) {
            localStorage.setItem(getStorageKey(), JSON.stringify(wheelPrizes));
        }
    }

    function renderPrizeList() {
        var list = $('#sector_list');
        list.empty();

        if ( ! wheelPrizes.length ) {
            list.removeClass('has-prizes');
            updateEntriesCount();
            return;
        }

        wheelPrizes.forEach(function(prize, index) {
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
        wheelPrizes.sort(function(a, b) {
            var titleA = (a.title || '').toLowerCase();
            var titleB = (b.title || '').toLowerCase();
            if ( titleA < titleB ) {
                return ascending ? -1 : 1;
            }
            if ( titleA > titleB ) {
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
        if ( ! title ) {
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
        if ( index < 0 || index >= wheelPrizes.length ) {
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
        return wheelPrizes.findIndex(function(item) {
            return item.id === prize.id || item.title === prize.title;
        });
    }

    function drawPointer(ctx, centerX, centerY, radius) {
        var pointer = wheelSettings.pointer || {};
        var size = Math.max(24, parseInt(pointer.size, 10) || 80);
        var imageUrl = pointer.image || '';

        if ( imageUrl ) {
            if ( pointerImageCache[ imageUrl ] ) {
                ctx.save();
                ctx.translate(centerX, centerY - radius - size / 2 - 8);
                ctx.drawImage(pointerImageCache[ imageUrl ], -size / 2, -size / 2, size, size);
                ctx.restore();
                return;
            }

            var pointerImage = new Image();
            pointerImage.onload = function() {
                pointerImageCache[ imageUrl ] = pointerImage;
                drawWheelCanvas(currentRotation);
            };
            pointerImage.src = imageUrl;
            return;
        }

        ctx.save();
        ctx.fillStyle = '#111';
        ctx.beginPath();
        ctx.moveTo(centerX, centerY - radius - 12);
        ctx.lineTo(centerX - 16, centerY - radius + 18);
        ctx.lineTo(centerX + 16, centerY - radius + 18);
        ctx.closePath();
        ctx.fill();
        ctx.restore();
    }

    function drawWheelCanvas(rotation) {
        var canvas = document.getElementById('wheel');
        if ( ! canvas || ! wheelPrizes.length ) {
            return;
        }

        var size = wheelSettings.wheel.size;
        var ctx = canvas.getContext('2d');
        var centerX = size / 2;
        var centerY = size / 2;
        var radius = centerX - wheelSettings.wheel.border;
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

        if ( wheelSettings.wheel.shadow ) {
            ctx.shadowColor = 'rgba(0,0,0,0.15)';
            ctx.shadowBlur = 20;
        }

        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
        ctx.fillStyle = wheelSettings.background.color || '#ffffff';
        ctx.fill();
        ctx.lineWidth = wheelSettings.wheel.border;
        ctx.strokeStyle = wheelSettings.wheel.border_color;
        ctx.stroke();

        ctx.shadowColor = 'transparent';

        wheelPrizes.forEach(function(prize, index) {
            var startAngle = index * angleStep;
            var endAngle = startAngle + angleStep;
            var fillColor = prize.color || (index % 2 === 0 ? '#f16f6f' : '#f6c85f');

            ctx.beginPath();
            ctx.moveTo(centerX, centerY);
            ctx.arc(centerX, centerY, radius, startAngle, endAngle);
            ctx.closePath();
            ctx.fillStyle = fillColor;
            ctx.fill();
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = 2;
            ctx.stroke();

            ctx.save();
            var textAngle = startAngle + angleStep / 2;
            var textRadius = radius * 0.65;
            ctx.translate(centerX + Math.cos(textAngle) * textRadius, centerY + Math.sin(textAngle) * textRadius);
            ctx.rotate(textAngle + Math.PI / 2);
            ctx.fillStyle = '#ffffff';
            ctx.font = '16px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            var text = prize.title || prize.name || 'Prize';
            var lines = text.split('\n');
            lines.forEach(function(line, lineIndex) {
                ctx.fillText(line, 0, (lineIndex - (lines.length - 1) / 2) * 18);
            });
            ctx.restore();
        });

        ctx.restore();
    }

    function startIdleAnimation() {
        if ( idleAnimationId ) {
            return;
        }
        function idleStep() {
            if ( spinning ) {
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
        if ( idleAnimationId ) {
            cancelAnimationFrame(idleAnimationId);
            idleAnimationId = null;
        }
    }

    function renderWheel() {
        var wrapper = $('#wheel-wrapper');
        wrapper.css({
            backgroundColor: wheelSettings.background.color,
            backgroundImage: wheelSettings.background.image ? 'url(' + wheelSettings.background.image + ')' : 'none',
            backgroundSize: wheelSettings.background.image ? 'cover' : 'auto',
            backgroundRepeat: wheelSettings.background.image ? 'no-repeat' : 'repeat',
            backgroundPosition: wheelSettings.background.image ? 'center center' : 'center',
        });

        $('#spin').text(wheelSettings.button.text || 'QUAY NGAY');
        $('#spin').css({
            backgroundColor: wheelSettings.button.background_image ? 'transparent' : wheelSettings.button.color,
            backgroundImage: wheelSettings.button.background_image ? 'url(' + wheelSettings.button.background_image + ')' : 'none',
            color: wheelSettings.button.text_color,
            borderRadius: wheelSettings.button.radius + 'px',
            backgroundSize: wheelSettings.button.background_image ? 'cover' : '',
            backgroundRepeat: wheelSettings.button.background_image ? 'no-repeat' : '',
            backgroundPosition: wheelSettings.button.background_image ? 'center center' : '',
        });

        if ( wheelSettings.custom_css ) {
            var styleId = 'wp-spin-wheel-custom-css-' + wheelId;
            var styleTag = document.getElementById( styleId );
            if ( ! styleTag ) {
                styleTag = document.createElement( 'style' );
                styleTag.id = styleId;
                document.head.appendChild( styleTag );
            }
            styleTag.textContent = wheelSettings.custom_css;
        }

        drawWheelCanvas(currentRotation);
    }

    function animateSpin(targetIndex, callback) {
        if ( spinning || ! wheelPrizes.length ) {
            return;
        }

        stopIdleAnimation();
        spinning = true;
        var segmentCount = wheelPrizes.length;
        var angleStep = (Math.PI * 2) / segmentCount;
        var normalizedRotation = currentRotation % (Math.PI * 2);
        if ( normalizedRotation < 0 ) {
            normalizedRotation += Math.PI * 2;
        }

        var pointerAngle = -Math.PI / 2;
        var targetAngle = pointerAngle - (targetIndex + 0.5) * angleStep;
        var rotations = 4;
        var totalRotation = rotations * Math.PI * 2 + targetAngle - normalizedRotation;
        while ( totalRotation < 0 ) {
            totalRotation += Math.PI * 2;
        }

        var duration = (wheelSettings.animation.duration || 6) * 1000;
        var startRotation = currentRotation;
        var startTime = null;

        function step(timestamp) {
            if ( ! startTime ) {
                startTime = timestamp;
            }
            var elapsed = timestamp - startTime;
            var progress = Math.min(elapsed / duration, 1);
            var ease = 1 - Math.pow(1 - progress, 3);
            var rotation = startRotation + totalRotation * ease;
            currentRotation = rotation;
            drawWheelCanvas(currentRotation);

            if ( progress < 1 ) {
                requestAnimationFrame(step);
                return;
            }

            spinning = false;
            currentRotation = (startRotation + totalRotation) % (Math.PI * 2);
            drawWheelCanvas(currentRotation);

            if ( wheelSettings.animation.confetti && typeof window.confetti === 'function' ) {
                window.confetti();
            }

            if ( typeof callback === 'function' ) {
                callback();
            }

            startIdleAnimation();
        }

        requestAnimationFrame(step);
    }

    function showResultPopup(prize) {
        $('#modal-result-title').text(prize.title || '');
        $('#modal-result-desc').text(prize.description || '');
        $('#modal-result').show().attr('aria-hidden', 'false');
    }

    function closeResultPopup() {
        $('#modal-result').hide().attr('aria-hidden', 'true');
    }

    function handleSpinSuccess(prize) {
        var index = getPrizeIndex(prize);
        if ( index === -1 ) {
            index = 0;
        }
        animateSpin(index, function() {
            var title = prize.title || '';
            var description = prize.description || '';
            var resultEntry = '<div class="wheel-result-item"><strong>' + title + '</strong><div>' + description + '</div></div>';
            $('#wheel_result').append(resultEntry);
            updateResultCount();
            $('#tab-result').trigger('click');
            showResultPopup(prize);
        });
    }

    wheelSettings = normalizeSettings(parseJson(rawSettings));
    wheelPrizes = loadPrizes();
    renderPrizeList();
    renderWheel();
    startIdleAnimation();

    $('#tab-entries').on('click', function() {
        $('#tab-entries').addClass('active');
        $('#tab-result').removeClass('active');
        $('#tab-content-entries').addClass('show active');
        $('#tab-content-result').removeClass('show active');
    });

    $('#tab-result').on('click', function() {
        $('#tab-result').addClass('active');
        $('#tab-entries').removeClass('active');
        $('#tab-content-result').addClass('show active');
        $('#tab-content-entries').removeClass('show active');
    });

    $('#spin').on('click', function() {
        if ( spinning || pendingRequest ) {
            return;
        }

        if ( ! wheelId ) {
            if ( ! wheelPrizes.length ) {
                alert( wp_spin_wheel_params.text_error );
                return;
            }

            var prizeIndex = Math.floor( Math.random() * wheelPrizes.length );
            var prize = wheelPrizes[ prizeIndex ] || { title: 'Thưởng' };
            handleSpinSuccess( prize );
            return;
        }

        var form = {};
        container.find('.spin-wheel-form input').each(function() {
            var name = $(this).attr('name');
            if ( name ) {
                form[name] = $(this).val();
            }
        });

        // Use REST API for spin (server decides winner)
        pendingRequest = true;
        var restBase = (wp_spin_wheel_params && wp_spin_wheel_params.rest_url) ? wp_spin_wheel_params.rest_url.replace(/\/$/, '') : (window.location.origin + '/wp-json/spin-wheel/v1');
        var endpoint = restBase + '/wheels/' + wheelId + '/spin';

        fetch(endpoint, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': (wp_spin_wheel_params && wp_spin_wheel_params.nonce) ? wp_spin_wheel_params.nonce : ''
            },
            body: JSON.stringify({ form: form })
        }).then(function(res) {
            pendingRequest = false;
            if ( ! res.ok ) {
                return res.json().then(function(err) { throw err; }).catch(function() { throw { message: wp_spin_wheel_params.text_error }; });
            }
            return res.json();
        }).then(function(data) {
            if ( data && data.prize ) {
                handleSpinSuccess(data.prize);
            } else if ( data && data.message ) {
                alert( data.message || wp_spin_wheel_params.text_error );
            } else {
                alert( wp_spin_wheel_params.text_error );
            }
        }).catch(function(err) {
            var msg = (err && err.message) ? err.message : (err && err.data && err.data.message) ? err.data.message : wp_spin_wheel_params.text_error;
            alert( msg );
            pendingRequest = false;
        });
    });

    $('#edit-content').on('click', function(e) {
        e.preventDefault();
        openTitleDescriptionEditor();
    });

    $('#saveTitleDesc').on('click', function(e) {
        e.preventDefault();
        saveTitleDesc();
    });

    $('#modal-edit-close, #modal-edit-cancel').on('click', function(e) {
        e.preventDefault();
        closeTitleDescriptionEditor();
    });

    $('#modal-edit').on('click', function(e) {
        if ( $(e.target).is('#modal-edit') ) {
            closeTitleDescriptionEditor();
        }
    });

    $('#modal-result-close, #modal-result-close-btn').on('click', function(e) {
        e.preventDefault();
        closeResultPopup();
    });

    $('#modal-result').on('click', function(e) {
        if ( $(e.target).is('#modal-result') ) {
            closeResultPopup();
        }
    });

    $(document).on('keydown', function(e) {
        if ( e.key === 'Escape' ) {
            closeTitleDescriptionEditor();
            closeResultPopup();
        }
    });

    $('#btn-clear-result').on('click', function() {
        $('#wheel_result').empty();
        updateResultCount();
    });

    $('#btn-sort-wheel-az').on('click', function() {
        sortPrizes(true);
        $('#btn-sort-wheel-az').addClass('d-none');
        $('#btn-sort-wheel-za').removeClass('d-none');
    });

    $('#btn-sort-wheel-za').on('click', function() {
        sortPrizes(false);
        $('#btn-sort-wheel-za').addClass('d-none');
        $('#btn-sort-wheel-az').removeClass('d-none');
    });

    $('#btn-clear-entry').on('click', function() {
        clearPrizes();
    });

    $('#btn-add-prize').on('click', function() {
        var title = $('#new_prize_title').val();
        var color = $('#new_prize_color').val();
        addPrize(title, color);
        $('#new_prize_title').val('');
    });

    $('#sector_list').on('click', '.btn-remove-prize', function() {
        var index = parseInt( $(this).data('index'), 10 );
        removePrize(index);
    });

    $('#btn-restore-defaults').on('click', function() {
        restoreDefaultPrizes();
        $('#btn-sort-wheel-za').addClass('d-none');
        $('#btn-sort-wheel-az').removeClass('d-none');
    });

    $('#btn-shuffle-wheel').on('click', function() {
        var items = $('#sector_list .wheel-prize-item').toArray();
        items.sort(function() { return 0.5 - Math.random(); });
        $('#sector_list').empty().append(items);

        var newOrder = [];
        items.forEach(function(item) {
            var prizeId = $(item).data('id');
            var prize = wheelPrizes.find(function(entry) {
                return entry.id === prizeId;
            });
            if ( prize ) {
                newOrder.push(prize);
            }
        });

        if ( newOrder.length ) {
            wheelPrizes = newOrder;
            savePrizes();
            renderPrizeList();
            renderWheel();
        }
    });
});
