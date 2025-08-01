<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminGuruController;
use App\Http\Controllers\Admin\AdminLokasiPrakerinController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\kelolaPembimngLokasiController;
use App\Http\Controllers\Admin\PenempatanSiswaController;
use App\Http\Controllers\FaceRecognitionController;
use App\Http\Controllers\Guru\AnakBimbingController;
use App\Http\Controllers\Guru\LokasiPembimbingPrakerin;
use App\Http\Controllers\Siswa\AbsensiController;
use App\Http\Controllers\Siswa\ProfileController;
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
        Route::get('/get-lokasi-prakerin', [LokasiPembimbingPrakerin::class, 'getAllLokasiPembimbing']);
        Route::get('/detail-lokasi-prakerin/{id}', [LokasiPembimbingPrakerin::class, 'getDetailLokasiPembimbing']);

        Route::get('/get-anak-bimbing', [AnakBimbingController::class, 'getAnakBimbing']);
        Route::get('/detail-anak-bimbing/{id}', [AnakBimbingController::class, 'getDetailAnakBimbing']);
    });

    // 👨‍🎓 Siswa
    Route::prefix('siswa')->middleware(['auth:sanctum', 'is_siswa'])->group(function () {
        Route::post('/registered-face', [SiswaController::class, 'registeredFace']);
        // get_profile
        Route::get("/get-profile", [ProfileController::class, 'getProfile']);
        // get_pembimbing_siswa
        Route::get("/get-pembimbing-siswa", [SiswaController::class, 'getPembimbingSiswa']);

        // absensi 
        Route::post('/absensi', [AbsensiController::class, 'absensi']);
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

        // kelola pembimbing - lokasi prakerin
        Route::get('/get-kelola-pembimbing', [kelolaPembimngLokasiController::class, 'getKelolaPembimbingLokasi']);
        Route::post("/kelola-pembimbing-lokasi", [kelolaPembimngLokasiController::class, 'addKelola']);
        Route::delete("/kelola-pembimbing-lokasi/{id}", [kelolaPembimngLokasiController::class, 'deleteKelola']);

        // Penempatan siswa
        Route::get("/penempatan-siswa", [PenempatanSiswaController::class, 'index']);
        Route::post("/tambah-penempatan-siswa", [PenempatanSiswaController::class, 'addPenempatan']);
        Route::delete("/hapus-penempatan-siswa/{id}", [PenempatanSiswaController::class, 'deletePenempatan']);
        Route::put("/batalkan-penempatan-siswa/{id}", [PenempatanSiswaController::class, 'batalkanPenempatan']);
    });
});

// 🤖 Face Recognition Routes (tidak perlu auth untuk testing)
Route::prefix('face-recognition')->group(function () {
    Route::post('/recognize-file', [FaceRecognitionController::class, 'recognizeFace']);
    Route::post('/recognize-base64', [FaceRecognitionController::class, 'recognizeFaceBase64']);
    Route::get('/available-faces', [FaceRecognitionController::class, 'getAvailableFaces']);
    Route::get('/test', [FaceRecognitionController::class, 'testRecognition']);
});

Route::fallback(function () {
    return response()->json([
        'message' => 'API not found',
    ], 404);
});
