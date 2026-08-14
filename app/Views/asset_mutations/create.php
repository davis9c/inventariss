<?= view('layout/header', ['title' => 'Mutasi Aset']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Mutasi Aset</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <?php if (session()->getFlashdata('error')): ?>

                <div class="alert alert-danger">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>

            <?php endif; ?>


            <form method="post"
                action="<?= base_url('asset-mutations/store') ?>">

                <?= csrf_field() ?>


                <!-- Barang -->
                <div class="mb-3">

                    <label class="form-label">
                        Barang / Aset
                    </label>

                    <select name="asset_id"
                        id="asset_id"
                        class="form-select"
                        required>

                        <option value="">
                            -- Pilih Barang --
                        </option>

                        <?php foreach ($assets as $asset): ?>

                            <option value="<?= $asset['id'] ?>"
                                data-unit="<?= esc($asset['unit_name'] ?? '-') ?>"
                                data-location="<?= esc($asset['location_name'] ?? '-') ?>">

                                <?= esc($asset['asset_code']) ?>
                                -
                                <?= esc($asset['name']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- Informasi Asal -->
                <div id="asset-origin"
                    class="alert alert-light border d-none">

                    <strong>Posisi Barang Saat Ini</strong>

                    <div>
                        Unit:
                        <span id="origin-unit">-</span>
                    </div>

                    <div>
                        Lokasi:
                        <span id="origin-location">-</span>
                    </div>

                </div>


                <!-- Tujuan -->
                <div class="row">

                    <!-- Lokasi Tujuan -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Lokasi Tujuan
                        </label>

                        <select name="to_location_id"
                            id="to_location_id"
                            class="form-select"
                            required>

                            <option value="">
                                -- Pilih Lokasi --
                            </option>

                            <?php foreach ($locations as $location): ?>

                                <option value="<?= $location['id'] ?>">
                                    <?= esc($location['name']) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <!-- Unit Tujuan -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Unit Tujuan
                        </label>

                        <select name="to_unit_id"
                            id="to_unit_id"
                            class="form-select"
                            required
                            disabled>

                            <option value="">
                                -- Pilih Lokasi Terlebih Dahulu --
                            </option>

                        </select>

                    </div>

                </div>


                <!-- Tanggal -->
                <div class="mb-3">

                    <label class="form-label">
                        Tanggal Mutasi
                    </label>

                    <input type="date"
                        name="mutation_date"
                        class="form-control"
                        value="<?= date('Y-m-d') ?>"
                        required>

                </div>


                <!-- Alasan -->
                <div class="mb-3">

                    <label class="form-label">
                        Alasan Mutasi
                    </label>

                    <textarea name="reason"
                        class="form-control"
                        rows="3"
                        placeholder="Contoh: Pemindahan barang ke ruangan lain"></textarea>

                </div>


                <!-- Catatan -->
                <div class="mb-3">

                    <label class="form-label">
                        Catatan
                    </label>

                    <textarea name="notes"
                        class="form-control"
                        rows="3"></textarea>

                </div>


                <!-- Tombol -->
                <a href="<?= base_url('asset-mutations') ?>"
                    class="btn btn-secondary">

                    Kembali

                </a>

                <button type="submit"
                    class="btn btn-primary">

                    Simpan Mutasi

                </button>

            </form>


            <!-- JavaScript -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {

                    const assetSelect = document.getElementById('asset_id');

                    const originBox = document.getElementById('asset-origin');
                    const originUnit = document.getElementById('origin-unit');
                    const originLocation = document.getElementById('origin-location');

                    const locationSelect =
                        document.getElementById('to_location_id');

                    const unitSelect =
                        document.getElementById('to_unit_id');


                    /*
                    |--------------------------------------------------------------------------
                    | Tampilkan posisi aset saat ini
                    |--------------------------------------------------------------------------
                    */

                    assetSelect.addEventListener('change', function() {

                        const option =
                            this.options[this.selectedIndex];

                        if (!this.value) {

                            originBox.classList.add('d-none');

                            originUnit.textContent = '-';
                            originLocation.textContent = '-';

                            return;
                        }

                        originUnit.textContent =
                            option.dataset.unit || '-';

                        originLocation.textContent =
                            option.dataset.location || '-';

                        originBox.classList.remove('d-none');
                    });


                    /*
                    |--------------------------------------------------------------------------
                    | Load Unit berdasarkan Lokasi Tujuan
                    |--------------------------------------------------------------------------
                    */

                    locationSelect.addEventListener('change', function() {

                        const locationId = this.value;


                        // Reset dropdown unit
                        unitSelect.innerHTML = '';


                        if (!locationId) {

                            const option =
                                document.createElement('option');

                            option.value = '';
                            option.textContent =
                                '-- Pilih Lokasi Terlebih Dahulu --';

                            unitSelect.appendChild(option);

                            unitSelect.disabled = true;

                            return;
                        }


                        // Tampilkan loading
                        const loadingOption =
                            document.createElement('option');

                        loadingOption.value = '';
                        loadingOption.textContent =
                            'Memuat unit...';

                        unitSelect.appendChild(loadingOption);

                        unitSelect.disabled = true;


                        /*
                        |--------------------------------------------------------------------------
                        | Request ke Controller
                        |--------------------------------------------------------------------------
                        */

                        fetch(
                                '<?= base_url('asset-mutations/units-by-location') ?>/' +
                                locationId
                            )
                            .then(response => {

                                if (!response.ok) {
                                    throw new Error(
                                        'Gagal mengambil data unit.'
                                    );
                                }

                                return response.json();
                            })
                            .then(units => {

                                unitSelect.innerHTML = '';


                                if (units.length === 0) {

                                    const option =
                                        document.createElement('option');

                                    option.value = '';

                                    option.textContent =
                                        '-- Tidak ada unit pada lokasi ini --';

                                    unitSelect.appendChild(option);

                                    unitSelect.disabled = true;

                                    return;
                                }


                                const defaultOption =
                                    document.createElement('option');

                                defaultOption.value = '';

                                defaultOption.textContent =
                                    '-- Pilih Unit Tujuan --';

                                unitSelect.appendChild(defaultOption);


                                units.forEach(unit => {

                                    const option =
                                        document.createElement('option');

                                    option.value = unit.id;

                                    option.textContent =
                                        unit.name +
                                        (unit.code ?
                                            ' (' + unit.code + ')' :
                                            '');

                                    unitSelect.appendChild(option);
                                });


                                unitSelect.disabled = false;
                            })
                            .catch(error => {

                                console.error(error);

                                unitSelect.innerHTML = '';

                                const option =
                                    document.createElement('option');

                                option.value = '';

                                option.textContent =
                                    '-- Gagal memuat unit --';

                                unitSelect.appendChild(option);

                                unitSelect.disabled = true;
                            });

                    });

                });
            </script>

        </div>
    </div>

</div>

<?= view('layout/footer') ?>