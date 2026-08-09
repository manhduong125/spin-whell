(function($){
    var pageMatch = window.location.search.indexOf('page=wp-spin-wheel-settings') !== -1;
    if ( ! pageMatch ) {
        return;
    }

    var optionBase = 'wp_spin_wheel_settings';
    var params = window.wp_spin_wheel_settings_params || window.wp_spin_wheel_admin_params || {};
    var restRoot = (params && params.rest_url) ? params.rest_url.replace(/\/$/, '') : (window.wpApiSettings && window.wpApiSettings.root ? window.wpApiSettings.root + 'spin-wheel/v1' : '/wp-json/spin-wheel/v1');
    var endpoint = restRoot + '/settings';
    var nonce = (params && params.nonce) ? params.nonce : (window.wpApiSettings && window.wpApiSettings.nonce ? window.wpApiSettings.nonce : '');

    function loadSettings() {
        fetch(endpoint, { credentials: 'same-origin', headers: { 'X-WP-Nonce': nonce } })
        .then(function(res){ return res.json(); })
        .then(function(data){
            if (!data) return;
            Object.keys(data).forEach(function(key){
                var sel = '[name="' + optionBase + '[' + key + ']"]';
                var el = $(sel);
                if (!el.length) return;
                if (el.is(':checkbox')) {
                    el.prop('checked', !!data[key]);
                } else {
                    el.val(data[key]);
                }
            });
        }).catch(function(){ /* ignore */ });
    }

    $(function(){
        var form = $('form[action="options.php"]');
        if (!form.length) return;

        form.on('submit', function(e){
            e.preventDefault();
            var payload = {};
            form.find('[name^="' + optionBase + '"]').each(function(){
                var name = $(this).attr('name');
                var m = name.match(/\[(.+)\]$/);
                if (!m) return;
                var key = m[1];
                if ($(this).is(':checkbox')) payload[key] = $(this).is(':checked') ? 1 : 0;
                else payload[key] = $(this).val();
            });

            fetch(endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify(payload)
            }).then(function(res){
                return res.json();
            }).then(function(data){
                if (params && params.text_saved) {
                    alert(params.text_saved);
                } else {
                    alert('Saved');
                }
            }).catch(function(){
                alert('Error saving settings');
            });
        });

        loadSettings();
    });
})(jQuery);
