<?= view('layout/header', ['title' => 'Laporan Inventaris']) ?>
<?= view('layout/sidebar') ?>

<div class="mb-4">

    <div class="mb-4">
        <h3>Laporan Inventaris</h3>

        <p class="text-muted mb-0">
            Daftar barang / aset inventaris.
        </p>
    </div>

    <!-- Filter -->
    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form method="get"
                action="<?= base_url('reports/assets') ?>">

                <div class="row">

                    <!-- Lokasi -->
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Lokasi
                        </label>

                        <select name="location_id"
                            class="form-select">

                            <option value="">
                                Semua Lokasi
                            </option>

                            <?php foreach ($locations as $location): ?>

                                <option value="<?= $location['id'] ?>"
                                    <?= ($filters['location_id'] ?? '') == $location['id']
                                        ? 'selected'
                                        : '' ?>>

                                    <?= esc($location['name']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Kategori -->
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Kategori
                        </label>

                        <select name="category_id"
                            class="form-select">

                            <option value="">
                                Semua Kategori
                            </option>

                            <?php foreach ($categories as $category): ?>

                                <option value="<?= $category['id'] ?>"
                                    <?= ($filters['category_id'] ?? '') == $category['id']
                                        ? 'selected'
                                        : '' ?>>

                                    <?= esc($category['name']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Kondisi -->
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Kondisi
                        </label>

                        <select name="condition_status"
                            class="form-select">

                            <option value="">
                                Semua Kondisi
                            </option>

                            <option value="Baik"
                                <?= ($filters['condition_status'] ?? '') === 'Baik'
                                    ? 'selected'
                                    : '' ?>>
                                Baik
                            </option>

                            <option value="Rusak Ringan"
                                <?= ($filters['condition_status'] ?? '') === 'Rusak Ringan'
                                    ? 'selected'
                                    : '' ?>>
                                Rusak Ringan
                            </option>

                            <option value="Rusak Berat"
                                <?= ($filters['condition_status'] ?? '') === 'Rusak Berat'
                                    ? 'selected'
                                    : '' ?>>
                                Rusak Berat
                            </option>

                        </select>

                    </div>


                    <!-- Status -->
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Status Aset
                        </label>

                        <select name="asset_status"
                            class="form-select">

                            <option value="">
                                Semua Status
                            </option>

                            <option value="Digunakan"
                                <?= ($filters['asset_status'] ?? '') === 'Digunakan'
                                    ? 'selected'
                                    : '' ?>>
                                Digunakan
                            </option>

                            <option value="Tidak Digunakan"
                                <?= ($filters['asset_status'] ?? '') === 'Tidak Digunakan'
                                    ? 'selected'
                                    : '' ?>>
                                Tidak Digunakan
                            </option>

                        </select>

                    </div>

                </div>


                <div>

                    <button type="submit"
                        class="btn btn-primary">

                        Tampilkan

                    </button>

                    <a href="<?= base_url('reports/assets') ?>"
                        class="btn btn-secondary">

                        Reset

                    </a>

                </div>

            </form>

        </div>

    </div>


    <!-- Ringkasan -->
    <div class="alert alert-light border">

        <strong>
            Total aset:
        </strong>

        <?= count($assets) ?>

    </div>


    <!-- Tabel -->
    <div class="card shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabel-report-assets" class="table table-hover align-middle">

                    <thead>

                        <tr>
                            <th>Kode Aset</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th>Unit</th>
                            <th>Lokasi</th>
                            <th>Kondisi</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody></tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<script>
(function() {
    var data = <?= json_encode($assets) ?>;

    new DataTable('#tabel-report-assets', {
        data: data,
        columns: [
            {
                data: 'asset_code',
                render: function(data) {
                    return '<strong>' + Inventaris.esc(data) + '</strong>';
                }
            },
            {
                data: null,
                render: function(row) {
                    var html = Inventaris.esc(row.name);
                    if (row.serial_number) {
                        html += '<br><small class="text-muted">SN: ' + Inventaris.esc(row.serial_number) + '</small>';
                    }
                    return html;
                }
            },
            {
                data: 'category_name',
                defaultContent: '-'
            },
            {
                data: 'unit_name',
                defaultContent: '-'
            },
            {
                data: null,
                render: function(row) {
                    var html = Inventaris.esc(row.location_name || '-');
                    if (row.building || row.room) {
                        var sub = Inventaris.esc(row.building || '');
                        if (row.room) {
                            sub += ' - ' + Inventaris.esc(row.room);
                        }
                        html += '<br><small class="text-muted">' + sub + '</small>';
                    }
                    return html;
                }
            },
            {
                data: 'condition_status',
                render: function(data) {
                    if (data === 'Baik') {
                        return '<span class="badge bg-success">Baik</span>';
                    } else if (data === 'Rusak Ringan') {
                        return '<span class="badge bg-warning text-dark">Rusak Ringan</span>';
                    } else {
                        return '<span class="badge bg-danger">' + Inventaris.esc(data) + '</span>';
                    }
                }
            },
            {
                data: 'asset_status',
                render: function(data) {
                    if (data === 'Digunakan') {
                        return '<span class="badge bg-success">Digunakan</span>';
                    }
                    return '<span class="badge bg-secondary">' + Inventaris.esc(data) + '</span>';
                }
            }
        ],
        language: {
            processing: 'Memuat...',
            search: 'Cari:',
            lengthMenu: 'Tampil _MENU_ baris',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
            infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords: 'Tidak ditemukan data yang cocok',
            emptyTable: 'Tidak ada data',
            paginate: { first: 'Awal', last: 'Akhir', next: 'Berikut', previous: 'Sebelum' }
        }
    });
})();
</script>

<?= view('layout/footer') ?>
