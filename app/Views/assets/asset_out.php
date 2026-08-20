<?= view('layout/header', ['title' => 'Barang Keluar Perusahaan']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Barang Keluar Perusahaan</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <div class="alert alert-info">
                <strong><?= esc($asset['asset_code']) ?></strong>
                - <?= esc($asset['name']) ?>
            </div>

            <p class="text-muted">
                Barang yang dicatat keluar akan berada di luar tanggung jawab
                perusahaan (misal: dikirim ke vendor, diberikan, atau dikeluarkan).
                Lokasi tujuan eksternal tidak perlu dibuat sebagai lokasi internal.
            </p>

            <form method="post"
                action="<?= base_url('assets/asset-out/' . $asset['id']) ?>">

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

                    <!-- Tanggal -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Keluar</label>

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

                    <!-- Alasan -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Alasan</label>

                        <input type="text"
                            name="reason"
                            class="form-control"
                            value="<?= esc(old('reason')) ?>"
                            placeholder="Contoh: Dikirim ke vendor">

                        <?php if (isset($errors['reason'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['reason']) ?>
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
                            placeholder="Tujuan eksternal, alamat, dll.">
                    </div>

                </div>

                <button type="submit"
                    class="btn btn-danger">
                    Simpan Barang Keluar
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
