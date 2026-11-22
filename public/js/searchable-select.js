(function () {
    'use strict';

    function initSelect2(root) {
        root = root || document;
        if (typeof jQuery === 'undefined' || !jQuery.fn.select2) return;
        var $ = jQuery;
        $(root).find('select').each(function () {
            var $el = $(this);
            if ($el.data('select2')) return;
            if ($el.is('[data-no-search]')) return;

            var ajaxUrl = $el.data('ajax-url');
            var opts = {
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: $el.find('option[value=""]').first().text() || 'Pilih...',
                allowClear: false
            };

            if (ajaxUrl) {
                opts.ajax = {
                    url: ajaxUrl,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { q: params.term || '' };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                };
                opts.minimumInputLength = 0;
            } else {
                if ($el.find('option').length <= 3 && !$el.data('force-search')) return;
            }

            $el.select2(opts);
        });
    }

    window.initSelect2 = initSelect2;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { initSelect2(document); });
    } else {
        initSelect2(document);
    }

    document.addEventListener('shown.bs.modal', function (e) {
        initSelect2(e.target);
    });
})();
