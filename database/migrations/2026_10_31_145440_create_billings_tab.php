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
    Schema::create('billings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('pendaftaran_id')->constrained('pendaftarans')->onDelete('cascade');
        $table->decimal('biaya_registrasi', 12, 2)->default(50000);
        $table->decimal('biaya_dokter', 12, 2)->default(100000);
        $table->decimal('biaya_obat', 12, 2)->default(0);
        $table->decimal('biaya_lab', 12, 2)->default(0);
        $table->decimal('total_bayar', 12, 2);
        $table->enum('status_pembayaran', ['UNPAID', 'PAID'])->default('UNPAID');
        $table->enum('metode_pembayaran', ['TUNAI', 'QRIS', 'TRANSFER', 'BPJS'])->nullable();
        $table->timestamp('tgl_pembayaran')->nullable();
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billings_tab');
    }
};
