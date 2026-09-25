<?= view('layout/header', ['title' => 'Stock Opname']) ?>
<?= view('layout/navbar') ?>

<div class="mb-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3>Stock Opname</h3>

            <p class="text-muted mb-0">
                Pemeriksaan fisik barang inventaris.
            </p>
        </div>

        <button type="button" class="btn btn-primary" id="btnCreateOpname">
            + Buat Stock Opname
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

                <table id="tabel-opname"
                       class="table table-hover align-middle w-100">

                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th class="text-nowrap">Aksi</th>
                        </tr>
                    </thead>

                </table>

            </div>

        </div>

    </div>

</div>



<div class="modal fade" id="createOpnameModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="createOpnameForm" method="post" action="<?= base_url('stock-opnames/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Buat Stock Opname</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Opname</label>
                        <input type="date" name="opname_date" class="form-control"
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi</label>
                        <select name="location_id" class="form-select">
                            <option value="">-- Semua Lokasi --</option>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?= (int) $location['id'] ?>">
                                    <?= esc($location['building']) ?> - <?= esc($location['room']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Kosongkan jika ingin memeriksa seluruh lokasi.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Mulai Stock Opname</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var baseUrl = Inventaris.baseUrl;
    var createModal = null;
    var createForm  = document.getElementById('createOpnameForm');

    function statusBadge(value) {
        return value === 'Selesai'
            ? '<span class="badge bg-success">Selesai</span>'
            : '<span class="badge bg-warning text-dark">Draft</span>';
    }

    function locationHtml(row) {
        if (row.location_id) {
            return Inventaris.esc(row.building) + ' - ' + Inventaris.esc(row.room);
        }
        return '<span class="text-muted">Semua lokasi</span>';
    }

    var dt = Inventaris.datatable('#tabel-opname', {
        url: baseUrl + 'stock-opnames?format=json',
        columns: [
            { data: 'opname_code', render: function (data) { return '<strong>' + Inventaris.esc(data) + '</strong>'; } },
            { data: 'opname_date' },
            { data: null, render: function (data, type, row) { return locationHtml(row); } },
            { data: 'status', render: function (data) { return statusBadge(data); } },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return '<a href="' + baseUrl + 'stock-opnames/' + row.id + '" class="btn btn-sm btn-primary">Detail</a>';
                }
            }
        ]
    });

    function showCreate() {
        createForm.reset();
        createForm.elements['opname_date'].value = new Date().toISOString().slice(0, 10);
        Inventaris.clearErrors(createForm);
        if (!createModal) createModal = Inventaris.openModal('createOpnameModal');
        else createModal.show();
    }

    document.getElementById('btnCreateOpname').addEventListener('click', showCreate);

    createForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function (json) {
                // Alur opname berlanjut di halaman detail (daftar barang
                // yang harus diperiksa), jadi navigasi ke sana, bukan
                // reload tabel.
                if (json.data && json.data.id) {
                    window.location.href = baseUrl + 'stock-opnames/' + json.data.id;
                } else {
                    dt.ajax.reload();
                }
            }
        });
    });

    // Deep link: /stock-opnames?create=1
    var params = new URLSearchParams(window.location.search);

    if (params.get('create') === '1') {
        window.history.replaceState({}, document.title, baseUrl + 'stock-opnames');
        showCreate();
    }
})();
</script>
<?= view('layout/footer') ?>

