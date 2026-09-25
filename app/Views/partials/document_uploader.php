<?php

/**
 * Modal + JavaScript untuk unggah dokumen.
 *
 * Dipakai oleh assets/show.php (dokumen aset) dan stock_movements/show.php
 * (dokumen catatan pergerakan). Formnya sama persis -- judul wajib,
 * deskripsi opsional, satu berkas per unggahan -- jadi diekstrak supaya
 * tidak ada dua salinan yang bisa berbeda.
 *
 * Variabel yang dibutuhkan:
 *
 *   $documentBase     'assets' atau 'stock-movements'
 *   $documentOwner    'Barang' atau 'Catatan pergerakan'
 *   $documentConfig   Config\AssetDocuments atau Config\TransactionDocuments
 *   $documentOwnerId  id induk (aset atau transaksi)
 *   $documentFormId   id unik untuk form, supaya dua halaman yang menyertakan
 *                      partial ini di satu DOM tidak bentrok
 */

$documentBase    = $documentBase    ?? 'assets';
$documentOwner   = $documentOwner   ?? 'Barang';
$documentFormId  = $documentFormId  ?? 'uploadDokumenForm';
$documentOwnerId = (int) ($documentOwnerId ?? 0);
?>

<!-- ===================================================== -->
<!-- MODAL UNGGAH DOKUMEN                                  -->
<!-- ===================================================== -->

<div class="modal fade"
     id="uploadDokumenModal"
     tabindex="-1"
     data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="<?= esc($documentFormId) ?>"
                  method="post"
                  action="<?= base_url($documentBase . '/' . $documentOwnerId . '/documents') ?>"
                  enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Unggah Dokumen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label" for="<?= esc($documentFormId) ?>Judul">
                            Judul <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="<?= esc($documentFormId) ?>Judul"
                               name="title"
                               maxlength="150"
                               required
                               placeholder="mis. Invoice pembelian 2026">
                        <small class="text-muted">
                            Judul membuat dokumen mudah dicari tanpa harus
                            membuka berkasnya.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="<?= esc($documentFormId) ?>Deskripsi">Deskripsi</label>
                        <textarea class="form-control"
                                  id="<?= esc($documentFormId) ?>Deskripsi"
                                  name="description"
                                  rows="2"
                                  maxlength="1000"
                                  placeholder="opsional: nomor seri, masa garansi, penerbit"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="<?= esc($documentFormId) ?>Input">
                            Berkas <span class="text-danger">*</span>
                        </label>
                        <input class="form-control"
                               type="file"
                               id="<?= esc($documentFormId) ?>Input"
                               name="document"
                               required
                               accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">
                            PDF, JPG, atau PNG. Maksimal
                            <?= esc(document_size_label((int) $documentConfig->maxSizeBytes)) ?>
                            per berkas,
                            <?= esc((string) $documentConfig->maxFiles) ?>
                            dokumen per
                            <?= esc(lcfirst($documentOwner)) ?>.
                        </small>
                    </div>

                    <div id="<?= esc($documentFormId) ?>Preview" class="mb-2"></div>
                    <div id="<?= esc($documentFormId) ?>Error" class="alert alert-danger d-none" role="alert"></div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Unggah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var FORM = <?= json_encode($documentFormId) ?>;
    var form = document.getElementById(FORM);

    if (!form) { return; }

    var input = document.getElementById(FORM + 'Input');
    var prev  = document.getElementById(FORM + 'Preview');
    var errBox = document.getElementById(FORM + 'Error');
    var submit = form.querySelector('[type="submit"]');
    var MAX_BYTES = <?= (int) $documentConfig->maxSizeBytes ?>;
    var DELETE_BASE = <?= json_encode($documentBase) ?>;

    function fail(message) {
        errBox.textContent = message;
        errBox.classList.remove('d-none');
        prev.innerHTML = '';
    }

    // Peringatan lebih dulu di browser supaya tidak perlu bolak-balik ke
    // server untuk penolakan yang sebenarnya sudah diketahui.
    input.addEventListener('change', function () {
        errBox.classList.add('d-none');
        prev.innerHTML = '';

        var file = this.files && this.files[0];
        if (!file) { return; }

        if (! /\.(pdf|jpe?g|png)$/i.test(file.name)) {
            this.value = '';
            fail('Tipe berkas tidak diizinkan. Gunakan PDF, JPG, atau PNG.');
            return;
        }

        if (file.size > MAX_BYTES) {
            this.value = '';
            fail('Ukuran berkas ' + Math.round(file.size / 1048576)
                 + ' MB melebihi batas ' + Math.round(MAX_BYTES / 1048576) + ' MB.');
            return;
        }

        prev.innerHTML = '<span class="badge text-bg-light text-dark">'
            + Inventaris.esc(file.name) + ' ('
            + Math.max(1, Math.round(file.size / 1024)) + ' KB)</span>';
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (!input.files || !input.files[0]) {
            fail('Pilih berkas terlebih dahulu.');
            return;
        }

        if (!form.elements.title.value.trim()) {
            fail('Judul dokumen wajib diisi.');
            return;
        }

        submit.disabled = true;
        errBox.classList.add('d-none');

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (r) {
                return r.json()
                    .catch(function () { return null; })
                    .then(function (j) {
                        if (!r.ok) { throw { json: j, status: r.status }; }
                        return j;
                    });
            })
            .then(function (json) {
                submit.disabled = false;

                if (json && json.success) {
                    Inventaris.toast(json.message);
                    Inventaris.hideModal('uploadDokumenModal');
                    form.reset();
                    prev.innerHTML = '';
                    // Daftar dokumen dirender server, jadi muat ulang halaman
                    // untuk menampilkan hasil unggahan.
                    window.location.reload();
                    return;
                }

                throw { json: json };
            })
            .catch(function (err) {
                submit.disabled = false;
                var msg = (err && err.json && err.json.message)
                    ? err.json.message
                    : 'Gagal mengunggah dokumen.';
                fail(msg);
                Inventaris.toast(msg, 'danger');
            });
    });

    var card = document.getElementById('dokumenCard');

    if (card) {
        card.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn-delete-dokumen');
            if (!btn) { return; }

            Inventaris.confirm({
                title: 'Hapus Dokumen',
                message: 'Hapus dokumen "' + btn.getAttribute('data-name') + '"?',
                okText: 'Hapus',
                onConfirm: function () {
                    Inventaris.fetchJson(
                        Inventaris.baseUrl + DELETE_BASE + '/documents/'
                            + btn.getAttribute('data-id') + '/delete',
                        {},
                        'POST'
                    )
                        .then(function (json) {
                            if (json.success) {
                                Inventaris.toast(json.message);
                                window.location.reload();
                            } else {
                                Inventaris.toast(json.message || 'Gagal menghapus.', 'danger');
                            }
                        })
                        .catch(function () {
                            Inventaris.toast('Koneksi gagal.', 'danger');
                        });
                }
            });
        });
    }
})();
</script>
