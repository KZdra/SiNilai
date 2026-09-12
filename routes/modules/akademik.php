<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\NilaiAkhirController;
use App\Http\Controllers\TpController;
use App\Http\Controllers\PeskulController;
use App\Http\Controllers\CatatanWalasController;
use App\Http\Controllers\NilaiAuditController;
use App\Http\Controllers\P5Controller;
use App\Http\Controllers\RaportExplorerController;

/**
 * Modul Akademik
 * Semua route yang berkaitan dengan penilaian, nilai akhir,
 * formatif, P5, eskul, catatan walas, dan audit trail nilai.
 *
 * Middleware: auth + checkClass
 */
Route::middleware(['auth', 'checkClass'])->group(function () {

    // ── Nilai (Sumatif) ────────────────────────────────────────
    Route::prefix('nilai')->name('value.')->group(function () {
        Route::get('/', [NilaiController::class, 'index'])->name('index');
        Route::post('/', [NilaiController::class, 'store'])->name('store');
        Route::get('/get', [NilaiController::class, 'getData'])->name('getByClass');
        Route::get('/getmapel', [NilaiController::class, 'getMapel'])->name('getMapel');
        Route::get('/template', [NilaiController::class, 'downloadTemplate'])->name('download');
        Route::get('/export', [NilaiController::class, 'exportPDF'])->name('exportPDF');
        Route::post('/import', [NilaiController::class, 'import'])->name('import');
        Route::put('/{id}', [NilaiController::class, 'update'])->name('update');
        Route::delete('/{id}', [NilaiController::class, 'destroy'])->name('destroy');
        Route::post('/cbt-sync', [NilaiController::class, 'syncFromCbt'])->name('cbtSync');
        Route::get('/cbt-test', [NilaiController::class, 'testCbtConnection'])->name('cbtTest');
        Route::get('/cbt-logs', [NilaiController::class, 'getCbtSyncLogs'])->name('cbtLogs');
    });

    // ── Nilai Akhir ────────────────────────────────────────────
    Route::prefix('akhir')->name('nilaiakhir.')->group(function () {
        Route::get('/', [NilaiAkhirController::class, 'index'])->name('index');
        Route::get('/siswa', [NilaiAkhirController::class, 'detailNilaiAkhir'])->name('detailNilaiAkhir');
        Route::get('/getAVG', [NilaiAkhirController::class, 'getAllStudentAveragesOnly'])->name('getAllStudentAVG');
        Route::get('/print', [NilaiAkhirController::class, 'exportPDF'])->name('print');
        Route::get('/excel', [NilaiAkhirController::class, 'ExportNilaiAkhirExcel'])->name('exportexcel');
        Route::get('/ranking', [NilaiAkhirController::class, 'exportRankingExcel'])->name('exportranking');
        Route::get('/leger', [NilaiAkhirController::class, 'exportLegerExcel'])->name('exportleger');
    });

    // ── Penjelajah Arsip Raport (jsTree) ───────────────────────
    Route::prefix('raport-explorer')->name('raport_explorer.')->group(function () {
        Route::get('/', [RaportExplorerController::class, 'index'])->name('index');
        Route::get('/tree', [RaportExplorerController::class, 'getTreeData'])->name('tree');
        Route::get('/download', [RaportExplorerController::class, 'download'])->name('download');
        Route::delete('/delete', [RaportExplorerController::class, 'destroy'])->name('destroy');
    });

    // ── Audit Trail Nilai ──────────────────────────────────────
    Route::prefix('audit-nilai')->name('audit.')->group(function () {
        Route::get('/', [NilaiAuditController::class, 'index'])->name('index');
        Route::get('/data', [NilaiAuditController::class, 'getData'])->name('getData');
    });

    // ── Tujuan Pembelajaran (TP) ───────────────────────────────
    Route::prefix('mastertp')->name('mastertp.')->group(function () {
        Route::get('/', [TpController::class, 'index'])->name('index');
        Route::get('/all', [TpController::class, 'getdata'])->name('getdata');
        Route::post('/', [TpController::class, 'store'])->name('store');
        Route::put('/{id}', [TpController::class, 'update'])->name('update');
        Route::delete('/{id}', [TpController::class, 'destroy'])->name('destroy');
    });

    // ── Asesmen Formatif ───────────────────────────────────────
    Route::prefix('formatif')->name('formatif.')->group(function () {
        Route::get('/', [TpController::class, 'indexFormatif'])->name('index');
        Route::get('/get', [TpController::class, 'getDataFormatif'])->name('getdata');
        Route::get('/grid', [TpController::class, 'getFormatifGrid'])->name('grid');
        Route::get('/getlist', [TpController::class, 'getTPList'])->name('gettplist');
        Route::post('/', [TpController::class, 'storeFormatif'])->name('store');
        Route::post('/bulk', [TpController::class, 'storeFormatifBulk'])->name('storeBulk');
        Route::delete('/{id}', [TpController::class, 'destroyFormatif'])->name('destroy');
    });

    // ── Penilaian Ekstrakurikuler ──────────────────────────────
    Route::prefix('peskul')->name('peskul.')->group(function () {
        Route::get('/', [PeskulController::class, 'index'])->name('index');
        Route::get('/get', [PeskulController::class, 'getdata'])->name('getdata');
        Route::post('/', [PeskulController::class, 'store'])->name('store');
        Route::post('/bulk', [PeskulController::class, 'storeBulk'])->name('storeBulk');
        Route::put('/{id}', [PeskulController::class, 'update'])->name('update');
        Route::delete('/{id}', [PeskulController::class, 'destroy'])->name('destroy');
    });

    // ── Catatan & Presensi Wali Kelas ──────────────────────────
    Route::prefix('walas')->name('walas.')->group(function () {
        Route::get('/', [CatatanWalasController::class, 'index'])->name('index');
        Route::get('/get', [CatatanWalasController::class, 'getData'])->name('getdata');
        Route::post('/', [CatatanWalasController::class, 'store'])->name('store');
        Route::post('/bulk', [CatatanWalasController::class, 'storeBulk'])->name('storeBulk');
    });

    // ── Projek P5 (Kurikulum Merdeka) ─────────────────────────
    Route::prefix('p5')->name('p5.')->group(function () {
        Route::get('/', [P5Controller::class, 'index'])->name('index');
        Route::post('/projek', [P5Controller::class, 'storeProjek'])->name('projek.store');
        Route::get('/projek/{id}/edit', [P5Controller::class, 'editProjek'])->name('projek.edit');
        Route::put('/projek/{id}', [P5Controller::class, 'updateProjek'])->name('projek.update');
        Route::delete('/projek/{id}', [P5Controller::class, 'destroyProjek'])->name('projek.destroy');
        Route::get('/penilaian/{id}', [P5Controller::class, 'penilaian'])->name('penilaian');
        Route::post('/penilaian/{id}', [P5Controller::class, 'storePenilaian'])->name('penilaian.store');
        Route::post('/cetak', [P5Controller::class, 'cetakRaportP5'])->name('cetak');
    });

});
