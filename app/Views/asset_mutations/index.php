<?= view('layout/header', ['title' => 'Mutasi Aset']) ?>
<?= view('layout/sidebar') ?>

<div class="p-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Mutasi Aset</h3>
            <p class="text-muted mb-0">
                Riwayat perpindahan barang.
            </p>
        </div>

        <button type="button"
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#mutasiModal">
            + Mutasi Aset
        </button>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="table-responsive">

                <table id="tabel-mutasi"
                       class="table table-hover align-middle w-100">

                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Barang</th>
                            <th>Dari</th>
                            <th>Ke</th>
                            <th>Alasan</th>
                        </tr>
                    </thead>

                </table>

            </div>

        </div>
    </div>

</div>

<!-- MODAL MUTASI -->
<div class="modal fade"
     id="mutasiModal"
     tabindex="-1"
     data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="mutasiForm"
                  method="post"
                  action="<?= base_url('asset-mutations/store') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Mutasi Aset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Barang</label>
                        <select name="asset_id" id="mutasiAsset" class="form-select" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php foreach ($assets as $asset): ?>
                                <option value="<?= $asset['id'] ?>"
                                        data-location-name="<?= esc($asset['location_name']) ?>">
                                    <?= esc($asset['asset_code'] . ' - ' . $asset['name']) ?>
                                    (<?= esc($asset['location_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="alert alert-info py-2 mb-3" id="mutasiInfo" hidden>
                        <strong id="mutasiAssetName"></strong><br>
                        <small class="text-muted" id="mutasiAssetLocation"></small>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Lokasi Tujuan</label>
                            <select name="to_location_id" id="mutasiToLocation" class="form-select" required>
                                <option value="">-- Pilih Lokasi --</option>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?= $location['id'] ?>"><?= esc($location['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit Tujuan</label>
                            <select name="to_unit_id" id="mutasiToUnit" class="form-select" required disabled>
                                <option value="">-- Pilih Lokasi Terlebih Dahulu --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Mutasi</label>
                            <input type="datetime-local" name="mutation_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alasan</label>
                            <input type="text" name="reason" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Mutasi</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
(function () {
    'use strict';

    var dt = Inventaris.datatable('#tabel-mutasi', {
        url: Inventaris.baseUrl + 'asset-mutations?format=json',
        columns: [
            { data: 'transaction_date' },
            {
                data: null,
                render: function (data, type, row) {
                    return '<strong>' + Inventaris.esc(row.asset_name) + '</strong><br>' +
                        '<small class="text-muted">' + Inventaris.esc(row.asset_code) + '</small>';
                }
            },
            { data: null, render: function (data, type, row) { return Inventaris.esc(row.from_location_name || '-') + ' — ' + Inventaris.esc(row.from_unit_name || '-'); } },
            { data: null, render: function (data, type, row) { return Inventaris.esc(row.to_location_name || '-') + ' — ' + Inventaris.esc(row.to_unit_name || '-'); } },
            { data: 'reason', render: function (data) { return Inventaris.esc(data || '-'); } }
        ]
    });

    var mutasiForm = document.getElementById('mutasiForm');
    var mutasiToUnit = document.getElementById('mutasiToUnit');

    function resetMutasiForm() {
        mutasiForm.reset();
        document.getElementById('mutasiInfo').hidden = true;
        mutasiToUnit.disabled = true;
        mutasiToUnit.innerHTML = '<option value="">-- Pilih Lokasi Terlebih Dahulu --</option>';
    }

    document.getElementById('mutasiAsset').addEventListener('change', function () {
        var opt = this.selectedOptions[0];
        if (!opt || !opt.value) {
            document.getElementById('mutasiInfo').hidden = true;
            return;
        }
        document.getElementById('mutasiAssetName').textContent = opt.textContent;
        document.getElementById('mutasiAssetLocation').textContent = 'Lokasi saat ini: ' + opt.getAttribute('data-location-name');
        document.getElementById('mutasiInfo').hidden = false;
    });

    document.getElementById('mutasiToLocation').addEventListener('change', function () {
        var id = this.value;
        mutasiToUnit.disabled = !id;
        mutasiToUnit.innerHTML = '<option value="">-- Pilih Unit --</option>';
        if (!id) return;
        Inventaris.fetchJson(Inventaris.baseUrl + 'asset-mutations/units-by-location/' + id)
            .then(function (units) {
                units.forEach(function (u) {
                    var opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.name;
                    mutasiToUnit.appendChild(opt);
                });
            })
            .catch(function () {
                Inventaris.toast('Gagal memuat daftar unit.', 'danger');
            });
    });

    mutasiForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(mutasiForm, {
            onSuccess: function () {
                Inventaris.hideModal('mutasiModal');
                resetMutasiForm();
                dt.ajax.reload();
            }
        });
    });

    document.getElementById('mutasiModal').addEventListener('hidden.bs.modal', resetMutasiForm);
})();
</script>
<?= view('layout/footer') ?>

