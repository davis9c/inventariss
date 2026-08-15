<?= view('layout/header', ['title' => 'Edit Barang Stok']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Edit Barang Stok</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <form method="post"
                action="<?= base_url('stock-items/update/' . $item['id']) ?>">

                <?= csrf_field() ?>

                <?php $errors = session()->getFlashdata('errors') ?? []; ?>

                <?php if (session()->getFlashdata('error')): ?>

                    <div class="alert alert-danger">
                        <?= esc(session()->getFlashdata('error')) ?>
                    </div>

                <?php endif; ?>

                <div class="row">

                    <!-- Kode -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kode Barang</label>

                        <input type="text"
                            name="item_code"
                            class="form-control <?= isset($errors['item_code']) ? 'is-invalid' : '' ?>"
                            value="<?= esc(old('item_code', $item['item_code'])) ?>"
                            required>

                        <?php if (isset($errors['item_code'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['item_code']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Nama -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Barang</label>

                        <input type="text"
                            name="name"
                            class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                            value="<?= esc(old('name', $item['name'])) ?>"
                            required>

                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['name']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Kategori -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kategori</label>

                        <select name="category_id"
                            class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>"
                            required>

                            <option value="">-- Pilih Kategori --</option>

                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"
                                    <?= old('category_id', $item['category_id']) == $category['id'] ? 'selected' : '' ?>>
                                    <?= esc($category['name']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                        <?php if (isset($errors['category_id'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['category_id']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Unit -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Unit / Departemen</label>

                        <select name="unit_id"
                            class="form-select <?= isset($errors['unit_id']) ? 'is-invalid' : '' ?>"
                            required>

                            <option value="">-- Pilih Unit --</option>

                            <?php foreach ($units as $unit): ?>
                                <option value="<?= $unit['id'] ?>"
                                    <?= old('unit_id', $item['unit_id']) == $unit['id'] ? 'selected' : '' ?>>
                                    <?= esc($unit['name']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                        <?php if (isset($errors['unit_id'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['unit_id']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Lokasi -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Lokasi</label>

                        <select name="location_id"
                            class="form-select <?= isset($errors['location_id']) ? 'is-invalid' : '' ?>"
                            required>

                            <option value="">-- Pilih Lokasi --</option>

                            <?php foreach ($locations as $location): ?>
                                <option value="<?= $location['id'] ?>"
                                    <?= old('location_id', $item['location_id']) == $location['id'] ? 'selected' : '' ?>>
                                    <?= esc($location['name']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                        <?php if (isset($errors['location_id'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['location_id']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Satuan -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Satuan</label>

                        <input type="text"
                            name="satuan"
                            class="form-control <?= isset($errors['satuan']) ? 'is-invalid' : '' ?>"
                            value="<?= esc(old('satuan', $item['satuan'])) ?>"
                            required>

                        <?php if (isset($errors['satuan'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['satuan']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Deskripsi -->
                    <div class="col-12 mb-3">
                        <label class="form-label">Deskripsi</label>

                        <textarea name="description"
                            class="form-control"
                            rows="3"><?= esc(old('description', $item['description'])) ?></textarea>
                    </div>

                    <div class="col-12 mb-4">
                        <div class="form-check">
                            <input type="checkbox"
                                name="is_active"
                                value="1"
                                class="form-check-input"
                                id="is_active"
                                <?= old('is_active', $item['is_active']) ? 'checked' : '' ?>>

                            <label class="form-check-label"
                                for="is_active">
                                Barang stok aktif
                            </label>
                        </div>
                    </div>

                </div>

                <button type="submit"
                    class="btn btn-primary">
                    Simpan Perubahan
                </button>

                <a href="<?= base_url('stock-items') ?>"
                    class="btn btn-secondary">
                    Batal
                </a>

            </form>

        </div>
    </div>

</div>

<?= view('layout/footer') ?>
