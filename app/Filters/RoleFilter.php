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

        $requiredRole = $arguments[0] ?? null;

        if (!$requiredRole) {
            return redirect()->to('/dashboard');
        }

        $roles = session()->get('roles') ?? [];

        if (!in_array($requiredRole, $roles)) {
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
