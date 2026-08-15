<?= view('layout/header', ['title' => 'Stock Movement']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Stock Movement</h3>
            <p class="text-muted mb-0">
                Histori seluruh pergerakan barang (aset & barang stok).
            </p>
        </div>
    </div>

    <!-- FILTER -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <form id="filterForm"
                  class="row g-2 align-items-end">

                <div class="col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date"
                           name="date_from"
                           data-filter
                           class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date"
                           name="date_to"
                           data-filter
                           class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Jenis Transaksi</label>
                    <select name="transaction_type[]"
                            data-filter
                            multiple
                            size="4"
                            class="form-select">
                        <?php
                        $allTypes = [
                            'Masuk',
                            'Keluar',
                            'Pindah',
                            'Penyesuaian Naik',
                            'Penyesuaian Turun',
                            'Perolehan',
                            'Mutasi',
                            'Keluar Perusahaan',
                            'Pengembalian',
                        ];
                        foreach ($allTypes as $type): ?>
                            <option value="<?= $type ?>">
                                <?= $type ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Jenis Barang</label>
                    <select name="item_type"
                            data-filter
                            class="form-select">
                        <option value="">Semua</option>
                        <option value="Aset">Aset</option>
                        <option value="Barang Stok">Barang Stok</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Lokasi</label>
                    <select name="location_id"
                            data-filter
                            class="form-select">
                        <option value="">Semua</option>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?= $location['id'] ?>">
                                <?= esc($location['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit"
                            class="btn btn-primary w-100">
                        Filter
                    </button>
                    <button type="reset"
                            class="btn btn-secondary w-100 mt-1">
                        Reset
                    </button>
                </div>

            </form>

        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="table-responsive">

                <table id="tabel-movement"
                       class="table table-hover align-middle w-100">

                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kode Transaksi</th>
                            <th>Jenis</th>
                            <th>Jenis Barang</th>
                            <th>Barang</th>
                            <th>Qty</th>
                            <th>Lokasi Asal</th>
                            <th>Lokasi Tujuan</th>
                            <th>User</th>
                            <th>Keterangan</th>
                            <th></th>
                        </tr>
                    </thead>

                </table>

            </div>

        </div>
    </div>

</div>



<script>
(function () {
    'use strict';

    function typeBadge(value) {
        var map = {
            'Masuk': 'success',
            'Penyesuaian Naik': 'success',
            'Pengembalian': 'success',
            'Keluar': 'danger',
            'Penyesuaian Turun': 'danger',
            'Keluar Perusahaan': 'danger',
            'Pindah': 'primary',
            'Mutasi': 'primary',
            'Perolehan': 'info'
        };
        return '<span class="badge bg-' + (map[value] || 'secondary') + '">' + Inventaris.esc(value) + '</span>';
    }

    var dt = Inventaris.datatable('#tabel-movement', {
        url: Inventaris.baseUrl + 'stock-movements?format=json',
        data: function (d) {
            Object.assign(d, Inventaris.filterParams('filterForm'));
        },
        columns: [
            { data: 'transaction_date' },
            { data: 'transaction_code', render: function (data) { return '<span class="badge bg-secondary">' + Inventaris.esc(data) + '</span>'; } },
            { data: 'transaction_type', render: function (data) { return typeBadge(data); } },
            { data: 'item_type' },
            {
                data: null,
                render: function (data, type, row) {
                    if (row.item_type === 'Aset') {
                        return '<strong>' + Inventaris.esc(row.asset_name || '-') + '</strong><br>' +
                            '<small class="text-muted">' + Inventaris.esc(row.asset_code || '') + '</small>';
                    }
                    return '<strong>' + Inventaris.esc(row.item_name || '-') + '</strong><br>' +
                        '<small class="text-muted">' + Inventaris.esc(row.item_code || '') + '</small>';
                }
            },
            {
                data: 'quantity',
                render: function (data, type, row) {
                    return Inventaris.esc(data) + (row.item_type === 'Barang Stok' ? ' ' + Inventaris.esc(row.satuan || '') : '');
                }
            },
            { data: 'from_location_name', render: function (data) { return Inventaris.esc(data || '-'); } },
            { data: 'to_location_name', render: function (data) { return Inventaris.esc(data || '-'); } },
            { data: 'created_by_name', render: function (data) { return Inventaris.esc(data || '-'); } },
            {
                data: null,
                render: function (data, type, row) {
                    return Inventaris.esc(row.reason || row.notes || '-');
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return '<a href="' + Inventaris.baseUrl + 'stock-movements/' + row.id + '" class="btn btn-sm btn-info">Detail</a>';
                }
            }
        ]
    });

    Inventaris.bindFilter('filterForm', dt);
})();
</script>
<?= view('layout/footer') ?>

