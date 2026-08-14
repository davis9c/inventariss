<?php

if (! function_exists('has_permission')) {
    function has_permission(string $permission): bool
    {
        $userId = session()->get('user_id');

        if (! $userId) {
            return false;
        }

        $userRoleModel = new \App\Models\UserRoleModel();

        return $userRoleModel
            ->select('permissions.id')
            ->join(
                'role_permissions',
                'role_permissions.role_id = user_roles.role_id'
            )
            ->join(
                'permissions',
                'permissions.id = role_permissions.permission_id'
            )
            ->where('user_roles.user_id', $userId)
            ->where('permissions.name', $permission)
            ->first() !== null;
    }
}
