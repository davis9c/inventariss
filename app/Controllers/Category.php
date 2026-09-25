<?php

namespace App\Controllers;

use App\Models\CategoryModel;

class Category extends BaseController
{
    protected CategoryModel $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
    }

    public function index()
    {
        // DataTables server-side. Tanpa ini, halaman harus menyertakan
        // seluruh baris di HTML dan tidak punya endpoint untuk
        // dt.ajax.reload().
        if ($this->request->getGet('format') === 'json') {
            return $this->respondAjax($this->datatableResponse(
                'categories',
                static fn ($b) => $b->select('categories.*'),
                ['name', 'description'],
                [0 => 'name', 1 => 'description', 2 => 'is_active'],
                'name'
            ));
        }

        return view('categories/index', [
            'title' => 'Kategori Barang',
        ]);
    }

    /**
     * Satu kategori sebagai JSON, untuk mengisi modal edit dari ?edit=<id>.
     */
    public function data($id)
    {
        $category = $this->categoryModel->find($id);

        if (!$category) {
            return $this->respondError('Kategori tidak ditemukan.', 404);
        }

        return $this->respondAjax($category);
    }

    public function create()
    {
        // Halaman create sudah tidak ada; form-nya berada di modal pada
        // halaman index. Route ini dipertahankan agar tautan lama /
        // bookmark tetap bekerja.
        return redirect()->to('/categories?create=1');
    }

    public function store()
    {
        $isAjax = $this->request->isAJAX();

        $this->categoryModel->insert([
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_active'   => $this->request->getPost('is_active') ? true : false,
        ]);

        if ($isAjax) {
            return $this->respondSuccess('Kategori berhasil ditambahkan.', [
                'id' => $this->categoryModel->getInsertID(),
            ]);
        }

        return redirect()->to('/categories')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit($id)
    {
        // Form edit berada di modal pada halaman index. Validasi
        // keberadaan record tetap di data(), yang dipanggil halaman index
        // saat membuka modal.
        return redirect()->to('/categories?edit=' . (int) $id);
    }

    public function update($id)
    {
        $isAjax = $this->request->isAJAX();

        $category = $this->categoryModel->find($id);

        if (!$category) {
            if ($isAjax) {
                return $this->respondError('Kategori tidak ditemukan.', 404);
            }

            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->categoryModel->update($id, [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_active'   => $this->request->getPost('is_active') ? true : false,
        ]);

        if ($isAjax) {
            return $this->respondSuccess('Kategori berhasil diperbarui.', [
                'id' => (int) $id,
            ]);
        }

        return redirect()->to('/categories')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function delete($id)
    {
        $isAjax = $this->request->isAJAX();

        $category = $this->categoryModel->find($id);

        if (!$category) {
            if ($isAjax) {
                return $this->respondError('Kategori tidak ditemukan.', 404);
            }

            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->categoryModel->delete($id);

        if ($isAjax) {
            return $this->respondSuccess('Kategori berhasil dihapus.', [
                'id' => (int) $id,
            ]);
        }

        return redirect()->to('/categories')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}