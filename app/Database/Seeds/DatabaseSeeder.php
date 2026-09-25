<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->seedRoles();
        $this->seedLocations();
        $this->seedCategories();
        $this->seedUnits();
    }

    /**
     * Daftar role aplikasi. Ini SEBAGAI-SATUNYA sumber role: tidak ada
     * lagi CRUD role di aplikasi (tidak ada route /roles).
     *
     * Untuk menambah atau mengubah role, edit daftar di bawah lalu jalankan
     * `php spark db:seed DatabaseSeeder`. Seed bersifat upsert berdasarkan
     * nama, jadi description/level/capabilities yang sudah ada ikut diperbarui.
     *
     * `level` = hierarchy privilege. Angka KECIL = privilege TINGGI.
     * Level ini dipakai oleh User::updateRoles() untuk membatasi role mana
     * yang boleh diberikan ke user lain. PENTING: level WAJIB diisi di sini.
     * Kolom `level` ber-default 1 (= privilege tertinggi) di database, jadi
     * role yang somehow tidak membawa level akan dianggap Super Admin oleh
     * isSuperAdmin() — gagal ke arah terbuka, bukan tertutup.
     *
     * `capabilities` = uraian fitur yang ditujukan untuk role ini (niat).
     * Belum tentu ditegakkan filter akses; lihat /help.
     *
     * JANGAN rename nama role di bawah. Nama berikut dirujuk sebagai
     * literal di dalam kode, sehingga mengganti namanya akan mematikan
     * aksesnya tanpa ada pemulihan lewat UI:
     *   - 'Super Admin'         → Filters/RoleFilter, Helpers/auth_helper,
     *                             Helpers/location_helper, Auth, Setup, User
     *   - 'Admin Inventaris',
     *     'Manajemen',
     *     'Petugas Inventaris',
     *     'Auditor'              → Views/layout/navbar.php
     *   - 'PIC Unit'             → Views/layout/navbar.php
     */
    private const ROLES = [
        [
            'name'         => 'Super Admin',
            'level'        => 1,
            'description'  => 'Akses penuh ke seluruh sistem',
            'capabilities' => 'Akses penuh: master data (kategori, lokasi, unit), barang & aset, '
                . 'barang stok, mutasi, stock opname, laporan inventaris, serta manajemen user dan role.',
        ],
        [
            'name'         => 'Admin Inventaris',
            'level'        => 2,
            'description'  => 'Mengelola data dan transaksi inventaris',
            'capabilities' => 'Master data, barang & aset, barang stok, mutasi, stock opname, '
                . 'laporan inventaris, serta manajemen user (termasuk memberikan role yang sudah ada).',
        ],
        [
            'name'         => 'Manajemen',
            'level'        => 3,
            'description'  => 'Melihat laporan dan melakukan approval',
            'capabilities' => 'Monitoring dan laporan inventaris. Fitur approval belum tersedia '
                . 'di sistem saat ini.',
        ],
        [
            'name'         => 'Petugas Inventaris',
            'level'        => 4,
            'description'  => 'Melakukan operasional inventaris',
            'capabilities' => 'Operasional inventaris harian: pencatatan barang masuk dan keluar, '
                . 'mutasi, stock opname, serta melihat data master.',
        ],
        [
            'name'         => 'PIC Unit',
            'level'        => 5,
            'description'  => 'Mengelola inventaris unit',
            'capabilities' => 'Kelola inventaris unit/departemen yang menjadi tanggung jawabnya, '
                . 'termasuk melihat dan memperbarui data barang di unit tersebut.',
        ],
        [
            'name'         => 'Auditor',
            'level'        => 6,
            'description'  => 'Melihat data dan audit log',
            'capabilities' => 'Read-only: melihat data inventaris dan jejak pergerakan '
                . '(stock movement) untuk keperluan audit.',
        ],
    ];

    /**
     * Upsert daftar ROLES berdasarkan nama.
     */
    private function seedRoles()
    {
        $builder = $this->db->table('roles');

        $existing = [];

        foreach ($builder->select('id, name')->get()->getResultArray() as $row) {
            $existing[$row['name']] = (int) $row['id'];
        }

        $toInsert = [];

        foreach (self::ROLES as $role) {
            if (isset($existing[$role['name']])) {
                $builder->where('id', $existing[$role['name']])->update([
                    'level'        => $role['level'],
                    'description'  => $role['description'],
                    'capabilities' => $role['capabilities'],
                ]);

                continue;
            }

            $toInsert[] = $role;
        }

        if ($toInsert) {
            $builder->insertBatch($toInsert);
        }
    }


    private function seedLocations()
    {
        $builder = $this->db->table('locations');

        $exists = $builder
            ->where('name', 'Kantor Pusat')
            ->countAllResults() > 0;

        if ($exists) {
            return;
        }

        $builder->insert([
            'name'        => 'Kantor Pusat',
            'building'    => 'Gedung A',
            'floor'       => 'Lantai 1',
            'room'        => 'A-101',
            'description' => 'Lokasi utama kantor pusat.',
            'is_active'   => true,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    private function seedCategories()
    {
        $categories = [
            [
                'name'        => 'Perangkat Komputer',
                'description' => 'Komputer, laptop, printer, dan perangkat IT lainnya.',
            ],
            [
                'name'        => 'Perabotan & Sarana',
                'description' => 'Meja, kursi, rak, dan sarana kantor.',
            ],
            [
                'name'        => 'Keamanan',
                'description' => 'Perangkat keamanan seperti CCTV.',
            ],
        ];

        $builder = $this->db->table('categories');

        $existingNames = array_column(
            $builder->select('name')->get()->getResultArray(),
            'name'
        );

        $inserts = array_filter(
            $categories,
            fn ($category) => !in_array($category['name'], $existingNames, true)
        );

        foreach ($inserts as $category) {
            $builder->insert([
                ...$category,
                'is_active'  => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function seedUnits()
    {
        $units = [
            [
                'name'        => 'Teknologi Informasi',
                'code'        => 'IT',
                'description' => 'Mengelola infrastruktur dan layanan teknologi informasi.',
            ],
            [
                'name'        => 'Keuangan',
                'code'        => 'KEU',
                'description' => 'Mengelola administrasi dan keuangan.',
            ],
            [
                'name'        => 'Sumber Daya Manusia',
                'code'        => 'SDM',
                'description' => 'Mengelola sumber daya manusia.',
            ],
            [
                'name'        => 'Logistik',
                'code'        => 'LOG',
                'description' => 'Mengelola penyimpanan dan distribusi barang.',
            ],
            [
                'name'        => 'Pengadaan',
                'code'        => 'PROC',
                'description' => 'Mengelola proses pengadaan barang.',
            ],
            [
                'name'        => 'Operasional',
                'code'        => 'OPS',
                'description' => 'Mengelola kegiatan operasional.',
            ],
            [
                'name'        => 'Keamanan',
                'code'        => 'SEC',
                'description' => 'Mengelola keamanan lingkungan.',
            ],
            [
                'name'        => 'Manajemen',
                'code'        => 'MGT',
                'description' => 'Mengelola kebijakan dan pengambilan keputusan.',
            ],
            [
                'name'        => 'Pemeliharaan',
                'code'        => 'MTC',
                'description' => 'Mengelola pemeliharaan dan perbaikan aset.',
            ],
            [
                'name'        => 'Umum',
                'code'        => 'GEN',
                'description' => 'Mengelola kebutuhan umum organisasi.',
            ],
        ];

        $builder = $this->db->table('units');

        $existingCodes = array_column(
            $builder->select('code')->get()->getResultArray(),
            'code'
        );

        $inserts = array_filter(
            $units,
            fn ($unit) => !in_array($unit['code'], $existingCodes, true)
        );

        foreach ($inserts as $unit) {
            $builder->insert([
                ...$unit,
                'is_active'  => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
