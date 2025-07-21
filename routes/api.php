<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminGuruController;
use App\Http\Controllers\Admin\AdminLokasiPrakerinController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\KelolaPembimbingController;
use App\Http\Controllers\Admin\PenempatanSiswaController;
use App\Http\Controllers\Siswa\SiswaController;
use Illuminate\Support\Facades\Route;

Route::middleware('api-session')->group(function () {

    Route::prefix('auth')->group(function () {
        // ✅ Login & Logout sekarang berada di group yang punya middleware session
        Route::post('/login', [AuthController::class, 'authenticate']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
        // change password
        Route::put('/password_changed_at/{id}', [AuthController::class, 'password_changed'])->middleware('auth:sanctum');
    });

    // 🧑‍🏫 Guru
    Route::prefix('guru')->middleware(['auth:sanctum', 'is_guru'])->group(function () {
        // Route::get('/get-guru', [AdminGuruController::class, 'getGuru']);
    });

    // 👨‍🎓 Siswa
    Route::prefix('siswa')->middleware(['auth:sanctum', 'is_siswa'])->group(function () {
        Route::post('/registered-face', [SiswaController::class, 'registeredFace']);
    });

    // 🧑‍💼 Admin
    Route::prefix('admin')->middleware(['auth:sanctum', 'is_admin'])->group(function () {
        Route::get('/get-siswa', [AdminController::class, 'getSiswa']);
        Route::post('/create-siswa', [AdminController::class, 'createSiswa']);
        Route::put('/update-siswa/{id}', [AdminController::class, 'updateSiswa']);
        Route::delete('/delete-siswa/{id}', [AdminController::class, 'deleteSiswa']);
        Route::get('/detail-siswa/{id}', [AdminController::class, 'detailSiswa']);
        Route::post('/siswa-import', [AdminController::class, 'siswaImport']);

        Route::get('/get-guru', [AdminGuruController::class, 'getGuru']);
        Route::delete('/guru-delete/{id}', [AdminGuruController::class, 'delete']);
        Route::post('/create-guru', [AdminGuruController::class, 'createGuru']);
        Route::put('/update-guru/{id}', [AdminGuruController::class, 'updateGuru']);
        Route::post('/guru-import', [AdminGuruController::class, 'importGuru']);

        Route::get('/get-lokasi-prakerin', [AdminLokasiPrakerinController::class, 'getAllLokasiPrakerin']);
        Route::post('/create-lokasi-prakerin', [AdminLokasiPrakerinController::class, 'createLokasiPrakerin']);
        Route::put('/update-lokasi-prakerin/{id}', [AdminLokasiPrakerinController::class, 'updateLokasiPrakerin']);
        Route::delete('/delete-lokasi-prakerin/{id}', [AdminLokasiPrakerinController::class, 'deleteLokasiPrakerin']);
        Route::post("/import-lokasi", [AdminLokasiPrakerinController::class, 'importLokasi']);
        Route::get("/detail-lokasi/{id}", [AdminLokasiPrakerinController::class, 'detailLokasi']);

        // kelola pembimbing
        Route::get('/get-kelola-pembimbing', [KelolaPembimbingController::class, 'getKelolaPembimbing']);
        Route::post("/kelola-pembimbing", [KelolaPembimbingController::class, 'addKelola']);
        Route::put("/kelola-pembimbing/edit/{id}", [KelolaPembimbingController::class, 'updated']);
        Route::delete("/kelola-pembimbing-delete/{id}", [KelolaPembimbingController::class, 'destroy']);

        // Penempatan siswa
        Route::get("/penempatan-siswa", [PenempatanSiswaController::class, 'index']);
        Route::post("/tambah-penempatan-siswa", [PenempatanSiswaController::class, 'update']);
    });
});


Route::fallback(function () {
    return response()->json([
        'message' => 'API not found',
    ], 404);
});
