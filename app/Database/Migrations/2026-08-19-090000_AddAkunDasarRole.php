<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAkunDasarRole extends Migration
{
    public function up()
    {
        $builder = $this->db->table('roles');

        $exists = $builder
            ->where('name', 'Akun Dasar')
            ->get()
            ->getRow();

        if (!$exists) {
            $builder->insert([
                'name'        => 'Akun Dasar',
                'description' => 'Akun tanpa akses ke modul apapun. Hubungi Administrator Inventaris untuk meminta akses.',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        $builder = $this->db->table('roles');

        $role = $builder->where('name', 'Akun Dasar')->get()->getRow();

        if (!$role) {
            return;
        }

        $this->db->table('role_permissions')
            ->where('role_id', $role->id)
            ->delete();

        $this->db->table('user_roles')
            ->where('role_id', $role->id)
            ->delete();

        $builder->where('id', $role->id)->delete();
    }
}