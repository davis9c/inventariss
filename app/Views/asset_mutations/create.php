<?= view('layout/header', ['title' => 'Detail Barang']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3>Detail Barang</h3>
            <p class="text-muted mb-0">
                Informasi lengkap aset dan riwayat pengelolaan.
            </p>
        </div>

        <div class="text-nowrap">
            <a href="<?= base_url('assets') ?>"
                class="btn btn-secondary">
                Kembali
            </a>

            <button type="button"
                    class="btn btn-danger btn-toggle-asset-out"
                    id="btnAssetOut"
                    <?= $asset['asset_status'] === 'Keluar Perusahaan' ? 'hidden' : '' ?>>
                Keluar Perusahaan
            </button>

            <button type="button"
                    class="btn btn-success btn-toggle-asset-out"
                    id="btnAssetReturn"
                    <?= $asset['asset_status'] !== 'Keluar Perusahaan' ? 'hidden' : '' ?>>
                Pengembalian
            </button>

            <button type="button"
                    class="btn btn-warning"
                    data-bs-toggle="modal"
                    data-bs-target="#editAssetModal">
                Edit
            </button>

            <button type="button"
                    class="btn btn-danger"
                    id="btnDeleteAsset">
                Hapus
            </button>
        </div>

    </div>


    <!-- INFORMASI BARANG -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <strong>Informasi Barang</strong>
        </div>

        <div class="card-body">

            <div class="row">

                <!-- KOLOM KIRI -->

                <div class="col-md-6">

                    <table class="table table-borderless">

                        <tr>
                            <th width="40%">Kode Barang</th>
                            <td>
                                <?= esc($asset['asset_code']) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Nama Barang</th>
                            <td>
                                <?= esc($asset['name']) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Kategori</th>
                            <td>
                                <?= esc($asset['category_name']) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Merk</th>
                            <td>
                                <?= esc($asset['brand'] ?: '-') ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Model</th>
                            <td>
                                <?= esc($asset['model'] ?: '-') ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Serial Number</th>
                            <td>
                                <?= esc($asset['serial_number'] ?: '-') ?>
                            </td>
                        </tr>

                    </table>

                </div>


                <!-- KOLOM KANAN -->

                <div class="col-md-6">

                    <table class="table table-borderless">

                        <tr>
                            <th width="40%">Unit</th>
                            <td id="asset-unit">
                                <?= esc($asset['unit_name']) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Lokasi</th>
                            <td id="asset-location">
                                <?= esc($asset['location_name']) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Tahun Perolehan</th>
                            <td>
                                <?= esc($asset['acquisition_year'] ?: '-') ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Harga Perolehan</th>
                            <td>
                                Rp <?= number_format(
                                        $asset['acquisition_price'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Kondisi</th>
                            <td>
                                <span class="badge bg-<?= match ($asset['condition_status']) {
                                    'Baik' => 'success',
                                    'Rusak Ringan' => 'warning',
                                    'Rusak Berat' => 'danger',
                                    default => 'secondary',
                                } ?>">
                                    <?= esc($asset['condition_status']) ?>
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th>Status</th>
                            <td id="asset-status-cell"></td>
                        </tr>

                    </table>

                </div>

            </div>


            <?php if (!empty($asset['description'])): ?>

                <hr>

                <strong>Deskripsi</strong>

                <p class="text-muted mt-2 mb-0">
                    <?= nl2br(esc($asset['description'])) ?>
                </p>

            <?php endif; ?>

        </div>

    </div>


    <!-- ===================================================== -->
    <!-- RIWAYAT PERGERAKAN -->
    <!-- ===================================================== -->

    <div class="card shadow-sm mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">

            <strong>Riwayat Pergerakan</strong>

            <button type="button"
                    class="btn btn-sm btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#mutasiModal">
                + Mutasi
            </button>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabel-pergerakan"
                       class="table table-hover align-middle w-100">

                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Dari Lokasi</th>
                            <th>Ke Lokasi</th>
                            <th>Keterangan</th>
                            <th>User</th>
                        </tr>
                    </thead>

                </table>

            </div>

        </div>

    </div>


    <!-- ===================================================== -->
    <!-- RIWAYAT STOCK OPNAME -->
    <!-- ===================================================== -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <strong>Riwayat Stok Opname</strong>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabel-opname"
                       class="table table-hover align-middle w-100">

                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Hasil</th>
                            <th>Kondisi</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>

                </table>

            </div>

        </div>

    </div>


</div>

<!-- ===================================================== -->
<!-- MODAL EDIT -->
<!-- ===================================================== -->

<div class="modal fade"
     id="editAssetModal"
     tabindex="-1"
     data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="editAssetForm"
                  method="post"
                  action="<?= base_url('assets/update/' . $asset['id']) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kode Barang</label>
                            <input type="text" name="asset_code" class="form-control" value="<?= esc($asset['asset_code']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Barang</label>
                            <input type="text" name="name" class="form-control" value="<?= esc($asset['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= $category['id'] == $asset['category_id'] ? 'selected' : '' ?>><?= esc($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit</label>
                            <select name="unit_id" class="form-select" required>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?= $unit['id'] ?>" <?= $unit['id'] == $asset['unit_id'] ? 'selected' : '' ?>><?= esc($unit['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lokasi</label>
                            <select name="location_id" class="form-select" required>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?= $location['id'] ?>" <?= $location['id'] == $asset['location_id'] ? 'selected' : '' ?>><?= esc($location['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Brand</label>
                            <input type="text" name="brand" class="form-control" value="<?= esc($asset['brand']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control" value="<?= esc($asset['model']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Serial Number</label>
                            <input type="text" name="serial_number" class="form-control" value="<?= esc($asset['serial_number']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tahun Perolehan</label>
                            <select name="acquisition_year" class="form-select">
                                <?= year_options($asset['acquisition_year']) ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Harga Perolehan</label>
                            <input type="number" step="0.01" min="1000" name="acquisition_price" class="form-control" value="<?= esc($asset['acquisition_price']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kondisi</label>
                            <select name="condition_status" class="form-select" required>
                                <option value="Baik" <?= $asset['condition_status'] === 'Baik' ? 'selected' : '' ?>>Baik</option>
                                <option value="Rusak Ringan" <?= $asset['condition_status'] === 'Rusak Ringan' ? 'selected' : '' ?>>Rusak Ringan</option>
                                <option value="Rusak Berat" <?= $asset['condition_status'] === 'Rusak Berat' ? 'selected' : '' ?>>Rusak Berat</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="asset_status" class="form-select" required>
                                <option value="Aktif" <?= $asset['asset_status'] === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="Dipinjam" <?= $asset['asset_status'] === 'Dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                                <option value="Tidak Digunakan" <?= $asset['asset_status'] === 'Tidak Digunakan' ? 'selected' : '' ?>>Tidak Digunakan</option>
                                <option value="Keluar Perusahaan" <?= $asset['asset_status'] === 'Keluar Perusahaan' ? 'selected' : '' ?>>Keluar Perusahaan</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2"><?= esc($asset['description']) ?></textarea>
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

<!-- ===================================================== -->
<!-- MODAL MUTASI -->
<!-- ===================================================== -->

<div class="modal fade"
     id="mutasiModal"
     tabindex="-1"
     data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form id="mutasiForm"
                  method="post"
                  action="<?= base_url('asset-mutations/store') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Mutasi Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        <strong><?= esc($asset['name']) ?></strong><br>
                        <small class="text-muted">
                            <?= esc($asset['asset_code']) ?>
                            â€” <?= esc($asset['location_name']) ?>
                        </small>
                    </div>
                    <input type="hidden" name="asset_id" value="<?= $asset['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Lokasi Tujuan</label>
                        <select name="to_location_id" id="mutasiToLocation" class="form-select" required>
                            <option value="">-- Pilih Lokasi --</option>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?= $location['id'] ?>"><?= esc($location['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit Tujuan</label>
                        <select name="to_unit_id" id="mutasiToUnit" class="form-select" required disabled>
                            <option value="">-- Pilih Lokasi Terlebih Dahulu --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mutasi</label>
                        <input type="date" name="mutation_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan</label>
                        <input type="text" name="reason" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Mutasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================================================== -->
<!-- MODAL KELUAR PERUSAHAAN -->
<!-- ===================================================== -->

<div class="modal fade"
     id="assetOutModal"
     tabindex="-1"
     data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form id="assetOutForm"
                  method="post"
                  action="<?= base_url('assets/asset-out/' . $asset['id']) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Barang Keluar Perusahaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger py-2 mb-3">
                        Barang akan dicatat keluar dari tanggung jawab perusahaan.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Pengeluaran</label>
                        <select name="outbound_type" class="form-select" required>
                            <option value="">-- Pilih --</option>
                            <?php foreach (['Pemindahan', 'Peminjaman', 'Hibah', 'Penjualan', 'Penghapusan', 'Retur', 'Lainnya'] as $type): ?>
                                <option value="<?= esc($type) ?>"><?= esc($type) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tujuan/Penerima</label>
                        <input type="text" name="recipient_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit/Departemen Tujuan</label>
                        <input type="text" name="destination_unit" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor Dokumen</label>
                        <input type="text" name="document_number" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pihak Menyerahkan</label>
                        <input type="text" name="handed_over_by" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pihak Menerima</label>
                        <input type="text" name="received_by" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan</label>
                        <input type="text" name="reason" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Keluar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===================================================== -->
<!-- MODAL PENGEMBALIAN -->
<!-- ===================================================== -->

<div class="modal fade"
     id="assetReturnModal"
     tabindex="-1"
     data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form id="assetReturnForm"
                  method="post"
                  action="<?= base_url('assets/asset-return/' . $asset['id']) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Pengembalian Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-success py-2 mb-3">
                        Barang kembali ke dalam tanggung jawab perusahaan.
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
                    <button type="submit" class="btn btn-success">Ya, Kembalikan</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
(function () {
    'use strict';

    var movements = <?= json_encode($movements, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var stockOpnames = <?= json_encode($stockOpnames, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var unitsMap = <?= json_encode(array_column($units, 'name', 'id'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var locationsMap = <?= json_encode(array_column($locations, 'name', 'id'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    function statusBadge(value) {
        var map = {
            'Aktif': 'success',
            'Dipinjam': 'primary',
            'Tidak Digunakan': 'secondary',
            'Keluar Perusahaan': 'danger'
        };
        return '<span class="badge bg-' + (map[value] || 'secondary') + '">' + Inventaris.esc(value) + '</span>';
    }

    function movementBadge(value) {
        var map = {
            'Perolehan': 'info',
            'Mutasi': 'primary',
            'Pindah': 'primary',
            'Keluar Perusahaan': 'danger',
            'Pengembalian': 'success'
        };
        return '<span class="badge bg-' + (map[value] || 'secondary') + '">' + Inventaris.esc(value) + '</span>';
    }

    function setStatus(value) {
        document.getElementById('asset-status-cell').innerHTML = statusBadge(value);
        var keluar = document.getElementById('btnAssetOut');
        var kembali = document.getElementById('btnAssetReturn');
        keluar.hidden = value === 'Keluar Perusahaan';
        kembali.hidden = value !== 'Keluar Perusahaan';
    }

    function prependMovement(row) {
        dtPergerakan.row.add(row).draw(false);
    }

    var dtPergerakan = Inventaris.datatable('#tabel-pergerakan', {
        serverSide: false,
        data: movements,
        columns: [
            { data: 'transaction_date' },
            { data: 'transaction_type', render: function (data) { return movementBadge(data); } },
            { data: 'from_location_name', render: function (data) { return Inventaris.esc(data || '-'); } },
            { data: 'to_location_name', render: function (data) { return Inventaris.esc(data || '-'); } },
            { data: null, render: function (data, type, row) { return Inventaris.esc(row.reason || row.notes || '-'); } },
            { data: 'created_by_name', render: function (data) { return Inventaris.esc(data || '-'); } }
        ]
    });

    var dtOpname = Inventaris.datatable('#tabel-opname', {
        serverSide: false,
        data: stockOpnames,
        columns: [
            { data: 'opname_date', render: function (data) { return Inventaris.esc(data || '-'); } },
            { data: 'result', render: function (data) { return Inventaris.esc(data || '-'); } },
            { data: 'condition_status', render: function (data) { return Inventaris.esc(data || '-'); } },
            { data: 'notes', render: function (data) { return Inventaris.esc(data || '-'); } }
        ]
    });

    setStatus(<?= json_encode($asset['asset_status']) ?>);

    document.getElementById('btnAssetOut').addEventListener('click', function () {
        Inventaris.openModal('assetOutModal');
    });

    document.getElementById('btnAssetReturn').addEventListener('click', function () {
        Inventaris.openModal('assetReturnModal');
    });

    var mutasiForm = document.getElementById('mutasiForm');
    var mutasiToUnit = document.getElementById('mutasiToUnit');

    document.getElementById('mutasiToLocation').addEventListener('change', function () {
        var id = this.value;
        mutasiToUnit.disabled = !id;
        mutasiToUnit.innerHTML = '<option value="">-- Pilih Unit --</option>';
        if (!id) return;
        Inventaris.fetchJson(Inventaris.baseUrl + 'asset-mutations/units-by-location/' + id)
            .then(function (units) {
                units.forEach(function (u) {
                    var opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.name;
                    mutasiToUnit.appendChild(opt);
                });
            })
            .catch(function () {
                Inventaris.toast('Gagal memuat daftar unit.', 'danger');
            });
    });

    mutasiForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(mutasiForm, {
            onSuccess: function (json) {
                Inventaris.hideModal('mutasiModal');
                document.getElementById('asset-unit').textContent = unitsMap[json.data.unit_id] || '-';
                document.getElementById('asset-location').textContent = locationsMap[json.data.location_id] || '-';
                prependMovement(json.data.transaction);
            }
        });
    });

    document.getElementById('assetOutForm').addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function (json) {
                Inventaris.hideModal('assetOutModal');
                setStatus(json.data.asset_status);
                prependMovement(json.data.transaction);
            }
        });
    });

    document.getElementById('assetReturnForm').addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function (json) {
                Inventaris.hideModal('assetReturnModal');
                setStatus(json.data.asset_status);
                prependMovement(json.data.transaction);
            }
        });
    });

    document.getElementById('editAssetForm').addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function () {
                Inventaris.hideModal('editAssetModal');
                location.reload();
            }
        });
    });

    document.getElementById('btnDeleteAsset').addEventListener('click', function () {
        Inventaris.confirm({
            title: 'Hapus Barang',
            message: 'Hapus barang "<?= esc($asset['name']) ?>"? Tindakan ini tidak dapat dibatalkan.',
            onConfirm: function () {
                Inventaris.fetchJson('<?= base_url('assets/delete/' . $asset['id']) ?>', {}, 'POST')
                    .then(function (json) {
                        if (json.success) {
                            Inventaris.toast(json.message);
                            window.location.href = '<?= base_url('assets') ?>';
                        } else {
                            Inventaris.toast(json.message, 'danger');
                        }
                    })
                    .catch(function () {
                        Inventaris.toast('Koneksi gagal. Silakan coba lagi.', 'danger');
                    });
            }
        });
    });
})();
</script>
<?= view('layout/footer') ?>

