<?= view('layout/header', ['title' => 'Pindah Stok']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <h3>Pindah Stok</h3>

    <div class="card shadow-sm mt-4">
        <div class="card-body">

            <div class="alert alert-info">
                <strong><?= esc($item['item_code']) ?></strong>
                - <?= esc($item['name']) ?>
                (Stok saat ini:
                <?= esc($item['quantity']) ?> <?= esc($item['satuan']) ?>)
            </div>

            <p class="text-muted">
                Seluruh stok barang ini akan dipindahkan ke unit/lokasi tujuan.
                Lokasi boleh dibiarkan sama bila hanya pindah unit.
            </p>

            <form method="post"
                action="<?= base_url('stock-items/transfer/' . $item['id']) ?>">

                <?= csrf_field() ?>

                <?php $errors = session()->getFlashdata('errors') ?? []; ?>

                <?php if (session()->getFlashdata('error')): ?>

                    <div class="alert alert-danger">
                        <?= esc(session()->getFlashdata('error')) ?>
                    </div>

                <?php endif; ?>

                <div class="row">

                    <!-- Lokasi tujuan -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Lokasi Tujuan</label>

                        <select name="to_location_id"
                            id="transferToLocation"
                            class="form-select <?= isset($errors['to_location_id']) ? 'is-invalid' : '' ?>">

                            <option value="">-- Tetap di Lokasi Saat Ini --</option>

                            <?php foreach ($locations as $location): ?>
                                <option value="<?= $location['id'] ?>"
                                    <?= (string) old('to_location_id', (string) $item['location_id']) === (string) $location['id'] ? 'selected' : '' ?>>
                                    <?= esc($location['name']) ?><?= $location['id'] == $item['location_id'] ? ' (saat ini)' : '' ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                        <div class="form-text">Kosongkan bila hanya pindah unit dalam lokasi yang sama.</div>

                        <?php if (isset($errors['to_location_id'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['to_location_id']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Unit tujuan -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Unit Tujuan</label>

                        <select name="to_unit_id"
                            id="transferToUnit"
                            class="form-select <?= isset($errors['to_unit_id']) ? 'is-invalid' : '' ?>"
                            required
                            disabled>

                            <option value="">-- Pilih Unit --</option>

                        </select>

                        <?php if (isset($errors['to_unit_id'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['to_unit_id']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tanggal -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tanggal Pindah</label>

                        <input type="datetime-local"
                            name="transaction_date"
                            class="form-control <?= isset($errors['transaction_date']) ? 'is-invalid' : '' ?>"
                            value="<?= esc(old('transaction_date', date('Y-m-d'))) ?>"
                            required>

                        <?php if (isset($errors['transaction_date'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['transaction_date']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Alasan -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Alasan</label>

                        <input type="text"
                            name="reason"
                            class="form-control"
                            value="<?= esc(old('reason')) ?>"
                            placeholder="Contoh: Pindah gudang, reorganisasi unit">

                        <?php if (isset($errors['reason'])): ?>
                            <div class="invalid-feedback">
                                <?= esc($errors['reason']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Keterangan -->
                    <div class="col-12 mb-3">
                        <label class="form-label">Keterangan</label>

                        <textarea name="notes"
                            class="form-control"
                            rows="2"><?= esc(old('notes')) ?></textarea>
                    </div>

                </div>

                <button type="submit"
                    class="btn btn-primary">
                    Simpan Pindah Stok
                </button>

                <a href="<?= base_url('stock-items/' . $item['id']) ?>"
                    class="btn btn-secondary">
                    Batal
                </a>

            </form>

        </div>
    </div>

</div>

<script>
(function () {
    'use strict';

    var currentLocationId = <?= (int) $item['location_id'] ?>;
    var currentUnitId = <?= (int) $item['unit_id'] ?>;
    var transferToLocation = document.getElementById('transferToLocation');
    var transferToUnit = document.getElementById('transferToUnit');

    function loadTransferUnits(locationId) {
        transferToUnit.disabled = !locationId;
        transferToUnit.innerHTML = '<option value="">-- Pilih Unit --</option>';
        if (!locationId) return;
        Inventaris.fetchJson(Inventaris.baseUrl + 'stock-items/units-by-location/' + locationId)
            .then(function (units) {
                units.forEach(function (u) {
                    var opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.name + (u.id === currentUnitId ? ' (saat ini)' : '');
                    transferToUnit.appendChild(opt);
                });
            })
            .catch(function () {
                Inventaris.toast('Gagal memuat daftar unit.', 'danger');
            });
    }

    transferToLocation.addEventListener('change', function () {
        loadTransferUnits(this.value || currentLocationId);
    });

    loadTransferUnits(transferToLocation.value || currentLocationId);
})();
</script>
<?= view('layout/footer') ?>