<?= view('layout/header', ['title' => 'Tambah Barang']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Tambah Barang</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <form method="post"
                action="<?= base_url('assets/store') ?>">

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

                <!-- Business Error -->
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
                            name="asset_code"
                            class="form-control <?= session('errors.asset_code') ? 'is-invalid' : '' ?>"
                            value="<?= old('asset_code') ?>"
                            placeholder="Contoh: AST-0011"
                            required>

                        <?php if (session('errors.asset_code')): ?>
                            <div class="invalid-feedback">
                                <?= esc(session('errors.asset_code')) ?>
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
                            class="form-select"
                            required>

                            <option value="">-- Pilih Unit --</option>

                            <?php foreach ($units as $unit): ?>

                                <option value="<?= $unit['id'] ?>"
                                    <?= old('unit_id') == $unit['id'] ? 'selected' : '' ?>>

                                    <?= esc($unit['name']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>
                    </div>

                    <!-- Lokasi -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Lokasi</label>

                        <select name="location_id"
                            class="form-select"
                            required>

                            <option value="">-- Pilih Lokasi --</option>

                            <?php foreach ($locations as $location): ?>

                                <option value="<?= $location['id'] ?>"
                                    <?= old('location_id') == $location['id'] ? 'selected' : '' ?>>

                                    <?= esc($location['name']) ?>
                                    -
                                    <?= esc($location['room']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>
                    </div>

                    <!-- Merk -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Merk</label>

                        <input type="text"
                            name="brand"
                            class="form-control"
                            value="<?= old('brand') ?>">
                    </div>

                    <!-- Model -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Model / Tipe</label>

                        <input type="text"
                            name="model"
                            class="form-control"
                            value="<?= old('model') ?>">
                    </div>

                    <!-- Serial -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nomor Seri</label>

                        <input type="text"
                            name="serial_number"
                            class="form-control"
                            value="<?= old('serial_number') ?>">
                    </div>

                    <!-- Tahun -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tahun Perolehan</label>

                        <select name="acquisition_year"
                            class="form-select <?= isset($errors['acquisition_year']) ? 'is-invalid' : '' ?>">
                            <?= year_options(old('acquisition_year') ?: date('Y')) ?>
                        </select>
                    </div>

                    <!-- Harga -->
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Harga Perolehan</label>

                        <input type="number"
                            name="acquisition_price"
                            class="form-control"
                            min="1000"
                            step="0.01"
                            value="<?= old('acquisition_price') ?>">
                    </div>

                    <div class="col-12"><hr><h5>Informasi Perolehan</h5></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Sumber Perolehan</label><select name="acquisition_source" class="form-select" required><option value="">-- Pilih sumber --</option><?php foreach (['Pembelian','Hibah','Donasi','Transfer dari unit lain','Pengadaan proyek','Penerimaan dari pihak ketiga','Lainnya'] as $source): ?><option value="<?= esc($source) ?>" <?= old('acquisition_source') === $source ? 'selected' : '' ?>><?= esc($source) ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Tanggal Perolehan</label><input type="date" name="acquisition_date" class="form-control" value="<?= old('acquisition_date', date('Y-m-d')) ?>" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Nomor Dokumen/Invoice</label><input type="text" name="acquisition_document_number" class="form-control" value="<?= old('acquisition_document_number') ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Supplier/Vendor</label><input type="text" name="supplier_name" class="form-control" value="<?= old('supplier_name') ?>"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Sumber Dana</label><input type="text" name="funding_source" class="form-control" value="<?= old('funding_source') ?>"></div>
                    <div class="col-12 mb-3"><label class="form-label">Keterangan Perolehan</label><textarea name="acquisition_notes" class="form-control" rows="2"><?= old('acquisition_notes') ?></textarea></div>

                    <!-- Kondisi -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kondisi</label>

                        <select name="condition_status"
                            class="form-select"
                            required>

                            <?php
                            $condition = old(
                                'condition_status',
                                'Baik'
                            );
                            ?>

                            <option value="Baik"
                                <?= $condition === 'Baik' ? 'selected' : '' ?>>
                                Baik
                            </option>

                            <option value="Rusak Ringan"
                                <?= $condition === 'Rusak Ringan' ? 'selected' : '' ?>>
                                Rusak Ringan
                            </option>

                            <option value="Rusak Berat"
                                <?= $condition === 'Rusak Berat' ? 'selected' : '' ?>>
                                Rusak Berat
                            </option>

                        </select>
                    </div>

                    <!-- Status -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status Aset</label>

                        <?php
                        $assetStatus = old(
                            'asset_status',
                            'Aktif'
                        );
                        ?>

                        <select name="asset_status"
                            class="form-select"
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
                    </div>

                    <!-- Keterangan -->
                    <div class="col-12 mb-3">
                        <label class="form-label">Keterangan</label>

                        <textarea name="description"
                            class="form-control"
                            rows="3"><?= old('description') ?></textarea>
                    </div>

                </div>

                <a href="<?= base_url('assets') ?>"
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
