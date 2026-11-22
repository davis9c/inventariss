(function () {
    'use strict';

    function enhance(select) {
        if (!select || select.dataset.searchable === '1') return;
        if (select.hasAttribute('data-no-search')) return;
        if (select.options.length <= 3) return;

        select.dataset.searchable = '1';

        var wrapper = document.createElement('div');
        wrapper.className = 'searchable-select';

        var input = document.createElement('input');
        input.type = 'search';
        input.className = 'form-control form-control-sm mb-1';
        input.placeholder = 'Cari...';
        input.setAttribute('aria-label', 'Cari opsi');

        // prevent form submit on enter
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') e.preventDefault();
        });

        input.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            var opts = select.options;
            for (var i = 0; i < opts.length; i++) {
                var opt = opts[i];
                if (opt.value === '') {
                    opt.hidden = false;
                    continue;
                }
                var text = opt.textContent.toLowerCase();
                opt.hidden = q !== '' && text.indexOf(q) === -1;
            }
        });

        // re-apply filter when options change (AJAX)
        var mo = new MutationObserver(function () {
            input.dispatchEvent(new Event('input'));
        });
        mo.observe(select, { childList: true });

        select.parentNode.insertBefore(input, select);
    }

    function init() {
        var selects = document.querySelectorAll('select');
        for (var i = 0; i < selects.length; i++) {
            enhance(selects[i]);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // re-run when modals open (Bootstrap)
    document.addEventListener('shown.bs.modal', function () {
        init();
    });
})();
