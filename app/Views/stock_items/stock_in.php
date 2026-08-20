<?= view('layout/header', ['title' => 'Stok Masuk']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Stok Masuk</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <div class="alert alert-info">
                <strong><?= esc($item['item_code']) ?></strong>
                - <?= esc($item['name']) ?>
                (Stok saat ini:
                <?= esc($item['quantity']) ?> <?= esc($item['satuan']) ?>)
            </div>

            <form method="post"
                action="<?= base_url('stock-items/stock-in/' . $item['id']) ?>">

                <?= csrf_field() ?>

                <?php $errors = session()->getFlashdata('errors') ?? []; ?>

                <?php if (session()->getFlashdata('error')): ?>

                    <div class="alert alert-danger">
                        <?= esc(session()->getFlashdata('error')) ?>
                    </div>

                <?php endif; ?>

                <div class="row">

                    <!-- Jumlah -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Jumlah Masuk</label>

                        <input type="number"
                            name="quantity"
                            min="1"
                            class="form-control <?= isset($errors['quantity']) ? 'is-invalid' : '' ?>"
                            value="<?= esc(old('quantity')) ?>"
                            placeholder="Contoh: 10"
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
                            placeholder="Contoh: Pembelian bulanan">
                    </div>

                </div>

                <button type="submit"
                    class="btn btn-success">
                    Simpan Stok Masuk
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
