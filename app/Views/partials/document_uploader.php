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
    <div class="modal-dialog modal-dialog-scrollable">
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

                    <div class="mb-3">
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm"
                                id="<?= esc($documentFormId) ?>KameraBtn">
                            <i class="bi bi-camera"></i> Ambil dari Kamera
                        </button>
                        <small class="text-muted ms-2">
                            Ambil foto langsung dari kamera (hasilnya JPEG).
                        </small>
                    </div>

                    <div id="<?= esc($documentFormId) ?>KameraWrap" class="mb-3 d-none">
                        <video id="<?= esc($documentFormId) ?>KameraVideo"
                               autoplay
                               playsinline
                               muted
                               class="w-100 rounded border bg-dark"
                               style="max-height: 220px; object-fit: cover;"></video>
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-primary btn-sm" id="<?= esc($documentFormId) ?>KameraCapture">
                                <i class="bi bi-camera-fill"></i> Ambil Foto
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" id="<?= esc($documentFormId) ?>KameraClose">
                                Tutup
                            </button>
                        </div>
                        <canvas id="<?= esc($documentFormId) ?>KameraCanvas" class="d-none"></canvas>
                    </div>

                    <!-- Fallback untuk perangkat/browser yang tidak mendukung getUserMedia -->
                    <input type="file"
                           id="<?= esc($documentFormId) ?>KameraInput"
                           accept="image/*"
                           capture="environment"
                           class="d-none">

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

    var cameraBtn     = document.getElementById(FORM + 'KameraBtn');
    var cameraWrap    = document.getElementById(FORM + 'KameraWrap');
    var cameraVideo   = document.getElementById(FORM + 'KameraVideo');
    var cameraCanvas  = document.getElementById(FORM + 'KameraCanvas');
    var cameraCapture = document.getElementById(FORM + 'KameraCapture');
    var cameraClose   = document.getElementById(FORM + 'KameraClose');
    var cameraFallback = document.getElementById(FORM + 'KameraInput');

    var cameraStream = null;
    var pendingDocFile = null;

    function fail(message) {
        errBox.textContent = message;
        errBox.classList.remove('d-none');
        prev.innerHTML = '';
    }

    function stopDocCamera() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(function (t) { t.stop(); });
            cameraStream = null;
        }
        if (cameraVideo) {
            cameraVideo.srcObject = null;
        }
        if (cameraWrap) {
            cameraWrap.classList.add('d-none');
        }
    }

    function openDocCamera() {
        if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
            if (cameraFallback) { cameraFallback.click(); }
            else { fail('Kamera tidak didukung di perangkat ini.'); }
            return;
        }

        navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' } },
            audio: false
        }).then(function (stream) {
            cameraStream = stream;
            if (cameraVideo) {
                cameraVideo.srcObject = stream;
            }
            if (cameraWrap) {
                cameraWrap.classList.remove('d-none');
            }
        }).catch(function (err) {
            if (cameraFallback) {
                cameraFallback.click();
            } else {
                fail('Tidak dapat mengakses kamera: ' + (err && err.message ? err.message : 'izin ditolak.'));
            }
        });
    }

    function captureDocPhoto() {
        if (!cameraVideo || !cameraCanvas) { return; }

        var w = cameraVideo.videoWidth || 640;
        var h = cameraVideo.videoHeight || 480;

        cameraCanvas.width = w;
        cameraCanvas.height = h;

        var ctx = cameraCanvas.getContext('2d');
        ctx.drawImage(cameraVideo, 0, 0, w, h);

        if (typeof cameraCanvas.toBlob !== 'function') {
            fail('Browser tidak mendukung pengambilan foto.');
            return;
        }

        cameraCanvas.toBlob(function (blob) {
            if (!blob) {
                fail('Gagal mengambil foto dari kamera.');
                return;
            }

            var file = new File([blob], 'kamera-' + Date.now() + '.jpg', {
                type: 'image/jpeg',
                lastModified: Date.now()
            });

            pendingDocFile = file;
            try {
                var dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
            } catch (e) {
                // pendingDocFile tetap dipakai saat submit.
            }

            stopDocCamera();
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }, 'image/jpeg', 0.9);
    }

    if (cameraBtn) {
        cameraBtn.addEventListener('click', openDocCamera);
    }

    if (cameraCapture) {
        cameraCapture.addEventListener('click', captureDocPhoto);
    }

    if (cameraClose) {
        cameraClose.addEventListener('click', stopDocCamera);
    }

    if (cameraFallback) {
        cameraFallback.addEventListener('change', function () {
            var file = this.files && this.files[0];
            if (!file) { return; }
            pendingDocFile = file;
            try {
                var dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
            } catch (e) {
                // pendingDocFile tetap dipakai.
            }
            this.value = '';
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    var docModalEl = document.getElementById('uploadDokumenModal');
    if (docModalEl) {
        docModalEl.addEventListener('hidden.bs.modal', stopDocCamera);
    }

    // Peringatan lebih dulu di browser supaya tidak perlu bolak-balik ke
    // server untuk penolakan yang sebenarnya sudah diketahui.
    input.addEventListener('change', function () {
        errBox.classList.add('d-none');
        prev.innerHTML = '';

        if (this.files && this.files.length) {
            pendingDocFile = null;
        }
        var file = (this.files && this.files[0]) || pendingDocFile;
        if (!file) { return; }

        if (! /\.(pdf|jpe?g|png)$/i.test(file.name)) {
            this.value = '';
            pendingDocFile = null;
            fail('Tipe berkas tidak diizinkan. Gunakan PDF, JPG, atau PNG.');
            return;
        }

        if (file.size > MAX_BYTES) {
            this.value = '';
            pendingDocFile = null;
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

        var file = (input.files && input.files[0]) || pendingDocFile;

        if (!file) {
            fail('Pilih berkas terlebih dahulu.');
            return;
        }

        if (!form.elements.title.value.trim()) {
            fail('Judul dokumen wajib diisi.');
            return;
        }

        submit.disabled = true;
        errBox.classList.add('d-none');

        var data = new FormData(form);
        if ((!input.files || !input.files[0]) && pendingDocFile) {
            data.set('document', pendingDocFile, pendingDocFile.name);
        }

        fetch(form.action, {
            method: 'POST',
            body: data,
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
                    pendingDocFile = null;
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
