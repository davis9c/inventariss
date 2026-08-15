<?= view('layout/header', ['title' => 'Tambah Barang Stok']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Tambah Barang Stok</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <form method="post"
                action="<?= base_url('stock-items/store') ?>">

                <?= csrf_field() ?>

                <!-- Validation Error -->
                <?php if (session()->getFlashdata('errors')): ?>

                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                <?php endif; ?>

                <div class="row">

                    <!-- Kode -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kode Barang</label>

                        <input type="text"
                            name="item_code"
                            class="form-control <?= session('errors.item_code') ? 'is-invalid' : '' ?>"
                            value="<?= old('item_code') ?>"
                            placeholder="Contoh: STK-0001"
                            required>

                        <?php if (session('errors.item_code')): ?>
                            <div class="invalid-feedback">
                                <?= esc(session('errors.item_code')) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Nama -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Barang</label>

                        <input type="text"
                            name="name"
                            class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>"
                            value="<?= old('name') ?>"
                            placeholder="Contoh: Kabel USB"
                            required>

                        <?php if (session('errors.name')): ?>
                            <div class="invalid-feedback">
                                <?= esc(session('errors.name')) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Kategori -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kategori</label>

                        <select name="category_id"
                            class="form-select <?= session('errors.category_id') ? 'is-invalid' : '' ?>"
                            required>

                            <option value="">-- Pilih Kategori --</option>

                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"
                                    <?= old('category_id') == $category['id'] ? 'selected' : '' ?>>
                                    <?= esc($category['name']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                        <?php if (session('errors.category_id')): ?>
                            <div class="invalid-feedback">
                                <?= esc(session('errors.category_id')) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Unit -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Unit / Departemen</label>

                        <select name="unit_id"
                            class="form-select <?= session('errors.unit_id') ? 'is-invalid' : '' ?>"
                            required>

                            <option value="">-- Pilih Unit --</option>

                            <?php foreach ($units as $unit): ?>
                                <option value="<?= $unit['id'] ?>"
                                    <?= old('unit_id') == $unit['id'] ? 'selected' : '' ?>>
                                    <?= esc($unit['name']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                        <?php if (session('errors.unit_id')): ?>
                            <div class="invalid-feedback">
                                <?= esc(session('errors.unit_id')) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Lokasi -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Lokasi</label>

                        <select name="location_id"
                            class="form-select <?= session('errors.location_id') ? 'is-invalid' : '' ?>"
                            required>

                            <option value="">-- Pilih Lokasi --</option>

                            <?php foreach ($locations as $location): ?>
                                <option value="<?= $location['id'] ?>"
                                    <?= old('location_id') == $location['id'] ? 'selected' : '' ?>>
                                    <?= esc($location['name']) ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                        <?php if (session('errors.location_id')): ?>
                            <div class="invalid-feedback">
                                <?= esc(session('errors.location_id')) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Satuan -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Satuan</label>

                        <input type="text"
                            name="satuan"
                            class="form-control <?= session('errors.satuan') ? 'is-invalid' : '' ?>"
                            value="<?= old('satuan', 'pcs') ?>"
                            placeholder="Contoh: pcs, box, roll"
                            required>

                        <?php if (session('errors.satuan')): ?>
                            <div class="invalid-feedback">
                                <?= esc(session('errors.satuan')) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Deskripsi -->
                    <div class="col-12 mb-3">
                        <label class="form-label">Deskripsi</label>

                        <textarea name="description"
                            class="form-control"
                            rows="3"><?= esc(old('description')) ?></textarea>
                    </div>

                </div>

                <button type="submit"
                    class="btn btn-primary">
                    Simpan
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
