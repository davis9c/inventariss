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
        $data = [
            'title'      => 'Kategori Barang',
            'categories' => $this->categoryModel
                ->orderBy('name', 'ASC')
                ->findAll(),
        ];

        return view('categories/index', $data);
    }

    public function create()
    {
        return view('categories/create', [
            'title' => 'Tambah Kategori',
        ]);
    }

    public function store()
    {
        $this->categoryModel->insert([
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_active'   => $this->request->getPost('is_active') ? true : false,
        ]);

        return redirect()->to('/categories')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $category = $this->categoryModel->find($id);

        if (!$category) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('categories/edit', [
            'title'    => 'Edit Kategori',
            'category' => $category,
        ]);
    }

    public function update($id)
    {
        $this->categoryModel->update($id, [
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'is_active'   => $this->request->getPost('is_active') ? true : false,
        ]);

        return redirect()->to('/categories')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function delete($id)
    {
        $this->categoryModel->delete($id);

        return redirect()->to('/categories')
            ->with('success', 'Kategori berhasil dihapus.');
    }
}