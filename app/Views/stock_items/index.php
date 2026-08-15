<?= view('layout/header', ['title' => 'Barang Stok']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Barang Stok</h3>
            <p class="text-muted mb-0">
                Kelola barang non-identitas beserta stoknya.
            </p>
        </div>

        <button type="button"
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#createStockModal">
            + Tambah Barang Stok
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

                <table id="tabel-stock"
                       class="table table-hover align-middle w-100">

                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Unit</th>
                            <th>Stok</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th class="text-nowrap">Aksi</th>
                        </tr>
                    </thead>

                </table>

            </div>

        </div>
    </div>

</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="createStockModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="createStockForm" method="post" action="<?= base_url('stock-items/store') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Barang Stok</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Barang</label>
                        <input type="text" name="item_code" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= esc($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit / Departemen</label>
                        <select name="unit_id" class="form-select" required>
                            <?php foreach ($units as $unit): ?>
                                <option value="<?= $unit['id'] ?>"><?= esc($unit['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi</label>
                        <select name="location_id" class="form-select" required>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?= $location['id'] ?>"><?= esc($location['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" placeholder="mis. pcs, box, kg" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
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

<!-- MODAL EDIT -->
<div class="modal fade" id="editStockModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editStockForm" method="post" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Barang Stok</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Barang</label>
                        <input type="text" name="item_code" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= esc($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit / Departemen</label>
                        <select name="unit_id" class="form-select" required>
                            <?php foreach ($units as $unit): ?>
                                <option value="<?= $unit['id'] ?>"><?= esc($unit['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi</label>
                        <select name="location_id" class="form-select" required>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?= $location['id'] ?>"><?= esc($location['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="editIsActive" checked>
                        <label class="form-check-label" for="editIsActive">Aktif</label>
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

<!-- MODAL STOK MASUK -->
<div class="modal fade" id="stockInModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="stockInForm" method="post" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Stok Masuk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3" id="stockInInfo"></div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="quantity" min="1" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL STOK KELUAR -->
<div class="modal fade" id="stockOutModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="stockOutForm" method="post" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Stok Keluar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3" id="stockOutInfo"></div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="quantity" min="1" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
(function () {
    'use strict';

    function quantityBadge(value) {
        return value > 0
            ? '<span class="badge bg-success">' + Inventaris.esc(value) + '</span>'
            : '<span class="badge bg-danger">' + Inventaris.esc(value) + '</span>';
    }

    function statusBadge(value) {
        return value
            ? '<span class="badge bg-success">Aktif</span>'
            : '<span class="badge bg-secondary">Non-aktif</span>';
    }

    function actionHtml(row) {
        var detailUrl = Inventaris.baseUrl + 'stock-items/' + row.id;
        var buttons =
            '<a href="' + detailUrl + '" class="btn btn-sm btn-info">Detail</a> ' +
            '<button type="button" class="btn btn-sm btn-warning btn-edit-stock" data-id="' + row.id + '">Edit</button> ' +
            '<button type="button" class="btn btn-sm btn-success btn-stock-in" data-id="' + row.id + '" data-name="' + Inventaris.esc(row.name) + '" data-satuan="' + Inventaris.esc(row.satuan) + '">+ Masuk</button> ' +
            '<button type="button" class="btn btn-sm btn-danger btn-stock-out" data-id="' + row.id + '" data-name="' + Inventaris.esc(row.name) + '" data-satuan="' + Inventaris.esc(row.satuan) + '" data-qty="' + row.quantity + '">- Keluar</button>';
        return '<div class="text-nowrap">' + buttons + '</div>';
    }

    var dt = Inventaris.datatable('#tabel-stock', {
        url: Inventaris.baseUrl + 'stock-items?format=json',
        columns: [
            { data: 'item_code' },
            { data: 'name' },
            { data: 'category_name' },
            { data: 'unit_name' },
            { data: 'quantity', render: function (data) { return quantityBadge(data); } },
            { data: 'location_name' },
            { data: 'is_active', render: function (data) { return statusBadge(data); } },
            { data: null, orderable: false, searchable: false, render: function (data, type, row) { return actionHtml(row); } }
        ]
    });

    var editForm = document.getElementById('editStockForm');
    var editModal = null;
    var inForm = document.getElementById('stockInForm');
    var outForm = document.getElementById('stockOutForm');
    var inModal = null;
    var outModal = null;

    function showModal(id, state) {
        if (state) state.show();
        else Inventaris.openModal(id);
    }

    document.getElementById('tabel-stock').addEventListener('click', function (e) {
        var btn;

        btn = e.target.closest('.btn-edit-stock');
        if (btn) {
            var row = dt.row(btn.closest('tr')).data();
            if (!row) return;
            editForm.action = Inventaris.baseUrl + 'stock-items/update/' + row.id;
            ['item_code', 'name', 'satuan', 'description'].forEach(function (name) {
                editForm.elements[name].value = (row[name] === null || row[name] === undefined) ? '' : row[name];
            });
            ['category_id', 'unit_id', 'location_id'].forEach(function (name) {
                if (editForm.elements[name].value) editForm.elements[name].value = row[name];
            });
            editForm.elements['is_active'].checked = row.is_active == 1;
            if (!editModal) editModal = Inventaris.openModal('editStockModal');
            else editModal.show();
            return;
        }

        btn = e.target.closest('.btn-stock-in');
        if (btn) {
            inForm.action = Inventaris.baseUrl + 'stock-items/stock-in/' + btn.getAttribute('data-id');
            document.getElementById('stockInInfo').innerHTML =
                '<strong>' + Inventaris.esc(btn.getAttribute('data-name')) + '</strong><br>' +
                '<small class="text-muted">Satuan: ' + Inventaris.esc(btn.getAttribute('data-satuan')) + '</small>';
            if (!inModal) inModal = Inventaris.openModal('stockInModal');
            else inModal.show();
            return;
        }

        btn = e.target.closest('.btn-stock-out');
        if (btn) {
            outForm.action = Inventaris.baseUrl + 'stock-items/stock-out/' + btn.getAttribute('data-id');
            document.getElementById('stockOutInfo').innerHTML =
                '<strong>' + Inventaris.esc(btn.getAttribute('data-name')) + '</strong><br>' +
                '<small class="text-muted">Satuan: ' + Inventaris.esc(btn.getAttribute('data-satuan')) +
                ' | Stok: ' + Inventaris.esc(btn.getAttribute('data-qty')) + '</small>';
            if (!outModal) outModal = Inventaris.openModal('stockOutModal');
            else outModal.show();
            return;
        }
    });

    document.getElementById('createStockForm').addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function () {
                Inventaris.hideModal('createStockModal');
                this.reset();
                dt.ajax.reload();
            }.bind(this)
        });
    });

    editForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(editForm, {
            onSuccess: function () {
                Inventaris.hideModal('editStockModal');
                dt.ajax.reload();
            }
        });
    });

    inForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(inForm, {
            onSuccess: function () {
                Inventaris.hideModal('stockInModal');
                inForm.reset();
                dt.ajax.reload();
            }
        });
    });

    outForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(outForm, {
            onSuccess: function () {
                Inventaris.hideModal('stockOutModal');
                outForm.reset();
                dt.ajax.reload();
            }
        });
    });
})();
</script>
<?= view('layout/footer') ?>

