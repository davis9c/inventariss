<?= view('layout/header', ['title' => 'Detail Barang Stok']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3>Detail Barang Stok</h3>
            <p class="text-muted mb-0">
                Informasi barang non-identitas dan riwayat transaksi stok.
            </p>
        </div>

        <div class="text-nowrap">
            <a href="<?= base_url('stock-items') ?>"
                class="btn btn-secondary">
                Kembali
            </a>

            <button type="button"
                    class="btn btn-warning"
                    data-bs-toggle="modal"
                    data-bs-target="#editStockModal">
                Edit
            </button>

            <button type="button"
                    class="btn btn-success"
                    data-bs-toggle="modal"
                    data-bs-target="#stockInModal">
                + Stok Masuk
            </button>

            <button type="button"
                    class="btn btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#stockOutModal">
                - Stok Keluar
            </button>

            <button type="button"
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#transferModal">
                Pindah Stok
            </button>

            <button type="button"
                    class="btn btn-warning"
                    data-bs-toggle="modal"
                    data-bs-target="#adjustmentModal">
                Penyesuaian
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

                <div class="col-md-6">

                    <table class="table table-borderless">

                        <tr>
                            <th width="40%">Kode Barang</th>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= esc($item['item_code']) ?>
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th>Nama Barang</th>
                            <td>
                                <?= esc($item['name']) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Kategori</th>
                            <td>
                                <?= esc($item['category_name']) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Unit / Departemen</th>
                            <td>
                                <?= esc($item['unit_name']) ?>
                            </td>
                        </tr>

                    </table>

                </div>

                <div class="col-md-6">

                    <table class="table table-borderless">

                        <tr>
                            <th width="40%">Lokasi</th>
                            <td id="stock-location">
                                <?= esc($item['location_name']) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Satuan</th>
                            <td>
                                <?= esc($item['satuan']) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Stok Tersedia</th>
                            <td id="stock-quantity"></td>
                        </tr>

                        <tr>
                            <th>Status</th>
                            <td>
                                <?php if ($item['is_active']): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Non-aktif</span>
                                <?php endif; ?>
                            </td>
                        </tr>

                    </table>

                </div>

            </div>

            <?php if (!empty($item['description'])): ?>

                <hr>

                <strong>Deskripsi</strong>

                <p class="text-muted mt-2 mb-0">
                    <?= nl2br(esc($item['description'])) ?>
                </p>

            <?php endif; ?>

        </div>

    </div>

    <div class="row mb-4">
        <div class="col-md-6"><div class="card shadow-sm h-100"><div class="card-header d-flex justify-content-between"><strong>Dokumen Barang</strong><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#documentModal">Tambah Dokumen</button></div><div class="card-body"><?php if (empty($documents)): ?><p class="text-muted mb-0">Belum ada dokumen.</p><?php else: ?><ul class="mb-0"><?php foreach ($documents as $document): ?><li><a target="_blank" href="<?= base_url('attachments/file/document/' . $document['id']) ?>"><?= esc($document['document_type']) ?> — <?= esc($document['document_number'] ?: $document['original_name']) ?></a> <small class="text-muted">(<?= esc(waktu_utc7($document['created_at'])) ?>)</small></li><?php endforeach; ?></ul><?php endif; ?></div></div></div>
        <div class="col-md-6"><div class="card shadow-sm h-100"><div class="card-header d-flex justify-content-between"><strong>Histori Foto Kondisi</strong><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#photoModal">Tambah Foto</button></div><div class="card-body"><?php if (empty($photos)): ?><p class="text-muted mb-0">Belum ada foto.</p><?php else: ?><div class="row g-2"><?php foreach ($photos as $photo): ?><div class="col-4"><a href="<?= base_url('attachments/file/photo/' . $photo['id']) ?>" target="_blank"><img class="img-fluid rounded" src="<?= base_url('attachments/file/photo/' . $photo['id']) ?>" alt="<?= esc($photo['caption'] ?: 'Foto barang') ?>"></a><small><?= esc($photo['caption']) ?></small></div><?php endforeach; ?></div><?php endif; ?></div></div></div>
    </div>

    <!-- RIWAYAT PERGERAKAN STOK -->
    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <strong>Riwayat Pergerakan Stok</strong>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table id="tabel-riwayat"
                       class="table table-hover align-middle w-100">

                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Transaksi</th>
                            <th>Masuk</th>
                            <th>Keluar</th>
                            <th>Saldo</th>
                            <th>Lokasi</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                </table>

            </div>

        </div>

    </div>

</div>

<!-- MODAL EDIT -->
<div class="modal fade" id="editStockModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form id="editStockForm" method="post" action="<?= base_url('stock-items/update/' . $item['id']) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Barang Stok</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Barang</label>
                        <input type="text" name="item_code" class="form-control" value="<?= esc($item['item_code']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" name="name" class="form-control" value="<?= esc($item['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-select" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $category['id'] == $item['category_id'] ? 'selected' : '' ?>><?= esc($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit / Departemen</label>
                        <select name="unit_id" class="form-select" required>
                            <?php foreach ($units as $unit): ?>
                                <option value="<?= $unit['id'] ?>" <?= $unit['id'] == $item['unit_id'] ? 'selected' : '' ?>><?= esc($unit['name']) ?></option>
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
                        <input type="text" name="satuan" class="form-control" value="<?= esc($item['satuan']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2"><?= esc($item['description']) ?></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="editIsActive" <?= $item['is_active'] ? 'checked' : '' ?>>
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
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form id="stockInForm" method="post" action="<?= base_url('stock-items/stock-in/' . $item['id']) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Stok Masuk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="quantity" min="1" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="datetime-local" name="transaction_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
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
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form id="stockOutForm" method="post" action="<?= base_url('stock-items/stock-out/' . $item['id']) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Stok Keluar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger py-2 mb-3">
                        Stok tersedia: <strong><?= esc($item['quantity']) ?> <?= esc($item['satuan']) ?></strong>
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
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="quantity" min="1" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="datetime-local" name="transaction_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
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

<!-- MODAL PINDAH LOKASI -->
<div class="modal fade" id="transferModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form id="transferForm" method="post" action="<?= base_url('stock-items/transfer/' . $item['id']) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Pindah Stok</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        Seluruh stok (<?= esc($item['quantity']) ?> <?= esc($item['satuan']) ?>) akan dipindahkan ke unit/lokasi tujuan.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi Tujuan</label>
                        <select name="to_location_id" id="transferToLocation" class="form-select">
                            <option value="">-- Tetap di Lokasi Saat Ini --</option>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?= $location['id'] ?>"><?= esc($location['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Kosongkan bila hanya pindah unit dalam lokasi yang sama.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Unit Tujuan</label>
                        <select name="to_unit_id" id="transferToUnit" class="form-select" required disabled>
                            <option value="">-- Pilih Unit --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="datetime-local" name="transaction_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
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
                    <button type="submit" class="btn btn-primary">Pindahkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL PENYESUAIAN -->
<div class="modal fade" id="adjustmentModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form id="adjustmentForm" method="post" action="<?= base_url('stock-items/adjustment/' . $item['id']) ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Penyesuaian Stok</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2 mb-3">
                        Stok tersedia: <strong><?= esc($item['quantity']) ?> <?= esc($item['satuan']) ?></strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Penyesuaian</label>
                        <select name="adjustment_type" class="form-select" required>
                            <option value="Naik">Naik</option>
                            <option value="Turun">Turun</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="quantity" min="1" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="datetime-local" name="transaction_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan</label>
                        <input type="text" name="reason" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="documentModal" tabindex="-1"><div class="modal-dialog modal-dialog-scrollable"><form class="modal-content" method="post" enctype="multipart/form-data" action="<?= base_url('attachments/document/stock_item/' . $item['id']) ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Dokumen</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input name="document_type" class="form-control mb-2" placeholder="Jenis dokumen" required><input name="document_number" class="form-control mb-2" placeholder="Nomor dokumen"><input name="document_date" type="date" class="form-control mb-2"><input name="valid_until" type="date" class="form-control mb-2" placeholder="Berlaku sampai"><input name="file" type="file" class="form-control" accept=".pdf,image/*" required></div><div class="modal-footer"><button class="btn btn-primary">Simpan</button></div></form></div></div>
<div class="modal fade" id="photoModal" tabindex="-1"><div class="modal-dialog modal-dialog-scrollable"><form class="modal-content" method="post" enctype="multipart/form-data" action="<?= base_url('attachments/photo/stock_item/' . $item['id']) ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Foto</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input name="caption" class="form-control mb-2" placeholder="Kondisi/keterangan foto"><input name="file" type="file" class="form-control" accept="image/*" capture="environment" required></div><div class="modal-footer"><button class="btn btn-primary">Simpan Foto</button></div></form></div></div>
<div class="modal fade" id="evidenceModal" tabindex="-1"><div class="modal-dialog modal-dialog-scrollable"><form class="modal-content" method="post" enctype="multipart/form-data" action="<?= base_url('attachments/evidence/0') ?>" id="evidenceForm"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Bukti</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label">Jenis Bukti</label><select name="evidence_type" class="form-select mb-3"><option>Foto</option><option>Dokumen</option></select><label class="form-label">File (PDF/JPG/PNG/WebP, max 10 MB)</label><input name="file" type="file" accept=".pdf,image/jpeg,image/png,image/webp" capture="environment" class="form-control mb-3" required><label class="form-label">Keterangan</label><textarea name="notes" class="form-control"></textarea></div><div class="modal-footer"><button class="btn btn-primary">Upload Bukti</button></div></form></div></div>

<script>
(function () {
    'use strict';

    var history = <?= json_encode($history, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var satuan = <?= json_encode($item['satuan']) ?>;
    var locationsMap = <?= json_encode(array_column($locations, 'name', 'id'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var unitsMap = <?= json_encode(array_column($units, 'name', 'id'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var inTypes = ['Masuk', 'Penyesuaian Naik'];
    var outTypes = ['Keluar', 'Penyesuaian Turun'];

    function quantityBadge(value) {
        return value > 0
            ? '<span class="badge bg-success fs-6">' + Inventaris.esc(value) + ' ' + Inventaris.esc(satuan) + '</span>'
            : '<span class="badge bg-danger fs-6">' + Inventaris.esc(value) + ' ' + Inventaris.esc(satuan) + '</span>';
    }

    function setQuantity(value) {
        document.getElementById('stock-quantity').innerHTML = quantityBadge(value);
    }

    function txBadge(type) {
        var cls = inTypes.indexOf(type) !== -1 ? 'success'
            : (outTypes.indexOf(type) !== -1 ? 'danger' : 'primary');
        return '<span class="badge bg-' + cls + '">' + Inventaris.esc(type) + '</span>';
    }

    function locationHtml(row) {
        if (row.transaction_type === 'Pindah') {
            return Inventaris.esc(row.from_location_name || '-') + ' (' + Inventaris.esc(row.from_unit_name || '-') + ') &rarr; ' +
                Inventaris.esc(row.to_location_name || '-') + ' (' + Inventaris.esc(row.to_unit_name || '-') + ')';
        }
        if (inTypes.indexOf(row.transaction_type) !== -1) {
            return Inventaris.esc(row.to_location_name || '-') + ' (' + Inventaris.esc(row.to_unit_name || '-') + ')';
        }
        return Inventaris.esc(row.from_location_name || '-') + ' (' + Inventaris.esc(row.from_unit_name || '-') + ')';
    }

    var dt = Inventaris.datatable('#tabel-riwayat', {
        serverSide: false,
        data: history,
        order: [[0, 'asc']],
        columns: [
            { data: 'transaction_date' },
            { data: 'transaction_type', render: function (data) { return txBadge(data); } },
            { data: null, render: function (data, type, row) {
                return inTypes.indexOf(row.transaction_type) !== -1
                    ? '<span class="badge bg-success">+' + Inventaris.esc(row.quantity) + ' ' + Inventaris.esc(satuan) + '</span>'
                    : '';
            } },
            { data: null, render: function (data, type, row) {
                return outTypes.indexOf(row.transaction_type) !== -1
                    ? '<span class="badge bg-danger">-' + Inventaris.esc(row.quantity) + ' ' + Inventaris.esc(satuan) + '</span>'
                    : '';
            } },
            { data: 'balance', render: function (data) { return '<strong>' + Inventaris.esc(data) + ' ' + Inventaris.esc(satuan) + '</strong>'; } },
            { data: null, render: function (data, type, row) { return locationHtml(row); } },
            { data: null, render: function (data, type, row) { return Inventaris.esc(row.reason || row.notes || '-'); } },
            { data: null, orderable: false, searchable: false, render: function (data, type, row) {
                return '<a href="' + Inventaris.baseUrl + 'stock-movements/' + row.id + '" class="btn btn-sm btn-info">Detail</a> ' +
                    '<button type="button" class="btn btn-sm btn-primary btn-evidence" data-id="' + row.id + '">Bukti</button>';
            } }
        ]
    });

    setQuantity(<?= (int) $item['quantity'] ?>);

    function afterTransaction(json, modalId, form) {
        Inventaris.hideModal(modalId);
        if (form) form.reset();
        setQuantity(json.data.quantity);
        dt.row.add({
            id: json.data.transaction.id,
            transaction_code: json.data.transaction.transaction_code,
            transaction_date: json.data.transaction.transaction_date,
            transaction_type: json.data.transaction.transaction_type,
            quantity: json.data.transaction.quantity,
            balance: json.data.quantity,
            from_location_name: json.data.transaction.from_location_name,
            to_location_name: json.data.transaction.to_location_name,
            from_unit_name: json.data.transaction.from_unit_name,
            to_unit_name: json.data.transaction.to_unit_name,
            reason: json.data.transaction.reason,
            notes: json.data.transaction.notes,
            created_by_name: json.data.transaction.created_by_name
        }).draw();
    }

    document.getElementById('editStockForm').addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function () {
                Inventaris.hideModal('editStockModal');
                location.reload();
            }
        });
    });

    document.getElementById('stockInForm').addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function (json) { afterTransaction(json, 'stockInModal', e.target); }
        });
    });

    document.getElementById('stockOutForm').addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function (json) { afterTransaction(json, 'stockOutModal', e.target); }
        });
    });

    document.getElementById('transferForm').addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function (json) {
                Inventaris.hideModal('transferModal');
                e.target.reset();
                document.getElementById('stock-location').textContent = locationsMap[json.data.location_id] || '-';
                transferToUnit.disabled = true;
                transferToUnit.innerHTML = '<option value="">-- Pilih Unit --</option>';
                dt.row.add({
                    id: json.data.transaction.id,
                    transaction_code: json.data.transaction.transaction_code,
                    transaction_date: json.data.transaction.transaction_date,
                    transaction_type: json.data.transaction.transaction_type,
                    quantity: json.data.transaction.quantity,
                    balance: json.data.transaction.quantity,
                    from_location_name: json.data.transaction.from_location_name,
                    to_location_name: json.data.transaction.to_location_name,
                    from_unit_name: json.data.transaction.from_unit_name,
                    to_unit_name: json.data.transaction.to_unit_name,
                    reason: json.data.transaction.reason,
                    notes: json.data.transaction.notes,
                    created_by_name: json.data.transaction.created_by_name
                }).draw();
            }
        });
    });

    var transferToLocation = document.getElementById('transferToLocation');
    var transferToUnit = document.getElementById('transferToUnit');
    var currentLocationId = <?= (int) $item['location_id'] ?>;

    function loadTransferUnits(locationId) {
        transferToUnit.disabled = !locationId;
        transferToUnit.innerHTML = '<option value="">-- Pilih Unit --</option>';
        if (!locationId) return;
        Inventaris.fetchJson(Inventaris.baseUrl + 'stock-items/units-by-location/' + locationId)
            .then(function (units) {
                units.forEach(function (u) {
                    var opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.name + (u.id === <?= (int) $item['unit_id'] ?> ? ' (saat ini)' : '');
                    transferToUnit.appendChild(opt);
                });
            })
            .catch(function () {
                Inventaris.toast('Gagal memuat daftar unit.', 'danger');
            });
    }

    transferToLocation.addEventListener('change', function () {
        loadTransferUnits(this.value || currentLocationId);
    });

    document.getElementById('transferModal').addEventListener('show.bs.modal', function () {
        loadTransferUnits(transferToLocation.value || currentLocationId);
    });

    document.getElementById('adjustmentForm').addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function (json) { afterTransaction(json, 'adjustmentModal', e.target); }
        });
    });

    document.getElementById('tabel-riwayat').addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-evidence');
        if (!btn) return;
        var evidenceForm = document.getElementById('evidenceForm');
        evidenceForm.action = Inventaris.baseUrl + 'attachments/evidence/' + btn.getAttribute('data-id');
        evidenceForm.reset();
        Inventaris.openModal('evidenceModal');
    });
})();
</script>
<?= view('layout/footer') ?>

