(function () {
    'use strict';

    var DEBOUNCE = 250;

    function debounce(fn, wait) {
        var t;
        return function () {
            var ctx = this, args = arguments;
            clearTimeout(t);
            t = setTimeout(function () { fn.apply(ctx, args); }, wait);
        };
    }

    function closeAll(except) {
        document.querySelectorAll('.cb-open').forEach(function (w) {
            if (w !== except) w.classList.remove('cb-open');
        });
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.cb-wrapper')) closeAll(null);
    });

    function enhance(select) {
        if (!select || select.dataset.cbEnhanced === '1') return;
        if (select.hasAttribute('data-no-search')) return;
        if (select.options.length <= 1 && !select.dataset.ajaxUrl && !select.dataset.forceSearch) return;

        select.dataset.cbEnhanced = '1';
        select.classList.add('cb-original');
        select.style.position = 'absolute';
        select.style.opacity = '0';
        select.style.height = '0';
        select.style.width = '0';
        select.style.pointerEvents = 'none';
        select.tabIndex = -1;

        // wrapper
        var wrapper = document.createElement('div');
        wrapper.className = 'cb-wrapper position-relative';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);

        var input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm';
        input.autocomplete = 'off';
        input.placeholder = select.querySelector('option[value=""]')
            ? select.querySelector('option[value=""]').textContent.trim()
            : 'Ketik untuk mencari...';
        // initial value
        var selOpt = select.options[select.selectedIndex];
        if (selOpt && selOpt.value !== '') {
            input.value = selOpt.textContent.trim();
        }

        var list = document.createElement('ul');
        list.className = 'cb-list list-group position-absolute w-100 shadow-sm';
        list.style.zIndex = '1055';
        list.style.maxHeight = '240px';
        list.style.overflowY = 'auto';
        list.style.display = 'none';
        list.style.top = '100%';
        list.style.left = '0';

        wrapper.appendChild(input);
        wrapper.appendChild(list);

        // client options cache
        var clientOptions = null;
        function collectClientOptions() {
            var opts = [];
            for (var i = 0; i < select.options.length; i++) {
                var o = select.options[i];
                if (o.value === '') continue;
                opts.push({ id: o.value, text: o.textContent.trim() });
            }
            return opts;
        }
        if (!select.dataset.ajaxUrl) {
            clientOptions = collectClientOptions();
        }

        var ajaxUrl = select.dataset.ajaxUrl || null;

        function render(items) {
            list.innerHTML = '';
            if (!items || items.length === 0) {
                var li = document.createElement('li');
                li.className = 'list-group-item text-muted small';
                li.textContent = 'Tidak ditemukan';
                list.appendChild(li);
                return;
            }
            items.forEach(function (it) {
                var li = document.createElement('li');
                li.className = 'list-group-item list-group-item-action py-1 px-2';
                li.style.cursor = 'pointer';
                li.textContent = it.text;
                li.dataset.id = it.id;
                li.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    select.value = it.id;
                    input.value = it.text;
                    list.style.display = 'none';
                    wrapper.classList.remove('cb-open');
                    select.dispatchEvent(new CustomEvent('cb:select', { bubbles: true, detail: it }));
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                });
                list.appendChild(li);
            });
        }

        function showList() {
            list.style.display = 'block';
            wrapper.classList.add('cb-open');
        }
        function hideList() {
            list.style.display = 'none';
            wrapper.classList.remove('cb-open');
        }

        function fetchAjax(q) {
            if (!ajaxUrl) return;
            var spinner = document.createElement('li');
            spinner.className = 'list-group-item text-muted small';
            spinner.textContent = 'Memuat...';
            list.innerHTML = '';
            list.appendChild(spinner);
            showList();

            var url = ajaxUrl + (ajaxUrl.indexOf('?') > -1 ? '&' : '?') + 'q=' + encodeURIComponent(q || '');
            // support location_id if data-location-source
            var locSrc = select.dataset.locationSource;
            if (locSrc) {
                var locEl = document.querySelector(locSrc);
                if (locEl && locEl.value) {
                    url += '&location_id=' + encodeURIComponent(locEl.value);
                }
            }

            fetch(url, { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    render(Array.isArray(data) ? data : (data.results || []));
                })
                .catch(function () {
                    list.innerHTML = '';
                    var li = document.createElement('li');
                    li.className = 'list-group-item text-danger small';
                    li.textContent = 'Gagal memuat data';
                    list.appendChild(li);
                });
        }

        var fetchDebounced = debounce(fetchAjax, DEBOUNCE);

        input.addEventListener('focus', function () {
            closeAll(wrapper);
            if (ajaxUrl) {
                fetchAjax('');
            } else {
                render(clientOptions);
                showList();
            }
        });

        input.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            if (ajaxUrl) {
                fetchDebounced(q);
                showList();
                return;
            }
            var filtered = clientOptions.filter(function (o) {
                return q === '' || o.text.toLowerCase().indexOf(q) !== -1;
            });
            render(filtered);
            showList();
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { hideList(); return; }
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                var first = list.querySelector('.list-group-item-action');
                if (first) first.focus();
                return;
            }
            if (e.key === 'Enter') {
                e.preventDefault();
                var firstItem = list.querySelector('.list-group-item-action');
                if (firstItem) firstItem.dispatchEvent(new MouseEvent('mousedown'));
            }
        });

        // keyboard nav within list
        list.addEventListener('keydown', function (e) {
            var items = Array.prototype.slice.call(list.querySelectorAll('.list-group-item-action'));
            var idx = items.indexOf(document.activeElement);
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (idx < items.length - 1) items[idx + 1].focus();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (idx > 0) items[idx - 1].focus();
                else input.focus();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (document.activeElement && document.activeElement.classList.contains('list-group-item-action')) {
                    document.activeElement.dispatchEvent(new MouseEvent('mousedown'));
                }
            } else if (e.key === 'Escape') {
                hideList();
                input.focus();
            }
        });

        // if input cleared, clear select
        input.addEventListener('blur', function () {
            setTimeout(function () {
                if (input.value.trim() === '') {
                    select.value = '';
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
                hideList();
            }, 200);
        });
    }

    function init(root) {
        root = root || document;
        var selects = root.querySelectorAll('select');
        for (var i = 0; i < selects.length; i++) enhance(selects[i]);
    }

    window.initCombobox = init;
    window.destroyCombobox = function (select) {
        if (!select || select.dataset.cbEnhanced !== '1') return;
        var wrapper = select.parentNode;
        if (wrapper && wrapper.classList.contains('cb-wrapper')) {
            // move select out
            wrapper.parentNode.insertBefore(select, wrapper);
            wrapper.remove();
        }
        select.classList.remove('cb-original');
        select.style.position = '';
        select.style.opacity = '';
        select.style.height = '';
        select.style.width = '';
        select.style.pointerEvents = '';
        select.tabIndex = 0;
        delete select.dataset.cbEnhanced;
    };

    window.refreshCombobox = function (select) {
        if (!select) return;
        destroyCombobox(select);
        // re-collect options happen on enhance
        enhance(select);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { init(document); });
    } else {
        init(document);
    }

    document.addEventListener('shown.bs.modal', function (e) { init(e.target); });
})();
