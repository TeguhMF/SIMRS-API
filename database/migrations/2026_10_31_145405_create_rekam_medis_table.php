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
    Schema::create('rekam_medis', function (Blueprint $table) {
        $table->id();
        $table->foreignId('pendaftaran_id')->constrained('pendaftarans')->onDelete('cascade');
        $table->foreignId('dokter_id')->constrained('users')->onDelete('cascade');
        $table->text('subjective');
        $table->text('objective');
        $table->string('kode_icd10', 10);
        $table->string('diagnosa_nama', 255);
        $table->text('plan');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekam_medis');
    }
};
