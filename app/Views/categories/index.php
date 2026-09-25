<?= view('layout/header', ['title' => 'Kategori Barang']) ?>
<?= view('layout/navbar') ?>

<div class="mb-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Kategori Barang</h3>
            <p class="text-muted mb-0">Kelola kategori barang inventaris.</p>
        </div>

        <button type="button" class="btn btn-primary" id="btnCreateCategory">
            + Tambah Kategori
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

<div class="modal fade" id="createCategoryModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="createCategoryForm" method="post" action="<?= base_url('categories/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="createCategoryActive" checked>
                        <label class="form-check-label" for="createCategoryActive">Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editCategoryModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCategoryForm" method="post" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="editCategoryActive">
                        <label class="form-check-label" for="editCategoryActive">Aktif</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';
    var baseUrl = Inventaris.baseUrl;

    // Inventaris.openModal() langsung menampilkan modal, jadi instance-nya
    // dibuat malas saat pertama kali dipakai — bukan saat halaman dimuat.
    var createModal = null;
    var editModal   = null;
    var createForm  = document.getElementById('createCategoryForm');
    var editForm    = document.getElementById('editCategoryForm');

    var dt = Inventaris.datatable('#tabel-categories', {
        url: baseUrl + 'categories?format=json',
        columns: [
            { data: 'name' },
            { data: 'description', render: function (d) { return Inventaris.esc(d) || '-'; } },
            { data: 'is_active', render: function (d) {
                return d ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>';
            }},
            { data: null, orderable: false, searchable: false, render: function (d, type, row) {
                return '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="' + row.id + '">Edit</button> ' +
                    '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' + row.id + '" data-name="' + Inventaris.esc(row.name) + '">Hapus</button>';
            }}
        ]
    });

    function fillEdit(row) {
        editForm.action = baseUrl + 'categories/update/' + row.id;
        editForm.elements['name'].value = row.name == null ? '' : row.name;
        editForm.elements['description'].value = row.description == null ? '' : row.description;
        document.getElementById('editCategoryActive').checked = !!Number(row.is_active);
        Inventaris.clearErrors(editForm);
    }

    function showCreate() {
        createForm.reset();
        document.getElementById('createCategoryActive').checked = true;
        Inventaris.clearErrors(createForm);
        if (!createModal) createModal = Inventaris.openModal('createCategoryModal');
        else createModal.show();
    }

    // Selalu ambil dari categories/data/:id agar isi modal sama dengan
    // data di server, bukan salinan baris tabel yang bisa sudah basi.
    function openEditById(id) {
        Inventaris.fetchJson(baseUrl + 'categories/data/' + encodeURIComponent(id))
            .then(function (row) {
                if (!row || !row.id) {
                    Inventaris.toast('Kategori tidak ditemukan.', 'danger');
                    return;
                }
                fillEdit(row);
                if (!editModal) editModal = Inventaris.openModal('editCategoryModal');
                else editModal.show();
            })
            .catch(function () {
                Inventaris.toast('Gagal memuat data kategori.', 'danger');
            });
    }

    document.getElementById('btnCreateCategory').addEventListener('click', showCreate);

    createForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function () {
                Inventaris.hideModal('createCategoryModal');
                this.reset();
                dt.ajax.reload();
            }.bind(this)
        });
    });

    editForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function () {
                Inventaris.hideModal('editCategoryModal');
                dt.ajax.reload();
            }
        });
    });

    document.getElementById('tabel-categories').addEventListener('click', function (e) {
        var editBtn = e.target.closest('.btn-edit');
        if (editBtn) {
            openEditById(editBtn.getAttribute('data-id'));
            return;
        }

        var delBtn = e.target.closest('.btn-delete');
        if (delBtn) {
            Inventaris.confirm({
                title: 'Hapus Kategori',
                message: 'Hapus kategori "' + delBtn.getAttribute('data-name') + '"?',
                onConfirm: function () {
                    Inventaris.fetchJson(baseUrl + 'categories/delete/' + delBtn.getAttribute('data-id'), {}, 'POST')
                        .then(function (json) {
                            if (json.success) {
                                Inventaris.toast(json.message);
                                dt.ajax.reload();
                            } else {
                                Inventaris.toast(json.message || 'Gagal menghapus.', 'danger');
                            }
                        })
                        .catch(function () {
                            Inventaris.toast('Koneksi gagal. Silakan coba lagi.', 'danger');
                        });
                }
            });
        }
    });

    // Deep link: /categories?create=1 atau /categories?edit=<id> membuka
    // modal yang sesuai. URL dibersihkan supaya refresh tidak membuka
    // modal lagi.
    var params = new URLSearchParams(window.location.search);
    var wantCreate = params.get('create') === '1';
    var wantEdit = params.get('edit');

    if (wantCreate || wantEdit) {
        window.history.replaceState({}, document.title, baseUrl + 'categories');
    }

    if (wantCreate) {
        showCreate();
    } else if (wantEdit) {
        openEditById(wantEdit);
    }
})();
</script>
<?= view('layout/footer') ?>
