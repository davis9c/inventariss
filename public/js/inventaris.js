(function () {
    'use strict';

    var baseUrl = window.inventarisBaseUrl || '';

    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function toast(message, type) {
        type = type || 'success';
        var container = document.getElementById('toast-container');
        if (!container) return;
        var colors = {
            success: 'text-bg-success',
            danger: 'text-bg-danger',
            warning: 'text-bg-warning',
            info: 'text-bg-info'
        };
        var el = document.createElement('div');
        el.className = 'toast align-items-center border-0 ' + (colors[type] || colors.success);
        el.innerHTML = '<div class="d-flex"><div class="toast-body"></div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
        el.querySelector('.toast-body').textContent = message;
        container.appendChild(el);
        var t = new bootstrap.Toast(el, { delay: 3500 });
        el.addEventListener('hidden.bs.toast', function () { el.remove(); });
        t.show();
    }

    function openModal(id) {
        var m = bootstrap.Modal.getOrCreateInstance(document.getElementById(id));
        m.show();
        return m;
    }

    function hideModal(id) {
        var m = bootstrap.Modal.getOrCreateInstance(document.getElementById(id));
        m.hide();
    }

    function confirm(opts) {
        var modalEl = document.getElementById('confirmModal');
        if (!modalEl) { opts.onConfirm && opts.onConfirm(); return; }
        document.getElementById('confirmModalTitle').textContent = opts.title || 'Konfirmasi';
        document.getElementById('confirmModalMessage').textContent = opts.message || 'Yakin ingin melanjutkan?';
        var okBtn = document.getElementById('confirmModalOk');
        okBtn.textContent = opts.okText || 'Ya, Lanjutkan';
        var m = bootstrap.Modal.getOrCreateInstance(modalEl);
        var confirmed = false;
        okBtn.onclick = function () {
            confirmed = true;
            m.hide();
        };
        modalEl.addEventListener('hidden.bs.modal', function handler() {
            modalEl.removeEventListener('hidden.bs.modal', handler);
            okBtn.onclick = null;
            if (confirmed && opts.onConfirm) opts.onConfirm();
        }, { once: true });
        m.show();
    }

    function clearErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
        form.querySelectorAll('.invalid-feedback').forEach(function (el) { el.remove(); });
    }

    function showErrors(form, errors) {
        Object.keys(errors).forEach(function (name) {
            var msg = Array.isArray(errors[name]) ? errors[name][0] : errors[name];
            var el = form.querySelector('[name="' + name + '"]');
            if (!el) return;
            var input = el.matches('.form-control, .form-select, .form-check-input')
                ? el : el.querySelector('.form-control, .form-select');
            if (!input) return;
            input.classList.add('is-invalid');
            var parent = input.parentElement;
            if (!parent) return;
            var fb = parent.querySelector(':scope > .invalid-feedback');
            if (!fb) {
                fb = document.createElement('div');
                fb.className = 'invalid-feedback';
                parent.appendChild(fb);
            }
            fb.textContent = msg;
        });
    }

    function submitAjax(form, opts) {
        opts = opts || {};
        var doSubmit = function () {
            var submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            clearErrors(form);
            var fd = new FormData(form);
            fetch(opts.url || form.action, {
                method: opts.method || (form.method || 'POST').toUpperCase(),
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (res) {
                    return res.json().catch(function () { return null; }).then(function (json) {
                        if (!res.ok) throw { status: res.status, json: json };
                        return json;
                    });
                })
                .then(function (json) {
                    if (submitBtn) submitBtn.disabled = false;
                    if (json && json.success) {
                        if (json.message) toast(json.message, 'success');
                        if (opts.onSuccess) opts.onSuccess(json);
                    } else {
                        toast((json && json.message) || 'Terjadi kesalahan.', 'danger');
                    }
                })
                .catch(function (err) {
                    if (submitBtn) submitBtn.disabled = false;
                    if (err && err.json) {
                        if (err.json.errors) showErrors(form, err.json.errors);
                        toast(err.json.message || 'Data tidak valid.', 'danger');
                    } else {
                        toast('Koneksi gagal. Silakan coba lagi.', 'danger');
                    }
                });
        };
        if (opts.confirm) {
            confirm({
                title: opts.confirm.title || 'Konfirmasi',
                message: opts.confirm.message,
                okText: opts.confirm.okText,
                onConfirm: doSubmit
            });
        } else {
            doSubmit();
        }
    }

    function fetchJson(url, params, method) {
        method = method || 'GET';
        if (method === 'GET' && params) {
            url += (url.indexOf('?') === -1 ? '?' : '&') + new URLSearchParams(params);
        }
        var init = { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' };
        if (method === 'POST') {
            init.method = 'POST';
            init.body = params instanceof FormData ? params : new URLSearchParams(params || {});
        }
        return fetch(url, init).then(function (res) { return res.json(); });
    }

    var language = {
        processing: 'Memuat...',
        search: 'Cari:',
        lengthMenu: 'Tampil _MENU_ baris',
        info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
        infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
        infoFiltered: '(difilter dari _MAX_ total data)',
        infoPostFix: '',
        zeroRecords: 'Tidak ditemukan data yang cocok',
        emptyTable: 'Tidak ada data',
        paginate: { first: 'Awal', last: 'Akhir', next: 'Berikut', previous: 'Sebelum' }
    };

    function datatable(el, opts) {
        opts = opts || {};
        var ajaxOpts = opts.ajax || null;
        if (!ajaxOpts && opts.url) {
            ajaxOpts = {
                url: opts.url,
                data: opts.data || null
            };
            opts.ajax = ajaxOpts;
            delete opts.url;
            delete opts.data;
        } else {
            delete opts.url;
        }

        // Wrap column render callbacks so a single bad cell can never throw
        // mid-draw and leave DataTables' internal "processing" state stuck
        // (this previously caused the "loading terus" / infinite spinner bug).
        if (Array.isArray(opts.columns)) {
            opts.columns = opts.columns.map(function (col) {
                if (typeof col.render === 'function') {
                    var originalRender = col.render;
                    col.render = function (data, type, row, meta) {
                        try {
                            return originalRender(data, type, row, meta);
                        } catch (err) {
                            console.error('Inventaris.datatable: render error, showing raw value instead.', err);
                            return data === null || data === undefined ? '' : escapeHtml(data);
                        }
                    };
                }
                return col;
            });
        }

        var cfg = {
            processing: true,
            serverSide: true,
            pageLength: 25,
            language: language,
            ajax: ajaxOpts ? {
                url: ajaxOpts.url,
                data: ajaxOpts.data || undefined,
                error: function (xhr, status, tableSettings) {
                    var msg = 'Gagal memuat data.';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    toast(msg, 'danger');
                    // Force-clear a stuck processing state on ajax failure.
                    if (tableSettings) {
                        tableSettings.bDrawing = false;
                        tableSettings.oInstance.trigger('processing.dt', [tableSettings, false]);
                    }
                }
            } : undefined,
            drawCallback: function (settings) {
                // Safety net: whatever happens during draw, never leave the
                // processing overlay stuck once a draw cycle has finished.
                try {
                    settings.bDrawing = false;
                    var wrapper = settings.nTableWrapper;
                    if (wrapper) {
                        var processingEl = wrapper.querySelector('.dt-processing');
                        if (processingEl) processingEl.style.display = 'none';
                    }
                } catch (err) {
                    console.error('Inventaris.datatable: drawCallback safety net error.', err);
                }
            }
        };
        Object.keys(opts).forEach(function (k) { if (k !== 'ajax') cfg[k] = opts[k]; });
        var table = new DataTable(el, cfg);

        // Ultimate safety net: independently watch the processing indicator
        // and force it to hide if it's been stuck visible too long, no matter
        // what internal DataTables state caused it to get stuck.
        var wrapperEl = table.table().container();
        var stuckSince = null;
        setInterval(function () {
            var processingEl = wrapperEl ? wrapperEl.querySelector('.dt-processing') : null;
            if (!processingEl) { stuckSince = null; return; }
            var visible = getComputedStyle(processingEl).display !== 'none';
            if (!visible) { stuckSince = null; return; }
            if (stuckSince === null) {
                stuckSince = Date.now();
                return;
            }
            if (Date.now() - stuckSince > 4000) {
                processingEl.style.display = 'none';
                var s = table.settings()[0];
                if (s) s.bDrawing = false;
                stuckSince = null;
            }
        }, 1000);

        return table;
    }

    function bindFilter(containerId, table) {
        var container = document.getElementById(containerId);
        if (!container) return;
        container.addEventListener('submit', function (e) {
            e.preventDefault();
            table.ajax.reload();
        });
        container.addEventListener('reset', function () {
            setTimeout(function () { table.ajax.reload(); }, 0);
        });
    }

    function filterParams(containerId) {
        var container = document.getElementById(containerId);
        var params = {};
        if (!container) return params;
        container.querySelectorAll('[data-filter]').forEach(function (el) {
            if (el.type === 'checkbox') { params[el.name] = el.checked; }
            else if (el.tagName === 'SELECT' && el.multiple) {
                params[el.name] = Array.from(el.selectedOptions).map(function (o) { return o.value; });
            }
            else if (el.value !== '') { params[el.name] = el.value; }
        });
        return params;
    }

    window.Inventaris = {
        baseUrl: baseUrl,
        esc: escapeHtml,
        toast: toast,
        openModal: openModal,
        hideModal: hideModal,
        confirm: confirm,
        submitAjax: submitAjax,
        fetchJson: fetchJson,
        datatable: datatable,
        bindFilter: bindFilter,
        filterParams: filterParams,
        clearErrors: clearErrors,
        showErrors: showErrors
    };
})();
