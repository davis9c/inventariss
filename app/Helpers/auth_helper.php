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
    function isSuperAdmin(): bool
    {
        return hasRole('Super Admin');
    }
}


if (!function_exists('hasAppAccess')) {
    function hasAppAccess(): bool
    {
        if (!session()->get('isLoggedIn')) {
            return false;
        }

        if (isSuperAdmin()) {
            return true;
        }

        return !empty(session()->get('roles') ?? []);
    }
}


/*
|--------------------------------------------------------------------------
| Permission
|--------------------------------------------------------------------------
*/

if (!function_exists('hasPermission')) {
    function hasPermission(string $permission): bool
    {
        // Super Admin memiliki seluruh permission
        if (isSuperAdmin()) {
            return true;
        }

        $permissions = session()->get('permissions') ?? [];

        return in_array($permission, $permissions, true);
    }
}

if (!function_exists('hasPermission')) {
    function hasPermission(string $permission): bool
    {
        $permissions = session()->get('permissions') ?? [];

        return in_array($permission, $permissions, true);
    }
}
