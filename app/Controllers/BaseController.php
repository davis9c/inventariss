<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);
    }

    /**
     * Apakah user yang sedang login Super Admin.
     *
     * Delegasi ke auth_helper::isSuperAdmin() supaya hanya ada satu
     * definisi. Jangan bandingkan nama role secara manual di
     * controller/view.
     */
    protected function isSuperAdmin(): bool
    {
        return isSuperAdmin();
    }

    /**
     * Tolak request bila user bukan Super Admin (fail-closed).
     *
     * Dipakai di dalam method, bukan hanya di route, supaya perubahan
     * konfigurasi route tidak bisa diam-diam membuka celah otorisasi.
     * Menyesuaikan dengan tipe request: JSON 403 untuk AJAX, redirect
     * dengan flash message untuk HTML.
     *
     * @return ResponseInterface|null null bila user Super Admin (lolos)
     */
    protected function requireSuperAdmin(string $message = 'Hanya Super Admin yang dapat melakukan tindakan ini.'): ?ResponseInterface
    {
        if ($this->isSuperAdmin()) {
            return null;
        }

        if ($this->request->isAJAX()) {
            return $this->respondError($message, 403);
        }

        return redirect()
            ->back()
            ->with('error', $message);
    }

    /**
     * Level privilege tertinggi yang dimiliki user yang sedang login.
     *
     * Level kecil = privilege tinggi (Super Admin = 1). Dipakai untuk
     * membatasi role mana yang boleh diberikan ke user lain: hanya role
     * dengan level lebih besar (privilege lebih rendah).
     *
     * Kalau user tidak punya role sama sekali, dikembalikan level
     * tertinggi yang ada — supaya user tanpa role tidak bisa
     * memberikan role apa pun.
     */
    protected function currentRoleLevel(): int
    {
        $roleModel = new \App\Models\RoleModel();

        $rows = $roleModel->select('level')->whereIn('name', session()->get('roles') ?? [])->findAll();

        $min = null;
        foreach ($rows as $row) {
            $level = (int) ($row['level'] ?? 1);
            $min = $min === null ? $level : min($min, $level);
        }

        if ($min !== null) {
            return $min;
        }

        // User tanpa role: pakai level tertinggi yang ada supaya dia
        // tidak bisa memberikan role apa pun.
        $max = (int) db_connect()->table('roles')->selectMax('level')->get()->getRow('max');

        return $max > 0 ? $max : 1;
    }

    /**
     * Kirim response JSON (umum dipakai untuk request AJAX).
     */
    protected function respondAjax($data = null, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($data);
    }

    /**
     * Response JSON sukses untuk AJAX.
     */
    protected function respondSuccess(string $message, $data = null): ResponseInterface
    {
        return $this->respondAjax([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ]);
    }

    /**
     * Response JSON gagal dengan daftar error per-field (validasi).
     */
    protected function respondErrors(string $message, array $errors): ResponseInterface
    {
        return $this->respondAjax([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], 422);
    }

    /**
     * Response JSON gagal dengan pesan umum.
     */
    protected function respondError(string $message, int $status = 400): ResponseInterface
    {
        return $this->respondAjax([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    /**
     * Membangun payload DataTables server-side dari query builder.
     *
     * @param string   $table            Nama tabel utama (untuk builder baru)
     * @param callable $baseConditions   Closure: menerima builder, set select/join/where dasar
     * @param array    $searchColumns    Daftar kolom yang bisa dicari (pakai prefix tabel)
     * @param array    $sortableColumns  Map indeks kolom DataTables -> kolom SQL
     * @param string   $defaultSort      Kolom default
     * @param string   $defaultDir       Arah default (ASC/DESC)
     */
    protected function datatableResponse(
        string $table,
        callable $baseConditions,
        array $searchColumns,
        array $sortableColumns,
        string $defaultSort,
        string $defaultDir = 'ASC'
    ): array {
        $request = $this->request;
        $db      = db_connect();

        $draw   = (int) ($request->getGet('draw') ?: 1);
        $start  = (int) ($request->getGet('start') ?: 0);
        $length = (int) ($request->getGet('length') ?: 25);

        $searchValue = '';
        $searchParam = $request->getGet('search');
        if (is_array($searchParam) && isset($searchParam['value'])) {
            $searchValue = trim((string) $searchParam['value']);
        }

        $sortColumn = $defaultSort;
        $sortDir    = strtoupper($defaultDir) === 'DESC' ? 'DESC' : 'ASC';
        $orderParam = $request->getGet('order');
        if (is_array($orderParam)
            && isset($orderParam[0]['column'], $orderParam[0]['dir'])
            && isset($sortableColumns[(int) $orderParam[0]['column']])) {
            $sortColumn = $sortableColumns[(int) $orderParam[0]['column']];
            $sortDir    = strtolower($orderParam[0]['dir']) === 'desc' ? 'DESC' : 'ASC';
        }

        $newBuilder = function () use ($db, $table, $baseConditions) {
            $builder = $db->table($table);
            $baseConditions($builder);
            return $builder;
        };

        $applySearch = function ($builder) use ($searchValue, $searchColumns) {
            if ($searchValue === '') {
                return;
            }
            $builder->groupStart();
            foreach ($searchColumns as $column) {
                $builder->orLike($column, $searchValue);
            }
            $builder->groupEnd();
        };

        $recordsTotal = $newBuilder()->countAllResults();

        $filteredBuilder = $newBuilder();
        $applySearch($filteredBuilder);
        $recordsFiltered = $filteredBuilder->countAllResults();

        $dataBuilder = $newBuilder();
        $applySearch($dataBuilder);
        $dataBuilder->orderBy($sortColumn, $sortDir);

        // DataTables mengirim length=-1 untuk opsi "Tampilkan semua".
        // LIMIT -1 tidak valid di MySQL, jadi dalam kasus itu lewati
        // limit altogether dan kirim seluruh baris yang cocok.
        if ($length >= 0) {
            $dataBuilder->limit($length, $start);
        }

        $rows = $dataBuilder->get()->getResultArray();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows,
        ];
    }
}
