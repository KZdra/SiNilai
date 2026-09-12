<?php

use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────
// Publik & Auth
// ─────────────────────────────────────────────
Route::get('auth/redirect', [\App\Http\Controllers\SsoController::class, 'redirect'])->name('sso.login');
Route::get('auth/callback', [\App\Http\Controllers\SsoController::class, 'callback'])->name('sso.callback');
Route::post('/sso/slo', [\App\Http\Controllers\SsoController::class, 'slo']);
Route::get('verifikasi-raport/{token}', [\App\Http\Controllers\PublicVerificationController::class, 'verify'])->name('raport.verify');

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Endpoint debug/test (bisa dihapus di production)
Route::get('es', [\App\Http\Controllers\PeskulController::class, 'getdata']);

// ─────────────────────────────────────────────
// Module Routes (semua di-load di sini)
// Setiap file sudah membungkus routenya sendiri
// dengan middleware yang sesuai.
// ─────────────────────────────────────────────
require __DIR__ . '/modules/portal.php';
require __DIR__ . '/modules/akademik.php';
require __DIR__ . '/modules/master.php';
require __DIR__ . '/modules/admin.php';
