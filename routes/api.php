<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'authenticate']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});


Route::prefix('admin')->middleware(['auth:sanctum', 'is_admin'])->group(function () {
    Route::get('/get-siswa', [AdminController::class, 'getSiswa']);
    Route::post('/create-siswa', [AdminController::class, 'createSiswa']);
    Route::delete('/delete-siswa/{id}', [AdminController::class, 'deleteSiswa']);
    Route::get('/detail-siswa/{id}', [AdminController::class, 'detailSiswa']);
    Route::post('/siswa-import', [AdminController::class, 'siswaImport']);
});

Route::prefix('siswa')->middleware(['auth:sanctum', 'is_siswa'])->group(function () {
    Route::get('/get-siswa', function (Request $request) {
        return response()->json([
            'users' => \App\Models\Siswa::all(),
        ]);
    });
});

Route::prefix('guru')->middleware(['auth:sanctum', 'is_guru'])->group(function () {
    Route::get('/get-siswa', function (Request $request) {
        return response()->json([
            'users' => \App\Models\Guru::all(),
        ]);
    });
});
