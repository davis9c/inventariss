<?= view('layout/header', ['title' => 'Stok Keluar']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Stok Keluar</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <div class="alert alert-info">
                <strong><?= esc($item['item_code']) ?></strong>
                - <?= esc($item['name']) ?>
                (Stok saat ini:
                <?= esc($item['quantity']) ?> <?= esc($item['satuan']) ?>)
            </div>

            <form method="post"
                action="<?= base_url('stock-items/stock-out/' . $item['id']) ?>">

                <?= csrf_field() ?>

                <?php $errors = session()->getFlashdata('errors') ?? []; ?>

                <?php if (session()->getFlashdata('error')): ?>

                    <div class="alert alert-danger">
                        <?= esc(session()->getFlashdata('error')) ?>
                    </div>

                <?php endif; ?>

                <div class="row">

                    <div class="col-md-4 mb-3"><label class="form-label">Jenis Pengeluaran</label><select name="outbound_type" class="form-select" required><option value="">-- Pilih --</option><?php foreach (['Pemindahan','Peminjaman','Hibah','Penjualan','Penghapusan','Retur','Lainnya'] as $type): ?><option value="<?= esc($type) ?>"><?= esc($type) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Tujuan/Penerima</label><input name="recipient_name" class="form-control" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Unit/Departemen Tujuan</label><input name="destination_unit" class="form-control"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Nomor Dokumen</label><input name="document_number" class="form-control"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Pihak Menyerahkan</label><input name="handed_over_by" class="form-control"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Pihak Menerima</label><input name="received_by" class="form-control"></div>

                    <!-- Jumlah -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Jumlah Keluar</label>

                        <input type="number"
                            name="quantity"
                            min="1"
                            max="<?= esc($item['quantity']) ?>"
                            class="form-control <?= isset($errors['quantity']) ? 'is-invalid' : '' ?>"
                            value="<?= esc(old('quantity')) ?>"
                            placeholder="Contoh: 4"
                            required>

                        <?php if (isset($errors['quantity'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['quantity']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tanggal -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Transaksi</label>

                        <input type="datetime-local"
                            name="transaction_date"
                            class="form-control <?= isset($errors['transaction_date']) ? 'is-invalid' : '' ?>"
                            value="<?= esc(old('transaction_date', date('Y-m-d'))) ?>"
                            required>

                        <?php if (isset($errors['transaction_date'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['transaction_date']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Keterangan -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Keterangan</label>

                        <input type="text"
                            name="notes"
                            class="form-control"
                            value="<?= esc(old('notes')) ?>"
                            placeholder="Contoh: Dipakai rapat">
                    </div>

                </div>

                <button type="submit"
                    class="btn btn-danger">
                    Simpan Stok Keluar
                </button>

                <a href="<?= base_url('stock-items/' . $item['id']) ?>"
                    class="btn btn-secondary">
                    Batal
                </a>

            </form>

        </div>
    </div>

</div>

<?= view('layout/footer') ?>
