<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KenaikanKelasController;
use App\Http\Controllers\SettingController;

/**
 * Modul Admin
 * Route yang hanya bisa diakses oleh role Admin (role_id = 1).
 *
 * Middleware: auth + roleCheck:1
 */
Route::middleware(['auth', 'roleCheck:1'])->group(function () {

    // ── Manajemen User ─────────────────────────────────────────
    Route::prefix('muser')->name('muser.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    });

    // ── Data Users (legacy endpoint — bisa dihapus jika tidak dipakai) ──
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/data', [UserController::class, 'getData'])->name('getData');
    });

    // ── Kenaikan Kelas & Kelulusan ────────────────────────────
    Route::prefix('kenaikan-kelas')->name('kenaikan_kelas.')->group(function () {
        Route::get('/', [KenaikanKelasController::class, 'index'])->name('index');
        Route::get('/siswa', [KenaikanKelasController::class, 'getStudents'])->name('get_students');
        Route::post('/', [KenaikanKelasController::class, 'promote'])->name('promote');
        Route::post('/tutup-tahun', [KenaikanKelasController::class, 'tutupTahunAjaran'])->name('tutup_tahun');
    });

    // ── Settings ───────────────────────────────────────────────
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/auth', [SettingController::class, 'editAuth'])->name('auth');
        Route::post('/auth', [SettingController::class, 'updateAuth'])->name('auth.update');
    });

});
