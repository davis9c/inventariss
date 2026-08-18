<?php

namespace App\Controllers;

class Landing extends BaseController
{
    public function index()
    {
        return view('landing/index', [
            'title'     => 'Sistem Inventaris',
            'installed' => is_app_installed(),
            'logged_in' => (bool) session()->get('user_id'),
        ]);
    }
}