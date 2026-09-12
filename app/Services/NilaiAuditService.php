<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class NilaiAuditService
{
    /**
     * Catat riwayat audit perubahan nilai ke database.
     *
     * @param int $studentId
     * @param int $mapelId
     * @param int $fstId
     * @param string $action ('INPUT_BARU', 'UPDATE', 'DELETE', 'IMPORT_CSV', 'SYNC_CBT')
     * @param array|null $oldValues
     * @param array|null $newValues
     */
    public static function log($studentId, $mapelId, $fstId, $action, $oldValues = null, $newValues = null)
    {
        try {
            DB::table('nilai_audit_logs')->insert([
                'user_id' => Auth::check() ? Auth::id() : null,
                'student_id' => $studentId,
                'mapel_id' => $mapelId,
                'fst_id' => $fstId,
                'action' => $action,
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Gagal mencatat audit log nilai: ' . $e->getMessage());
        }
    }
}
