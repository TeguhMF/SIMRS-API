<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    /**
     * Get List Antrean Pasien Billing / Kasir
     */
    public function index()
    {
        // Ambil semua pasien yang berstatus MENUNGGU_BILLING
        $antrean = Pendaftaran::with([
            'pasien', 
            'dokter', 
            'rekamMedis'
        ])
        ->where('status', 'MENUNGGU_BILLING')
        ->orderBy('id', 'desc')
        ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $antrean
        ]);
    }

    /**
     * Proses Pelunasan Tagihan Kasir & Selesaikan Pasien
     */
    public function bayar(Request $request)
    {
        $request->validate([
            'pendaftaran_id' => 'required|exists:pendaftarans,id',
            'metode_bayar'   => 'nullable|string',
            'total_bayar'    => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            $pendaftaran = Pendaftaran::findOrFail($request->pendaftaran_id);

            // Update status akhir pendaftaran menjadi SELESAI_LUNAS
            $updateData = ['status' => 'SELESAI_LUNAS'];
            
            if ($request->has('metode_bayar')) {
                $updateData['metode_bayar'] = $request->metode_bayar;
            }
            if ($request->has('total_bayar')) {
                $updateData['total_bayar'] = $request->total_bayar;
            }

            $pendaftaran->update($updateData);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Pembayaran lunas! Seluruh alur pelayanan pasien telah SELESAI.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }
}