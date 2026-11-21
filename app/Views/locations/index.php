<?= view('layout/header', ['title' => 'Lokasi']) ?>
<?= view('layout/navbar') ?>

<div class="mb-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Lokasi</h3>
            <p class="text-muted mb-0">Kelola lokasi penyimpanan barang inventaris.</p>
        </div>

        <button type="button" class="btn btn-primary" id="btnCreateLocation">
            + Tambah Lokasi
        </button>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabel-locations" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama</th>
                            <th>Gedung</th>
                            <th>Lantai</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

</div>

<?php
/**
 * Checkbox unit, dipakai ulang di modal create dan edit.
 */
$unitChecks = function (string $prefix) use ($units) {
    if (empty($units)) {
        echo '<div class="text-muted">Belum ada unit.</div>';
        return;
    }
    foreach ($units as $unit):
        $id = (int) $unit['id'];
        ?>
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   name="unit_ids[]" value="<?= $id ?>"
                   id="<?= $prefix ?>Unit<?= $id ?>">
            <label class="form-check-label" for="<?= $prefix ?>Unit<?= $id ?>">
                <?= esc($unit['name']) ?>
            </label>
        </div>
    <?php endforeach;
};
?>

<div class="modal fade" id="createLocationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="createLocationForm" method="post" action="<?= base_url('locations/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Lokasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lokasi</label>
                            <input type="text" name="name" class="form-control"
                                   placeholder="Contoh: Ruang IT" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gedung</label>
                            <input type="text" name="building" class="form-control"
                                   placeholder="Contoh: Gedung Utama">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lantai</label>
                            <input type="text" name="floor" class="form-control"
                                   placeholder="Contoh: Lantai 2">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ruangan</label>
                            <input type="text" name="room" class="form-control"
                                   placeholder="Contoh: Ruang 204">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1"
                                       class="form-check-input" id="createLocationActive" checked>
                                <label class="form-check-label" for="createLocationActive">Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editLocationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="editLocationForm" method="post" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Lokasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lokasi</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gedung</label>
                            <input type="text" name="building" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lantai</label>
                            <input type="text" name="floor" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ruangan</label>
                            <input type="text" name="room" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Keterangan</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check mb-3">
                                <input type="checkbox" name="is_active" value="1"
                                       class="form-check-input" id="editLocationActive">
                                <label class="form-check-label" for="editLocationActive">Aktif</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Unit yang Menangani</label>
                            <div class="border rounded p-3">
                                <?php $unitChecks('editLocation'); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="detailLocationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Lokasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header">
                                <strong>Foto Lokasi</strong>
                            </div>
                            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                <img id="detailLocationPhoto"
                                     alt="Foto lokasi"
                                     class="img-fluid rounded mb-3 d-none"
                                     style="max-height: 220px; width: 100%; object-fit: cover;">

                                <div id="detailLocationPhotoEmpty" class="text-muted small mb-3 text-center">
                                    Belum ada foto lokasi.
                                </div>

                                <div class="d-flex flex-wrap gap-2 justify-content-center">
                                    <button type="button" class="btn btn-sm btn-primary" id="detailLocationPhotoUpload">
                                        Unggah Foto
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="detailLocationPhotoCamera">
                                        <i class="bi bi-camera"></i> Ambil dari Kamera
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger d-none" id="detailLocationPhotoDelete">
                                        Hapus Foto
                                    </button>
                                </div>

                                <div id="detailLocationCameraWrap" class="mt-2 d-none w-100">
                                    <video id="detailLocationCameraVideo"
                                           autoplay
                                           playsinline
                                           muted
                                           class="w-100 rounded border bg-dark"
                                           style="max-height: 220px; object-fit: cover;"></video>
                                    <div class="d-flex gap-2 mt-2 justify-content-center">
                                        <button type="button" class="btn btn-sm btn-primary" id="detailLocationCameraCapture">
                                            <i class="bi bi-camera-fill"></i> Ambil Foto
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary" id="detailLocationCameraClose">
                                            Tutup
                                        </button>
                                    </div>
                                    <canvas id="detailLocationCameraCanvas" class="d-none"></canvas>
                                </div>

                                <div class="form-text text-center mt-2">
                                    JPG/PNG. Foto dikecilkan otomatis sebelum diunggah.
                                </div>

                                <form id="detailLocationPhotoForm" method="post" action="" enctype="multipart/form-data"
                                      style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">
                                    <?= csrf_field() ?>
                                    <input type="file" name="image" id="detailLocationPhotoInput" accept="image/jpeg,image/png">
                                    <input type="file" id="detailLocationPhotoCameraInput" accept="image/*" capture="environment" class="d-none">
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label text-muted small">Nama Lokasi</label>
                                <div class="fw-semibold" id="detailLocationName">-</div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-muted small">Gedung</label>
                                <div id="detailLocationBuilding">-</div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-muted small">Lantai</label>
                                <div id="detailLocationFloor">-</div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-muted small">Ruangan</label>
                                <div id="detailLocationRoom">-</div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label text-muted small">Status</label>
                                <div id="detailLocationStatus">-</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted small">Deskripsi</label>
                                <div id="detailLocationDescription">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="text-muted small">Aset di Lokasi</div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <div class="fs-3 fw-semibold" id="detailLocationAssetCount">0</div>
                                    <div class="text-muted">aset</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="text-muted small">Barang Stok di Lokasi</div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <div class="fs-3 fw-semibold" id="detailLocationStockCount">0</div>
                                    <div class="text-muted">jenis</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between">
                        <strong>Unit yang Menangani</strong>
                        <span class="badge bg-primary" id="detailLocationUnitCount">0 unit</span>
                    </div>
                    <div class="card-body">
                        <div id="detailLocationUnits"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-warning" id="detailLocationEdit">Edit</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    var baseUrl = Inventaris.baseUrl;

    var photoOpts = {
        maxDimension: <?= (int) $imageConfig->maxDimension ?>,
        quality: <?= (float) $imageConfig->jpegQuality ?>,
        maxBytes: <?= (int) $imageConfig->maxSizeBytes ?>,
        maxOriginalBytes: <?= (int) $imageConfig->maxOriginalBytes ?>
    };

    function photoThumb(row) {
        if (!row.image_id) {
            return '<span class="text-muted small">-</span>';
        }

        return '<img src="' + Inventaris.esc(baseUrl + 'locations/images/' + row.image_id) + '"'
            + ' class="img-thumbnail rounded" width="48"'
            + ' alt="" loading="lazy">';
    }

    var createModal = null;
    var editModal   = null;
    var createForm  = document.getElementById('createLocationForm');
    var editForm    = document.getElementById('editLocationForm');

    var dt = Inventaris.datatable('#tabel-locations', {
        url: baseUrl + 'locations?format=json',
        // Kolom 0 adalah thumbnail dan tidak bisa diurutkan; urutkan
        // berdasarkan nama seperti default server.
        order: [[1, 'asc']],
        columns: [
            {
                data: 'image_id',
                orderable: false,
                searchable: false,
                render: function (d, type, row) { return photoThumb(row); }
            },
            { data: 'name' },
            {
                data: 'building',
                render: function (d) { return Inventaris.esc(d) || '-'; }
            },
            {
                data: 'floor',
                render: function (d) { return Inventaris.esc(d) || '-'; }
            },
            {
                data: 'description',
                render: function (d) { return Inventaris.esc(d) || '-'; }
            },
            {
                data: 'is_active',
                render: function (d) {
                    return Number(d) === 1
                        ? '<span class="badge bg-success">Aktif</span>'
                        : '<span class="badge bg-secondary">Tidak Aktif</span>';
                }
            },
            { data: 'created_at', render: function (d) { return Inventaris.esc(d || '-'); } },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (d, type, row) {
                    return '<button type="button" class="btn btn-outline-primary btn-sm btn-detail" data-id="' + row.id + '">Detail</button> ' +
                        '<button type="button" class="btn btn-warning btn-sm btn-edit" data-id="' + row.id + '">Edit</button>';
                }
            }
        ]
    });

    function fillEdit(row) {
        editForm.action = baseUrl + 'locations/update/' + row.id;
        ['name', 'building', 'floor', 'room', 'description'].forEach(function (name) {
            editForm.elements[name].value = row[name] == null ? '' : row[name];
        });
        document.getElementById('editLocationActive').checked = !!Number(row.is_active);

        var assigned = (row.unit_ids || []).map(Number);
        editForm.querySelectorAll('[name="unit_ids[]"]').forEach(function (cb) {
            cb.checked = assigned.indexOf(Number(cb.value)) !== -1;
        });

        Inventaris.clearErrors(editForm);
    }

    function showCreate() {
        createForm.reset();
        document.getElementById('createLocationActive').checked = true;
        Inventaris.clearErrors(createForm);
        if (!createModal) createModal = Inventaris.openModal('createLocationModal');
        else createModal.show();
    }

    // ── Modal detail ────────────────────────────────────────
    // Tidak ada ?detail=<id>: ID tidak pernah masuk URL, jadi tidak
    // muncul di address bar maupun riwayat browser.
    var detailModal = null;
    var detailId = null;
    var detailImageId = null;

    var photoForm       = document.getElementById('detailLocationPhotoForm');
    var photoInput      = document.getElementById('detailLocationPhotoInput');
    var photoImage      = document.getElementById('detailLocationPhoto');
    var photoEmpty      = document.getElementById('detailLocationPhotoEmpty');
    var photoUploadBtn  = document.getElementById('detailLocationPhotoUpload');
    var photoDeleteBtn  = document.getElementById('detailLocationPhotoDelete');
    var photoCameraBtn  = document.getElementById('detailLocationPhotoCamera');
    var photoCameraWrap = document.getElementById('detailLocationCameraWrap');
    var photoCameraVideo= document.getElementById('detailLocationCameraVideo');
    var photoCameraCanvas= document.getElementById('detailLocationCameraCanvas');
    var photoCameraCapture= document.getElementById('detailLocationCameraCapture');
    var photoCameraClose = document.getElementById('detailLocationCameraClose');
    var photoCameraFallback = document.getElementById('detailLocationPhotoCameraInput');

    var photoCameraStream = null;
    var pendingPhotoFile = null;

    function updateDetailPhoto(imageId) {
        detailImageId = imageId ? Number(imageId) : null;

        if (detailImageId) {
            photoImage.src = baseUrl + 'locations/images/' + detailImageId;
            photoImage.classList.remove('d-none');
            photoEmpty.classList.add('d-none');
            photoDeleteBtn.classList.remove('d-none');
            photoUploadBtn.textContent = 'Ganti Foto';
        } else {
            photoImage.removeAttribute('src');
            photoImage.classList.add('d-none');
            photoEmpty.classList.remove('d-none');
            photoDeleteBtn.classList.add('d-none');
            photoUploadBtn.textContent = 'Unggah Foto';
        }
    }

    function fillDetail(row) {
        detailId = row.id;
        document.getElementById('detailLocationName').textContent = row.name || '-';
        document.getElementById('detailLocationBuilding').textContent = row.building || '-';
        document.getElementById('detailLocationFloor').textContent = row.floor || '-';
        document.getElementById('detailLocationRoom').textContent = row.room || '-';
        document.getElementById('detailLocationDescription').textContent = row.description || '-';
        document.getElementById('detailLocationStatus').innerHTML = Number(row.is_active) === 1
            ? '<span class="badge bg-success">Aktif</span>'
            : '<span class="badge bg-secondary">Tidak Aktif</span>';

        var units = row.units || [];
        document.getElementById('detailLocationUnitCount').textContent = units.length + ' unit';
        document.getElementById('detailLocationUnits').innerHTML = units.length === 0
            ? '<div class="text-muted">Belum ada unit yang menangani lokasi ini.</div>'
            : '<div class="table-responsive"><table class="table table-bordered table-hover mb-0">' +
              '<thead><tr><th>Kode</th><th>Unit / Departemen</th></tr></thead><tbody>' +
              units.map(function (u) {
                  return '<tr><td>' + Inventaris.esc(u.code || '-') + '</td>' +
                      '<td>' + Inventaris.esc(u.name) + '</td></tr>';
              }).join('') + '</tbody></table></div>';

        // Ringkasan saja: jumlah aset dan jumlah jenis barang stok.
        document.getElementById('detailLocationAssetCount').textContent = Number(row.asset_count || 0);
        document.getElementById('detailLocationStockCount').textContent = Number(row.stock_item_count || 0);

        updateDetailPhoto(row.image_id ? Number(row.image_id) : null);
    }

    function openDetailById(id) {
        Inventaris.fetchJson(baseUrl + 'locations/data/' + encodeURIComponent(id))
            .then(function (row) {
                if (!row || !row.id) {
                    Inventaris.toast((row && row.message) || 'Lokasi tidak ditemukan.', 'danger');
                    return;
                }
                stopLocationCamera();
                fillDetail(row);
                if (!detailModal) detailModal = Inventaris.openModal('detailLocationModal');
                else detailModal.show();
            })
            .catch(function () {
                Inventaris.toast('Gagal memuat data lokasi.', 'danger');
            });
    }

    document.getElementById('detailLocationEdit').addEventListener('click', function () {
        if (!detailId) return;
        Inventaris.hideModal('detailLocationModal');
        openEditById(detailId);
    });

    function stopLocationCamera() {
        if (photoCameraStream) {
            photoCameraStream.getTracks().forEach(function (t) { t.stop(); });
            photoCameraStream = null;
        }
        if (photoCameraVideo) {
            photoCameraVideo.srcObject = null;
        }
        if (photoCameraWrap) {
            photoCameraWrap.classList.add('d-none');
        }
    }

    function openLocationCamera() {
        if (!detailId) return;

        if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
            if (photoCameraFallback) { photoCameraFallback.click(); }
            else { Inventaris.toast('Kamera tidak didukung di perangkat ini.', 'danger'); }
            return;
        }

        navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' } },
            audio: false
        }).then(function (stream) {
            photoCameraStream = stream;
            if (photoCameraVideo) {
                photoCameraVideo.srcObject = stream;
            }
            if (photoCameraWrap) {
                photoCameraWrap.classList.remove('d-none');
            }
        }).catch(function (err) {
            if (photoCameraFallback) {
                photoCameraFallback.click();
            } else {
                Inventaris.toast('Tidak dapat mengakses kamera: ' + (err && err.message ? err.message : 'izin ditolak.'), 'danger');
            }
        });
    }

    function captureLocationPhoto() {
        if (!photoCameraVideo || !photoCameraCanvas || !detailId) { return; }

        var w = photoCameraVideo.videoWidth || 640;
        var h = photoCameraVideo.videoHeight || 480;

        photoCameraCanvas.width = w;
        photoCameraCanvas.height = h;

        var ctx = photoCameraCanvas.getContext('2d');
        ctx.drawImage(photoCameraVideo, 0, 0, w, h);

        if (typeof photoCameraCanvas.toBlob !== 'function') {
            Inventaris.toast('Browser tidak mendukung pengambilan foto.', 'danger');
            return;
        }

        photoCameraCanvas.toBlob(function (blob) {
            if (!blob) {
                Inventaris.toast('Gagal mengambil foto dari kamera.', 'danger');
                return;
            }

            var file = new File([blob], 'kamera-' + Date.now() + '.jpg', {
                type: 'image/jpeg',
                lastModified: Date.now()
            });

            pendingPhotoFile = file;
            try {
                var dt = new DataTransfer();
                dt.items.add(file);
                photoInput.files = dt.files;
            } catch (e) {
                // pendingPhotoFile tetap dipakai.
            }

            stopLocationCamera();
            photoInput.dispatchEvent(new Event('change', { bubbles: true }));
        }, 'image/jpeg', 0.9);
    }

    if (photoCameraBtn) {
        photoCameraBtn.addEventListener('click', openLocationCamera);
    }

    if (photoCameraCapture) {
        photoCameraCapture.addEventListener('click', captureLocationPhoto);
    }

    if (photoCameraClose) {
        photoCameraClose.addEventListener('click', stopLocationCamera);
    }

    if (photoCameraFallback) {
        photoCameraFallback.addEventListener('change', function () {
            var file = this.files && this.files[0];
            if (!file || !detailId) { return; }
            pendingPhotoFile = file;
            try {
                var dt = new DataTransfer();
                dt.items.add(file);
                photoInput.files = dt.files;
            } catch (e) {
                // pendingPhotoFile tetap dipakai.
            }
            this.value = '';
            photoInput.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    // Hentikan kamera saat modal detail ditutup.
    var detailModalEl = document.getElementById('detailLocationModal');
    if (detailModalEl) {
        detailModalEl.addEventListener('hidden.bs.modal', stopLocationCamera);
    }

    photoUploadBtn.addEventListener('click', function () {
        if (!detailId) return;
        photoInput.click();
    });

    photoInput.addEventListener('change', function () {
        if (this.files && this.files.length) {
            pendingPhotoFile = null;
        }
        var file = (this.files && this.files[0]) || pendingPhotoFile;

        if (!file || !detailId) {
            return;
        }

        // Simpan id saat ini: kalau modal sudah pindah lokasi sebelum
        // unggahan selesai, respons tidak boleh menimpa foto lokasi lain.
        var locationId = detailId;

        if (file.size > photoOpts.maxOriginalBytes) {
            // Kosongkan input supaya berkas raksasa tidak ikut terkirim.
            this.value = '';
            pendingPhotoFile = null;
            Inventaris.toast(
                'Ukuran berkas asli melebihi batas '
                + Math.round(photoOpts.maxOriginalBytes / 1048576) + ' MB.',
                'danger'
            );
            return;
        }

        var label = photoUploadBtn.textContent;
        photoUploadBtn.disabled = true;
        photoUploadBtn.textContent = 'Mengunggah...';

        Inventaris.resizeImage(file, photoOpts)
            .then(function (result) {
                var data = new FormData();
                var token = photoForm.querySelector('[name="csrf_test_name"]');

                if (token) {
                    data.append('csrf_test_name', token.value);
                }

                data.append('image', result.file, result.file.name);

                return fetch(baseUrl + 'locations/' + encodeURIComponent(locationId) + '/images', {
                    method: 'POST',
                    body: data,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                }).then(function (response) {
                    return response.json().catch(function () { return null; })
                        .then(function (json) {
                            if (!response.ok) { throw { json: json }; }
                            return json;
                        });
                });
            })
            .then(function (json) {
                photoUploadBtn.disabled = false;

                if (detailId === locationId) {
                    photoUploadBtn.textContent = label;
                    photoInput.value = '';
                    pendingPhotoFile = null;
                }

                if (!json || !json.success) {
                    throw { json: json };
                }

                if (detailId === locationId) {
                    updateDetailPhoto(json.data && json.data.id ? json.data.id : null);
                }

                Inventaris.toast(json.message);
                dt.ajax.reload(null, false);
            })
            .catch(function (err) {
                photoUploadBtn.disabled = false;

                if (detailId === locationId) {
                    photoUploadBtn.textContent = label;
                    photoInput.value = '';
                    pendingPhotoFile = null;
                }

                var message = (err && err.json && err.json.message)
                    ? err.json.message
                    : (err && err.message ? err.message : 'Gagal mengunggah foto.');
                Inventaris.toast(message, 'danger');
            });
    });

    photoDeleteBtn.addEventListener('click', function () {
        if (!detailImageId) return;

        Inventaris.confirm({
            title: 'Hapus Foto Lokasi',
            message: 'Hapus foto lokasi ini?',
            okText: 'Hapus',
            onConfirm: function () {
                Inventaris.fetchJson(
                    baseUrl + 'locations/images/' + detailImageId + '/delete',
                    {},
                    'POST'
                )
                    .then(function (json) {
                        if (json.success) {
                            updateDetailPhoto(null);
                            Inventaris.toast(json.message);
                            dt.ajax.reload(null, false);
                        } else {
                            Inventaris.toast(json.message || 'Gagal menghapus foto.', 'danger');
                        }
                    })
                    .catch(function () {
                        Inventaris.toast('Koneksi gagal.', 'danger');
                    });
            }
        });
    });

    // Selalu ambil dari locations/data/:id: baris tabel tidak memuat
    // unit_ids, jadi mengisi checkbox dari baris akan menghapus semua
    // relasi unit saat disimpan.
    function openEditById(id) {
        Inventaris.fetchJson(baseUrl + 'locations/data/' + encodeURIComponent(id))
            .then(function (row) {
                if (!row || !row.id) {
                    Inventaris.toast('Lokasi tidak ditemukan.', 'danger');
                    return;
                }
                fillEdit(row);
                if (!editModal) editModal = Inventaris.openModal('editLocationModal');
                else editModal.show();
            })
            .catch(function () {
                Inventaris.toast('Gagal memuat data lokasi.', 'danger');
            });
    }

    document.getElementById('btnCreateLocation').addEventListener('click', showCreate);

    createForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function () {
                Inventaris.hideModal('createLocationModal');
                this.reset();
                dt.ajax.reload();
            }.bind(this)
        });
    });

    editForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function () {
                Inventaris.hideModal('editLocationModal');
                dt.ajax.reload();
            }
        });
    });

    document.getElementById('tabel-locations').addEventListener('click', function (e) {
        var detailBtn = e.target.closest('.btn-detail');
        if (detailBtn) {
            openDetailById(detailBtn.getAttribute('data-id'));
            return;
        }

        var editBtn = e.target.closest('.btn-edit');
        if (editBtn) openEditById(editBtn.getAttribute('data-id'));
    });

    // Deep link: /locations?create=1 atau /locations?edit=<id>
    var params = new URLSearchParams(window.location.search);
    var wantCreate = params.get('create') === '1';
    var wantEdit = params.get('edit');

    if (wantCreate || wantEdit) {
        window.history.replaceState({}, document.title, baseUrl + 'locations');
    }

    if (wantCreate) {
        showCreate();
    } else if (wantEdit) {
        openEditById(wantEdit);
    }
})();
</script>

<?= view('layout/footer') ?>
