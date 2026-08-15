<?= view('layout/header', ['title' => 'Pindah Stok']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Pindah Stok</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <div class="alert alert-info">
                <strong><?= esc($item['item_code']) ?></strong>
                - <?= esc($item['name']) ?>
                (Stok saat ini:
                <?= esc($item['quantity']) ?> <?= esc($item['satuan']) ?>)
            </div>

            <p class="text-muted">
                Seluruh stok barang ini akan dipindahkan ke lokasi tujuan.
            </p>

            <form method="post"
                action="<?= base_url('stock-items/transfer/' . $item['id']) ?>">

                <?= csrf_field() ?>

                <?php $errors = session()->getFlashdata('errors') ?? []; ?>

                <?php if (session()->getFlashdata('error')): ?>

                    <div class="alert alert-danger">
                        <?= esc(session()->getFlashdata('error')) ?>
                    </div>

                <?php endif; ?>

                <div class="row">

                    <!-- Lokasi tujuan -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Lokasi Tujuan</label>

                        <select name="to_location_id"
                            class="form-select <?= isset($errors['to_location_id']) ? 'is-invalid' : '' ?>"
                            required>

                            <option value="">-- Pilih Lokasi --</option>

                            <?php foreach ($locations as $location): ?>
                                <option value="<?= $location['id'] ?>"
                                    <?= (string) old('to_location_id') === (string) $location['id'] ? 'selected' : '' ?>>
                                    <?= esc($location['name']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                        <?php if (isset($errors['to_location_id'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['to_location_id']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tanggal -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Pindah</label>

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
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Alasan</label>

                        <input type="text"
                            name="reason"
                            class="form-control"
                            value="<?= esc(old('reason')) ?>"
                            placeholder="Contoh: Pindah gudang">

                        <?php if (isset($errors['reason'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['reason']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Keterangan -->
                    <div class="col-12 mb-3">
                        <label class="form-label">Keterangan</label>

                        <textarea name="notes"
                            class="form-control"
                            rows="2"><?= esc(old('notes')) ?></textarea>
                    </div>

                </div>

                <button type="submit"
                    class="btn btn-primary">
                    Simpan Pindah Stok
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
