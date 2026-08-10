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
});
