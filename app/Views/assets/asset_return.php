<?= view('layout/header', ['title' => 'Pengembalian Barang']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Pengembalian Barang</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <div class="alert alert-info">
                <strong><?= esc($asset['asset_code']) ?></strong>
                - <?= esc($asset['name']) ?>
            </div>

            <p class="text-muted">
                Barang yang sebelumnya keluar dari tanggung jawab perusahaan
                dikembalikan ke lokasi semula dan kembali berstatus aktif.
            </p>

            <form method="post"
                action="<?= base_url('assets/asset-return/' . $asset['id']) ?>">

                <?= csrf_field() ?>

                <?php $errors = session()->getFlashdata('errors') ?? []; ?>

                <?php if (session()->getFlashdata('error')): ?>

                    <div class="alert alert-danger">
                        <?= esc(session()->getFlashdata('error')) ?>
                    </div>

                <?php endif; ?>

                <div class="row">

                    <!-- Tanggal -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Kembali</label>

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

                    <!-- Keterangan -->
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Keterangan</label>

                        <input type="text"
                            name="notes"
                            class="form-control"
                            value="<?= esc(old('notes')) ?>"
                            placeholder="Contoh: Selesai ditangani vendor">
                    </div>

                </div>

                <button type="submit"
                    class="btn btn-success">
                    Simpan Pengembalian
                </button>

                <a href="<?= base_url('assets/' . $asset['id']) ?>"
                    class="btn btn-secondary">
                    Batal
                </a>

            </form>

        </div>
    </div>

</div>

<?= view('layout/footer') ?>
