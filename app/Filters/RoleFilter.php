<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RoleFilter implements FilterInterface
{
    public function before(
        RequestInterface $request,
        $arguments = null
    ) {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        // Super Admin selalu boleh akses
        $userRoles = session()->get('roles') ?? [];
        if (in_array('Super Admin', $userRoles, true)) {
            return null;
        }

        if (empty($arguments)) {
            return redirect()->to('/dashboard');
        }

        // Dukung multiple role: "Admin Inventaris,Super Admin"
        $requiredRoles = array_map('trim', explode(',', $arguments[0]));

        $hasAccess = !empty(array_intersect($requiredRoles, $userRoles));

        if (!$hasAccess) {
            return redirect()
                ->to('/dashboard')
                ->with(
                    'error',
                    'Anda tidak memiliki akses ke halaman tersebut.'
                );
        }
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {}
}
