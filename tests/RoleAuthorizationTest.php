<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * Regresi otorisasi role user.
 *
 * Bug asal: "Admin Inventaris" bisa mengubah role user tanpa batas,
 * termasuk memberikan role Super Admin, karena:
 *   - User::update() menghapus lalu menulis ulang user_roles tanpa cek
 *     otorisasi di dalam method
 *
 * Aturan yang berlaku sekarang:
 *   1. Admin Inventaris BOLEH menetapkan role.
 *   2. Hanya Super Admin yang boleh memberikan ATAU mencabut role
 *      Super Admin (termasuk mengubah role user yang sudah Super Admin).
 *   3. role_ids harus benar-benar ada di tabel roles.
 *   4. Minimal satu role.
 *   5. Super Admin terakhir tidak boleh kehilangan role-nya.
 *   6. User tidak dapat menghapus akunnya sendiri.
 *
 * @internal
 */
final class RoleAuthorizationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate = true;

    protected $migrateOnce = true;

    protected $namespace = null;

    protected int $superAdminId = 0;
    protected int $otherSuperId = 0;
    protected int $adminInvId = 0;
    protected int $petugasId = 0;
    protected int $superRoleId = 0;
    protected int $adminInvRoleId = 0;
    protected int $manajemenRoleId = 0;
    protected int $petugasRoleId = 0;
    protected int $picRoleId = 0;
    protected int $auditorRoleId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        // ─── Roles (level kecil = privilege tinggi) ─────────
        $this->superRoleId    = (int) $this->insert('roles', ['name' => 'Super Admin', 'level' => 1]);
        $this->adminInvRoleId = (int) $this->insert('roles', ['name' => 'Admin Inventaris', 'level' => 2]);
        $this->manajemenRoleId = (int) $this->insert('roles', ['name' => 'Manajemen', 'level' => 3]);
        $this->petugasRoleId  = (int) $this->insert('roles', ['name' => 'Petugas Inventaris', 'level' => 4]);
        $this->picRoleId      = (int) $this->insert('roles', ['name' => 'PIC Unit', 'level' => 5]);
        $this->auditorRoleId  = (int) $this->insert('roles', ['name' => 'Auditor', 'level' => 6]);

        // ─── Users ───────────────────────────────────────────
        $this->superAdminId = (int) $this->insert('users', [
            'username' => 'superadmin1', 'name' => 'Super Admin Satu', 'is_active' => 1,
        ]);
        $this->otherSuperId = (int) $this->insert('users', [
            'username' => 'superadmin2', 'name' => 'Super Admin Dua', 'is_active' => 1,
        ]);
        $this->adminInvId = (int) $this->insert('users', [
            'username' => 'admininv1', 'name' => 'Admin Inventaris', 'is_active' => 1,
        ]);
        $this->petugasId = (int) $this->insert('users', [
            'username' => 'petugas1', 'name' => 'Petugas', 'is_active' => 1,
        ]);

        $this->assignRole($this->superAdminId, $this->superRoleId);
        $this->assignRole($this->adminInvId, $this->adminInvRoleId);
        $this->assignRole($this->petugasId, $this->petugasRoleId);
    }

    private function insert(string $table, array $data): int
    {
        $db = db_connect();
        $db->table($table)->insert($data);

        return (int) $db->insertID();
    }

    private function assignRole(int $userId, int $roleId): void
    {
        $this->insert('user_roles', [
            'user_id'    => $userId,
            'role_id'    => $roleId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** @return array<int, int> daftar role_id milik user */
    private function rolesOf(int $userId): array
    {
        $rows = db_connect()->table('user_roles')->where('user_id', $userId)->get()->getResultArray();

        return array_map('intval', array_column($rows, 'role_id'));
    }

    private function loginAsSuperAdmin(): void
    {
        $this->withSession([
            'user_id'      => $this->superAdminId,
            'username'     => 'superadmin1',
            'name'         => 'Super Admin Satu',
            'location_ids' => [],
            'role_ids'     => [$this->superRoleId],
            'roles'        => ['Super Admin'],
            'permissions'  => ['*'],
            'isLoggedIn'   => true,
        ]);
    }

    private function loginAsAdminInventaris(): void
    {
        $this->withSession([
            'user_id'      => $this->adminInvId,
            'username'     => 'admininv1',
            'name'         => 'Admin Inventaris',
            'location_ids' => [],
            'role_ids'     => [$this->adminInvRoleId],
            'roles'        => ['Admin Inventaris'],
            'permissions'  => [],
            'isLoggedIn'   => true,
        ]);
    }

    // ─── 1. Admin Inventaris BOLEH menetapkan role ────────

    public function testAdminInventarisCanAssignRoleBelowTheirLevel(): void
    {
        $this->loginAsAdminInventaris();

        $this->post("/users/{$this->petugasId}/roles", [
            'role_ids' => [$this->manajemenRoleId],
        ]);

        $this->assertContains(
            $this->manajemenRoleId,
            $this->rolesOf($this->petugasId),
            'Admin Inventaris (level 2) tidak boleh memberikan role level 3'
        );
    }

    public function testAdminInventarisCannotAssignOwnTier(): void
    {
        $this->loginAsAdminInventaris();

        $before = $this->rolesOf($this->petugasId);

        $this->post("/users/{$this->petugasId}/roles", [
            'role_ids' => [$this->adminInvRoleId],
        ]);

        $this->assertSame(
            $before,
            $this->rolesOf($this->petugasId),
            'Admin Inventaris berhasil memberikan role setingkat dirinya sendiri'
        );
    }

    public function testMixedPostTouchingOwnTierIsRejectedWhole(): void
    {
        $this->loginAsAdminInventaris();

        $before = $this->rolesOf($this->petugasId);

        $this->post("/users/{$this->petugasId}/roles", [
            'role_ids' => [$this->superRoleId, $this->auditorRoleId],
        ]);

        $this->assertSame(
            $before,
            $this->rolesOf($this->petugasId),
            'Request dengan role di level sendiri diterapkan sebagian'
        );
    }

    public function testAuthorityLevelUsesTheMostPrivilegedRoleHeld(): void
    {
        // Pemegang Admin Inventaris(2) + Petugas(4) punya otoritas level 2.
        $this->withSession([
            'user_id'      => $this->adminInvId,
            'username'     => 'admininv1',
            'name'         => 'Admin Inventaris',
            'location_ids' => [],
            'role_ids'     => [$this->adminInvRoleId, $this->petugasRoleId],
            'roles'        => ['Admin Inventaris', 'Petugas Inventaris'],
            'permissions'  => [],
            'isLoggedIn'   => true,
        ]);

        $before = $this->rolesOf($this->petugasId);

        // level 6 > 2 -> boleh
        $this->post("/users/{$this->petugasId}/roles", ['role_ids' => [$this->auditorRoleId]]);
        $this->assertContains($this->auditorRoleId, $this->rolesOf($this->petugasId));

        // level 4 > 2 -> boleh (walaupun pemanggil juga memegang role ini)
        $this->post("/users/{$this->petugasId}/roles", ['role_ids' => [$this->picRoleId]]);
        $this->assertContains($this->picRoleId, $this->rolesOf($this->petugasId));

        // level 1 dan 2 -> ditolak
        $this->post("/users/{$this->petugasId}/roles", ['role_ids' => [$this->superRoleId]]);
        $this->assertNotContains($this->superRoleId, $this->rolesOf($this->petugasId));
    }

    // ─── 2. Super Admin bersifat mutlak ────────────────────

    public function testAdminInventarisCannotGrantSuperAdmin(): void
    {
        $this->loginAsAdminInventaris();

        $this->post("/users/{$this->adminInvId}/roles", [
            'role_ids' => [$this->superRoleId],
        ]);

        $this->assertNotContains(
            $this->superRoleId,
            $this->rolesOf($this->adminInvId),
            'Admin Inventaris berhasilMEMBERIKAN role Super Admin ke dirinya sendiri'
        );
    }

    public function testAdminInventarisCannotGrantSuperAdminToOthers(): void
    {
        $this->loginAsAdminInventaris();

        $this->post("/users/{$this->petugasId}/roles", [
            'role_ids' => [$this->superRoleId, $this->picRoleId],
        ]);

        $roles = $this->rolesOf($this->petugasId);
        $this->assertNotContains($this->superRoleId, $roles);
    }

    /**
     * Mengubah role user yang sudah Super Admin selalu berarti
     * mencabut Super Admin-nya — jadi harus ditolak untuk non-SuperAdmin.
     */
    public function testAdminInventarisCannotChangeRolesOfASuperAdmin(): void
    {
        $this->loginAsAdminInventaris();

        $this->post("/users/{$this->superAdminId}/roles", [
            'role_ids' => [$this->picRoleId],
        ]);

        $this->assertContains(
            $this->superRoleId,
            $this->rolesOf($this->superAdminId),
            'Super Admin kehilangan role-nya karena Admin Inventaris'
        );
    }

    public function testSuperAdminCannotDemoteTheLastSuperAdmin(): void
    {
        $this->loginAsSuperAdmin();

        $this->post("/users/{$this->superAdminId}/roles", [
            'role_ids' => [$this->adminInvRoleId],
        ]);

        $this->assertContains(
            $this->superRoleId,
            $this->rolesOf($this->superAdminId),
            'Super Admin terakhir berhasil kehilangan role-nya — sistem terkunci'
        );
    }

    public function testSuperAdminMayGrantSuperAdmin(): void
    {
        $this->loginAsSuperAdmin();

        $this->post("/users/{$this->petugasId}/roles", [
            'role_ids' => [$this->superRoleId],
        ]);

        $this->assertContains($this->superRoleId, $this->rolesOf($this->petugasId));
    }

    public function testSuperAdminMayDemoteWhenAnotherSuperAdminRemains(): void
    {
        $this->assignRole($this->otherSuperId, $this->superRoleId);
        $this->loginAsSuperAdmin();

        $this->post("/users/{$this->superAdminId}/roles", [
            'role_ids' => [$this->adminInvRoleId],
        ]);

        $roles = $this->rolesOf($this->superAdminId);
        $this->assertNotContains($this->superRoleId, $roles);
        $this->assertContains($this->adminInvRoleId, $roles);
    }

    // ─── 3. Role tidak bisa diselundupkan lewat User::update ─

    public function testRoleCannotBeSmuggledThroughUserUpdate(): void
    {
        $this->loginAsAdminInventaris();

        $before = $this->rolesOf($this->superAdminId);

        $this->post("/users/update/{$this->superAdminId}", [
            'full_name' => 'Super Admin Satu',
            'is_active' => 1,
            'role_ids'  => [$this->picRoleId],
        ]);

        $this->assertSame(
            $before,
            $this->rolesOf($this->superAdminId),
            'User::update() masih mengubah role — selundupan kembali'
        );
    }

    // ─── 4. Validasi input ─────────────────────────────────

    public function testUnknownRoleIdIsRejected(): void
    {
        $this->loginAsSuperAdmin();
        $before = $this->rolesOf($this->petugasId);

        $this->post("/users/{$this->petugasId}/roles", ['role_ids' => [999999]]);

        $this->assertSame($before, $this->rolesOf($this->petugasId));
    }

    public function testEmptyRoleSetIsRejected(): void
    {
        $this->loginAsSuperAdmin();
        $before = $this->rolesOf($this->petugasId);

        $this->post("/users/{$this->petugasId}/roles", []);

        $this->assertSame($before, $this->rolesOf($this->petugasId));
    }

    // ─── 5. UI: checkbox Super Admin disembunyikan ─────────

    public function testEditPageHidesSuperAdminOptionFromAdminInventaris(): void
    {
        $this->loginAsAdminInventaris();

        $result = $this->get("/users/edit/{$this->petugasId}");

        $result->assertOK();
        $body = (string) $result->getBody();

        // Hanya role berlevel > 2 yang boleh diberikan oleh Admin Inventaris.
        $this->assertStringNotContainsString('value="' . $this->superRoleId . '"', $body);
        $this->assertStringNotContainsString('value="' . $this->adminInvRoleId . '"', $body);

        foreach ([$this->manajemenRoleId, $this->petugasRoleId, $this->picRoleId, $this->auditorRoleId] as $allowed) {
            $this->assertStringContainsString('value="' . $allowed . '"', $body);
        }

        $this->assertStringContainsString('Simpan Role', $body, 'Admin Inventaris harus tetap bisa menetapkan role');
    }

    public function testEditPageShowsSuperAdminOptionToSuperAdmin(): void
    {
        $this->loginAsSuperAdmin();

        $result = $this->get("/users/edit/{$this->petugasId}");

        $result->assertOK();
        $this->assertStringContainsString('value="' . $this->superRoleId . '"', (string) $result->getBody());
    }

    public function testEditPageIsReadOnlyForSuperAdminTargetWhenViewerIsNotSuperAdmin(): void
    {
        $this->loginAsAdminInventaris();

        $result = $this->get("/users/edit/{$this->superAdminId}");

        $result->assertOK();
        $body = (string) $result->getBody();
        $this->assertStringNotContainsString('Simpan Role', $body);
        $this->assertStringContainsString('hanya dapat diubah oleh Super Admin', $body);
    }

    // ─── 6. Delete protections ─────────────────────────────

    public function testUserCannotDeleteOwnAccount(): void
    {
        $this->loginAsSuperAdmin();

        $this->post("/users/delete/{$this->superAdminId}");

        $this->assertTrue(
            (bool) db_connect()->table('users')->where('id', $this->superAdminId)->get()->getRow(),
            'User dapat menghapus akunnya sendiri'
        );
    }

    public function testLastSuperAdminCannotBeDeleted(): void
    {
        $this->withSession([
            'user_id'      => $this->otherSuperId,
            'username'     => 'superadmin2',
            'name'         => 'Super Admin Dua',
            'location_ids' => [],
            'role_ids'     => [$this->superRoleId],
            'roles'        => ['Super Admin'],
            'permissions'  => ['*'],
            'isLoggedIn'   => true,
        ]);

        $this->post("/users/delete/{$this->superAdminId}");

        $this->assertTrue(
            (bool) db_connect()->table('users')->where('id', $this->superAdminId)->get()->getRow(),
            'Super Admin terakhir berhasil dihapus'
        );
    }

    public function testAdminInventarisCannotDeleteASuperAdmin(): void
    {
        $this->loginAsAdminInventaris();

        $this->post("/users/delete/{$this->superAdminId}");

        $this->assertTrue(
            (bool) db_connect()->table('users')->where('id', $this->superAdminId)->get()->getRow(),
            'Admin Inventaris berhasil menghapus Super Admin'
        );
    }

    // ─── 7. Manajemen role: khusus Super Admin ─────────────

    public function testAdminInventarisCannotReachRoleManagement(): void
    {
        // Definisi role menentukan privilege seluruh sistem, jadi hanya
        // Super Admin. Admin Inventaris tetap bisa memberikan role yang
        // sudah ada lewat /users, tapi tidak bisa membuat/mengubah role.
        $this->loginAsAdminInventaris();

        $this->get('/roles')->assertRedirect();
    }

    public function testPetugasCannotReachRoleManagement(): void
    {
        $this->withSession([
            'user_id'      => $this->petugasId,
            'username'     => 'petugas1',
            'name'         => 'Petugas',
            'location_ids' => [],
            'role_ids'     => [$this->petugasRoleId],
            'roles'        => ['Petugas Inventaris'],
            'permissions'  => [],
            'isLoggedIn'   => true,
        ]);

        $this->get('/roles')->assertRedirect();
    }

    public function testSuperAdminCanReachRoleManagement(): void
    {
        $this->loginAsSuperAdmin();

        $this->get('/roles')->assertOK();
    }

    public function testSuperAdminRoleCannotBeDeletedByAnyone(): void
    {
        $this->loginAsSuperAdmin();

        $this->post("/roles/delete/{$this->superRoleId}")->assertRedirect();

        $this->assertNotNull(
            db_connect()->table('roles')->where('id', $this->superRoleId)->get()->getRow(),
            'Role Super Admin berhasil dihapus'
        );
    }

    public function testRoleStillInUseCannotBeDeleted(): void
    {
        // user_roles memakai ON DELETE CASCADE: menghapus role yang dipakai
        // akan diam-diam melepas role itu dari semua user.
        $this->loginAsSuperAdmin();

        $this->post("/roles/delete/{$this->adminInvRoleId}")->assertRedirect();

        $this->assertNotNull(
            db_connect()->table('roles')->where('id', $this->adminInvRoleId)->get()->getRow()
        );
        $this->assertNotEmpty($this->rolesOf($this->adminInvId));
    }

    public function testSuperAdminCannotCreateDuplicateRoleName(): void
    {
        $this->loginAsSuperAdmin();

        $this->post('/roles/store', ['name' => 'Super Admin', 'level' => 4]);

        $count = (int) db_connect()->table('roles')
            ->where('name', 'Super Admin')->countAllResults();

        $this->assertSame(1, $count, 'Role decoy "Super Admin" berhasil dibuat — eskalasi privilege');
    }

    public function testSuperAdminCanManageCustomRoles(): void
    {
        $this->loginAsSuperAdmin();

        $this->post('/roles/store', ['name' => 'Probe Custom', 'level' => 4]);
        $row = db_connect()->table('roles')->where('name', 'Probe Custom')->get()->getRow();
        $this->assertNotNull($row, 'Super Admin tidak dapat membuat role');

        $this->post("/roles/update/{$row->id}", [
            'name'  => 'Probe Custom Renamed',
            'level' => 5,
        ]);
        $after = db_connect()->table('roles')->where('id', $row->id)->get()->getRow();
        $this->assertSame('Probe Custom Renamed', $after->name);
        $this->assertSame(5, (int) $after->level);

        $this->post("/roles/delete/{$row->id}")->assertRedirect();
        $this->assertNull(db_connect()->table('roles')->where('id', $row->id)->get()->getRow());
    }

    public function testProtectedRoleNamesCannotBeRenamed(): void
    {
        $this->loginAsSuperAdmin();

        foreach (['Petugas Inventaris', 'Manajemen', 'Auditor'] as $name) {
            $id = (int) db_connect()->table('roles')->where('name', $name)->get()->getRow('id');

            $this->post("/roles/update/{$id}", ['name' => 'Renamed X', 'level' => 4]);

            $this->assertNotNull(
                db_connect()->table('roles')->where('name', $name)->get()->getRow(),
                "Nama role '$name' bisa di-rename — filter akses ikut mati"
            );
        }
    }
}
