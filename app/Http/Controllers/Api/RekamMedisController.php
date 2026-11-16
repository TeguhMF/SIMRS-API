<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RekamMedis;
use App\Models\Pendaftaran;
use App\Models\PemeriksaanPerawat;
use App\Models\OrderLab;
use App\Models\ResepDetail;
use App\Models\Obat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RekamMedisController extends Controller
{
    /**
     * Get Antrean Pasien Dokter (Tarik semua pasien berstatus MENUNGGU_DOKTER / PROSES_DOKTER)
     */
    public function getAntrean(Request $request)
    {
        $antrean = Pendaftaran::with(['pasien', 'pemeriksaanPerawat'])
            ->whereIn('status', ['MENUNGGU_DOKTER', 'PROSES_DOKTER'])
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $antrean
        ]);
    }

    /**
     * Get Data Pemeriksaan Perawat (TTV) Pasien Terpilih
     */
    public function getTtvPasien($pendaftaranId)
    {
        $ttv = PemeriksaanPerawat::where('pendaftaran_id', $pendaftaranId)->latest()->first();

        if ($ttv) {
            if (empty($ttv->tekanan_darah) && (!empty($ttv->sistol) || !empty($ttv->diastol))) {
                $ttv->tekanan_darah = "{$ttv->sistol}/{$ttv->diastol}";
            }
        }

        return response()->json([
            'status' => 'success',
            'data'   => $ttv
        ]);
    }

    /**
     * Simpan Rekam Medis (Resep Catatan Teks, Toggle Lab Opsional)
     */
    public function store(Request $request)
    {
        $request->validate([
            'pendaftaran_id' => 'required|exists:pendaftarans,id',
            'anamnesis'      => 'required|string',
            'diagnosis'      => 'required|string',
            'tindakan'       => 'nullable|string',
            'catatan_resep'  => 'required|string', // Catatan resep wajib
            'kirim_ke_lab'   => 'nullable|boolean', // Status tombol lab
        ]);

        DB::beginTransaction();
        try {
            // 1. Simpan Rekam Medis (SOAP + Catatan Resep)
            $rmeData = [
                'pendaftaran_id' => $request->pendaftaran_id,
                'dokter_id'      => $request->user()->id,
                'subjective'     => $request->anamnesis,
                'anamnesis'      => $request->anamnesis,
                'objective'      => 'TTV Terlampir dari Nurse Station',
                'diagnosa_nama'  => $request->diagnosis,
                'nama_diagnosa'  => $request->diagnosis,
                'kode_icd10'     => $request->diagnosis,
                'diagnosis'      => $request->diagnosis,
                'diagnosa'       => $request->diagnosis,
                'assessment'     => $request->diagnosis,
                'plan'           => $request->tindakan ?? 'Terapi Medis',
                'tindakan'       => $request->tindakan,
                'catatan_resep'  => $request->catatan_resep,
            ];

            $tableColumns = Schema::getColumnListing('rekam_medis');
            $filteredData = array_intersect_key($rmeData, array_flip($tableColumns));
            $rme = RekamMedis::create($filteredData);

            // 2. Cari obat_id default agar tidak crash saat insert resep_details
            $defaultObat = Obat::first();
            $fallbackObatId = $defaultObat ? $defaultObat->id : 1;

            $resepData = [
                'rekam_medis_id' => $rme->id,
                'obat_id'        => $request->obat_id ?? $fallbackObatId, // Fallback aman untuk NOT NULL constraint
                'jumlah'         => $request->jumlah ?? 1,
                'dosis'          => $request->catatan_resep,
                'aturan_pakai'   => $request->catatan_resep,
                'instruksi'      => $request->catatan_resep,
                'status'         => 'MENUNGGU'
            ];
            
            $resepColumns = Schema::getColumnListing('resep_details');
            ResepDetail::create(array_intersect_key($resepData, array_flip($resepColumns)));

            // 3. Simpan Order Lab Dummy jika Tombol Lab Didaftarkan
            $isLab = $request->boolean('kirim_ke_lab');
            if ($isLab) {
                OrderLab::create([
                    'rekam_medis_id'    => $rme->id,
                    'jenis_pemeriksaan' => 'Pemeriksaan Laboratorium Umum',
                    'status'            => 'MENUNGGU'
                ]);
            }

            // 4. Update Status Kunjungan Pasien
            $pendaftaran = Pendaftaran::findOrFail($request->pendaftaran_id);
            if ($isLab) {
                // Ke Antrean Lab jika tombol lab aktif
                $pendaftaran->update(['status' => 'MENUNGGU_LAB']);
            } else {
                // LANGSUNG LONCAT KE FARMASI jika tombol lab tidak aktif
                $pendaftaran->update(['status' => 'MENUNGGU_FARMASI']);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => $isLab 
                    ? 'Rekam Medis disimpan. Pasien diteruskan ke Laboratorium.' 
                    : 'Rekam Medis disimpan. Pasien langsung diteruskan ke Farmasi.'
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan Rekam Medis: ' . $e->getMessage()
            ], 500);
        }
    }
}