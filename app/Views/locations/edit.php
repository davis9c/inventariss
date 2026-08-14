<?= view('layout/header', ['title' => 'Edit Lokasi']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3>Edit Lokasi</h3>

            <p class="text-muted mb-0">
                Perbarui informasi dan unit terkait lokasi.
            </p>
        </div>

        <a href="<?= base_url('locations') ?>"
            class="btn btn-secondary">
            Kembali
        </a>

    </div>

    <?php if (session()->getFlashdata('error')): ?>

        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>

    <?php endif; ?>

    <div class="card shadow-sm">

        <div class="card-body">

            <form method="post"
                action="<?= base_url('locations/update/' . $location['id']) ?>">

                <?= csrf_field() ?>

                <div class="mb-3">

                    <label class="form-label">
                        Nama Lokasi
                    </label>

                    <input type="text"
                        name="name"
                        class="form-control"
                        value="<?= esc($location['name']) ?>"
                        required>

                </div>

                <div class="row">

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Gedung
                        </label>

                        <input type="text"
                            name="building"
                            class="form-control"
                            value="<?= esc($location['building'] ?? '') ?>">

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Lantai
                        </label>

                        <input type="text"
                            name="floor"
                            class="form-control"
                            value="<?= esc($location['floor'] ?? '') ?>">

                    </div>

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Ruangan
                        </label>

                        <input type="text"
                            name="room"
                            class="form-control"
                            value="<?= esc($location['room'] ?? '') ?>">

                    </div>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Deskripsi
                    </label>

                    <textarea name="description"
                        class="form-control"
                        rows="3"><?= esc($location['description'] ?? '') ?></textarea>

                </div>

                <div class="mb-4">

                    <label class="form-label">
                        Unit Terkait
                    </label>

                    <div class="border rounded p-3">

                        <?php foreach ($units as $unit): ?>

                            <div class="form-check mb-2">

                                <input type="checkbox"
                                    class="form-check-input"
                                    name="unit_ids[]"
                                    value="<?= $unit['id'] ?>"
                                    id="unit_<?= $unit['id'] ?>"
                                    <?= in_array(
                                        $unit['id'],
                                        $selectedUnitIds
                                    ) ? 'checked' : '' ?>>

                                <label class="form-check-label"
                                    for="unit_<?= $unit['id'] ?>">

                                    <?= esc($unit['name']) ?>

                                    <?php if (!empty($unit['code'])): ?>

                                        <span class="text-muted">
                                            (<?= esc($unit['code']) ?>)
                                        </span>

                                    <?php endif; ?>

                                </label>

                            </div>

                        <?php endforeach; ?>

                    </div>

                    <small class="text-muted">
                        Pilih unit yang terkait atau menggunakan lokasi ini.
                    </small>

                </div>

                <div class="form-check mb-4">

                    <input type="checkbox"
                        name="is_active"
                        value="1"
                        class="form-check-input"
                        id="is_active"
                        <?= $location['is_active'] ? 'checked' : '' ?>>

                    <label class="form-check-label"
                        for="is_active">

                        Lokasi aktif

                    </label>

                </div>

                <button type="submit"
                    class="btn btn-primary">

                    Simpan Perubahan

                </button>

            </form>

        </div>

    </div>

</div>

<?= view('layout/footer') ?>