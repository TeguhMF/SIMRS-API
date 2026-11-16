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
    Schema::create('order_lab_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_lab_id')->constrained('order_labs')->onDelete('cascade');
        $table->string('nama_pemeriksaan', 100);
        $table->string('hasil', 100)->nullable();
        $table->string('nilai_rujukan', 100)->nullable();
        $table->decimal('biaya', 12, 2);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('order_lab_details');
}
};
