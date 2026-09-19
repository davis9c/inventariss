<?= view('layout/header', ['title' => 'Kategori Barang']) ?>
<?= view('layout/sidebar') ?>

<div class="mb-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Kategori Barang</h3>
            <p class="text-muted mb-0">Kelola kategori barang inventaris.</p>
        </div>

        <a href="<?= base_url('categories/create') ?>" class="btn btn-primary">
            + Tambah Kategori
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tabel-categories" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

</div>

<script>
(function () {
    'use strict';
    var baseUrl = Inventaris.baseUrl;
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
    var data = <?= json_encode($categories) ?>;

    var dt = new DataTable('#tabel-categories', {
        data: data,
        columns: [
            { data: 'name' },
            { data: 'description', render: function (d) { return Inventaris.esc(d) || '-'; } },
            { data: 'is_active', render: function (d) {
                return d ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>';
            }},
            { data: null, orderable: false, searchable: false, render: function (d) {
                return '<a href="' + baseUrl + 'categories/edit/' + d.id + '" class="btn btn-sm btn-warning">Edit</a> ' +
                    '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' + d.id + '" data-name="' + Inventaris.esc(d.name) + '">Hapus</button>';
            }}
        ],
        language: { processing: 'Memuat...', search: 'Cari:', lengthMenu: 'Tampil _MENU_ baris', info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data', infoEmpty: 'Tidak ada data', infoFiltered: '(difilter dari _MAX_ total)', zeroRecords: 'Tidak ditemukan', emptyTable: 'Tidak ada data', paginate: { first: 'Awal', last: 'Akhir', next: 'Berikut', previous: 'Sebelum' } }
    });

    document.getElementById('tabel-categories').addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-delete');
        if (!btn) return;
        Inventaris.confirm({
            title: 'Hapus Kategori',
            message: 'Hapus kategori "' + btn.getAttribute('data-name') + '"?',
            onConfirm: function () {
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = baseUrl + 'categories/delete/' + btn.getAttribute('data-id');
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = csrfName;
                input.value = csrfHash;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
})();
</script>
<?= view('layout/footer') ?>
