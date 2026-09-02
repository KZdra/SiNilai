<?php

use App\Http\Controllers\Api\CbtSyncController;
use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:cbt-sync', 'cbt.auth'])->prefix('cbt')->group(function () {
    // Classes & Students
    Route::get('/classes', [CbtSyncController::class, 'classes']);
    Route::get('/students', [CbtSyncController::class, 'students']);

    // FST / Tahun Ajaran & Semester
    Route::get('/fst', [CbtSyncController::class, 'fst']);
    Route::get('/academic-years', [CbtSyncController::class, 'fst']);

    // Mata Pelajaran
    Route::get('/mapel', [CbtSyncController::class, 'mapel']);
    Route::get('/subjects', [CbtSyncController::class, 'mapel']);
});
