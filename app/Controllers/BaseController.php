<?php

namespace App\Controllers;

use App\Models\AuditLogModel;
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

    /** Server-side lock: regular users and Admin may edit only for 96 hours. */
    protected function mayEdit(array $record): bool
    {
        if (isSuperAdmin() || empty($record['created_at'])) {
            return true;
        }

        return strtotime($record['created_at']) + (96 * 3600) >= time();
    }

    protected function editLockedResponse(bool $isAjax)
    {
        $message = 'Data sudah terkunci setelah 96 jam. Hanya Super Admin yang dapat mengubahnya.';
        return $isAjax ? $this->respondError($message, 403) : redirect()->back()->with('error', $message);
    }

    /** Persist an immutable snapshot for every important application action. */
    protected function audit(string $action, string $module, ?int $recordId, ?array $before = null, ?array $after = null, ?string $notes = null): void
    {
        (new AuditLogModel())->insert([
            'user_id' => session()->get('user_id'),
            'role_name' => implode(', ', session()->get('roles') ?? []),
            'action' => $action,
            'module' => $module,
            'record_id' => $recordId,
            'before_data' => $before ? json_encode($before, JSON_UNESCAPED_UNICODE) : null,
            'after_data' => $after ? json_encode($after, JSON_UNESCAPED_UNICODE) : null,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
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
        $dataBuilder
            ->orderBy($sortColumn, $sortDir)
            ->limit($length, $start);

        $rows = $dataBuilder->get()->getResultArray();

        return [
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $rows,
        ];
    }
}
