<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PortalSiswaController;

/**
 * Portal Mandiri Siswa & Orang Tua
 * Middleware: auth
 */
Route::middleware('auth')->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [PortalSiswaController::class, 'dashboard'])->name('dashboard');
    Route::get('/nilai', [PortalSiswaController::class, 'nilai'])->name('nilai');
    Route::get('/nilai/raport-download', [PortalSiswaController::class, 'downloadRaport'])->name('raport.download');
    Route::get('/p5', [PortalSiswaController::class, 'p5'])->name('p5');
    Route::get('/password', [PortalSiswaController::class, 'password'])->name('password');
    Route::put('/password', [PortalSiswaController::class, 'updatePassword'])->name('password.update');
    Route::post('/generate-accounts', [PortalSiswaController::class, 'generateStudentAccounts'])->name('generate_accounts');
});
