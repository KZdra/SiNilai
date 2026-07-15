<?php

use App\Http\Controllers\ClassController;
use App\Http\Controllers\DataSekolahContoller;
use App\Http\Controllers\EskulController;
use App\Http\Controllers\FstController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\NilaiAkhirController;
use App\Http\Controllers\PeskulController;
use App\Http\Controllers\TpController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('es',[PeskulController::class,'getdata']);
Route::middleware('auth')->group(function () {

    // Begin Useless
    Route::get('users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    // Kelas
    Route::get('kelas', [ClassController::class, 'index'])->name('class.index');
    Route::post('kelas', [ClassController::class, 'store'])->name('class.store');
    Route::put('kelas/{id}', [ClassController::class, 'update'])->name('class.update');
    Route::delete('kelas/{id}', [ClassController::class, 'destroy'])->name('class.destroy');

    Route::middleware('checkClass')->group(function () {
        // SISWA
        Route::get('siswa', [SiswaController::class, 'index'])->name('student.index');
        Route::post('siswa', [SiswaController::class, 'store'])->name('student.store');
        Route::get('siswa/template', [SiswaController::class, 'downloadTemplate'])->name('student.download');
        Route::post('siswa/import', [SiswaController::class, 'import'])->name('student.import');
        Route::put('siswa/{id}', [SiswaController::class, 'update'])->name('student.update');
        Route::delete('siswa/{id}', [SiswaController::class, 'destroy'])->name('student.destroy');
        // Nilai
        Route::get('nilai', [NilaiController::class, 'index'])->name('value.index');
        Route::post('nilai', [NilaiController::class, 'store'])->name('value.store');
        Route::get('nilai/get', [NilaiController::class, 'getData'])->name('value.getByClass');
        Route::get('nilai/getmapel', [NilaiController::class, 'getMapel'])->name('value.getMapel');
        Route::get('nilai/template', [NilaiController::class, 'downloadTemplate'])->name('value.download');
        Route::get('nilai/export', [NilaiController::class, 'exportPDF'])->name('value.exportPDF');
        Route::post('nilai/import', [NilaiController::class, 'import'])->name('value.import');
        Route::put('nilai/{id}', [NilaiController::class, 'update'])->name('value.update');
        Route::delete('nilai/{id}', [NilaiController::class, 'destroy'])->name('value.destroy');
        //Nilai Akhir
        Route::get('akhir', [NilaiAkhirController::class, 'index'])->name('nilaiakhir.index');
        Route::get('akhir/siswa', [NilaiAkhirController::class, 'detailNilaiAkhir'])->name('nilaiakhir.detailNilaiAkhir');
        Route::get('akhir/getAVG', [NilaiAkhirController::class, 'getAllStudentAveragesOnly'])->name('nilaiakhir.getAllStudentAVG');
        Route::get('akhir/print',[NilaiAkhirController::class,'exportPDF'])->name('nilaiakhir.print');
        Route::get('akhir/excel',[NilaiAkhirController::class,'ExportNilaiAkhirExcel'])->name('nilaiakhir.exportexcel');
        Route::get('akhir/ranking',[NilaiAkhirController::class,'exportRankingExcel'])->name('nilaiakhir.exportranking');
        // Tujuan Pembelajaran
        Route::get('mastertp',[TpController::class,'index'])->name('mastertp.index');
        Route::get('mastertp/all',[TpController::class,'getdata'])->name('mastertp.getdata');
        Route::post('mastertp',[TpController::class,'store'])->name('mastertp.store');
        Route::put('mastertp/{id}',[TpController::class,'update'])->name('mastertp.update');
        Route::delete('mastertp/{id}',[TpController::class,'destroy'])->name('mastertp.destroy');
        // INput Formatif
        Route::get('formatif',[TpController::class,'indexFormatif'])->name('formatif.index');
        Route::get('formatif/get',[TpController::class,'getDataFormatif'])->name('formatif.getdata');
        Route::get('formatif/getlist',[TpController::class,'getTPList'])->name('formatif.gettplist');
        Route::post('formatif',[TpController::class,'storeFormatif'])->name('formatif.store');
        Route::delete('formatif/{id}',[TpController::class,'destroyFormatif'])->name('formatif.destroy');
        // Penilaian Eskul
        Route::get('peskul',[PeskulController::class,'index'])->name('peskul.index');
        Route::get('peskul/get',[PeskulController::class,'getdata'])->name('peskul.getdata');
        Route::post('peskul',[PeskulController::class,'store'])->name('peskul.store');
        Route::put('peskul/{id}',[PeskulController::class,'update'])->name('peskul.update');
        Route::delete('peskul/{id}',[PeskulController::class,'destroy'])->name('peskul.destroy');
    });

    // mapel
    Route::get('mapel', [MapelController::class, 'index'])->name('mapel.index');
    Route::post('mapel', [MapelController::class, 'store'])->name('mapel.store');
    Route::get('mapel/all', [MapelController::class, 'getAll'])->name('mapel.getAll');
    Route::put('mapel/{id}', [MapelController::class, 'update'])->name('mapel.update');
    Route::delete('mapel/{id}', [MapelController::class, 'destroy'])->name('mapel.destroy');
    // Mapel Mapping
    Route::get('mapel-mapping', [App\Http\Controllers\MapelMappingController::class, 'index'])->name('mapel_mapping.index');
    Route::post('mapel-mapping/toggle', [App\Http\Controllers\MapelMappingController::class, 'toggleMapel'])->name('mapel_mapping.toggle');
    Route::post('mapel-mapping/copy', [App\Http\Controllers\MapelMappingController::class, 'copyFromPrevious'])->name('mapel_mapping.copy');
    Route::post('mapel-mapping/activate-all', [App\Http\Controllers\MapelMappingController::class, 'activateAll'])->name('mapel_mapping.activate_all');
    //DataSekolah
    Route::get('datasekolah',[DataSekolahContoller::class,'index'])->name('datasekolah.index');
    Route::post('datasekolah',[DataSekolahContoller::class,'store'])->name('datasekolah.store');
    Route::delete('datasekolah/{id}',[DataSekolahContoller::class,'destroy'])->name('datasekolah.destroy');
    // Fase/Semester/Tahun Ajaran
    Route::get('mfst',[FstController::class,'index'])->name('mfst.index');
    Route::get('mfst/get',[FstController::class,'getData'])->name('mfst.getData');
    Route::post('mfst',[FstController::class,'store'])->name('mfst.store');
    Route::put('mfst/{id}',[FstController::class,'update'])->name('mfst.update');
    Route::delete('mfst/{id}',[FstController::class,'destroy'])->name('mfst.destroy');
    // User Management
    Route::middleware(['roleCheck:1'])->group(function () {
    Route::get('muser',[UserController::class,'index'])->name('muser.index');
    Route::post('muser',[UserController::class,'store'])->name('muser.store');
    Route::put('muser/{id}',[UserController::class,'update'])->name('muser.update');
    Route::delete('muser/{id}',[UserController::class,'destroy'])->name('muser.destroy');
    });
    // Eskul
    Route::get('meskul',[EskulController::class,'index'])->name('meskul.index');
    Route::get('meskul/get',[EskulController::class,'getdata'])->name('meskul.getdata');
    Route::post('meskul',[EskulController::class,'store'])->name('meskul.store');
    Route::put('meskul/{id}',[EskulController::class,'update'])->name('meskul.update');
    Route::delete('meskul/{id}',[EskulController::class,'destroy'])->name('meskul.destroy');
});
