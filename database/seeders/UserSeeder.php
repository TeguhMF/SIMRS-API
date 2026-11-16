<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Role Petugas Lab
        $roleLab = Role::firstOrCreate(['name' => 'petugas-lab']);

        // 2. Buat User Petugas Lab
        $userLab = User::firstOrCreate(
            ['email' => 'lab@simrs.com'],
            [
                'name' => 'Siti Rahma, A.Md.AK',
                'password' => bcrypt('password'),
            ]
        );

        $userLab->assignRole($roleLab);
    }
}