<?= view('layout/header', ['title' => 'Detail Transaksi']) ?>
<?= view('layout/navbar') ?>

<div class="mb-4">

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
                                <?= esc($transaction['transaction_date']) ?>
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
                            <th>Lokasi Tujuan</th>
                            <td>
                                <?= esc($transaction['to_location_name'] ?? '-') ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Alasan</th>
                            <td>
                                <?= esc($transaction['reason'] ?? '-') ?>
                            </td>
                        </tr>

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
                                <?= esc($transaction['created_at'] ?? '-') ?>
                            </td>
                        </tr>

                    </table>

                </div>

            </div>

        </div>

    </div>

    <!-- ===================================================== -->
    <!-- LAMPIRAN: dokumen dan gambar catatan pergerakan       -->
    <!-- ===================================================== -->

    <?= view('partials/image_gallery', [
        'images'      => $images,
        'imageConfig' => $imageConfig,
        'imageBase'   => 'stock-movements',
        'imageOwner'  => 'Catatan pergerakan',
    ]) ?>

    <?= view('partials/document_list', [
        'documents'      => $documents,
        'documentConfig' => $documentConfig,
        'documentBase'   => 'stock-movements',
        'documentOwner'  => 'Catatan pergerakan',
    ]) ?>

    <?= view('partials/image_uploader', [
        'assetId'              => (int) $transaction['id'],
        'imageConfig'          => $imageConfig,
        'imageBase'            => 'stock-movements',
        'imageOwner'           => 'Catatan pergerakan',
        'imageFormId'          => 'uploadGambarMovementForm',
        'imageMaxOriginalBytes' => 15728640,
    ]) ?>

    <?= view('partials/document_uploader', [
        'documentConfig'  => $documentConfig,
        'documentBase'    => 'stock-movements',
        'documentOwner'   => 'Catatan pergerakan',
        'documentOwnerId' => (int) $transaction['id'],
        'documentFormId'  => 'uploadDokumenMovementForm',
    ]) ?>

</div>

<?= view('layout/footer') ?>
