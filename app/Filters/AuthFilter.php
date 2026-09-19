<?php

namespace App\Filters;

use App\Libraries\UserGateLibrary;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()
                ->to('/login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // ─── Cek & refresh token jika mendekati expiry ──────
        $tokenExpiry = session()->get('token_expiry') ?? 0;
        $now         = time();

        // Jika token masih valid (sisa > 120 detik), lanjutkan
        if (($tokenExpiry - $now) > 120) {
            return null;
        }

        // Token sudah expired atau mendekati expiry → refresh
        $refreshToken = session()->get('refresh_token');

        if (empty($refreshToken)) {
            session()->destroy();
            return redirect()
                ->to('/login')
                ->with('error', 'Sesi telah berakhir. Silakan login kembali.');
        }

        $ug     = new UserGateLibrary();
        $result = $ug->refreshToken($refreshToken);

        if (!$result['status']) {
            session()->destroy();
            return redirect()
                ->to('/login')
                ->with('error', 'Sesi telah berakhir. Silakan login kembali.');
        }

        // Update session dengan token baru
        $data = $result['data'];
        session()->set([
            'access_token'  => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'token_expiry'  => time() + ($data['expires_in'] ?? 900),
        ]);

        return null;
    }

    public function after(
        RequestInterface $request,
        ResponseInterface $response,
        $arguments = null
    ) {
    }
}
