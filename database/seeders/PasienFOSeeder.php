<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pasien;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class PasienFOSeeder extends Seeder
{
    public function run(): void
    {
        // Kosongkan tabel transaksi lama agar bersih mulai dari 30 data FO
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        if (Schema::hasTable('order_lab_details')) DB::table('order_lab_details')->truncate();
        if (Schema::hasTable('order_labs')) DB::table('order_labs')->truncate();
        if (Schema::hasTable('resep_details')) DB::table('resep_details')->truncate();
        if (Schema::hasTable('rekam_medis')) DB::table('rekam_medis')->truncate();
        if (Schema::hasTable('pemeriksaan_perawats')) DB::table('pemeriksaan_perawats')->truncate();
        if (Schema::hasTable('pendaftarans')) DB::table('pendaftarans')->truncate();
        if (Schema::hasTable('pasiens')) DB::table('pasiens')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Ambil ID dokter dengan aman
        $dokterIds = [];
        if (method_exists(User::class, 'scopeRole')) {
            try {
                $dokterIds = User::role('dokter')->pluck('id')->toArray();
            } catch (\Exception $e) {
                $dokterIds = [];
            }
        }
        if (empty($dokterIds) && Schema::hasColumn('users', 'role')) {
            $dokterIds = User::where('role', 'dokter')->pluck('id')->toArray();
        }
        if (empty($dokterIds)) {
            $dokterIds = User::where('email', 'like', '%dokter%')->pluck('id')->toArray();
        }
        if (empty($dokterIds)) {
            $firstUser = User::first();
            $dokterIds = [$firstUser ? $firstUser->id : 1];
        }

        // Deteksi kolom aktual yang tersedia di tabel pasiens & pendaftarans
        $pasienCols = Schema::getColumnListing('pasiens');
        $pendaftaranCols = Schema::getColumnListing('pendaftarans');

        // Deteksi nilai ENUM yang valid untuk kolom jenis_penjamin
        $penjamins = ['UMUM', 'BPJS', 'ASURANSI'];
        if (in_array('jenis_penjamin', $pendaftaranCols)) {
            $colInfo = DB::select("SHOW COLUMNS FROM `pendaftarans` WHERE Field = 'jenis_penjamin'");
            if (!empty($colInfo) && preg_match("/^enum\((.*)\)$/", $colInfo[0]->Type, $matches)) {
                $enumValues = array_map(fn($val) => trim($val, "'"), explode(',', $matches[1]));
                if (!empty($enumValues)) {
                    $penjamins = $enumValues;
                }
            }
        }

        $daftarPasien = [
            ['nama' => 'Adi Nugroho', 'jk' => 'L', 'alamat' => 'Jl. Raya Tenjo No. 12, Bogor'],
            ['nama' => 'Siti Aminah', 'jk' => 'P', 'alamat' => 'Perum Griya Parung Panjang Blok B4, Bogor'],
            ['nama' => 'Budi Santoso', 'jk' => 'L', 'alamat' => 'Jl. Raya Serpong No. 45, Tangerang Selatan'],
            ['nama' => 'Rina Marlina', 'jk' => 'P', 'alamat' => 'Jl. Surya Kencana No. 88, Pamulang'],
            ['nama' => 'Dedi Kurniawan', 'jk' => 'L', 'alamat' => 'Kp. Babakan No. 03, Tenjo, Bogor'],
            ['nama' => 'Novianti Putri', 'jk' => 'P', 'alamat' => 'Jl. Pajajaran No. 19, Bogor Tengah'],
            ['nama' => 'Eko Prasetyo', 'jk' => 'L', 'alamat' => 'Jl. Puspiptek Raya No. 7, Setu'],
            ['nama' => 'Fitriani Rahma', 'jk' => 'P', 'alamat' => 'Perum Bukit Dago Blok C12, Gunung Sindur'],
            ['nama' => 'Hendra Wijaya', 'jk' => 'L', 'alamat' => 'Jl. Raya Cisauk No. 21, Tangerang'],
            ['nama' => 'Dewi Sartika', 'jk' => 'P', 'alamat' => 'Jl. Siliwangi No. 54, Pamulang Barat'],
            ['nama' => 'Rizky Ramadhan', 'jk' => 'L', 'alamat' => 'Jl. Raya Jasinga Km. 5, Bogor'],
            ['nama' => 'Maya Anggraini', 'jk' => 'P', 'alamat' => 'Komplek Villa Dago Tol Blok H2, Ciputat'],
            ['nama' => 'Fajar Sidik', 'jk' => 'L', 'alamat' => 'Kp. Singabraja No. 14, Tenjo, Bogor'],
            ['nama' => 'Lina Herlina', 'jk' => 'P', 'alamat' => 'Jl. Raya Muncul No. 30, Serpong'],
            ['nama' => 'Agus Setiawan', 'jk' => 'L', 'alamat' => 'Jl. Rawa Buntu Utara No. 9, BSD'],
            ['nama' => 'Wulan Guritno', 'jk' => 'P', 'alamat' => 'Jl. Ciater Raya No. 66, Tangerang Selatan'],
            ['nama' => 'Irfan Hakim', 'jk' => 'L', 'alamat' => 'Perum Puri Harmoni Blok A7, Parung Panjang'],
            ['nama' => 'Nurul Hidayah', 'jk' => 'P', 'alamat' => 'Jl. Raya Tigaraksa No. 102, Tangerang'],
            ['nama' => 'Taufik Hidayat', 'jk' => 'L', 'alamat' => 'Jl. Pahlawan Seribu No. 11, BSD City'],
            ['nama' => 'Indah Permatasari', 'jk' => 'P', 'alamat' => 'Jl. Cempaka Putih No. 27, Ciputat Timur'],
            ['nama' => 'Zainal Abidin', 'jk' => 'L', 'alamat' => 'Kp. Cilaku No. 08, Tenjo, Bogor'],
            ['nama' => 'Ratna Sari', 'jk' => 'P', 'alamat' => 'Jl. Raya Viktor No. 41, Buaran, Serpong'],
            ['nama' => 'Dimas Anggara', 'jk' => 'L', 'alamat' => 'Jl. Aria Putra No. 15, Kedaung, Pamulang'],
            ['nama' => 'Ayu Tingting', 'jk' => 'P', 'alamat' => 'Jl. Raya Margonda No. 210, Depok'],
            ['nama' => 'Reza Rahadian', 'jk' => 'L', 'alamat' => 'Jl. Bintaro Utama Sektor 9, Tangerang Selatan'],
            ['nama' => 'Kartika Putri', 'jk' => 'P', 'alamat' => 'Perum Telaga Bestari Blok D3, Balaraja'],
            ['nama' => 'Surya Saputra', 'jk' => 'L', 'alamat' => 'Jl. Raya Sawangan No. 73, Depok'],
            ['nama' => 'Melisa Oktaviani', 'jk' => 'P', 'alamat' => 'Jl. Dr. Setiabudi No. 18, Pamulang Timur'],
            ['nama' => 'Bayu Skak', 'jk' => 'L', 'alamat' => 'Kp. Bojong Kamal No. 22, Legok, Tangerang'],
            ['nama' => 'Anisa Rahma', 'jk' => 'P', 'alamat' => 'Jl. Raya Dramaga Km. 7, Bogor'],
        ];

        foreach ($daftarPasien as $index => $data) {
            $urut = $index + 1;
            $tglLahir = Carbon::now()->subYears(rand(19, 58))->subDays(rand(1, 300))->format('Y-m-d');
            $noHp = '08128899' . str_pad($urut, 4, '0', STR_PAD_LEFT);

            // 1. Simpan ke Master Pasien (pasiens)
            $pasienData = [];
            if (in_array('no_rm', $pasienCols)) $pasienData['no_rm'] = 'RM-' . date('Y') . '-' . str_pad($urut, 4, '0', STR_PAD_LEFT);
            if (in_array('nik', $pasienCols)) $pasienData['nik'] = '320123' . str_pad($urut, 10, '0', STR_PAD_LEFT);
            if (in_array('nama_lengkap', $pasienCols)) $pasienData['nama_lengkap'] = $data['nama'];
            if (in_array('nama', $pasienCols)) $pasienData['nama'] = $data['nama'];
            if (in_array('jenis_kelamin', $pasienCols)) $pasienData['jenis_kelamin'] = $data['jk'];
            if (in_array('tanggal_lahir', $pasienCols)) $pasienData['tanggal_lahir'] = $tglLahir;
            if (in_array('tgl_lahir', $pasienCols)) $pasienData['tgl_lahir'] = $tglLahir;
            if (in_array('tempat_lahir', $pasienCols)) $pasienData['tempat_lahir'] = 'Bogor';
            if (in_array('alamat', $pasienCols)) $pasienData['alamat'] = $data['alamat'];
            if (in_array('no_hp', $pasienCols)) $pasienData['no_hp'] = $noHp;
            if (in_array('no_telepon', $pasienCols)) $pasienData['no_telepon'] = $noHp;
            if (in_array('telepon', $pasienCols)) $pasienData['telepon'] = $noHp;
            if (in_array('golongan_darah', $pasienCols)) $pasienData['golongan_darah'] = ['A', 'B', 'AB', 'O'][$index % 4];

            $pasien = Pasien::create($pasienData);

            // 2. Daftarkan Kunjungan di Front Office (Status Awal: MENUNGGU_TTV)
            $waktu = Carbon::now()->subMinutes(300 - ($urut * 8));
            $pendaftaranData = [
                'pasien_id'  => $pasien->id,
                'status'     => 'MENUNGGU_TTV',
                'created_at' => $waktu,
                'updated_at' => $waktu,
            ];

            if (in_array('no_pendaftaran', $pendaftaranCols)) {
                $pendaftaranData['no_pendaftaran'] = 'REG-' . date('Ymd') . '-' . str_pad($urut, 3, '0', STR_PAD_LEFT);
            }
            if (in_array('dokter_id', $pendaftaranCols)) {
                $pendaftaranData['dokter_id'] = $dokterIds[$index % count($dokterIds)];
            }
            if (in_array('jenis_penjamin', $pendaftaranCols)) {
                $pendaftaranData['jenis_penjamin'] = $penjamins[$index % count($penjamins)];
            }
            if (in_array('penjamin', $pendaftaranCols)) {
                $pendaftaranData['penjamin'] = $penjamins[$index % count($penjamins)];
            }
            if (in_array('keluhan', $pendaftaranCols)) {
                $pendaftaranData['keluhan'] = 'Pemeriksaan Rawat Jalan Umum';
            }

            Pendaftaran::create($pendaftaranData);
        }
    }
}