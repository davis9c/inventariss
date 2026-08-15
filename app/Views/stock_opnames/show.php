<?= view('layout/header', ['title' => 'Detail Stock Opname']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3>
                <?= esc($opname['opname_code']) ?>
            </h3>

            <p class="text-muted mb-0">

                Tanggal:
                <?= esc($opname['opname_date']) ?>

                |

                Lokasi:

                <?php if ($opname['location_id']): ?>
                    <?= esc($opname['building']) ?>
                    -
                    <?= esc($opname['room']) ?>
                <?php else: ?>
                    Semua lokasi
                <?php endif; ?>

                |

                Status:
                <span id="opname-status-badge"></span>

            </p>

        </div>

        <div>
            <a href="<?= base_url('stock-opnames') ?>"
               class="btn btn-secondary">
                Kembali
            </a>

            <button type="button"
                   class="btn btn-success"
                   id="btnFinishOpname"
                   <?= $opname['status'] === 'Selesai' ? 'hidden' : '' ?>>
                Selesaikan Opname
            </button>
        </div>

    </div>

    <div class="card shadow-sm">

        <div class="card-header">
            <strong>Pemeriksaan Aset</strong>
        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle" id="tabel-asset-check">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Barang</th>
                            <th>Serial Number</th>
                            <th>Ditemukan</th>
                            <th>Kondisi</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($details as $i => $detail): ?>

                        <tr id="detail-<?= $detail['id'] ?>">

                            <td><?= $i + 1 ?></td>

                            <td>
                                <strong><?= esc($detail['asset_name']) ?></strong>
                                <br>
                                <small class="text-muted"><?= esc($detail['asset_code']) ?></small>
                            </td>

                            <td><?= esc($detail['serial_number'] ?? '-') ?></td>

                            <td id="found-<?= $detail['id'] ?>">
                                <?php if ($detail['is_found']): ?>
                                    <span class="badge bg-success">Ditemukan</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Tidak Ditemukan</span>
                                <?php endif; ?>
                            </td>

                            <td id="condition-<?= $detail['id'] ?>">
                                <span class="badge bg-<?= match ($detail['condition_status']) {
                                    'Baik' => 'success',
                                    'Rusak Ringan' => 'warning',
                                    'Rusak Berat' => 'danger',
                                    default => 'secondary',
                                } ?>">
                                    <?= esc($detail['condition_status']) ?>
                                </span>
                            </td>

                            <td id="note-<?= $detail['id'] ?>">
                                <?= esc($detail['notes'] ?? '') ?>
                            </td>

                            <td>
                                <button type="button"
                                        class="btn btn-sm btn-primary btn-check-asset"
                                        data-id="<?= $detail['id'] ?>"
                                        data-name="<?= esc($detail['asset_name'], 'attr') ?>"
                                        data-found="<?= (int) $detail['is_found'] ?>"
                                        data-condition="<?= esc($detail['condition_status'], 'attr') ?>"
                                        data-notes="<?= esc($detail['notes'] ?? '', 'attr') ?>"
                                        <?= $opname['status'] === 'Selesai' ? 'disabled' : '' ?>>
                                    Periksa
                                </button>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <div class="card shadow-sm mt-4">

        <div class="card-header">
            <strong>Barang Stok (Perhitungan Fisik)</strong>
        </div>

        <div class="card-body">

            <p class="text-muted">
                Hitung jumlah fisik barang stok dan bandingkan dengan stok sistem.
            </p>

            <?php if (empty($stockDetails)): ?>

                <div class="text-center text-muted py-4">
                    Tidak ada barang stok pada opname ini.
                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-hover align-middle" id="tabel-stock-check">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Barang</th>
                                <th>Satuan</th>
                                <th>Stok Sistem</th>
                                <th>Fisik</th>
                                <th>Selisih</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php foreach ($stockDetails as $i => $detail): ?>

                            <?php
                            $diff = $detail['physical_qty'] === null
                                ? null
                                : (int) $detail['physical_qty'] - (int) $detail['system_qty'];
                            ?>

                            <tr id="stock-detail-<?= $detail['id'] ?>">

                                <td><?= $i + 1 ?></td>

                                <td>
                                    <strong><?= esc($detail['item_name']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= esc($detail['item_code']) ?></small>
                                </td>

                                <td><?= esc($detail['satuan']) ?></td>

                                <td><?= esc($detail['system_qty']) ?></td>

                                <td id="physical-<?= $detail['id'] ?>">
                                    <?= $detail['physical_qty'] === null ? '-' : esc($detail['physical_qty']) ?>
                                </td>

                                <td id="diff-<?= $detail['id'] ?>">
                                    <?php if ($diff === null): ?>
                                        <span class="text-muted">-</span>
                                    <?php elseif ($diff === 0): ?>
                                        <span class="badge bg-success">0</span>
                                    <?php elseif ($diff > 0): ?>
                                        <span class="badge bg-primary">+<?= $diff ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><?= $diff ?></span>
                                    <?php endif; ?>
                                </td>

                                <td id="stock-note-<?= $detail['id'] ?>">
                                    <?= esc($detail['notes'] ?? '') ?>
                                </td>

                                <td>
                                    <button type="button"
                                            class="btn btn-sm btn-primary btn-check-stock"
                                            data-id="<?= $detail['id'] ?>"
                                            data-name="<?= esc($detail['item_name'], 'attr') ?>"
                                            data-system="<?= (int) $detail['system_qty'] ?>"
                                            data-physical="<?= esc($detail['physical_qty'] ?? '', 'attr') ?>"
                                            data-notes="<?= esc($detail['notes'] ?? '', 'attr') ?>"
                                            <?= $opname['status'] === 'Selesai' ? 'disabled' : '' ?>>
                                        Periksa
                                    </button>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<!-- MODAL PERIKSA ASET -->
<div class="modal fade" id="checkAssetModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="checkAssetForm" method="post" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Periksa Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3" id="checkAssetInfo"></div>
                    <div class="mb-3">
                        <label class="form-label">Ditemukan</label>
                        <select name="is_found" class="form-select" required>
                            <option value="1">Ditemukan</option>
                            <option value="0">Tidak Ditemukan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kondisi</label>
                        <select name="condition_status" class="form-select" required>
                            <option value="Baik">Baik</option>
                            <option value="Rusak Ringan">Rusak Ringan</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
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

<!-- MODAL PERIKSA STOK -->
<div class="modal fade" id="checkStockModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="checkStockForm" method="post" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title">Hitung Fisik</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3" id="checkStockInfo"></div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Fisik</label>
                        <input type="number" name="physical_qty" min="0" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
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

    var status = <?= json_encode($opname['status']) ?>;

    function statusBadge(value) {
        return value === 'Selesai'
            ? '<span class="badge bg-success">Selesai</span>'
            : '<span class="badge bg-warning text-dark">Draft</span>';
    }

    function foundBadge(value) {
        return value
            ? '<span class="badge bg-success">Ditemukan</span>'
            : '<span class="badge bg-danger">Tidak Ditemukan</span>';
    }

    function conditionBadge(value) {
        var map = { 'Baik': 'success', 'Rusak Ringan': 'warning', 'Rusak Berat': 'danger' };
        return '<span class="badge bg-' + (map[value] || 'secondary') + '">' + Inventaris.esc(value) + '</span>';
    }

    function diffBadge(diff) {
        if (diff === null || diff === undefined) return '<span class="text-muted">-</span>';
        if (diff === 0) return '<span class="badge bg-success">0</span>';
        if (diff > 0) return '<span class="badge bg-primary">+' + diff + '</span>';
        return '<span class="badge bg-danger">' + diff + '</span>';
    }

    function setStatus(value) {
        status = value;
        document.getElementById('opname-status-badge').innerHTML = statusBadge(value);
        document.getElementById('btnFinishOpname').hidden = value === 'Selesai';
        if (value === 'Selesai') {
            document.querySelectorAll('#tabel-asset-check .btn-check-asset, #tabel-stock-check .btn-check-stock')
                .forEach(function (btn) { btn.disabled = true; });
        }
    }

    setStatus(status);

    var checkAssetForm = document.getElementById('checkAssetForm');
    var checkStockForm = document.getElementById('checkStockForm');

    document.getElementById('tabel-asset-check').addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-check-asset');
        if (!btn) return;
        var id = btn.getAttribute('data-id');
        checkAssetForm.action = Inventaris.baseUrl + 'stock-opnames/detail/' + id + '/update';
        document.getElementById('checkAssetInfo').innerHTML = '<strong>' + Inventaris.esc(btn.getAttribute('data-name')) + '</strong>';
        checkAssetForm.elements['is_found'].value = btn.getAttribute('data-found');
        checkAssetForm.elements['condition_status'].value = btn.getAttribute('data-condition');
        checkAssetForm.elements['notes'].value = btn.getAttribute('data-notes');
        checkAssetForm.setAttribute('data-detail-id', id);
        Inventaris.openModal('checkAssetModal');
    });

    checkAssetForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(checkAssetForm, {
            onSuccess: function (json) {
                Inventaris.hideModal('checkAssetModal');
                var id = checkAssetForm.getAttribute('data-detail-id');
                document.getElementById('found-' + id).innerHTML = foundBadge(json.data.is_found);
                document.getElementById('condition-' + id).innerHTML = conditionBadge(json.data.condition_status);
                document.getElementById('note-' + id).textContent = json.data.notes || '';
            }
        });
    });

    document.getElementById('tabel-stock-check').addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-check-stock');
        if (!btn) return;
        var id = btn.getAttribute('data-id');
        checkStockForm.action = Inventaris.baseUrl + 'stock-opnames/stock-detail/' + id + '/update';
        document.getElementById('checkStockInfo').innerHTML =
            '<strong>' + Inventaris.esc(btn.getAttribute('data-name')) + '</strong><br>' +
            '<small class="text-muted">Stok sistem: ' + Inventaris.esc(btn.getAttribute('data-system')) + '</small>';
        checkStockForm.elements['physical_qty'].value = btn.getAttribute('data-physical');
        checkStockForm.elements['notes'].value = btn.getAttribute('data-notes');
        checkStockForm.setAttribute('data-detail-id', id);
        Inventaris.openModal('checkStockModal');
    });

    checkStockForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(checkStockForm, {
            onSuccess: function (json) {
                Inventaris.hideModal('checkStockModal');
                var id = checkStockForm.getAttribute('data-detail-id');
                document.getElementById('physical-' + id).textContent = json.data.physical_qty;
                document.getElementById('diff-' + id).innerHTML = diffBadge(json.data.diff);
                document.getElementById('stock-note-' + id).textContent = json.data.notes || '';
            }
        });
    });

    document.getElementById('btnFinishOpname').addEventListener('click', function () {
        Inventaris.confirm({
            title: 'Selesaikan Opname',
            message: 'Selesaikan stock opname <?= esc($opname['opname_code']) ?>? Data pemeriksaan tidak dapat diubah setelah diselesaikan.',
            onConfirm: function () {
                Inventaris.fetchJson('<?= base_url('stock-opnames/' . $opname['id'] . '/finish') ?>', {}, 'POST')
                    .then(function (json) {
                        if (json.success) {
                            Inventaris.toast(json.message);
                            setStatus(json.data.status);
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

