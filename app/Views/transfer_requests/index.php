<?= view('layout/header', ['title' => 'Permintaan Mutasi']) ?>
<?= view('layout/navbar') ?>

<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3>Permintaan Mutasi</h3>
            <p class="text-muted mb-0">Mutasi ke luar tanggung jawab dan permintaan barang antar lokasi.</p>
        </div>
        <a href="<?= base_url('transfer-requests/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Buat Permintaan
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <!-- MASUK -->
    <div class="card shadow-sm mb-4">
        <div class="card-header"><strong>Permintaan Masuk (ke lokasi Anda)</strong></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Barang</th>
                            <th>Dari</th>
                            <th>Ke</th>
                            <th>Jumlah</th>
                            <th>Peminta</th>
                            <th>Bukti</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($incoming)): ?>
                            <tr><td colspan="8" class="text-center text-muted">Tidak ada permintaan masuk.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($incoming as $r): ?>
                            <tr>
                                <td><?= esc($r['created_at']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $r['request_kind'] === 'Penawaran' ? 'info' : 'warning' ?>">
                                        <?= esc($r['request_kind']) ?>
                                    </span>
                                </td>
                                <td><?= esc($r['item_name']) ?></td>
                                <td><?= esc($r['from_location_name']) ?></td>
                                <td><?= esc($r['to_location_name']) ?></td>
                                <td><?= esc($r['quantity']) ?></td>
                                <td><?= esc($r['requester_name']) ?></td>
                                <td>
                                    <?php if (!empty($r['evidence_path'])): ?>
                                        <a href="<?= base_url('transfer-requests/evidence/' . $r['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat</a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                    <form method="post" action="<?= base_url('transfer-requests/evidence/' . $r['id']) ?>" enctype="multipart/form-data" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="file" name="evidence" class="d-none" onchange="this.form.submit()" accept="image/*,application/pdf">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="this.previousElementSibling.click()">Unggah</button>
                                    </form>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-success btn-confirm" data-id="<?= $r['id'] ?>">Setujui</button>
                                    <button class="btn btn-sm btn-danger btn-reject" data-id="<?= $r['id'] ?>">Tolak</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- KELUAR -->
    <div class="card shadow-sm mb-4">
        <div class="card-header"><strong>Permintaan Keluar (dari lokasi Anda)</strong></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Barang</th>
                            <th>Dari</th>
                            <th>Ke</th>
                            <th>Jumlah</th>
                            <th>Bukti</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($outgoing)): ?>
                            <tr><td colspan="8" class="text-center text-muted">Tidak ada permintaan keluar.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($outgoing as $r): ?>
                            <tr>
                                <td><?= esc($r['created_at']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $r['request_kind'] === 'Penawaran' ? 'info' : 'warning' ?>">
                                        <?= esc($r['request_kind']) ?>
                                    </span>
                                </td>
                                <td><?= esc($r['item_name']) ?></td>
                                <td><?= esc($r['from_location_name']) ?></td>
                                <td><?= esc($r['to_location_name']) ?></td>
                                <td><?= esc($r['quantity']) ?></td>
                                <td>
                                    <?php if (!empty($r['evidence_path'])): ?>
                                        <a href="<?= base_url('transfer-requests/evidence/' . $r['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat</a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($r['status']) ?></td>
                                <td>
                                    <?php if ($r['status'] === 'Pending'): ?>
                                        <button class="btn btn-sm btn-outline-danger btn-cancel" data-id="<?= $r['id'] ?>">Batalkan</button>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';
    function post(url) {
        return fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        }).then(r => r.json());
    }

    document.querySelectorAll('.btn-confirm').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!confirm('Setujui permintaan ini?')) return;
            post('<?= base_url('transfer-requests/confirm/') ?>' + this.dataset.id)
                .then(j => { if (j.success) location.reload(); else alert(j.message); });
        });
    });
    document.querySelectorAll('.btn-reject').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!confirm('Tolak permintaan ini?')) return;
            post('<?= base_url('transfer-requests/reject/') ?>' + this.dataset.id)
                .then(j => { if (j.success) location.reload(); else alert(j.message); });
        });
    });
    document.querySelectorAll('.btn-cancel').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!confirm('Batalkan permintaan ini?')) return;
            post('<?= base_url('transfer-requests/cancel/') ?>' + this.dataset.id)
                .then(j => { if (j.success) location.reload(); else alert(j.message); });
        });
    });
})();
</script>

<?= view('layout/footer') ?>