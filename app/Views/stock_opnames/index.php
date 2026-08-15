<?= view('layout/header', ['title' => 'Stock Opname']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3>Stock Opname</h3>

            <p class="text-muted mb-0">
                Pemeriksaan fisik barang inventaris.
            </p>
        </div>

        <a href="<?= base_url('stock-opnames/create') ?>"
           class="btn btn-primary">
            + Buat Stock Opname
        </a>

    </div>

    <?php if (session()->getFlashdata('success')): ?>

        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>

    <?php endif; ?>

    <div class="card shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabel-opname"
                       class="table table-hover align-middle w-100">

                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
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



<script>
(function () {
    'use strict';

    function statusBadge(value) {
        return value === 'Selesai'
            ? '<span class="badge bg-success">Selesai</span>'
            : '<span class="badge bg-warning text-dark">Draft</span>';
    }

    function locationHtml(row) {
        if (row.location_id) {
            return Inventaris.esc(row.building) + ' - ' + Inventaris.esc(row.room);
        }
        return '<span class="text-muted">Semua lokasi</span>';
    }

    Inventaris.datatable('#tabel-opname', {
        url: Inventaris.baseUrl + 'stock-opnames?format=json',
        columns: [
            { data: 'opname_code', render: function (data) { return '<strong>' + Inventaris.esc(data) + '</strong>'; } },
            { data: 'opname_date' },
            { data: null, render: function (data, type, row) { return locationHtml(row); } },
            { data: 'status', render: function (data) { return statusBadge(data); } },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return '<a href="' + Inventaris.baseUrl + 'stock-opnames/' + row.id + '" class="btn btn-sm btn-primary">Detail</a>';
                }
            }
        ]
    });
})();
</script>
<?= view('layout/footer') ?>

