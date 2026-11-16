<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Obat;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class SimrsInitialSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Roles via Spatie Permission
        $roles = [
            'super-admin',
            'petugas-fo',
            'perawat',
            'dokter',
            'petugas-lab',
            'apoteker',
            'kasir',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // 2. Buat Akun Pegawai Contoh
        $users = [
            [
                'name'     => 'Super Admin',
                'email'    => 'admin@simrs.com',
                'password' => Hash::make('password'),
                'role'     => 'super-admin',
            ],
            [
                'name'     => 'Siti Petugas FO',
                'email'    => 'fo@simrs.com',
                'password' => Hash::make('password'),
                'role'     => 'petugas-fo',
            ],
            [
                'name'     => 'Ns. Ani Perawat',
                'email'    => 'perawat@simrs.com',
                'password' => Hash::make('password'),
                'role'     => 'perawat',
            ],
            [
                'name'     => 'dr. Budi Santoso, Sp.PD',
                'email'    => 'dokter@simrs.com',
                'password' => Hash::make('password'),
                'role'     => 'dokter',
            ],
            [
                'name'     => 'Ahmad Petugas Lab',
                'email'    => 'lab@simrs.com',
                'password' => Hash::make('password'),
                'role'     => 'petugas-lab',
            ],
            [
                'name'     => 'Apt. Dewi Farmasi',
                'email'    => 'apoteker@simrs.com',
                'password' => Hash::make('password'),
                'role'     => 'apoteker',
            ],
            [
                'name'     => 'Rina Kasir',
                'email'    => 'kasir@simrs.com',
                'password' => Hash::make('password'),
                'role'     => 'kasir',
            ],
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            $user->assignRole($roleName);
        }

        // 3. Buat Master Obat Contoh
        $obats = [
            [
                'kode_obat' => 'OBT-001',
                'nama_obat' => 'Paracetamol 500mg Tablet',
                'satuan'    => 'Strip',
                'harga'     => 10000.00,
                'stok'      => 100,
            ],
            [
                'kode_obat' => 'OBT-002',
                'nama_obat' => 'Amoxicillin 500mg Kaplet',
                'satuan'    => 'Strip',
                'harga'     => 15000.00,
                'stok'      => 50,
            ],
            [
                'kode_obat' => 'OBT-003',
                'nama_obat' => 'Omeprazole 20mg Kapsul',
                'satuan'    => 'Strip',
                'harga'     => 25000.00,
                'stok'      => 75,
            ],
            [
                'kode_obat' => 'OBT-004',
                'nama_obat' => 'OBH Combi Batuk Sirup 100ml',
                'satuan'    => 'Botol',
                'harga'     => 20000.00,
                'stok'      => 30,
            ],
            [
                'kode_obat' => 'OBT-005',
                'nama_obat' => 'Cefadroxil 500mg Kapsul',
                'satuan'    => 'Strip',
                'harga'     => 30000.00,
                'stok'      => 40,
            ],
        ];

        foreach ($obats as $obatData) {
            Obat::firstOrCreate(
                ['kode_obat' => $obatData['kode_obat']],
                $obatData
            );
        }
    }
}