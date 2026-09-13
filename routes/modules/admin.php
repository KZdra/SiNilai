<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KenaikanKelasController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\RaportExplorerController;

/**
 * Modul Admin
 * Route yang hanya bisa diakses oleh role Admin (role_id = 1).
 *
 * Middleware: auth + roleCheck:1
 */
Route::middleware(['auth', 'roleCheck:1'])->group(function () {

    // ── Backup Database ─────────────────────────────────────────
    Route::prefix('backup')->name('backup.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])->name('index');
        Route::post('/', [BackupController::class, 'store'])->name('store');
        Route::get('/download/{filename}', [BackupController::class, 'download'])->name('download');
        Route::delete('/{filename}', [BackupController::class, 'destroy'])->name('destroy');
    });

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
        Route::get('/modules', [SettingController::class, 'editModules'])->name('modules');
        Route::post('/modules', [SettingController::class, 'updateModules'])->name('modules.update');
    });

    // ── Penjelajah Arsip Raport (jsTree - Khusus Admin) ────────
    Route::prefix('raport-explorer')->name('raport_explorer.')->group(function () {
        Route::get('/', [RaportExplorerController::class, 'index'])->name('index');
        Route::get('/tree', [RaportExplorerController::class, 'getTreeData'])->name('tree');
        Route::get('/download', [RaportExplorerController::class, 'download'])->name('download');
        Route::delete('/delete', [RaportExplorerController::class, 'destroy'])->name('destroy');
    });

});
