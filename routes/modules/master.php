<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\MapelMappingController;
use App\Http\Controllers\EskulController;
use App\Http\Controllers\FstController;
use App\Http\Controllers\DataSekolahContoller;

/**
 * Modul Master Data
 * Berisi manajemen data induk: kelas, siswa, mapel,
 * eskul, FST (Fase/Semester/Tahun Ajaran), data sekolah.
 *
 * Middleware: auth
 */
Route::middleware('auth')->group(function () {

    // ── Kelas ──────────────────────────────────────────────────
    Route::prefix('kelas')->name('class.')->group(function () {
        Route::get('/', [ClassController::class, 'index'])->name('index');
        Route::get('/data', [ClassController::class, 'getData'])->name('getData');
        Route::post('/', [ClassController::class, 'store'])->name('store');
        Route::put('/{id}', [ClassController::class, 'update'])->name('update');
        Route::delete('/{id}', [ClassController::class, 'destroy'])->name('destroy');
    });

    // ── Siswa ──────────────────────────────────────────────────
    // Middleware checkClass ditambahkan karena siswa terikat kelas aktif
    Route::middleware('checkClass')->prefix('siswa')->name('student.')->group(function () {
        Route::get('/', [SiswaController::class, 'index'])->name('index');
        Route::get('/data', [SiswaController::class, 'getData'])->name('getData');
        Route::post('/', [SiswaController::class, 'store'])->name('store');
        Route::get('/template', [SiswaController::class, 'downloadTemplate'])->name('download');
        Route::post('/import', [SiswaController::class, 'import'])->name('import');
        Route::put('/{id}', [SiswaController::class, 'update'])->name('update');
        Route::delete('/{id}', [SiswaController::class, 'destroy'])->name('destroy');
    });

    // ── Mata Pelajaran ─────────────────────────────────────────
    Route::prefix('mapel')->name('mapel.')->group(function () {
        Route::get('/', [MapelController::class, 'index'])->name('index');
        Route::get('/all', [MapelController::class, 'getAll'])->name('getAll');
        Route::post('/', [MapelController::class, 'store'])->name('store');
        Route::put('/{id}', [MapelController::class, 'update'])->name('update');
        Route::delete('/{id}', [MapelController::class, 'destroy'])->name('destroy');
    });

    // ── Mapel Mapping (Penugasan Mapel ke Kelas) ───────────────
    Route::prefix('mapel-mapping')->name('mapel_mapping.')->group(function () {
        Route::get('/', [MapelMappingController::class, 'index'])->name('index');
        Route::post('/toggle', [MapelMappingController::class, 'toggleMapel'])->name('toggle');
        Route::post('/copy', [MapelMappingController::class, 'copyFromPrevious'])->name('copy');
        Route::post('/activate-all', [MapelMappingController::class, 'activateAll'])->name('activate_all');
    });

    // ── Ekstrakurikuler (Master) ───────────────────────────────
    Route::prefix('meskul')->name('meskul.')->group(function () {
        Route::get('/', [EskulController::class, 'index'])->name('index');
        Route::get('/get', [EskulController::class, 'getdata'])->name('getdata');
        Route::post('/', [EskulController::class, 'store'])->name('store');
        Route::put('/{id}', [EskulController::class, 'update'])->name('update');
        Route::delete('/{id}', [EskulController::class, 'destroy'])->name('destroy');
    });

    // ── FST (Fase / Semester / Tahun Ajaran) ──────────────────
    Route::prefix('mfst')->name('mfst.')->group(function () {
        Route::get('/', [FstController::class, 'index'])->name('index');
        Route::get('/get', [FstController::class, 'getData'])->name('getData');
        Route::post('/', [FstController::class, 'store'])->name('store');
        Route::put('/{id}', [FstController::class, 'update'])->name('update');
        Route::delete('/{id}', [FstController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-lock', [FstController::class, 'toggleLock'])->name('toggleLock');
    });

    // ── Data Sekolah ───────────────────────────────────────────
    Route::prefix('datasekolah')->name('datasekolah.')->group(function () {
        Route::get('/', [DataSekolahContoller::class, 'index'])->name('index');
        Route::post('/', [DataSekolahContoller::class, 'store'])->name('store');
        Route::delete('/{id}', [DataSekolahContoller::class, 'destroy'])->name('destroy');
    });

    // ── Profile ────────────────────────────────────────────────
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ProfileController::class, 'show'])->name('show');
        Route::put('/', [\App\Http\Controllers\ProfileController::class, 'update'])->name('update');
    });

});
