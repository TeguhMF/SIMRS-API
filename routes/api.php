<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasienController;
use App\Http\Controllers\Api\PendaftaranController;
use App\Http\Controllers\Api\PemeriksaanPerawatController;
use App\Http\Controllers\Api\RekamMedisController;
use App\Http\Controllers\Api\LaboratoriumController;
use App\Http\Controllers\Api\FarmasiController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\AdminController;

// Public Route
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Perlu Auth Token Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth User
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Admin Control Tower (Monitoring & Override Status)
    Route::get('/admin/overview', [AdminController::class, 'getOverview']);
    Route::post('/admin/override-status', [AdminController::class, 'overrideStatus']);

    // Modul Front Office (Pasien & Pendaftaran)
    Route::get('/pasiens', [PasienController::class, 'index']);
    Route::post('/pasiens', [PasienController::class, 'store']);
    Route::get('/pendaftarans', [PendaftaranController::class, 'index']);
    Route::post('/pendaftarans', [PendaftaranController::class, 'store']);
    Route::get('/dokters', [PendaftaranController::class, 'getDokters']);

    // Modul Nurse Station (TTV)
    Route::get('/nurse/antrean', [PemeriksaanPerawatController::class, 'index']);
    Route::post('/nurse/ttv', [PemeriksaanPerawatController::class, 'store']);

    // Modul RME Dokter
    Route::get('/dokter/antrean', [RekamMedisController::class, 'getAntrean']);
    Route::get('/dokter/ttv/{pendaftaranId}', [RekamMedisController::class, 'getTtvPasien']);
    Route::get('/dokter/obats', [RekamMedisController::class, 'getMasterObat']);
    Route::post('/dokter/rme', [RekamMedisController::class, 'store']);

    // Modul Laboratorium
    Route::get('/lab/antrean', [LaboratoriumController::class, 'index']);
    Route::post('/lab/submit', [LaboratoriumController::class, 'submitHasil']);

    // Modul Farmasi / Apotek
    Route::get('/farmasi/antrean', [FarmasiController::class, 'index']);
    Route::post('/farmasi/proses', [FarmasiController::class, 'prosesResep']);
    Route::post('/farmasi/serah-obat', [FarmasiController::class, 'serahObat']); // Alias pendukung

    // Modul Billing / Kasir
    Route::get('/billing/antrean', [BillingController::class, 'index']);
    Route::get('/billing/calculate/{pendaftaranId}', [BillingController::class, 'calculate']);
    Route::post('/billing/bayar', [BillingController::class, 'bayar']);

    Route::delete('/pendaftarans/{id}', [PendaftaranController::class, 'destroy']);

});