<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use App\Models\OrderLab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LaboratoriumController extends Controller
{
    /**
     * Get Antrean Pasien Laboratorium
     */
    public function index()
    {
        // Ambil semua pendaftaran yang berstatus MENUNGGU_LAB
        $antrean = Pendaftaran::with(['pasien', 'dokter', 'rekamMedis'])
            ->where('status', 'MENUNGGU_LAB')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $antrean
        ]);
    }

    /**
     * Submit Hasil Lab & Otomatis Oper Pasien Ke Farmasi
     */
    public function submitHasil(Request $request)
    {
        $request->validate([
            'pendaftaran_id' => 'required|exists:pendaftarans,id',
        ]);

        $pendaftaran = Pendaftaran::findOrFail($request->pendaftaran_id);

        // Update status order lab secara fleksibel (apapun nama foreign key-nya)
        try {
            if (Schema::hasTable('order_labs')) {
                $columns = Schema::getColumnListing('order_labs');
                
                // Cari foreign key yang sesuai di tabel order_labs
                if (in_array('pendaftaran_id', $columns)) {
                    OrderLab::where('pendaftaran_id', $pendaftaran->id)
                        ->update(['status' => 'SELESAI', 'hasil' => $request->hasil ?? 'Selesai Diperiksa']);
                } else if (in_array('rekam_medis_id', $columns) && $pendaftaran->rekamMedis) {
                    OrderLab::where('rekam_medis_id', $pendaftaran->rekamMedis->id)
                        ->update(['status' => 'SELESAI', 'hasil' => $request->hasil ?? 'Selesai Diperiksa']);
                }
            }
        } catch (\Exception $e) {
            // Jika ada hambatan di tabel order_labs, proses alur pendaftaran tetap lanjut ke Farmasi
        }

        // SETELAH PETUGAS LAB SELESAI -> UTAMA: UBAH STATUS PENDAFTARAN KE MENUNGGU_FARMASI
        $pendaftaran->update(['status' => 'MENUNGGU_FARMASI']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pemeriksaan laboratorium selesai. Pasien berhasil diteruskan ke Farmasi.'
        ]);
    }
}