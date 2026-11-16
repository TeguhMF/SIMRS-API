<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PendaftaranController extends Controller
{
    // List Antrean Pendaftaran Hari Ini
    public function index(Request $request)
    {
        $pendaftarans = Pendaftaran::with(['pasien', 'dokter'])
            ->whereDate('created_at', Carbon::today())
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $pendaftarans
        ]);
    }

    // List Dokter untuk Dropdown Pendaftaran
    public function getDokters()
    {
        $dokters = User::role('dokter')->get(['id', 'name']);

        return response()->json([
            'status' => 'success',
            'data' => $dokters
        ]);
    }

    // Buat Pendaftaran Baru
    public function store(Request $request)
    {
        $request->validate([
            'pasien_id' => 'required|exists:pasiens,id',
            'dokter_id' => 'required|exists:users,id',
            'jenis_layanan' => 'required|in:RJ,RI,IGD',
            'jenis_penjamin' => 'required|in:UMUM,BPJS,ASURANSI_SWASTA',
        ]);

        // Generate No. Pendaftaran: REG-YYYYMMDD-XXXX
        $today = Carbon::now()->format('Ymd');
        $lastReg = Pendaftaran::where('no_pendaftaran', 'like', "REG-{$today}-%")->orderBy('id', 'desc')->first();

        if ($lastReg) {
            $lastNum = (int) substr($lastReg->no_pendaftaran, -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        $noReg = "REG-{$today}-{$nextNum}";

        $pendaftaran = Pendaftaran::create([
            'no_pendaftaran' => $noReg,
            'pasien_id' => $request->pasien_id,
            'dokter_id' => $request->dokter_id,
            'jenis_layanan' => $request->jenis_layanan,
            'jenis_penjamin' => $request->jenis_penjamin,
            'status' => 'MENUNGGU_TTV',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Pendaftaran berobat berhasil dibuat.',
            'data' => $pendaftaran->load(['pasien', 'dokter'])
        ], 201);
    }

    public function destroy($id)
    {
        $pendaftaran = Pendaftaran::findOrFail($id);

        // Validasi ketat: hanya boleh dibatalkan jika belum masuk TTV
        if ($pendaftaran->status !== 'MENUNGGU_TTV') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Antrean tidak dapat dibatalkan karena pasien sudah masuk tahap pemeriksaan (' . $pendaftaran->status . ').'
            ], 422);
        }

        $pendaftaran->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Antrean pendaftaran pasien berhasil dibatalkan.'
        ]);
    }
}