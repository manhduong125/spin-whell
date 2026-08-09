jQuery(document).ready(function($) {
    var container = $('#spin-wheel-prizes');
    var template = $('#spin-wheel-prize-template').html();
    var index = container.find('.spin-prize-item').length;

    $('#add-spin-prize').on('click', function(e) {
        e.preventDefault();
        var html = template.replace(/__index__/g, index);
        container.append(html);
        index++;
    });

    container.on('click', '.remove-spin-prize', function() {
        $(this).closest('.spin-prize-item').remove();
    });

    if ( typeof $.fn.wpColorPicker === 'function' ) {
        $('.wp-spin-wheel-color-field').wpColorPicker();
    }

    $(document).on('click', '.wp-spin-wheel-media-upload-button', function(e) {
        e.preventDefault();
        var button = $(this);
        var target = $( '#' + button.data('target') );

        if ( typeof wp === 'undefined' || ! wp.media ) {
            return;
        }

        var frame = wp.media({
            title: wp_spin_wheel_admin_params.media_title,
            button: { text: wp_spin_wheel_admin_params.media_button },
            multiple: false,
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            if ( target.length ) {
                target.val( attachment.url ).trigger('change');
            }
        });

        frame.open();
    });

    function showLibraryMessage(message, type) {
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        $('#library-action-message').html('<div class="alert ' + alertClass + ' py-2">' + message + '</div>');
    }

    $('.save-library-item').on('click', function() {
        var button = $(this);
        var title = button.data('title');
        var config = button.data('config');

        $.post(wp_spin_wheel_admin_params.ajax_url, {
            action: 'spin_wheel_save_library_item',
            nonce: wp_spin_wheel_admin_params.nonce,
            title: title,
            config: JSON.stringify(config),
        }, function(response) {
            if (response.success) {
                showLibraryMessage(wp_spin_wheel_admin_params.text_saved, 'success');
            } else {
                showLibraryMessage(response.data.message || response.statusText, 'error');
            }
        });
    });

    $('.apply-library-item').on('click', function() {
        var button = $(this);
        var title = button.data('title');
        var config = button.data('config');
        var wheelId = $('#library-wheel-select').val();

        if (!wheelId) {
            showLibraryMessage(wp_spin_wheel_admin_params.text_select_wheel, 'error');
            return;
        }

        $.post(wp_spin_wheel_admin_params.ajax_url, {
            action: 'spin_wheel_apply_library_item',
            nonce: wp_spin_wheel_admin_params.nonce,
            title: title,
            config: JSON.stringify(config),
            wheel_id: wheelId,
        }, function(response) {
            if (response.success) {
                showLibraryMessage(wp_spin_wheel_admin_params.text_applied, 'success');
            } else {
                showLibraryMessage(response.data.message || response.statusText, 'error');
            }
        });
    });

    $(document).on('click', '.delete-library-preset', function() {
        var button = $(this);
        var presetId = button.data('preset-id');

        if ( ! presetId || ! confirm( wp_spin_wheel_admin_params.text_delete_confirm ) ) {
            return;
        }

        $.post(wp_spin_wheel_admin_params.ajax_url, {
            action: 'spin_wheel_delete_preset',
            nonce: wp_spin_wheel_admin_params.nonce,
            preset_id: presetId,
        }, function(response) {
            if (response.success) {
                showLibraryMessage(wp_spin_wheel_admin_params.text_deleted, 'success');
                button.closest('.col-md-4').remove();
            } else {
                showLibraryMessage(response.data.message || response.statusText, 'error');
            }
        });
    });
});
