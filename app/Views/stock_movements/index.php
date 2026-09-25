<?= view('layout/header', ['title' => 'Stock Movement']) ?>
<?= view('layout/navbar') ?>

<div class="mb-4">

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



<div class="modal fade" id="detailMovementModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Kode Transaksi</label>
                        <div class="fw-semibold" id="detailMovementCode">-</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Tanggal</label>
                        <div id="detailMovementDate">-</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Tipe</label>
                        <div id="detailMovementType">-</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-muted small">Jenis Barang</label>
                        <div id="detailMovementItemType">-</div>
                    </div>
                    <div class="col-md-8" id="detailMovementAssetWrap">
                        <label class="form-label text-muted small">Aset</label>
                        <div id="detailMovementAsset">-</div>
                    </div>
                    <div class="col-md-8" id="detailMovementItemWrap" hidden>
                        <label class="form-label text-muted small">Barang Stok</label>
                        <div id="detailMovementItem">-</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-muted small">Quantity</label>
                        <div id="detailMovementQty">-</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Dari Lokasi</label>
                        <div id="detailMovementFrom">-</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted small">Ke Lokasi</label>
                        <div id="detailMovementTo">-</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-muted small">Alasan</label>
                        <div id="detailMovementReason">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Catatan</label>
                        <div id="detailMovementNotes">-</div>
                    </div>

                    <div class="col-12">
                        <hr>
                        <p class="mb-0 text-muted small">
                            Dibuat: <span id="detailMovementCreatedAt">-</span>
                            <span id="detailMovementCreatedBy"></span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';
    var baseUrl = Inventaris.baseUrl;

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
        url: baseUrl + 'stock-movements?format=json',
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
                    return '<button type="button" class="btn btn-sm btn-outline-primary btn-detail" data-id="' + row.id + '">Detail</button>';
                }
            }
        ]
    });

    Inventaris.bindFilter('filterForm', dt);

    // ── Modal detail ────────────────────────────────────────
    // Tidak ada ?detail=<id>: ID tidak pernah masuk URL, jadi tidak
    // muncul di address bar maupun riwayat browser.
    var detailModal = null;

    function fillDetail(row) {
        document.getElementById('detailMovementCode').textContent = row.transaction_code || '-';
        document.getElementById('detailMovementDate').textContent = row.transaction_date || '-';
        document.getElementById('detailMovementType').innerHTML = typeBadge(row.transaction_type);
        document.getElementById('detailMovementItemType').textContent = row.item_type || '-';

        // Tampilkan blok aset ATAU barang stok, sesuai jenisnya.
        var isStock = (row.item_type || '') === 'Stok';
        document.getElementById('detailMovementAssetWrap').hidden = isStock;
        document.getElementById('detailMovementItemWrap').hidden = !isStock;

        if (isStock) {
            document.getElementById('detailMovementItem').textContent =
                (row.item_code || '-') + ' - ' + (row.item_name || '-') +
                ' (' + (row.satuan || '-') + ')';
        } else {
            document.getElementById('detailMovementAsset').textContent =
                (row.asset_code || '-') + ' - ' + (row.asset_name || '-');
        }

        var qty = row.quantity;
        document.getElementById('detailMovementQty').textContent =
            (qty === null || qty === undefined || qty === '') ? '-' : String(qty);
        document.getElementById('detailMovementFrom').textContent = row.from_location_name || '-';
        document.getElementById('detailMovementTo').textContent = row.to_location_name || '-';
        document.getElementById('detailMovementReason').textContent = row.reason || '-';
        document.getElementById('detailMovementNotes').textContent = row.notes || '-';
        document.getElementById('detailMovementCreatedAt').textContent = row.created_at || '-';
        document.getElementById('detailMovementCreatedBy').textContent =
            row.created_by_name ? ' oleh ' + row.created_by_name : '';
    }

    function openDetailById(id) {
        Inventaris.fetchJson(baseUrl + 'stock-movements/data/' + encodeURIComponent(id))
            .then(function (row) {
                if (!row || !row.id) {
                    Inventaris.toast((row && row.message) || 'Transaksi tidak ditemukan.', 'danger');
                    return;
                }
                fillDetail(row);
                if (!detailModal) detailModal = Inventaris.openModal('detailMovementModal');
                else detailModal.show();
            })
            .catch(function () {
                Inventaris.toast('Gagal memuat data transaksi.', 'danger');
            });
    }

    document.getElementById('tabel-movement').addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-detail');
        if (btn) openDetailById(btn.getAttribute('data-id'));
    });
})();
</script>
<?= view('layout/footer') ?>

