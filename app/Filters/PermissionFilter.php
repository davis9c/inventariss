<?php

namespace App\Filters;

use App\Models\UserRoleModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class PermissionFilter implements FilterInterface
{
    public function before(
        RequestInterface $request,
        $arguments = null
    ) {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        if (empty($arguments)) {
            return redirect()
                ->to('/dashboard')
                ->with('error', 'Permission tidak ditentukan.');
        }

        $permission = $arguments[0];

        $userId = session()->get('user_id');

        $userRoleModel = new UserRoleModel();

        $hasPermission = $userRoleModel
            ->select('permissions.id')
            ->join(
                'role_permissions',
                'role_permissions.role_id = user_roles.role_id'
            )
            ->join(
                'permissions',
                'permissions.id = role_permissions.permission_id'
            )
            ->where(
                'user_roles.user_id',
                $userId
            )
            ->where(
                'permissions.name',
                $permission
            )
            ->first();

        if (!$hasPermission) {
            return redirect()
                ->to('/dashboard')
                ->with(
                    'error',
                    'Anda tidak memiliki permission untuk melakukan tindakan tersebut.'
                );
        }
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {}
}
