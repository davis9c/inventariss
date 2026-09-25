<?php

/**
 * Modal + JavaScript untuk unggah gambar barang.
 *
 * Dipakai oleh assets/show.php dan stock_items/show.php. Variabel yang
 * dibutuhkan:
 *
 *   $imageBase    'assets' atau 'stock-items'
 *   $imageOwner   'Barang' atau 'Barang stok'
 *   $imageConfig  Config\ItemImages
 *   $imageMaxOriginalBytes  batas berkas asli sebelum dikecilkan
 *
 * Berkas dikecilkan di browser (Inventaris.resizeImage) supaya tidak perlu
 * GD di server -- lihat komentar fungsi itu.
 */

$imageOwner  = $imageOwner ?? 'Barang';
$imageFormId = $imageFormId ?? 'uploadGambarForm';
$imageMaxOriginal = (int) ($imageMaxOriginalBytes ?? 15728640);
$imageMaxOut      = (int) ($imageConfig->maxSizeBytes ?? 2097152);
$imageMaxDim      = (int) ($imageConfig->maxDimension ?? 1280);
$imageQuality     = (float) ($imageConfig->jpegQuality ?? 0.82);
?>

<!-- ===================================================== -->
<!-- MODAL UNGGAH GAMBAR                                  -->
<!-- ===================================================== -->

<div class="modal fade"
     id="uploadGambarModal"
     tabindex="-1"
     data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="<?= esc($imageFormId) ?>"
                  method="post"
                  action="<?= base_url($imageBase . '/' . (int) $assetId . '/images') ?>"
                  enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Gambar <?= esc($imageOwner) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label" for="gambarJudul">Judul <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control"
                               id="gambarJudul"
                               name="title"
                               maxlength="150"
                               required
                               placeholder="mis. Tampak depan laptop">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="gambarDeskripsi">Deskripsi</label>
                        <textarea class="form-control"
                                  id="gambarDeskripsi"
                                  name="description"
                                  rows="2"
                                  maxlength="1000"
                                  placeholder="opsional: kondisi, kelainan, atau sudut pengambilan"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="gambarInput">Gambar <span class="text-danger">*</span></label>
                        <input class="form-control"
                               type="file"
                               id="gambarInput"
                               name="image"
                               required
                               accept="image/jpeg,image/png">
                        <small class="text-muted">
                            JPG atau PNG. Gambar dikecilkan otomatis sebelum diunggah,
                            maksimal <?= esc(document_size_label($imageMaxOut)) ?>.
                        </small>
                    </div>

                    <div id="gambarInfo" class="small text-muted mb-2"></div>
                    <div id="gambarError" class="alert alert-danger d-none" role="alert"></div>

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

    var form    = document.getElementById(<?= json_encode($imageFormId) ?>);
    if (!form) { return; }

    var input   = document.getElementById('gambarInput');
    var info    = document.getElementById('gambarInfo');
    var errBox  = document.getElementById('gambarError');
    var submit  = form.querySelector('[type="submit"]');

    var OPTS = {
        maxDimension: <?= (int) $imageMaxDim ?>,
        quality: <?= $imageQuality ?>,
        maxBytes: <?= (int) $imageMaxOut ?>,
        maxOriginalBytes: <?= (int) $imageMaxOriginal ?>
    };

    function fail(message) {
        errBox.textContent = message;
        errBox.classList.remove('d-none');
        info.textContent = '';
    }

    input.addEventListener('change', function () {
        errBox.classList.add('d-none');
        info.textContent = '';

        var file = this.files && this.files[0];
        if (!file) { return; }

        if (file.size > OPTS.maxOriginalBytes) {
            // Segera dihapus supaya berkas raksasa tidak ikut terkirim kalau
            // pengguna menekan tombol Unggah tanpa melihat pesan ini.
            this.value = '';
            fail('Ukuran berkas asli ' + Math.round(file.size / 1048576)
                 + ' MB melebihi batas ' + Math.round(OPTS.maxOriginalBytes / 1048576) + ' MB.');
            return;
        }

        info.textContent = 'Memproses gambar...';
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var file = input.files && input.files[0];

        if (!file) {
            fail('Pilih gambar terlebih dahulu.');
            return;
        }

        var title = form.elements.title.value.trim();
        if (!title) {
            fail('Judul gambar wajib diisi.');
            return;
        }

        submit.disabled = true;
        errBox.classList.add('d-none');
        info.textContent = 'Mengunggah...';

        Inventaris.resizeImage(file, OPTS)
            .then(function (result) {
                // Nama field tetap 'image'; berkas hasil resize yang
                // dikirim, bukan aslinya.
                var data = new FormData();
                data.append('csrf_test_name', form.elements.csrf_test_name.value);
                data.append('title', title);
                data.append('description', form.elements.description.value.trim());
                data.append('image', result.file, result.file.name);

                info.textContent = 'Mengunggah ' + result.width + '×' + result.height
                    + ', ' + Math.max(1, Math.round(result.file.size / 1024)) + ' KB...';

                return fetch(form.action, {
                    method: 'POST',
                    body: data,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                }).then(function (r) {
                    return r.json().catch(function () { return null; })
                        .then(function (json) {
                            if (!r.ok) { throw { json: json }; }
                            return json;
                        });
                });
            })
            .then(function (json) {
                submit.disabled = false;

                if (json && json.success) {
                    Inventaris.toast(json.message);
                    // Galeri dirender server, jadi halaman dimuat ulang agar
                    // gambar barunya langsung tampil.
                    window.location.reload();
                    return;
                }

                throw { json: json };
            })
            .catch(function (err) {
                submit.disabled = false;
                info.textContent = '';
                var msg = (err && err.json && err.json.message)
                    ? err.json.message
                    : (err && err.message ? err.message : 'Gagal mengunggah gambar.');
                fail(msg);
                Inventaris.toast(msg, 'danger');
            });
    });

    // Hapus gambar: satu klik, tanpa reload, dengan konfirmasi.
    var card = document.getElementById('gambarCard');

    if (card) {
        card.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn-hapus-gambar');
            if (!btn) { return; }

            Inventaris.confirm({
                title: 'Hapus Gambar',
                message: 'Hapus gambar "' + btn.getAttribute('data-title') + '"?',
                okText: 'Hapus',
                onConfirm: function () {
                    Inventaris.fetchJson(
                        Inventaris.baseUrl + <?= json_encode($imageBase) ?>
                            + '/images/' + btn.getAttribute('data-id') + '/delete',
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
