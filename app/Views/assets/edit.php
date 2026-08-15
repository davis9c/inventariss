<?= view('layout/header', ['title' => 'Edit Barang']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Edit Barang</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <form method="post"
                action="<?= base_url('assets/update/' . $asset['id']) ?>">

                <?= csrf_field() ?>

                <?php $errors = session()->getFlashdata('errors') ?? []; ?>

                <div class="row">

                    <!-- Kode -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Kode Barang
                        </label>

                        <input type="text"
                            name="asset_code"
                            class="form-control <?= isset($errors['asset_code']) ? 'is-invalid' : '' ?>"
                            value="<?= esc(old('asset_code', $asset['asset_code'])) ?>"
                            required>

                        <?php if (isset($errors['asset_code'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['asset_code']) ?>
                            </div>
                        <?php endif; ?>

                    </div>


                    <!-- Nama -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Nama Barang
                        </label>

                        <input type="text"
                            name="name"
                            class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                            value="<?= esc(old('name', $asset['name'])) ?>"
                            required>

                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['name']) ?>
                            </div>
                        <?php endif; ?>

                    </div>


                    <!-- Kategori -->
                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Kategori
                        </label>

                        <select name="category_id"
                            class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>"
                            required>

                            <option value="">
                                -- Pilih Kategori --
                            </option>

                            <?php foreach ($categories as $category): ?>

                                <option value="<?= $category['id'] ?>"
                                    <?= old('category_id', $asset['category_id']) == $category['id'] ? 'selected' : '' ?>>

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

                        <label class="form-label">
                            Unit / Departemen
                        </label>

                        <select name="unit_id"
                            class="form-select <?= isset($errors['unit_id']) ? 'is-invalid' : '' ?>"
                            required>

                            <option value="">
                                -- Pilih Unit --
                            </option>

                            <?php foreach ($units as $unit): ?>

                                <option value="<?= $unit['id'] ?>"
                                    <?= old('unit_id', $asset['unit_id']) == $unit['id'] ? 'selected' : '' ?>>

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

                        <label class="form-label">
                            Lokasi
                        </label>

                        <select name="location_id"
                            class="form-select <?= isset($errors['location_id']) ? 'is-invalid' : '' ?>"
                            required>

                            <option value="">
                                -- Pilih Lokasi --
                            </option>

                            <?php foreach ($locations as $location): ?>

                                <option value="<?= $location['id'] ?>"
                                    <?= old('location_id', $asset['location_id']) == $location['id'] ? 'selected' : '' ?>>

                                    <?= esc($location['name']) ?>
                                    -
                                    <?= esc($location['room']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                        <?php if (isset($errors['location_id'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['location_id']) ?>
                            </div>
                        <?php endif; ?>

                    </div>


                    <!-- Merk -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Merk
                        </label>

                        <input type="text"
                            name="brand"
                            class="form-control"
                            value="<?= esc(old('brand', $asset['brand'] ?? '')) ?>">

                    </div>


                    <!-- Model -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Model / Tipe
                        </label>

                        <input type="text"
                            name="model"
                            class="form-control"
                            value="<?= esc(old('model', $asset['model'] ?? '')) ?>">

                    </div>


                    <!-- Serial -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Nomor Seri
                        </label>

                        <input type="text"
                            name="serial_number"
                            class="form-control"
                            value="<?= esc(old('serial_number', $asset['serial_number'] ?? '')) ?>">

                    </div>


                    <!-- Tahun -->
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Tahun Perolehan
                        </label>

                        <input type="number"
                            name="acquisition_year"
                            class="form-control <?= isset($errors['acquisition_year']) ? 'is-invalid' : '' ?>"
                            value="<?= esc(old('acquisition_year', $asset['acquisition_year'] ?? '')) ?>"
                            min="1900"
                            max="<?= date('Y') ?>">

                        <?php if (isset($errors['acquisition_year'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['acquisition_year']) ?>
                            </div>
                        <?php endif; ?>

                    </div>


                    <!-- Harga -->
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Harga Perolehan
                        </label>

                        <input type="number"
                            name="acquisition_price"
                            class="form-control <?= isset($errors['acquisition_price']) ? 'is-invalid' : '' ?>"
                            value="<?= esc(old('acquisition_price', $asset['acquisition_price'] ?? '')) ?>"
                            min="0"
                            step="0.01">

                        <?php if (isset($errors['acquisition_price'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['acquisition_price']) ?>
                            </div>
                        <?php endif; ?>

                    </div>


                    <!-- Kondisi -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Kondisi
                        </label>

                        <?php
                        $conditionStatus = old(
                            'condition_status',
                            $asset['condition_status']
                        );
                        ?>

                        <select name="condition_status"
                            class="form-select <?= isset($errors['condition_status']) ? 'is-invalid' : '' ?>"
                            required>

                            <option value="Baik"
                                <?= $conditionStatus === 'Baik' ? 'selected' : '' ?>>
                                Baik
                            </option>

                            <option value="Rusak Ringan"
                                <?= $conditionStatus === 'Rusak Ringan' ? 'selected' : '' ?>>
                                Rusak Ringan
                            </option>

                            <option value="Rusak Berat"
                                <?= $conditionStatus === 'Rusak Berat' ? 'selected' : '' ?>>
                                Rusak Berat
                            </option>

                        </select>

                        <?php if (isset($errors['condition_status'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['condition_status']) ?>
                            </div>
                        <?php endif; ?>

                    </div>


                    <!-- Status -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Status Aset
                        </label>

                        <?php
                        $assetStatus = old(
                            'asset_status',
                            $asset['asset_status']
                        );
                        ?>

                        <select name="asset_status"
                            class="form-select <?= isset($errors['asset_status']) ? 'is-invalid' : '' ?>"
                            required>

                            <option value="Aktif"
                                <?= $assetStatus === 'Aktif' ? 'selected' : '' ?>>
                                Aktif
                            </option>

                            <option value="Dipinjam"
                                <?= $assetStatus === 'Dipinjam' ? 'selected' : '' ?>>
                                Dipinjam
                            </option>

                            <option value="Tidak Digunakan"
                                <?= $assetStatus === 'Tidak Digunakan' ? 'selected' : '' ?>>
                                Tidak Digunakan
                            </option>

                            <option value="Keluar Perusahaan"
                                <?= $assetStatus === 'Keluar Perusahaan' ? 'selected' : '' ?>>
                                Keluar Perusahaan
                            </option>

                        </select>

                        <?php if (isset($errors['asset_status'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['asset_status']) ?>
                            </div>
                        <?php endif; ?>

                    </div>


                    <!-- Keterangan -->
                    <div class="col-12 mb-3">

                        <label class="form-label">
                            Keterangan
                        </label>

                        <textarea name="description"
                            class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                            rows="3"><?= esc(old('description', $asset['description'] ?? '')) ?></textarea>

                        <?php if (isset($errors['description'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['description']) ?>
                            </div>
                        <?php endif; ?>

                    </div>

                </div>


                <a href="<?= base_url('assets') ?>"
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