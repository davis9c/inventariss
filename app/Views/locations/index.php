<?= view('layout/header', ['title' => 'Lokasi']) ?>
<?= view('layout/sidebar') ?>

<div class="mb-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3>Lokasi</h3>

            <p class="text-muted mb-0">
                Daftar lokasi penyimpanan dan penempatan aset.
            </p>
        </div>

        <a href="<?= base_url('locations/create') ?>"
            class="btn btn-primary">
            + Tambah Lokasi
        </a>

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

                <table id="tabel-locations" class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Nama Lokasi</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody></tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<script>
(function() {
    var data = <?= json_encode($locations) ?>;
    var baseUrl = '<?= base_url() ?>';

    new DataTable('#tabel-locations', {
        data: data,
        columns: [
            { data: 'name' },
            {
                data: 'description',
                defaultContent: '-'
            },
            {
                data: 'is_active',
                render: function(data) {
                    if (data) {
                        return '<span class="badge bg-success">Aktif</span>';
                    }
                    return '<span class="badge bg-secondary">Tidak Aktif</span>';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(row) {
                    var detail = '<a href="' + baseUrl + '/locations/' + row.id + '" class="btn btn-info btn-sm">Detail</a> ';
                    var edit = '<a href="' + baseUrl + '/locations/edit/' + row.id + '" class="btn btn-warning btn-sm">Edit</a>';
                    return detail + edit;
                }
            }
        ],
        language: {
            processing: 'Memuat...',
            search: 'Cari:',
            lengthMenu: 'Tampil _MENU_ baris',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
            infoFiltered: '(difilter dari _MAX_ total data)',
            zeroRecords: 'Tidak ditemukan data yang cocok',
            emptyTable: 'Tidak ada data',
            paginate: { first: 'Awal', last: 'Akhir', next: 'Berikut', previous: 'Sebelum' }
        }
    });
})();
</script>

<?= view('layout/footer') ?>
