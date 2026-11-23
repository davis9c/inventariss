<?= view('layout/header', ['title' => 'Konversi ke Aset']) ?>
<?= view('layout/navbar') ?>

<div class="mb-4">

    <h3>Konversi Barang Stok ke Aset</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <div class="alert alert-info">
                <strong><?= esc($item['item_code']) ?></strong>
                - <?= esc($item['name']) ?>
                (Stok saat ini:
                <?= esc($item['quantity']) ?> <?= esc($item['satuan']) ?>)
            </div>

            <form method="post"
                action="<?= base_url('stock-items/to-asset/' . $item['id']) ?>">

                <?= csrf_field() ?>

                <?php $errors = session()->getFlashdata('errors') ?? []; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?= esc(session()->getFlashdata('error')) ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success">
                        <?= esc(session()->getFlashdata('success')) ?>
                    </div>
                <?php endif; ?>

                <div class="row">

                    <!-- Jumlah -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Jumlah Konversi</label>
                        <input type="number"
                            name="quantity"
                            min="1"
                            max="<?= esc($item['quantity']) ?>"
                            class="form-control <?= isset($errors['quantity']) ? 'is-invalid' : '' ?>"
                            value="<?= esc(old('quantity', 1)) ?>"
                            required>
                        <?php if (isset($errors['quantity'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['quantity']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Kode aset -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kode Barang (dasar)</label>
                        <input type="text"
                            name="asset_code"
                            class="form-control <?= isset($errors['asset_code']) ? 'is-invalid' : '' ?>"
                            value="<?= esc(old('asset_code', $item['item_code'])) ?>"
                            placeholder="Contoh: MON-001"
                            required>
                        <?php if (isset($errors['asset_code'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['asset_code']) ?>
                            </div>
                        <?php endif; ?>
                        <small class="text-muted">
                            Jika lebih dari 1 unit, kode akan diberi suffix otomatis.
                        </small>
                    </div>

                    <!-- Nama -->
                    <div class="col-md-4 mb-3">
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
                    </div>

                    <!-- Unit -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Unit</label>
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
                    </div>

                    <!-- Lokasi -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Lokasi Tujuan</label>
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
                    </div>

                    <!-- Brand -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Brand</label>
                        <input type="text"
                            name="brand"
                            class="form-control"
                            value="<?= esc(old('brand')) ?>">
                    </div>

                    <!-- Model -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Model</label>
                        <input type="text"
                            name="model"
                            class="form-control"
                            value="<?= esc(old('model')) ?>">
                    </div>

                    <!-- Serial -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Serial Number</label>
                        <input type="text"
                            name="serial_number"
                            class="form-control"
                            value="<?= esc(old('serial_number')) ?>">
                    </div>

                    <!-- Tahun -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tahun Perolehan</label>
                        <input type="number"
                            name="acquisition_year"
                            class="form-control <?= isset($errors['acquisition_year']) ? 'is-invalid' : '' ?>"
                            value="<?= esc(old('acquisition_year')) ?>">
                    </div>

                    <!-- Harga -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Harga Perolehan</label>
                        <input type="number"
                            step="0.01"
                            name="acquisition_price"
                            class="form-control <?= isset($errors['acquisition_price']) ? 'is-invalid' : '' ?>"
                            value="<?= esc(old('acquisition_price')) ?>">
                    </div>

                    <!-- Kondisi -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Kondisi</label>
                        <select name="condition_status"
                            class="form-select <?= isset($errors['condition_status']) ? 'is-invalid' : '' ?>"
                            required>
                            <option value="Baik" <?= old('condition_status', 'Baik') === 'Baik' ? 'selected' : '' ?>>Baik</option>
                            <option value="Rusak Ringan" <?= old('condition_status') === 'Rusak Ringan' ? 'selected' : '' ?>>Rusak Ringan</option>
                            <option value="Rusak Berat" <?= old('condition_status') === 'Rusak Berat' ? 'selected' : '' ?>>Rusak Berat</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status</label>
                        <select name="asset_status"
                            class="form-select <?= isset($errors['asset_status']) ? 'is-invalid' : '' ?>"
                            required>
                            <option value="Aktif" <?= old('asset_status', 'Aktif') === 'Aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="Dipinjam" <?= old('asset_status') === 'Dipinjam' ? 'selected' : '' ?>>Dipinjam</option>
                            <option value="Tidak Digunakan" <?= old('asset_status') === 'Tidak Digunakan' ? 'selected' : '' ?>>Tidak Digunakan</option>
                            <option value="Keluar Perusahaan" <?= old('asset_status') === 'Keluar Perusahaan' ? 'selected' : '' ?>>Keluar Perusahaan</option>
                        </select>
                    </div>

                    <!-- Tanggal -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Transaksi</label>
                        <input type="date"
                            name="transaction_date"
                            class="form-control <?= isset($errors['transaction_date']) ? 'is-invalid' : '' ?>"
                            value="<?= esc(old('transaction_date', date('Y-m-d'))) ?>"
                            required>
                    </div>

                    <!-- Deskripsi -->
                    <div class="col-12 mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description"
                            class="form-control"
                            rows="2"><?= esc(old('description', $item['description'])) ?></textarea>
                    </div>

                    <!-- Catatan -->
                    <div class="col-12 mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes"
                            class="form-control"
                            rows="2"><?= esc(old('notes')) ?></textarea>
                    </div>

                </div>

                <button type="submit" class="btn btn-primary">
                    Konversi ke Aset
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