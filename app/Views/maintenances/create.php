<?= view('layout/header', ['title' => 'Tambah Maintenance']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Tambah Maintenance</h3>

    <div class="card shadow-sm mt-4">

        <div class="card-body">

            <form method="post"
                  action="<?= base_url('maintenances/store') ?>">

                <?= csrf_field() ?>

                <div class="mb-3">

                    <label class="form-label">
                        Barang
                    </label>

                    <select name="asset_id"
        class="form-select"
        required>

    <option value="">
        -- Pilih Barang --
    </option>

    <?php foreach ($assets as $asset): ?>

        <option value="<?= $asset['id'] ?>"
            <?= ($selectedAssetId == $asset['id']) ? 'selected' : '' ?>>

            <?= esc($asset['asset_code']) ?>
            -
            <?= esc($asset['name']) ?>

        </option>

    <?php endforeach; ?>

</select>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Tanggal
                        </label>

                        <input type="date"
                               name="maintenance_date"
                               class="form-control"
                               value="<?= date('Y-m-d') ?>"
                               required>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Jenis
                        </label>

                        <select name="maintenance_type"
                                class="form-select"
                                required>

                            <option value="Preventive">
                                Preventive
                            </option>

                            <option value="Corrective">
                                Corrective / Perbaikan
                            </option>

                            <option value="Inspection">
                                Inspection
                            </option>

                        </select>

                    </div>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Masalah / Keluhan
                    </label>

                    <textarea name="problem"
                              class="form-control"
                              rows="3"></textarea>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Tindakan / Perbaikan
                    </label>

                    <textarea name="action_taken"
                              class="form-control"
                              rows="3"></textarea>

                </div>

                <div class="mb-3">

    <label class="form-label">
        Teknisi
    </label>

    <input type="text"
           class="form-control"
           value="<?= esc(session()->get('name')) ?>"
           readonly>

    <div class="form-text">
        Teknisi otomatis berdasarkan akun yang sedang login.
    </div>

</div>

                <div class="mb-3"
     id="vendor-field">

    <label class="form-label">
        Nama Vendor
    </label>

    <input type="text"
           name="vendor_name"
           class="form-control">

</div>

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Status
                        </label>

                        <select name="status"
                                class="form-select">

                            <option value="Diajukan">
                                Diajukan
                            </option>

                            <option value="Diproses">
                                Diproses
                            </option>

                            <option value="Selesai">
                                Selesai
                            </option>

                            <option value="Dibatalkan">
                                Dibatalkan
                            </option>

                        </select>

                    </div>

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Biaya
                        </label>

                        <input type="number"
                               name="cost"
                               class="form-control"
                               value="0"
                               min="0">

                    </div>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Tanggal Selesai
                    </label>

                    <input type="date"
                           name="completed_date"
                           class="form-control">

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Catatan
                    </label>

                    <textarea name="notes"
                              class="form-control"
                              rows="3"></textarea>

                </div>

                <a href="<?= base_url('maintenances') ?>"
                   class="btn btn-secondary">

                    Kembali

                </a>

                <button type="submit"
                        class="btn btn-primary">

                    Simpan

                </button>

            </form>

        </div>

    </div>

</div>

<?= view('layout/footer') ?>