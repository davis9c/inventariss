<?= view('layout/header', ['title' => 'Detail Transaksi']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3>Detail Transaksi</h3>
            <p class="text-muted mb-0">
                Informasi lengkap pergerakan barang.
            </p>
        </div>

        <div>
            <a href="<?= base_url('stock-movements') ?>"
                class="btn btn-secondary">
                Kembali
            </a>
        </div>

    </div>

    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <strong>Informasi Transaksi</strong>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6">

                    <table class="table table-borderless">

                        <tr>
                            <th width="40%">Kode Transaksi</th>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= esc($transaction['transaction_code']) ?>
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th>Tanggal</th>
                            <td>
                                <?= esc(waktu_utc7($transaction['transaction_date'])) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Jenis Transaksi</th>
                            <td>
                                <?php
                                $typeBadge = match ($transaction['transaction_type']) {
                                    'Masuk' => 'success',
                                    'Penyesuaian Naik' => 'success',
                                    'Pengembalian' => 'success',
                                    'Keluar' => 'danger',
                                    'Penyesuaian Turun' => 'danger',
                                    'Keluar Perusahaan' => 'danger',
                                    'Pindah', 'Mutasi' => 'primary',
                                    'Perolehan' => 'info',
                                    default => 'secondary',
                                };
                                ?>

                                <span class="badge bg-<?= $typeBadge ?>">
                                    <?= esc($transaction['transaction_type']) ?>
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th>Jenis Barang</th>
                            <td>
                                <?= esc($transaction['item_type']) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Barang</th>
                            <td>
                                <?php if ($transaction['item_type'] === 'Aset'): ?>
                                    <strong><?= esc($transaction['asset_name']) ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        Kode: <?= esc($transaction['asset_code']) ?>
                                    </small>
                                <?php else: ?>
                                    <strong><?= esc($transaction['item_name']) ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        Kode: <?= esc($transaction['item_code']) ?>
                                        (<?= esc($transaction['satuan'] ?? '-') ?>)
                                    </small>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Quantity</th>
                            <td>
                                <?= esc($transaction['quantity']) ?>
                            </td>
                        </tr>

                    </table>

                </div>

                <div class="col-md-6">

                    <table class="table table-borderless">

                        <tr>
                            <th width="40%">Lokasi Asal</th>
                            <td>
                                <?= esc($transaction['from_location_name'] ?? '-') ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Unit Asal</th>
                            <td>
                                <?= esc($transaction['from_unit_name'] ?? '-') ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Lokasi Tujuan</th>
                            <td>
                                <?= esc($transaction['to_location_name'] ?? '-') ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Unit Tujuan</th>
                            <td>
                                <?= esc($transaction['to_unit_name'] ?? '-') ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Alasan</th>
                            <td>
                                <?= esc($transaction['reason'] ?? '-') ?>
                            </td>
                        </tr>
                        <?php if (in_array($transaction['transaction_type'], ['Keluar', 'Keluar Perusahaan'])): ?>
                        <tr><th>Jenis/Tujuan</th><td><?= esc(($transaction['outbound_type'] ?? '-') . ' / ' . ($transaction['recipient_name'] ?? '-')) ?></td></tr>
                        <tr><th>Unit & Dokumen</th><td><?= esc(($transaction['destination_unit'] ?? '-') . ' / ' . ($transaction['document_number'] ?? '-')) ?></td></tr>
                        <tr><th>Serah Terima</th><td><?= esc(($transaction['handed_over_by'] ?? '-') . ' / ' . ($transaction['received_by'] ?? '-')) ?></td></tr>
                        <?php endif; ?>

                        <tr>
                            <th>Catatan</th>
                            <td>
                                <?= nl2br(esc($transaction['notes'] ?? '-')) ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Dibuat Oleh</th>
                            <td>
                                <?= esc($transaction['created_by_name'] ?? '-') ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Dibuat Pada</th>
                            <td>
                                <?= esc(waktu_utc7($transaction['created_at'] ?? null)) ?>
                            </td>
                        </tr>

                    </table>

                </div>

            </div>

        </div>

    </div>

    <div class="card shadow-sm mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">

            <strong>Bukti Transaksi</strong>

            <button type="button"
                    class="btn btn-sm btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#evidenceModal">
                + Tambah Bukti
            </button>

        </div>

        <div class="card-body">

            <?php if (empty($evidence)): ?>

                <p class="text-muted mb-0">Belum ada bukti transaksi.</p>

            <?php else: ?>

                <ul class="mb-0">

                    <?php foreach ($evidence as $file): ?>

                        <li class="mb-2">
                            <span class="badge bg-<?= $file['evidence_type'] === 'Foto' ? 'info' : 'secondary' ?>">
                                <?= esc($file['evidence_type']) ?>
                            </span>
                            <a target="_blank"
                               href="<?= base_url('attachments/file/evidence/' . $file['id']) ?>">
                                <?= esc($file['original_name']) ?>
                            </a>
                            <?php if ($file['evidence_type'] === 'Foto'): ?>
                                <a target="_blank"
                                   href="<?= base_url('attachments/file/evidence/' . $file['id']) ?>">
                                    <img src="<?= base_url('attachments/file/evidence/' . $file['id']) ?>"
                                         class="img-thumbnail ms-2"
                                         style="max-width: 60px; max-height: 60px;"
                                         alt="<?= esc($file['original_name']) ?>">
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($file['notes'])): ?>
                                <small class="text-muted">— <?= esc($file['notes']) ?></small>
                            <?php endif; ?>
                            <small class="text-muted">(<?= esc(waktu_utc7($file['created_at'])) ?>)</small>
                        </li>

                    <?php endforeach; ?>

                </ul>

            <?php endif; ?>

        </div>

    </div>

</div>

<div class="modal fade" id="evidenceModal" tabindex="-1"><div class="modal-dialog modal-dialog-scrollable"><form class="modal-content" method="post" enctype="multipart/form-data" action="<?= base_url('attachments/evidence/' . $transaction['id']) ?>"><?= csrf_field() ?><div class="modal-header"><h5 class="modal-title">Tambah Bukti</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label">Jenis Bukti</label><select name="evidence_type" class="form-select mb-3"><option>Foto</option><option>Dokumen</option></select><label class="form-label">File (PDF/JPG/PNG/WebP, max 10 MB)</label><input name="file" type="file" accept=".pdf,image/jpeg,image/png,image/webp" capture="environment" class="form-control mb-3" required><label class="form-label">Keterangan</label><textarea name="notes" class="form-control"></textarea></div><div class="modal-footer"><button class="btn btn-primary">Upload Bukti</button></div></form></div></div>

<?= view('layout/footer') ?>
