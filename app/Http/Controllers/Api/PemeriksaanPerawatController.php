<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PemeriksaanPerawat;
use App\Models\Pendaftaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PemeriksaanPerawatController extends Controller
{
    /**
     * Get List Antrean Pasien untuk Perawat (Nurse Station)
     */
    public function index()
    {
        $antrean = Pendaftaran::with('pasien')
            ->whereIn('status', ['MENUNGGU_TTV', 'PROSES_PERAWAT'])
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $antrean
        ]);
    }

    /**
     * Simpan Data TTV Perawat & Ubah Status Pendaftaran ke MENUNGGU_DOKTER
     */
    public function store(Request $request)
    {
        $request->validate([
            'pendaftaran_id' => 'required|exists:pendaftarans,id',
            'keluhan_utama'  => 'required|string',
            'sistol'         => 'nullable|numeric',
            'diastol'        => 'nullable|numeric',
            'tekanan_darah'  => 'nullable|string',
            'nadi'           => 'nullable|numeric',
            'suhu'           => 'nullable|numeric',
            'pernapasan'     => 'nullable|numeric',
            'berat_badan'    => 'nullable|numeric',
            'tinggi_badan'   => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            $sistol  = $request->sistol ?? 0;
            $diastol = $request->diastol ?? 0;

            $tekananDarah = $request->tekanan_darah;
            if (empty($tekananDarah)) {
                $tekananDarah = "{$sistol}/{$diastol}";
            }

            // Data TTV dengan fallback default agar MySQL NOT NULL tidak crash
            $ttvData = [
                'pendaftaran_id' => $request->pendaftaran_id,
                'perawat_id'     => $request->user()->id,
                'keluhan_utama'  => $request->keluhan_utama,
                'sistol'         => $sistol,
                'diastol'        => $diastol,
                'tekanan_darah'  => $tekananDarah,
                'nadi'           => $request->nadi ?? 0,
                'suhu'           => $request->suhu ?? 0,
                'pernapasan'     => $request->pernapasan ?? 20,
                'berat_badan'    => $request->berat_badan ?? 0,
                'tinggi_badan'   => $request->tinggi_badan ?? 0,
            ];

            // Filter kolom dinamis sesuai kolom tabel pemeriksaan_perawats
            $tableColumns = Schema::getColumnListing('pemeriksaan_perawats');
            $filteredTtvData = array_intersect_key($ttvData, array_flip($tableColumns));

            // 1. Simpan TTV
            $ttv = PemeriksaanPerawat::create($filteredTtvData);

            // 2. Wajib update status pendaftaran ke MENUNGGU_DOKTER
            $pendaftaran = Pendaftaran::findOrFail($request->pendaftaran_id);
            $pendaftaran->update(['status' => 'MENUNGGU_DOKTER']);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Data TTV berhasil disimpan. Pasien diteruskan ke Dokter.',
                'data'    => $ttv
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan data TTV: ' . $e->getMessage()
            ], 500);
        }
    }
}