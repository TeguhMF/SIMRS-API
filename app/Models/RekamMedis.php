<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekamMedis extends Model
{
    use HasFactory;

    protected $table = 'rekam_medis';
    protected $guarded = ['id'];

    // Otomatis menyertakan atribut ini di response JSON untuk Lab, Farmasi, & Kasir
    protected $appends = ['anamnesis', 'diagnosis', 'catatan_resep'];

    public function pendaftaran()
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    public function dokter()
    {
        return $this->belongsTo(User::class, 'dokter_id');
    }

    public function resepDetails()
    {
        return $this->hasMany(ResepDetail::class, 'rekam_medis_id');
    }

    // Mapping otomatis kolom anamnesis -> subjective
    public function getAnamnesisAttribute()
    {
        return $this->attributes['anamnesis']
            ?? $this->attributes['subjective']
            ?? '-';
    }

    // Mapping otomatis kolom diagnosis -> diagnosa_nama / assessment / kode_icd10
    public function getDiagnosisAttribute()
    {
        return $this->attributes['diagnosis']
            ?? $this->attributes['diagnosa_nama']
            ?? $this->attributes['nama_diagnosa']
            ?? $this->attributes['assessment']
            ?? $this->attributes['kode_icd10']
            ?? '-';
    }

    // Mapping otomatis catatan_resep -> tabel resep_details atau plan
    public function getCatatanResepAttribute()
    {
        if (!empty($this->attributes['catatan_resep'])) {
            return $this->attributes['catatan_resep'];
        }

        $resep = $this->resepDetails()->latest()->first();
        if ($resep) {
            return $resep->instruksi ?? $resep->aturan_pakai ?? $resep->dosis ?? '-';
        }

        return $this->attributes['plan'] ?? '-';
    }
}