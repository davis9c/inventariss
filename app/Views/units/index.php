<?= view('layout/header', ['title' => 'Unit / Departemen']) ?>
<?= view('layout/navbar') ?>

<div class="mb-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Unit / Departemen</h3>
            <p class="text-muted mb-0">Kelola unit atau departemen dan lokasi yang ditangani.</p>
        </div>

        <button type="button" class="btn btn-primary" id="btnCreateUnit">
            + Tambah Unit
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
                <table id="tabel-units" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
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
 * Checkbox lokasi, dipakai ulang di modal create dan edit.
 * $prefix membedakan id agar tidak bentrok antar modal.
 */
$locationChecks = function (string $prefix, array $checked = []) use ($locations) {
    if (empty($locations)) {
        echo '<div class="text-muted">Belum ada lokasi aktif.</div>';
        return;
    }
    foreach ($locations as $loc):
        $id = (int) $loc['id'];
        ?>
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   name="location_ids[]" value="<?= $id ?>"
                   id="<?= $prefix ?>Location<?= $id ?>"
                   <?= in_array($id, $checked, true) ? 'checked' : '' ?>>
            <label class="form-check-label" for="<?= $prefix ?>Location<?= $id ?>">
                <?= esc($loc['name']) ?>
            </label>
        </div>
    <?php endforeach;
};
?>

