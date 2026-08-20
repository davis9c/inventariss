<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AppAccessFilter implements FilterInterface
{
    public function before(
        RequestInterface $request,
        $arguments = null
    ) {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        if (!hasAppAccess()) {
            return redirect()
                ->to('/dashboard')
                ->with(
                    'error',
                    'Akun Anda belum memiliki akses ke modul apapun. Silakan hubungi Administrator Inventaris untuk meminta akses.'
                );
        }
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {}
}