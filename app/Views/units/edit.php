<?= view('layout/header', ['title' => 'Edit Unit']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3>Edit Unit / Departemen</h3>

            <p class="text-muted mb-0">
                Perbarui informasi unit dan lokasi yang ditangani.
            </p>
        </div>

    </div>


    <?php if (session()->getFlashdata('error')): ?>

        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>

    <?php endif; ?>


    <div class="card shadow-sm">

        <div class="card-body">

            <form method="post"
                action="<?= base_url('units/update/' . $unit['id']) ?>">

                <?= csrf_field() ?>


                <!-- Nama -->
                <div class="mb-3">

                    <label class="form-label">
                        Nama Unit
                    </label>

                    <input type="text"
                        name="name"
                        class="form-control"
                        value="<?= old('name', $unit['name']) ?>"
                        required>

                </div>


                <!-- Kode -->
                <div class="mb-3">

                    <label class="form-label">
                        Kode Unit
                    </label>

                    <input type="text"
                        name="code"
                        class="form-control"
                        value="<?= old('code', $unit['code']) ?>"
                        required>

                </div>


                <!-- Lokasi -->
                <div class="mb-3">

                    <label class="form-label">
                        Lokasi yang Ditangani
                    </label>

                    <div class="border rounded p-3">

                        <?php if (empty($locations)): ?>

                            <div class="text-muted">
                                Belum ada lokasi aktif.
                            </div>

                        <?php else: ?>

                            <?php
                            $selectedLocations = old(
                                'location_ids',
                                $locationIds ?? []
                            );
                            ?>

                            <?php foreach ($locations as $location): ?>

                                <div class="form-check mb-2">

                                    <input class="form-check-input"
                                        type="checkbox"
                                        name="location_ids[]"
                                        value="<?= $location['id'] ?>"
                                        id="location_<?= $location['id'] ?>"
                                        <?= in_array(
                                            $location['id'],
                                            $selectedLocations
                                        ) ? 'checked' : '' ?>>

                                    <label class="form-check-label"
                                        for="location_<?= $location['id'] ?>">

                                        <?= esc($location['name']) ?>

                                    </label>

                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>

                    <small class="text-muted">
                        Satu unit dapat menangani beberapa lokasi.
                    </small>

                </div>


                <!-- Deskripsi -->
                <div class="mb-3">

                    <label class="form-label">
                        Deskripsi
                    </label>

                    <textarea name="description"
                        class="form-control"
                        rows="3"><?= old('description', $unit['description']) ?></textarea>

                </div>


                <!-- Status -->
                <div class="form-check mb-4">

                    <input type="checkbox"
                        name="is_active"
                        value="1"
                        class="form-check-input"
                        id="is_active"
                        <?= old(
                            'is_active',
                            $unit['is_active']
                        ) ? 'checked' : '' ?>>

                    <label class="form-check-label"
                        for="is_active">

                        Unit Aktif

                    </label>

                </div>


                <!-- Tombol -->
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