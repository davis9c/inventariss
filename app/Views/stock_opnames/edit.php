<?= view('layout/header', ['title' => 'Edit Unit']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Edit Unit</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <form method="post"
                  action="<?= base_url('units/update/' . $unit['id']) ?>">

                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label">Kode Unit</label>

                    <input type="text"
                           name="code"
                           class="form-control"
                           value="<?= esc($unit['code']) ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Unit</label>

                    <input type="text"
                           name="name"
                           class="form-control"
                           value="<?= esc($unit['name']) ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>

                    <textarea name="description"
                              class="form-control"
                              rows="3"><?= esc($unit['description'] ?? '') ?></textarea>
                </div>

                <div class="form-check mb-4">

                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           class="form-check-input"
                           <?= $unit['is_active'] ? 'checked' : '' ?>>

                    <label class="form-check-label">
                        Aktif
                    </label>

                </div>

                <a href="<?= base_url('units') ?>"
                   class="btn btn-secondary">
                    Kembali
                </a>

                <button type="submit"
                        class="btn btn-primary">
                    Simpan Perubahan
                </button>

            </form>

        </div>
    </div>

</div>

<?= view('layout/footer') ?>