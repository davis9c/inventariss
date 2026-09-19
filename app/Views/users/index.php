<?= view('layout/header', ['title' => 'User Management']) ?>
<?= view('layout/sidebar') ?>

<div class="mb-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>User Management</h3>
            <p class="text-muted mb-0">
                Kelola pengguna dari UserGate dan atur role/lokasi lokal.
            </p>
        </div>

        <a href="<?= base_url('users/create') ?>"
            class="btn btn-primary">
            + Tambah User
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

                <table id="tabel-users" class="table table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role (Lokal)</th>
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
    var data = <?= json_encode($users) ?>;
    var baseUrl = '<?= base_url() ?>';

    new DataTable('#tabel-users', {
        data: data,
        columns: [
            { data: 'name' },
            { data: 'username' },
            {
                data: 'ug_data',
                render: function(ugData) {
                    return (ugData && ugData.email) ? Inventaris.esc(ugData.email) : '-';
                }
            },
            {
                data: 'roles',
                render: function(roles) {
                    if (!roles || roles.length === 0) {
                        return '<span class="text-muted">Belum ada role</span>';
                    }
                    var html = '';
                    for (var i = 0; i < roles.length; i++) {
                        html += '<span class="badge bg-primary me-1">' + Inventaris.esc(roles[i].name) + '</span>';
                    }
                    return html;
                }
            },
            {
                data: 'is_active',
                render: function(data) {
                    if (data) {
                        return '<span class="badge bg-success">Aktif</span>';
                    }
                    return '<span class="badge bg-secondary">Nonaktif</span>';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(row) {
                    var detail = '<a href="' + baseUrl + '/users/' + row.id + '" class="btn btn-sm btn-info">Detail</a> ';
                    var edit = '<a href="' + baseUrl + '/users/edit/' + row.id + '" class="btn btn-sm btn-warning">Edit</a>';
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