<div class="modal fade" id="createUnitModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="createUnitForm" method="post" action="<?= base_url('units/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Unit</label>
                            <input type="text" name="name" class="form-control"
                                   placeholder="Contoh: Teknologi Informasi" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kode Unit</label>
                            <input type="text" name="code" class="form-control"
                                   placeholder="Contoh: IT" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Lokasi yang Ditangani</label>
                            <div class="border rounded p-3">
                                <?php $locationChecks('createUnit'); ?>
                            </div>
                            <small class="text-muted">Satu unit dapat menangani beberapa lokasi.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3"
                                      placeholder="Keterangan unit"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       value="1" id="createUnitActive" checked>
                                <label class="form-check-label" for="createUnitActive">Unit Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editUnitModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="editUnitForm" method="post" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Unit</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kode Unit</label>
                            <input type="text" name="code" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Lokasi yang Ditangani</label>
                            <div class="border rounded p-3" id="editUnitLocations">
                                <?php $locationChecks('editUnit'); ?>
                            </div>
                            <small class="text-muted">Satu unit dapat menangani beberapa lokasi.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       value="1" id="editUnitActive">
                                <label class="form-check-label" for="editUnitActive">Unit Aktif</label>
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

<div class="modal fade" id="detailUnitModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Nama Unit</label>
                        <div class="fw-semibold" id="detailUnitName">-</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Kode</label>
                        <div id="detailUnitCode">-</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Status</label>
                        <div id="detailUnitStatus">-</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small">Deskripsi</label>
                        <div id="detailUnitDescription">-</div>
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header d-flex justify-content-between">
                        <strong>Lokasi Unit</strong>
                        <span class="badge bg-primary" id="detailUnitLocationCount">0 lokasi</span>
                    </div>
                    <div class="card-body">
                        <div id="detailUnitLocations"></div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between">
                        <strong>Aset pada Unit</strong>
                        <span class="badge bg-primary" id="detailUnitAssetCount">0 aset</span>
                    </div>
                    <div class="card-body">
                        <div id="detailUnitAssets"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-warning" id="detailUnitEdit">Edit</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';
    var baseUrl = Inventaris.baseUrl;

    // openModal() langsung menampilkan modal, jadi instance-nya dibuat
    // malas saat pertama kali dipakai.
    var createModal = null;
    var editModal   = null;
    var createForm  = document.getElementById('createUnitForm');
    var editForm    = document.getElementById('editUnitForm');

    var dt = Inventaris.datatable('#tabel-units', {
        url: baseUrl + 'units?format=json',
        columns: [
            { data: 'code', render: function (d) { return '<span class="badge bg-secondary">' + Inventaris.esc(d) + '</span>'; } },
            { data: 'name' },
            { data: 'description', render: function (d) { return Inventaris.esc(d) || '-'; } },
            { data: 'is_active', render: function (d) {
                return d ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>';
            }},
            { data: null, orderable: false, searchable: false, render: function (d, type, row) {
                return '<button type="button" class="btn btn-sm btn-outline-primary btn-detail" data-id="' + row.id + '">Detail</button> ' +
                    '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="' + row.id + '">Edit</button> ' +
                    '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' + row.id + '" data-name="' + Inventaris.esc(row.name) + '">Hapus</button>';
            }}
        ]
    });

    function fillEdit(row) {
        editForm.action = baseUrl + 'units/update/' + row.id;
        editForm.elements['name'].value = row.name == null ? '' : row.name;
        editForm.elements['code'].value = row.code == null ? '' : row.code;
        editForm.elements['description'].value = row.description == null ? '' : row.description;
        document.getElementById('editUnitActive').checked = !!Number(row.is_active);

        var assigned = (row.location_ids || []).map(Number);
        editForm.querySelectorAll('[name="location_ids[]"]').forEach(function (cb) {
            cb.checked = assigned.indexOf(Number(cb.value)) !== -1;
        });

        Inventaris.clearErrors(editForm);
    }

    function showCreate() {
        createForm.reset();
        document.getElementById('createUnitActive').checked = true;
        Inventaris.clearErrors(createForm);
        if (!createModal) createModal = Inventaris.openModal('createUnitModal');
        else createModal.show();
    }

    // ── Modal detail ────────────────────────────────────────
    // Tidak ada ?detail=<id>: ID tidak pernah masuk URL, jadi tidak
    // muncul di address bar maupun riwayat browser.
    var detailModal = null;
    var detailId = null;

    function activeBadge(v) {
        return Number(v) === 1
            ? '<span class="badge bg-success">Aktif</span>'
            : '<span class="badge bg-secondary">Tidak Aktif</span>';
    }

    function fillDetail(row) {
        detailId = row.id;
        document.getElementById('detailUnitName').textContent = row.name || '-';
        document.getElementById('detailUnitCode').innerHTML =
            '<span class="badge bg-secondary">' + Inventaris.esc(row.code || '-') + '</span>';
        document.getElementById('detailUnitStatus').innerHTML = activeBadge(row.is_active);
        document.getElementById('detailUnitDescription').textContent = row.description || '-';

        var locs = row.locations || [];
        document.getElementById('detailUnitLocationCount').textContent = locs.length + ' lokasi';
        document.getElementById('detailUnitLocations').innerHTML = locs.length === 0
            ? '<div class="text-muted">Belum ada lokasi yang terkait dengan unit ini.</div>'
            : '<div class="table-responsive"><table class="table table-bordered table-hover mb-0">' +
              '<thead><tr><th>Nama Lokasi</th><th>Gedung</th><th>Lantai</th><th>Ruangan</th><th>Status</th></tr></thead><tbody>' +
              locs.map(function (l) {
                  return '<tr><td>' + Inventaris.esc(l.name) + '</td>' +
                      '<td>' + Inventaris.esc(l.building || '-') + '</td>' +
                      '<td>' + Inventaris.esc(l.floor || '-') + '</td>' +
                      '<td>' + Inventaris.esc(l.room || '-') + '</td>' +
                      '<td>' + activeBadge(l.is_active) + '</td></tr>';
              }).join('') + '</tbody></table></div>';

        var assets = row.assets || [];
        document.getElementById('detailUnitAssetCount').textContent = assets.length + ' aset';
        document.getElementById('detailUnitAssets').innerHTML = assets.length === 0
            ? '<div class="text-muted">Belum ada aset pada unit ini.</div>'
            : '<div class="table-responsive"><table class="table table-bordered table-hover mb-0">' +
              '<thead><tr><th>Kode Aset</th><th>Nama Aset</th><th>Lokasi</th><th>Kondisi</th><th>Status</th></tr></thead><tbody>' +
              assets.map(function (a) {
                  return '<tr><td>' + Inventaris.esc(a.asset_code) + '</td>' +
                      '<td>' + Inventaris.esc(a.name) + '</td>' +
                      '<td>' + Inventaris.esc(a.location_name || '-') + '</td>' +
                      '<td>' + Inventaris.esc(a.condition_status || '-') + '</td>' +
                      '<td>' + Inventaris.esc(a.asset_status || '-') + '</td></tr>';
              }).join('') + '</tbody></table></div>';
    }

    function openDetailById(id) {
        Inventaris.fetchJson(baseUrl + 'units/data/' + encodeURIComponent(id))
            .then(function (row) {
                if (!row || !row.id) {
                    Inventaris.toast((row && row.message) || 'Unit tidak ditemukan.', 'danger');
                    return;
                }
                fillDetail(row);
                if (!detailModal) detailModal = Inventaris.openModal('detailUnitModal');
                else detailModal.show();
            })
            .catch(function () {
                Inventaris.toast('Gagal memuat data unit.', 'danger');
            });
    }

    document.getElementById('detailUnitEdit').addEventListener('click', function () {
        if (!detailId) return;
        Inventaris.hideModal('detailUnitModal');
        openEditById(detailId);
    });

    // Selalu ambil dari units/data/:id, bukan dari baris DataTable:
    // baris tabel hanya berisi kolom units.* dan TIDAK punya
    // location_ids. Mengisi checkbox dari baris akan menunchecked semua
    // lokasi lalu menghapusnya saat disimpan.
    function openEditById(id) {
        Inventaris.fetchJson(baseUrl + 'units/data/' + encodeURIComponent(id))
            .then(function (row) {
                if (!row || !row.id) {
                    Inventaris.toast('Unit tidak ditemukan.', 'danger');
                    return;
                }
                fillEdit(row);
                if (!editModal) editModal = Inventaris.openModal('editUnitModal');
                else editModal.show();
            })
            .catch(function () {
                Inventaris.toast('Gagal memuat data unit.', 'danger');
            });
    }

    document.getElementById('btnCreateUnit').addEventListener('click', showCreate);

    createForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function () {
                Inventaris.hideModal('createUnitModal');
                this.reset();
                dt.ajax.reload();
            }.bind(this)
        });
    });

    editForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function () {
                Inventaris.hideModal('editUnitModal');
                dt.ajax.reload();
            }
        });
    });

    document.getElementById('tabel-units').addEventListener('click', function (e) {
        var detailBtn = e.target.closest('.btn-detail');
        if (detailBtn) {
            openDetailById(detailBtn.getAttribute('data-id'));
            return;
        }

        var editBtn = e.target.closest('.btn-edit');
        if (editBtn) {
            openEditById(editBtn.getAttribute('data-id'));
            return;
        }

        var delBtn = e.target.closest('.btn-delete');
        if (delBtn) {
            Inventaris.confirm({
                title: 'Hapus Unit',
                message: 'Hapus unit "' + delBtn.getAttribute('data-name') + '"?',
                onConfirm: function () {
                    Inventaris.fetchJson(baseUrl + 'units/delete/' + delBtn.getAttribute('data-id'), {}, 'POST')
                        .then(function (json) {
                            if (json.success) {
                                Inventaris.toast(json.message);
                                dt.ajax.reload();
                            } else {
                                Inventaris.toast(json.message || 'Gagal menghapus.', 'danger');
                            }
                        })
                        .catch(function () {
                            Inventaris.toast('Koneksi gagal. Silakan coba lagi.', 'danger');
                        });
                }
            });
        }
    });

    // Deep link: /units?create=1 atau /units?edit=<id>
    var params = new URLSearchParams(window.location.search);
    var wantCreate = params.get('create') === '1';
    var wantEdit = params.get('edit');

    if (wantCreate || wantEdit) {
        window.history.replaceState({}, document.title, baseUrl + 'units');
    }

    if (wantCreate) {
        showCreate();
    } else if (wantEdit) {
        openEditById(wantEdit);
    }
})();
</script>
<?= view('layout/footer') ?>
