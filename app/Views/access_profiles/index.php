<?= view('layout/header', ['title' => 'Profile Akses']) ?>
<?= view('layout/navbar') ?>

<div class="mb-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Profile Akses</h3>
            <p class="text-muted mb-0">Kelola profile akses — grup lokasi yang dapat diterapkan ke user.</p>
        </div>

        <button type="button" class="btn btn-primary" id="btnCreateProfile">
            + Tambah Profile
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
                <table id="tabel-profiles" class="table table-hover align-middle w-100">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Deskripsi</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

</div>

<?php
$locationChecks = function (string $prefix, array $checked = []) use ($locations) {
    if (empty($locations)) {
        echo '<div class="text-muted">Belum ada lokasi aktif.</div>';
        return;
    }
    ?>
    <div class="mb-2">
        <button type="button" class="btn btn-sm btn-outline-secondary me-1 select-all-locs">
            Pilih Semua
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary unselect-all-locs">
            Kosongkan
        </button>
    </div>
    <?php foreach ($locations as $loc):
        $id = (int) $loc['id'];
        ?>
        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   name="location_ids[]" value="<?= $id ?>"
                   id="<?= $prefix ?>Location<?= $id ?>"
                   <?= in_array($id, $checked, true) ? 'checked' : '' ?>>
            <label class="form-check-label" for="<?= $prefix ?>Location<?= $id ?>">
                <?= esc($loc['name']) ?>
            </label>
        </div>
    <?php endforeach;
};
?>

<div class="modal fade" id="createProfileModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="createProfileForm" method="post" action="<?= base_url('access-profiles/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Profile</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi</label>
                        <div class="border rounded p-3">
                            <?php $locationChecks('createProfile'); ?>
                        </div>
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

<div class="modal fade" id="editProfileModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="editProfileForm" method="post" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Profile</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi</label>
                        <div class="border rounded p-3" id="editProfileLocations">
                            <?php $locationChecks('editProfile'); ?>
                        </div>
                    </div>
                    <div class="mb-3" id="editProfileUserCount" style="display:none">
                        <div class="alert alert-info mb-0">
                            <span id="editProfileUserCountText"></span> user memakai profile ini.
                        </div>
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

    var createModal = null;
    var editModal   = null;
    var createForm  = document.getElementById('createProfileForm');
    var editForm    = document.getElementById('editProfileForm');

    var dt = Inventaris.datatable('#tabel-profiles', {
        url: baseUrl + 'access-profiles?format=json',
        columns: [
            { data: 'name' },
            { data: 'description', render: function (d) { return Inventaris.esc(d) || '-'; } },
            { data: 'created_at', render: function (d) { return Inventaris.esc(d || '-'); } },
            { data: null, orderable: false, searchable: false, render: function (d, type, row) {
                return '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="' + row.id + '">Edit</button> ' +
                    '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="' + row.id + '" data-name="' + Inventaris.esc(row.name) + '">Hapus</button>';
            }}
        ]
    });

    function bindSelectAll(container) {
        var btnAll = container.querySelector('.select-all-locs');
        var btnNone = container.querySelector('.unselect-all-locs');
        if (btnAll) {
            btnAll.addEventListener('click', function () {
                container.querySelectorAll('[name="location_ids[]"]').forEach(function (cb) {
                    cb.checked = true;
                });
            });
        }
        if (btnNone) {
            btnNone.addEventListener('click', function () {
                container.querySelectorAll('[name="location_ids[]"]').forEach(function (cb) {
                    cb.checked = false;
                });
            });
        }
    }

    bindSelectAll(document.querySelector('#createProfileModal'));
    bindSelectAll(document.querySelector('#editProfileLocations').closest('.modal'));

    function fillEdit(row) {
        editForm.action = baseUrl + 'access-profiles/update/' + row.id;
        editForm.elements['name'].value = row.name == null ? '' : row.name;
        editForm.elements['description'].value = row.description == null ? '' : row.description;

        var locs = (row.location_ids || []).map(Number);
        editForm.querySelectorAll('[name="location_ids[]"]').forEach(function (cb) {
            cb.checked = locs.indexOf(Number(cb.value)) !== -1;
        });

        var userCount = row.user_count || 0;
        var el = document.getElementById('editProfileUserCount');
        if (userCount > 0) {
            el.style.display = '';
            document.getElementById('editProfileUserCountText').textContent = userCount;
        } else {
            el.style.display = 'none';
        }

        Inventaris.clearErrors(editForm);
    }

    function showCreate() {
        createForm.reset();
        createForm.querySelectorAll('[name="location_ids[]"]').forEach(function (cb) {
            cb.checked = false;
        });
        Inventaris.clearErrors(createForm);
        if (!createModal) createModal = Inventaris.openModal('createProfileModal');
        else createModal.show();
    }

    function openEditById(id) {
        Inventaris.fetchJson(baseUrl + 'access-profiles/data/' + encodeURIComponent(id))
            .then(function (row) {
                if (!row || !row.id) {
                    Inventaris.toast('Profile tidak ditemukan.', 'danger');
                    return;
                }
                fillEdit(row);
                if (!editModal) editModal = Inventaris.openModal('editProfileModal');
                else editModal.show();
            })
            .catch(function () {
                Inventaris.toast('Gagal memuat data profile.', 'danger');
            });
    }

    document.getElementById('btnCreateProfile').addEventListener('click', showCreate);

    createForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function () {
                Inventaris.hideModal('createProfileModal');
                this.reset();
                dt.ajax.reload();
            }.bind(this)
        });
    });

    editForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function () {
                Inventaris.hideModal('editProfileModal');
                dt.ajax.reload();
            }
        });
    });

    document.getElementById('tabel-profiles').addEventListener('click', function (e) {
        var editBtn = e.target.closest('.btn-edit');
        if (editBtn) {
            openEditById(editBtn.getAttribute('data-id'));
            return;
        }

        var delBtn = e.target.closest('.btn-delete');
        if (delBtn) {
            Inventaris.confirm({
                title: 'Hapus Profile',
                message: 'Hapus profile "' + delBtn.getAttribute('data-name') + '"?',
                onConfirm: function () {
                    Inventaris.fetchJson(baseUrl + 'access-profiles/delete/' + delBtn.getAttribute('data-id'), {}, 'POST')
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

    var params = new URLSearchParams(window.location.search);
    var wantCreate = params.get('create') === '1';
    var wantEdit = params.get('edit');

    if (wantCreate || wantEdit) {
        window.history.replaceState({}, document.title, baseUrl + 'access-profiles');
    }

    if (wantCreate) {
        showCreate();
    } else if (wantEdit) {
        openEditById(wantEdit);
    }
})();
</script>
<?= view('layout/footer') ?>
