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
                            <th>Nama</th>
                            <th>Gedung</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
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

                <div class="card shadow-sm mb-3">
                    <div class="card-header d-flex justify-content-between">
                        <strong>Unit yang Menangani</strong>
                        <span class="badge bg-primary" id="detailLocationUnitCount">0 unit</span>
                    </div>
                    <div class="card-body">
                        <div id="detailLocationUnits"></div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between">
                        <strong>Aset di Lokasi</strong>
                        <span class="badge bg-primary" id="detailLocationAssetCount">0 aset</span>
                    </div>
                    <div class="card-body">
                        <div id="detailLocationAssets"></div>
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

    var createModal = null;
    var editModal   = null;
    var createForm  = document.getElementById('createLocationForm');
    var editForm    = document.getElementById('editLocationForm');

    var dt = Inventaris.datatable('#tabel-locations', {
        url: baseUrl + 'locations?format=json',
        columns: [
            { data: 'name' },
            {
                data: 'building',
                render: function (d) { return Inventaris.esc(d) || '-'; }
            },
            {
                data: 'description',
                render: function (d) { return Inventaris.esc(d) || '-'; }
            },
            {
                data: 'is_active',
                render: function (d) {
                    return d
                        ? '<span class="badge bg-success">Aktif</span>'
                        : '<span class="badge bg-secondary">Tidak Aktif</span>';
                }
            },
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

        var assets = row.assets || [];
        document.getElementById('detailLocationAssetCount').textContent = assets.length + ' aset';
        document.getElementById('detailLocationAssets').innerHTML = assets.length === 0
            ? '<div class="text-muted">Belum ada aset di lokasi ini.</div>'
            : '<div class="table-responsive"><table class="table table-bordered table-hover mb-0">' +
              '<thead><tr><th>Kode Aset</th><th>Nama Barang</th><th>Unit</th><th>Status</th></tr></thead><tbody>' +
              assets.map(function (a) {
                  return '<tr><td>' + Inventaris.esc(a.asset_code) + '</td>' +
                      '<td>' + Inventaris.esc(a.name) + '</td>' +
                      '<td>' + Inventaris.esc(a.unit_name || '-') + '</td>' +
                      '<td>' + Inventaris.esc(a.asset_status || '-') + '</td></tr>';
              }).join('') + '</tbody></table></div>';
    }

    function openDetailById(id) {
        Inventaris.fetchJson(baseUrl + 'locations/data/' + encodeURIComponent(id))
            .then(function (row) {
                if (!row || !row.id) {
                    Inventaris.toast((row && row.message) || 'Lokasi tidak ditemukan.', 'danger');
                    return;
                }
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
