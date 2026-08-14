<?= view('layout/header', ['title' => 'Tambah Lokasi']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Tambah Lokasi</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <form method="post"
                  action="<?= base_url('locations/store') ?>">

                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label">Nama Lokasi</label>

                    <input type="text"
                           name="name"
                           class="form-control"
                           placeholder="Contoh: Ruang IT"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Gedung</label>

                    <input type="text"
                           name="building"
                           class="form-control"
                           placeholder="Contoh: Gedung Utama">
                </div>

                <div class="mb-3">
                    <label class="form-label">Lantai</label>

                    <input type="text"
                           name="floor"
                           class="form-control"
                           placeholder="Contoh: Lantai 2">
                </div>

                <div class="mb-3">
                    <label class="form-label">Ruangan</label>

                    <input type="text"
                           name="room"
                           class="form-control"
                           placeholder="Contoh: Ruang 204">
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan</label>

                    <textarea name="description"
                              class="form-control"
                              rows="3"></textarea>
                </div>

                <div class="form-check mb-4">

                    <input type="checkbox"
                           name="is_active"
                           value="1"
                           class="form-check-input"
                           checked>

                    <label class="form-check-label">
                        Aktif
                    </label>

                </div>

                <a href="<?= base_url('locations') ?>"
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