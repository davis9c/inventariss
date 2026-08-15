<?= view('layout/header', ['title' => 'Penyesuaian Stok']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Penyesuaian Stok</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <div class="alert alert-info">
                <strong><?= esc($item['item_code']) ?></strong>
                - <?= esc($item['name']) ?>
                (Stok saat ini:
                <?= esc($item['quantity']) ?> <?= esc($item['satuan']) ?>)
            </div>

            <form method="post"
                action="<?= base_url('stock-items/adjustment/' . $item['id']) ?>">

                <?= csrf_field() ?>

                <?php $errors = session()->getFlashdata('errors') ?? []; ?>

                <?php if (session()->getFlashdata('error')): ?>

                    <div class="alert alert-danger">
                        <?= esc(session()->getFlashdata('error')) ?>
                    </div>

                <?php endif; ?>

                <div class="row">

                    <!-- Jenis -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Jenis Penyesuaian</label>

                        <select name="adjustment_type"
                            class="form-select <?= isset($errors['adjustment_type']) ? 'is-invalid' : '' ?>"
                            required>

                            <option value="Naik"
                                <?= old('adjustment_type') === 'Turun' ? '' : 'selected' ?>>
                                Penambahan (Naik)
                            </option>

                            <option value="Turun"
                                <?= old('adjustment_type') === 'Turun' ? 'selected' : '' ?>>
                                Pengurangan (Turun)
                            </option>

                        </select>

                        <?php if (isset($errors['adjustment_type'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['adjustment_type']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Jumlah -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Jumlah</label>

                        <input type="number"
                            name="quantity"
                            min="1"
                            class="form-control <?= isset($errors['quantity']) ? 'is-invalid' : '' ?>"
                            value="<?= esc(old('quantity')) ?>"
                            required>

                        <?php if (isset($errors['quantity'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['quantity']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tanggal -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tanggal Transaksi</label>

                        <input type="date"
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

                    <!-- Alasan -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Alasan</label>

                        <input type="text"
                            name="reason"
                            class="form-control <?= isset($errors['reason']) ? 'is-invalid' : '' ?>"
                            value="<?= esc(old('reason')) ?>"
                            placeholder="Wajib diisi"
                            required>

                        <?php if (isset($errors['reason'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['reason']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>

                <button type="submit"
                    class="btn btn-warning">
                    Simpan Penyesuaian
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
