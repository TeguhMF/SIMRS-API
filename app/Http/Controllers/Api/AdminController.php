<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use App\Models\Pasien;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Get Overview Metrics & Seluruh Antrean Pasien
     */
    public function getOverview()
    {
        // 1. Ringkasan Statistik Per Status Alur SIMRS
        $stats = [
            'total_pendaftaran' => Pendaftaran::count(),
            'menunggu_ttv'      => Pendaftaran::where('status', 'MENUNGGU_TTV')->count(),
            'menunggu_dokter'   => Pendaftaran::where('status', 'MENUNGGU_DOKTER')->count(),
            'menunggu_lab'      => Pendaftaran::where('status', 'MENUNGGU_LAB')->count(),
            'menunggu_farmasi'  => Pendaftaran::where('status', 'MENUNGGU_FARMASI')->count(),
            'menunggu_billing'  => Pendaftaran::where('status', 'MENUNGGU_BILLING')->count(),
            'selesai_lunas'     => Pendaftaran::where('status', 'SELESAI_LUNAS')->count(),
        ];

        // 2. Data Master Seluruh Pendaftaran Pasien (Termasuk Relasi Medis)
        $pendaftarans = Pendaftaran::with([
            'pasien',
            'dokter',
            'pemeriksaanPerawat',
            'rekamMedis'
        ])
        ->orderBy('id', 'desc')
        ->get();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'stats'        => $stats,
                'pendaftarans' => $pendaftarans
            ]
        ]);
    }

    /**
     * Force Override Status Pendaftaran Pasien (Kirim/Pindahkan Data Manual oleh Admin)
     */
    public function overrideStatus(Request $request)
    {
        $request->validate([
            'pendaftaran_id' => 'required|exists:pendaftarans,id',
            'target_status'  => 'required|in:MENUNGGU_TTV,MENUNGGU_DOKTER,MENUNGGU_LAB,MENUNGGU_FARMASI,MENUNGGU_BILLING,SELESAI_LUNAS'
        ]);

        DB::beginTransaction();
        try {
            $pendaftaran = Pendaftaran::findOrFail($request->pendaftaran_id);
            $oldStatus = $pendaftaran->status;

            // Update status ke modul tujuan
            $pendaftaran->update([
                'status' => $request->target_status
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Status pasien berhasil diubah dari [{$oldStatus}] menjadi [{$request->target_status}]."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengubah status pasien: ' . $e->getMessage()
            ], 500);
        }
    }
}