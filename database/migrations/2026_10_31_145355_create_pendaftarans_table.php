<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('pendaftarans', function (Blueprint $table) {
        $table->id();
        $table->string('no_pendaftaran', 25)->unique();
        $table->foreignId('pasien_id')->constrained('pasiens')->onDelete('cascade');
        $table->foreignId('dokter_id')->constrained('users')->onDelete('cascade');
        $table->enum('jenis_layanan', ['RJ', 'RI', 'IGD']);
        $table->enum('jenis_penjamin', ['UMUM', 'BPJS', 'ASURANSI_SWASTA']);
        $table->enum('status', [
            'MENUNGGU_TTV',
            'MENUNGGU_DOKTER',
            'PEMERIKSAAN_LAB',
            'PROSES_FARMASI',
            'MENUNGGU_PEMBAYARAN',
            'SELESAI',
            'BATAL'
        ])->default('MENUNGGU_TTV');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftarans');
    }
};
