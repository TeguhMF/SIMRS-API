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
    Schema::create('pemeriksaan_perawats', function (Blueprint $table) {
        $table->id();
        $table->foreignId('pendaftaran_id')->constrained('pendaftarans')->onDelete('cascade');
        $table->foreignId('perawat_id')->constrained('users')->onDelete('cascade');
        $table->integer('sistol');
        $table->integer('diastol');
        $table->decimal('suhu', 4, 1);
        $table->integer('nadi');
        $table->integer('pernapasan');
        $table->integer('berat_badan')->nullable();
        $table->integer('tinggi_badan')->nullable();
        $table->text('keluhan_utama');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemeriksaan_perawats');
    }
};
