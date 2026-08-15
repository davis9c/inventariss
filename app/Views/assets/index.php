<?= view('layout/header', ['title' => 'Barang / Aset']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Barang / Aset</h3>
            <p class="text-muted mb-0">
                Kelola seluruh barang inventaris.
            </p>
        </div>

        <button type="button"
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#createAssetModal">
            + Tambah Barang
        </button>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="table-responsive">

                <table id="tabel-asset"
                       class="table table-hover align-middle w-100">

                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Unit</th>
                            <th>Lokasi</th>
                            <th>Kondisi</th>
                            <th>Status</th>
                            <th class="text-nowrap">Aksi</th>
                        </tr>
                    </thead>

                </table>

            </div>

        </div>
    </div>

</div>

<div class="modal fade"
     id="createAssetModal"
     tabindex="-1"
     data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="createAssetForm"
                  method="post"
                  action="<?= base_url('assets/store') ?>"
                  class="needs-validation"
                  novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kode Barang</label>
                            <input type="text" name="asset_code" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Barang</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>"><?= esc($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit</label>
                            <select name="unit_id" class="form-select" required>
                                <option value="">-- Pilih Unit --</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?= $unit['id'] ?>"><?= esc($unit['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lokasi</label>
                            <select name="location_id" class="form-select" required>
                                <option value="">-- Pilih Lokasi --</option>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?= $location['id'] ?>"><?= esc($location['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Brand</label>
                            <input type="text" name="brand" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Serial Number</label>
                            <input type="text" name="serial_number" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tahun Perolehan</label>
                            <input type="number" name="acquisition_year" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Perolehan</label>
                            <input type="number" step="0.01" name="acquisition_price" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kondisi</label>
                            <select name="condition_status" class="form-select" required>
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="asset_status" class="form-select" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Dipinjam">Dipinjam</option>
                                <option value="Tidak Digunakan">Tidak Digunakan</option>
                                <option value="Keluar Perusahaan">Keluar Perusahaan</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
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

<div class="modal fade"
     id="editAssetModal"
     tabindex="-1"
     data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="editAssetForm" method="post" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kode Barang</label>
                            <input type="text" name="asset_code" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Barang</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>"><?= esc($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit</label>
                            <select name="unit_id" class="form-select" required>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?= $unit['id'] ?>"><?= esc($unit['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lokasi</label>
                            <select name="location_id" class="form-select" required>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?= $location['id'] ?>"><?= esc($location['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Brand</label>
                            <input type="text" name="brand" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Serial Number</label>
                            <input type="text" name="serial_number" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tahun Perolehan</label>
                            <input type="number" name="acquisition_year" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Perolehan</label>
                            <input type="number" step="0.01" name="acquisition_price" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kondisi</label>
                            <select name="condition_status" class="form-select" required>
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="asset_status" class="form-select" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Dipinjam">Dipinjam</option>
                                <option value="Tidak Digunakan">Tidak Digunakan</option>
                                <option value="Keluar Perusahaan">Keluar Perusahaan</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
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



<script>
(function () {
    'use strict';

    function conditionBadge(value) {
        var map = { 'Baik': 'success', 'Rusak Ringan': 'warning', 'Rusak Berat': 'danger' };
        return '<span class="badge bg-' + (map[value] || 'secondary') + '">' + Inventaris.esc(value) + '</span>';
    }

    function statusBadge(value) {
        var map = {
            'Aktif': 'success',
            'Dipinjam': 'primary',
            'Tidak Digunakan': 'secondary',
            'Keluar Perusahaan': 'danger'
        };
        return '<span class="badge bg-' + (map[value] || 'secondary') + '">' + Inventaris.esc(value) + '</span>';
    }

    function actionHtml(row) {
        var detailUrl = Inventaris.baseUrl + 'assets/' + row.id;
        var buttons =
            '<a href="' + detailUrl + '" class="btn btn-sm btn-info">Detail</a> ' +
            '<button type="button" class="btn btn-sm btn-warning btn-edit-asset" data-id="' + row.id + '">Edit</button> ' +
            '<button type="button" class="btn btn-sm btn-danger btn-delete-asset" data-id="' + row.id + '" data-name="' + Inventaris.esc(row.name) + '">Hapus</button>';
        return '<div class="text-nowrap">' + buttons + '</div>';
    }

    var dt = Inventaris.datatable('#tabel-asset', {
        url: Inventaris.baseUrl + 'assets?format=json',
        columns: [
            { data: 'asset_code' },
            { data: 'name' },
            { data: 'category_name' },
            { data: 'unit_name' },
            { data: 'location_name' },
            { data: 'condition_status', render: function (data) { return conditionBadge(data); } },
            { data: 'asset_status', render: function (data) { return statusBadge(data); } },
            { data: null, orderable: false, searchable: false, render: function (data, type, row) { return actionHtml(row); } }
        ]
    });

    var editModalEl = document.getElementById('editAssetModal');
    var editForm = document.getElementById('editAssetForm');
    var editModal = null;

    document.getElementById('tabel-asset').addEventListener('click', function (e) {
        var editBtn = e.target.closest('.btn-edit-asset');
        if (editBtn) {
            var row = dt.row(editBtn.closest('tr')).data();
            if (!row) return;
            editForm.action = Inventaris.baseUrl + 'assets/update/' + row.id;
            ['asset_code', 'name', 'brand', 'model', 'serial_number', 'acquisition_year', 'acquisition_price', 'description'].forEach(function (name) {
                editForm.elements[name].value = (row[name] === null || row[name] === undefined) ? '' : row[name];
            });
            ['category_id', 'unit_id', 'location_id', 'condition_status', 'asset_status'].forEach(function (name) {
                if (editForm.elements[name] && editForm.elements[name].value) editForm.elements[name].value = row[name];
            });
            if (!editModal) editModal = Inventaris.openModal('editAssetModal');
            else editModal.show();
            return;
        }

        var delBtn = e.target.closest('.btn-delete-asset');
        if (delBtn) {
            var id = delBtn.getAttribute('data-id');
            var name = delBtn.getAttribute('data-name');
            Inventaris.confirm({
                title: 'Hapus Barang',
                message: 'Hapus barang "' + name + '"? Tindakan ini tidak dapat dibatalkan.',
                onConfirm: function () {
                    Inventaris.fetchJson(Inventaris.baseUrl + 'assets/delete/' + id, {}, 'POST')
                        .then(function (json) {
                            if (json.success) {
                                Inventaris.toast(json.message);
                                dt.ajax.reload();
                            } else {
                                Inventaris.toast(json.message, 'danger');
                            }
                        })
                        .catch(function () {
                            Inventaris.toast('Koneksi gagal. Silakan coba lagi.', 'danger');
                        });
                }
            });
        }
    });

    document.getElementById('createAssetForm').addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function () {
                Inventaris.hideModal('createAssetModal');
                this.reset();
                dt.ajax.reload();
            }.bind(this)
        });
    });

    editForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(editForm, {
            onSuccess: function () {
                Inventaris.hideModal('editAssetModal');
                dt.ajax.reload();
            }
        });
    });
})();
</script>
<?= view('layout/footer') ?>

