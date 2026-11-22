<?php

namespace App\Controllers;

use App\Models\AssetModel;
use App\Models\CategoryModel;
use App\Models\LocationModel;
use App\Models\StockItemModel;
use App\Models\UnitModel;

class Autocomplete extends BaseController
{
    public function assets()
    {
        $q = trim((string) $this->request->getGet('q'));
        $loc = (int) $this->request->getGet('location_id');
        $m = new AssetModel();
        $b = $m->select('id, asset_code, name')
            ->where('asset_status', 'Aktif')
            ->orderBy('name', 'ASC')
            ->limit(20);

        if ($loc > 0) {
            $b->where('location_id', $loc);
        }

        if ($q !== '') {
            $b->groupStart()
                ->like('asset_code', $q)
                ->orLike('name', $q)
                ->groupEnd();
        }

        $rows = $b->findAll();
        $out = array_map(static fn ($r) => [
            'id'   => (int) $r['id'],
            'text' => $r['asset_code'] . ' - ' . $r['name'],
        ], $rows);

        return $this->response->setJSON($out);
    }

    public function stockItems()
    {
        $q = trim((string) $this->request->getGet('q'));
        $loc = (int) $this->request->getGet('location_id');
        $m = new StockItemModel();
        $b = $m->select('id, item_code, name, quantity, satuan')
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->limit(20);

        if ($loc > 0) {
            $b->where('location_id', $loc);
        }

        if ($q !== '') {
            $b->groupStart()
                ->like('item_code', $q)
                ->orLike('name', $q)
                ->groupEnd();
        }

        $rows = $b->findAll();
        $out = array_map(static fn ($r) => [
            'id'       => (int) $r['id'],
            'text'     => $r['item_code'] . ' - ' . $r['name'] . ' (stok: ' . $r['quantity'] . ' ' . $r['satuan'] . ')',
            'quantity' => (int) $r['quantity'],
        ], $rows);

        return $this->response->setJSON($out);
    }

    public function locations()
    {
        $q = trim((string) $this->request->getGet('q'));
        $m = new LocationModel();
        $b = $m->select('id, name')->where('is_active', 1)->orderBy('name', 'ASC')->limit(20);
        if ($q !== '') {
            $b->like('name', $q);
        }
        $rows = $b->findAll();
        $out = array_map(static fn ($r) => ['id' => (int) $r['id'], 'text' => $r['name']], $rows);
        return $this->response->setJSON($out);
    }

    public function units()
    {
        $q = trim((string) $this->request->getGet('q'));
        $m = new UnitModel();
        $b = $m->select('id, name')->where('is_active', 1)->orderBy('name', 'ASC')->limit(20);
        if ($q !== '') {
            $b->like('name', $q);
        }
        $rows = $b->findAll();
        $out = array_map(static fn ($r) => ['id' => (int) $r['id'], 'text' => $r['name']], $rows);
        return $this->response->setJSON($out);
    }

    public function categories()
    {
        $q = trim((string) $this->request->getGet('q'));
        $m = new CategoryModel();
        $b = $m->select('id, name')->where('is_active', 1)->orderBy('name', 'ASC')->limit(20);
        if ($q !== '') {
            $b->like('name', $q);
        }
        $rows = $b->findAll();
        $out = array_map(static fn ($r) => ['id' => (int) $r['id'], 'text' => $r['name']], $rows);
        return $this->response->setJSON($out);
    }
}