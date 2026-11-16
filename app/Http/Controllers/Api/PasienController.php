<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PasienController extends Controller
{
    // Search / List Pasien
    public function index(Request $request)
    {
        $query = Pasien::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('no_rm', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
        }

        $pasiens = $query->orderBy('id', 'desc')->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $pasiens
        ]);
    }

    // Registrasi Pasien Baru (Auto No. RM)
    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|numeric|digits:16|unique:pasiens,nik',
            'nama_lengkap' => 'required|string|max:150',
            'jenis_kelamin' => 'required|in:L,P',
            'tanggal_lahir' => 'required|date',
            'alamat' => 'required|string',
            'no_hp' => 'nullable|string|max:15',
            'golongan_darah' => 'nullable|in:A,B,AB,O',
        ]);

        // Generate No. RM Otomatis: RM-YYYYMMDD-XXXX
        $today = Carbon::now()->format('Ymd');
        $lastPasien = Pasien::where('no_rm', 'like', "RM-{$today}-%")->orderBy('id', 'desc')->first();

        if ($lastPasien) {
            $lastNum = (int) substr($lastPasien->no_rm, -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        $noRm = "RM-{$today}-{$nextNum}";

        $pasien = Pasien::create([
            'no_rm' => $noRm,
            'nik' => $request->nik,
            'nama_lengkap' => $request->nama_lengkap,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tanggal_lahir' => $request->tanggal_lahir,
            'alamat' => $request->alamat,
            'no_hp' => $request->no_hp,
            'golongan_darah' => $request->golongan_darah,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pasien baru berhasil didaftarkan.',
            'data' => $pasien
        ], 201);
    }
}