<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DokterSeeder extends Seeder
{
    public function run(): void
    {
        $dokters = [
            ['name' => 'dr. Andi Pratama, Sp.PD', 'email' => 'dokter@simrs.com'],
            ['name' => 'dr. Sinta Dewi, Sp.A', 'email' => 'dokter2@simrs.com'],
            ['name' => 'dr. Budi Hartono, Sp.B', 'email' => 'dokter3@simrs.com'],
            ['name' => 'dr. Maya Kusuma, Sp.OG', 'email' => 'dokter4@simrs.com'],
            ['name' => 'dr. Reza Gunawan, Sp.JP', 'email' => 'dokter5@simrs.com'],
        ];

        $hasRoleColumn = Schema::hasColumn('users', 'role');
        $useSpatie = method_exists(User::class, 'assignRole');

        foreach ($dokters as $data) {
            $payload = [
                'name'     => $data['name'],
                'password' => Hash::make('password123'),
            ];

            if ($hasRoleColumn) {
                $payload['role'] = 'dokter';
            }

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                $payload
            );

            if ($useSpatie) {
                try {
                    $user->assignRole('dokter');
                } catch (\Exception $e) {
                    // Abaikan jika role belum terbuat atau sudah terpasang
                }
            }
        }
    }
}