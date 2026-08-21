(function($){
    var isWheelSettings = window.location.search.indexOf('wp-spin-wheel-settings') !== -1;
    var isBoxSettings   = window.location.search.indexOf('wp-spin-box-settings') !== -1;

    if ( ! isWheelSettings && ! isBoxSettings ) {
        return;
    }

    var params   = window.wp_spin_wheel_settings_params || window.wp_spin_wheel_admin_params || {};
    var restRoot = (params && params.rest_url) ? params.rest_url.replace(/\/$/, '') : (window.wpApiSettings && window.wpApiSettings.root ? window.wpApiSettings.root + 'spin-wheel/v1' : '/wp-json/spin-wheel/v1');
    var nonce    = (params && params.nonce) ? params.nonce : (window.wpApiSettings && window.wpApiSettings.nonce ? window.wpApiSettings.nonce : '');

    // 1. Wheel Form Handler
    var wheelForm = $('#form-spin-wheel-settings, form[data-option-base="wp_spin_wheel_settings"]');
    if (wheelForm.length) {
        var wheelBase = 'wp_spin_wheel_settings';
        var wheelEndpoint = restRoot + '/settings';

        function loadWheelSettings() {
            fetch(wheelEndpoint, { credentials: 'same-origin', headers: { 'X-WP-Nonce': nonce } })
            .then(function(res){ return res.json(); })
            .then(function(data){
                if (!data) return;
                Object.keys(data).forEach(function(key){
                    var sel = '[name="' + wheelBase + '[' + key + ']"]';
                    var el = $(sel);
                    if (!el.length) return;
                    if (el.is(':checkbox')) {
                        el.prop('checked', !!data[key]);
                    } else {
                        el.val(data[key]);
                    }
                });
            }).catch(function(){});
        }
        loadWheelSettings();
    }

    // 2. Box Form Handler
    var boxForm = $('#form-spin-box-settings, form[data-option-base="wp_spin_box_settings"]');
    if (boxForm.length) {
        var boxBase = 'wp_spin_box_settings';
        var boxEndpoint = restRoot + '/box-settings';

        function loadBoxSettings() {
            fetch(boxEndpoint, { credentials: 'same-origin', headers: { 'X-WP-Nonce': nonce } })
            .then(function(res){ return res.json(); })
            .then(function(data){
                if (!data) return;
                Object.keys(data).forEach(function(key){
                    var sel = '[name="' + boxBase + '[' + key + ']"]';
                    var el = $(sel);
                    if (!el.length) return;
                    if (el.is(':checkbox')) {
                        el.prop('checked', !!data[key]);
                    } else {
                        el.val(data[key]);
                    }
                });
            }).catch(function(){});
        }
        loadBoxSettings();
    }
})(jQuery);
