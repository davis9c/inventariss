<?= view('layout/header', ['title' => 'Buat Stock Opname']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Buat Stock Opname</h3>

    <div class="card shadow-sm mt-4">

        <div class="card-body">

            <form method="post"
                  action="<?= base_url('stock-opnames/store') ?>">

                <?= csrf_field() ?>

                <div class="mb-3">

                    <label class="form-label">
                        Tanggal Opname
                    </label>

                    <input type="date"
                           name="opname_date"
                           class="form-control"
                           value="<?= date('Y-m-d') ?>"
                           required>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Lokasi
                    </label>

                    <select name="location_id"
                            class="form-select">

                        <option value="">
                            -- Semua Lokasi --
                        </option>

                        <?php foreach ($locations as $location): ?>

                            <option value="<?= $location['id'] ?>">

                                <?= esc($location['building']) ?>
                                -
                                <?= esc($location['room']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                    <small class="text-muted">
                        Kosongkan jika ingin memeriksa seluruh lokasi.
                    </small>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Catatan
                    </label>

                    <textarea name="notes"
                              class="form-control"
                              rows="3"></textarea>

                </div>

                <a href="<?= base_url('stock-opnames') ?>"
                   class="btn btn-secondary">

                    Kembali

                </a>

                <button type="submit"
                        class="btn btn-primary">

                    Mulai Stock Opname

                </button>

            </form>

        </div>

    </div>

</div>

<?= view('layout/footer') ?>