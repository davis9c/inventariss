<?= view('layout/header', ['title' => 'Edit Kategori']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Edit Kategori</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <form method="post"
                  action="<?= base_url('categories/update/' . $category['id']) ?>">

                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label">Nama Kategori</label>

                    <input type="text"
                           name="name"
                           class="form-control"
                           value="<?= esc($category['name']) ?>"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>

                    <textarea name="description"
                              class="form-control"
                              rows="3"><?= esc($category['description'] ?? '') ?></textarea>
                </div>

                <div class="form-check mb-4">

                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           class="form-check-input"
                           <?= $category['is_active'] ? 'checked' : '' ?>>

                    <label class="form-check-label">
                        Aktif
                    </label>

                </div>

                <a href="<?= base_url('categories') ?>"
                   class="btn btn-secondary">
                    Kembali
                </a>

                <button type="submit" class="btn btn-primary">
                    Simpan Perubahan
                </button>

            </form>

        </div>
    </div>

</div>

<?= view('layout/footer') ?>