<?= view('layout/header', ['title' => 'User Management']) ?>
<?= view('layout/navbar') ?>

<div class="mb-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>User Management</h3>
            <p class="text-muted mb-0">
                Kelola pengguna dari UserGate dan atur role/lokasi lokal.
            </p>
        </div>

        <button type="button" class="btn btn-primary" id="btnCreateUser">
            + Tambah User
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
                <table id="tabel-users" class="table table-hover align-middle w-100">
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
                </table>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="createUserModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form id="createUserForm" method="post" action="<?= base_url('users/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Tambah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" minlength="8" required>
                        <small class="text-muted">Minimal 8 karakter.</small>
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

<div class="modal fade" id="editUserModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <!--
                  Profil dan role sengaja dipisah menjadi dua form.
                  Perubahan role harus lewat User::updateRoles(), yang
                  memvalidasi hierarchy level dan melindungi role Super
                  Admin. Menggabungkannya ke users/update/{id} akan
                  melewati proteksi itu.
                -->
                <form id="editUserProfileForm" method="post" action="">
                    <?= csrf_field() ?>
                    <h6 class="text-uppercase text-muted small">Profil</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="full_name" id="editUserFullName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email (UserGate)</label>
                            <input type="email" id="editUserEmail" class="form-control" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Akun (UserGate)</label>
                            <select name="status" id="editUserStatus" class="form-select">
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="INACTIVE">INACTIVE</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input type="checkbox" name="is_active" value="1"
                                       class="form-check-input" id="editUserActive">
                                <label class="form-check-label" for="editUserActive">Aktif (lokal)</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Lokasi yang Dapat Diakses</label>
                            <div class="border rounded p-3">
                                <?php if (empty($locations)): ?>
                                    <div class="text-muted">Belum ada lokasi.</div>
                                <?php else: ?>
                                    <?php foreach ($locations as $loc): ?>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox"
                                                   name="location_ids[]" value="<?= (int) $loc['id'] ?>"
                                                   id="editUserLocation<?= (int) $loc['id'] ?>">
                                            <label class="form-check-label"
                                                   for="editUserLocation<?= (int) $loc['id'] ?>">
                                                <?= esc($loc['name']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">Simpan Profil</button>
                    </div>
                </form>

                <hr>

                <form id="editUserRoleForm" method="post" action="">
                    <?= csrf_field() ?>
                    <h6 class="text-uppercase text-muted small">Role (Lokal)</h6>
                    <?php if (empty($assignableRoles)): ?>
                        <div class="text-muted">Tidak ada role yang dapat Anda berikan.</div>
                    <?php else: ?>
                        <div class="border rounded p-3">
                            <?php foreach ($assignableRoles as $role): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox"
                                           name="role_ids[]" value="<?= (int) $role['id'] ?>"
                                           id="editUserRole<?= (int) $role['id'] ?>">
                                    <label class="form-check-label"
                                           for="editUserRole<?= (int) $role['id'] ?>">
                                        <?= esc($role['name']) ?>
                                        <span class="text-muted">(level <?= (int) $role['level'] ?>)</span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-warning" id="btnSaveUserRoles"
                                <?= empty($assignableRoles) ? 'disabled' : '' ?>>
                            Simpan Role
                        </button>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detailUserModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Username:</strong> <span id="detailUserUsername">-</span></p>
                        <p class="mb-1"><strong>Nama:</strong> <span id="detailUserName">-</span></p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1" id="detailUserUgRow"><strong>Email:</strong> <span id="detailUserEmail">-</span></p>
                        <p class="mb-1" id="detailUserUgStatusRow"><strong>Status UserGate:</strong> <span id="detailUserUgStatus">-</span></p>
                        <p class="mb-1"><strong>Status Lokal:</strong> <span id="detailUserLocalStatus">-</span></p>
                    </div>
                </div>

                <hr>

                <h6>Role (Lokal)</h6>
                <div id="detailUserRoles" class="mb-3"></div>

                <h6>Lokasi yang Diizinkan</h6>
                <div id="detailUserLocations" class="mb-1"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="detailUserEdit">Edit User</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    var baseUrl = Inventaris.baseUrl;

    var createModal = null;
    var editModal   = null;
    var createForm  = document.getElementById('createUserForm');
    var profileForm = document.getElementById('editUserProfileForm');
    var roleForm    = document.getElementById('editUserRoleForm');

    var dt = Inventaris.datatable('#tabel-users', {
        url: baseUrl + 'users?format=json',
        columns: [
            { data: 'name', orderable: false },
            { data: 'username', orderable: false },
            {
                data: 'ug_data',
                orderable: false,
                render: function(ugData) {
                    return (ugData && ugData.email) ? Inventaris.esc(ugData.email) : '-';
                }
            },
            {
                data: 'roles',
                orderable: false,
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
                orderable: false,
                render: function(data) {
                    return data
                        ? '<span class="badge bg-success">Aktif</span>'
                        : '<span class="badge bg-secondary">Nonaktif</span>';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(d, type, row) {
                    return '<button type="button" class="btn btn-sm btn-outline-primary btn-detail" data-id="' + row.id + '">Detail</button> ' +
                        '<button type="button" class="btn btn-sm btn-warning btn-edit" data-id="' + row.id + '">Edit</button>';
                }
            }
        ]
    });

    function fillEdit(row) {
        var ug = row.ug_data || {};

        profileForm.action = baseUrl + 'users/update/' + row.id;
        document.getElementById('editUserFullName').value = row.name == null ? '' : row.name;
        document.getElementById('editUserEmail').value = ug.email || '';
        document.getElementById('editUserStatus').value = ug.status || 'ACTIVE';
        document.getElementById('editUserActive').checked = !!Number(row.is_active);

        roleForm.action = baseUrl + 'users/' + row.id + '/roles';

        var locs = (row.location_ids || []).map(Number);
        profileForm.querySelectorAll('[name="location_ids[]"]').forEach(function (cb) {
            cb.checked = locs.indexOf(Number(cb.value)) !== -1;
        });

        var roles = (row.role_ids || []).map(Number);
        roleForm.querySelectorAll('[name="role_ids[]"]').forEach(function (cb) {
            cb.checked = roles.indexOf(Number(cb.value)) !== -1;
        });

        Inventaris.clearErrors(profileForm);
        Inventaris.clearErrors(roleForm);
    }

    function showCreate() {
        createForm.reset();
        Inventaris.clearErrors(createForm);
        if (!createModal) createModal = Inventaris.openModal('createUserModal');
        else createModal.show();
    }

    // ── Modal detail ────────────────────────────────────────
    // Tidak ada ?detail=<id>: ID tidak pernah masuk URL, jadi tidak
    // muncul di address bar maupun riwayat browser.
    var detailModal = null;
    var detailId = null;

    function fillDetail(row) {
        detailId = row.id;
        var ug = row.ug_data || {};

        document.getElementById('detailUserUsername').textContent = row.username || '-';
        document.getElementById('detailUserName').textContent = row.name || '-';

        // Baris UserGate hanya ditampilkan bila user punya data di sana,
        // sama seperti halaman detail.
        document.getElementById('detailUserUgRow').hidden = !ug.email;
        document.getElementById('detailUserUgStatusRow').hidden = !ug.status;
        document.getElementById('detailUserEmail').textContent = ug.email || '-';
        document.getElementById('detailUserUgStatus').innerHTML = (ug.status || 'ACTIVE') === 'ACTIVE'
            ? '<span class="badge bg-success">Aktif</span>'
            : '<span class="badge bg-secondary">Nonaktif</span>';

        document.getElementById('detailUserLocalStatus').innerHTML = Number(row.is_active) === 1
            ? '<span class="badge bg-success">Aktif</span>'
            : '<span class="badge bg-secondary">Nonaktif</span>';

        var roles = row.roles || [];
        document.getElementById('detailUserRoles').innerHTML = roles.length === 0
            ? '<div class="alert alert-warning mb-0">User belum memiliki role.</div>'
            : '<div class="list-group">' + roles.map(function (r) {
                return '<div class="list-group-item"><strong>' + Inventaris.esc(r.name) + '</strong>' +
                    (r.description ? '<div class="text-muted small">' + Inventaris.esc(r.description) + '</div>' : '') +
                    '</div>';
            }).join('') + '</div>';

        var locs = row.locations || [];
        document.getElementById('detailUserLocations').innerHTML = locs.length === 0
            ? '<div class="alert alert-warning mb-0">User belum memiliki lokasi.</div>'
            : '<div class="list-group">' + locs.map(function (l) {
                return '<div class="list-group-item"><strong>' + Inventaris.esc(l.name) + '</strong>' +
                    '<div class="text-muted small">' +
                    Inventaris.esc(l.building || '-') + ' - ' +
                    Inventaris.esc(l.floor || '-') + ' - ' +
                    Inventaris.esc(l.room || '-') +
                    '</div></div>';
            }).join('') + '</div>';
    }

    function openDetailById(id) {
        Inventaris.fetchJson(baseUrl + 'users/data/' + encodeURIComponent(id))
            .then(function (row) {
                if (!row || !row.id) {
                    Inventaris.toast((row && row.message) || 'User tidak ditemukan.', 'danger');
                    return;
                }
                fillDetail(row);
                if (!detailModal) detailModal = Inventaris.openModal('detailUserModal');
                else detailModal.show();
            })
            .catch(function () {
                Inventaris.toast('Gagal memuat data user.', 'danger');
            });
    }

    document.getElementById('detailUserEdit').addEventListener('click', function () {
        if (!detailId) return;
        Inventaris.hideModal('detailUserModal');
        openEditById(detailId);
    });

    // Ambil dari users/data/:id: baris tabel tidak memuat location_ids
    // maupun role_ids, jadi mengisi checkbox dari baris akan menghapus
    // keduanya saat disimpan.
    function openEditById(id) {
        Inventaris.fetchJson(baseUrl + 'users/data/' + encodeURIComponent(id))
            .then(function (row) {
                if (!row || !row.id) {
                    Inventaris.toast('User tidak ditemukan.', 'danger');
                    return;
                }
                fillEdit(row);
                if (!editModal) editModal = Inventaris.openModal('editUserModal');
                else editModal.show();
            })
            .catch(function () {
                Inventaris.toast('Gagal memuat data user.', 'danger');
            });
    }

    document.getElementById('btnCreateUser').addEventListener('click', showCreate);

    createForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function () {
                Inventaris.hideModal('createUserModal');
                this.reset();
                dt.ajax.reload();
            }.bind(this)
        });
    });

    profileForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function () {
                Inventaris.toast('Profil user berhasil diperbarui.');
                dt.ajax.reload();
            }
        });
    });

    roleForm.addEventListener('submit', function (e) {
        e.preventDefault();
        Inventaris.submitAjax(this, {
            onSuccess: function () {
                Inventaris.hideModal('editUserModal');
                dt.ajax.reload();
            }
        });
    });

    document.getElementById('tabel-users').addEventListener('click', function (e) {
        var detailBtn = e.target.closest('.btn-detail');
        if (detailBtn) {
            openDetailById(detailBtn.getAttribute('data-id'));
            return;
        }

        var editBtn = e.target.closest('.btn-edit');
        if (editBtn) openEditById(editBtn.getAttribute('data-id'));
    });

    // Deep link: /users?create=1 atau /users?edit=<id>
    var params = new URLSearchParams(window.location.search);
    var wantCreate = params.get('create') === '1';
    var wantEdit = params.get('edit');

    if (wantCreate || wantEdit) {
        window.history.replaceState({}, document.title, baseUrl + 'users');
    }

    if (wantCreate) {
        showCreate();
    } else if (wantEdit) {
        openEditById(wantEdit);
    }
})();
</script>

<?= view('layout/footer') ?>
