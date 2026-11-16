<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use Illuminate\Http\Request;

class FarmasiController extends Controller
{
    /**
     * Get Antrean Pasien Farmasi / Apoteker
     */
    public function index()
    {
        // Ambil semua pasien yang berstatus MENUNGGU_FARMASI
        $antrean = Pendaftaran::with([
            'pasien', 
            'dokter', 
            'rekamMedis'
        ])
        ->where('status', 'MENUNGGU_FARMASI')
        ->orderBy('id', 'desc')
        ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $antrean
        ]);
    }

    /**
     * Proses Resep Selesai & Oper Pasien Ke Billing Kasir
     */
    public function prosesResep(Request $request)
    {
        $request->validate([
            'pendaftaran_id' => 'required|exists:pendaftarans,id',
        ]);

        $pendaftaran = Pendaftaran::findOrFail($request->pendaftaran_id);
        
        // SETELAH FARMASI SELESAI -> PASIEN DIOPER KE KASIR
        $pendaftaran->update(['status' => 'MENUNGGU_BILLING']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Penyerahan resep selesai. Pasien diteruskan ke Billing Kasir.'
        ]);
    }

    public function serahObat(Request $request)
    {
        return $this->prosesResep($request);
    }
}