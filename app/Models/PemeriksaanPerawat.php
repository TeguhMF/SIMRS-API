<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemeriksaanPerawat extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function pendaftaran()
    {
        return $this->belongsTo(Pendaftaran::class);
    }

    public function perawat()
    {
        return $this->belongsTo(User::class, 'perawat_id');
    }

    public function pemeriksaanPerawat()
    {
        return $this->hasOne(PemeriksaanPerawat::class, 'pendaftaran_id')->latestOfMany();
    }

    public function rekamMedis()
    {
        return $this->hasOne(RekamMedis::class, 'pendaftaran_id');
    }
}