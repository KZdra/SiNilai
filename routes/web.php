<?php

use App\Http\Controllers\ClassController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\SiswaController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {

    // Begin Useless
    Route::get('users', [\App\Http\Controllers\UserController::class, 'index'])->name('users.index');
    Route::get('profile', [\App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    //
    Route::get('kelas', [ClassController::class, 'index'])->name('class.index');
    Route::post('kelas', [ClassController::class, 'store'])->name('class.store');
    Route::put('kelas/{id}', [ClassController::class, 'update'])->name('class.update');
    Route::delete('kelas/{id}', [ClassController::class, 'destroy'])->name('class.destroy');
    // SISWA
    Route::get('siswa', [SiswaController::class, 'index'])->name('student.index');
    Route::post('siswa', [SiswaController::class, 'store'])->name('student.store');
    route::get('siswa/template', [SiswaController::class, 'downloadTemplate'])->name('student.download');
    Route::post('siswa/import', [SiswaController::class, 'import'])->name('student.import');
    Route::put('siswa/{id}', [SiswaController::class, 'update'])->name('student.update');
    Route::delete('siswa/{id}', [SiswaController::class, 'destroy'])->name('student.destroy');
    // Nilai
    Route::get('nilai', [NilaiController::class, 'index'])->name('value.index');
    Route::post('nilai', [NilaiController::class, 'store'])->name('value.store');
    Route::get('nilai/get', [NilaiController::class, 'getData'])->name('value.getByClass');
    Route::get('nilai/template', [NilaiController::class, 'downloadTemplate'])->name('value.download');
    Route::get('nilai/export', [NilaiController::class, 'exportPDF'])->name('value.exportPDF');
    Route::post('nilai/import', [NilaiController::class, 'import'])->name('value.import');
    Route::put('nilai/{id}', [NilaiController::class, 'update'])->name('value.update');
    Route::delete('nilai/{id}', [NilaiController::class, 'destroy'])->name('value.destroy');

    Route::get('mapel', [MapelController::class, 'index'])->name('mapel.index');
    Route::post('mapel', [MapelController::class, 'store'])->name('mapel.store');
    Route::get('mapel/all', [MapelController::class, 'getAll'])->name('mapel.getAll');
    Route::put('mapel/{id}', [MapelController::class, 'update'])->name('mapel.update');
    Route::delete('mapel/{id}', [MapelController::class, 'destroy'])->name('mapel.destroy');
});
