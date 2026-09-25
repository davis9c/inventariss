<?php


if (!function_exists('hasRole')) {
    function hasRole(string $role): bool
    {
        $roles = session()->get('roles') ?? [];

        return in_array($role, $roles, true);
    }
}


if (!function_exists('hasAnyRole')) {
    function hasAnyRole(array $requiredRoles): bool
    {
        $roles = session()->get('roles') ?? [];

        return !empty(array_intersect($requiredRoles, $roles));
    }
}


if (!function_exists('isSuperAdmin')) {
    /**
     * Super Admin = memegang role bernama 'Super Admin' ATAU role
     * berlevel 1.
     *
     * Pengecekan nama adalah jalur cepat (tanpa query) dan tetap
     * dipertahankan karena nama role adalah kunci otorisasi di seluruh
     * aplikasi. Pengecekan level adalah defence in depth: nama role kini
     * dijamin unik oleh roles.uniq_roles_name, tapi bila suatu saat ada
     * jalur lain yang memasukkan nama ganda, role level 1 tetap diakui
     * sehingga privilege tidak bisaditiru.
     */
    function isSuperAdmin(): bool
    {
        if (hasRole('Super Admin')) {
            return true;
        }

        $levelOne = new \App\Models\RoleModel();

        foreach ($levelOne->select('name')->where('level', 1)->findAll() as $role) {
            if (hasRole($role['name'])) {
                return true;
            }
        }

        return false;
    }
}

// Catatan: helper hasPermission() sengaja dihapus. Permission-nya berasal dari
// modul "maintenance" yang tidak pernah dibangun, dan tabel permissions,
// role_permissions, serta maintenances semuanya sudah di-drop. Otorisasi
// saat ini murni berbasis role lewat RoleFilter.
