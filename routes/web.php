<?php

use App\Http\Controllers\ClassController;
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
    route::get('siswa/template',[SiswaController::class, 'downloadTemplate'])->name('student.download');
    Route::post('siswa/import', [SiswaController::class, 'import'])->name('student.import');
    Route::put('siswa/{id}', [SiswaController::class, 'update'])->name('student.update');
    Route::delete('siswa/{id}', [SiswaController::class, 'destroy'])->name('student.destroy');
});
