<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NilaiAuditDummySeeder extends Seeder
{
    /**
     * Seed sample audit trail logs for testing.
     */
    public function run(): void
    {
        $user = DB::table('users')->first();
        $student = DB::table('students')->first();
        $mapel = DB::table('mata_pelajarans')->first();
        $fst = DB::table('m_fst_pembelajaran')->first();

        if (!$student || !$mapel || !$fst) {
            return;
        }

        $logs = [
            [
                'user_id' => $user ? $user->id : 1,
                'student_id' => $student->id,
                'mapel_id' => $mapel->id,
                'fst_id' => $fst->id,
                'action' => 'INPUT_BARU',
                'old_values' => null,
                'new_values' => json_encode(['value_daily' => 85, 'value_sts' => 80, 'value_sas' => 88]),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => now()->subDays(3),
            ],
            [
                'user_id' => $user ? $user->id : 1,
                'student_id' => $student->id,
                'mapel_id' => $mapel->id,
                'fst_id' => $fst->id,
                'action' => 'UPDATE',
                'old_values' => json_encode(['value_daily' => 85, 'value_sts' => 80, 'value_sas' => 88]),
                'new_values' => json_encode(['value_daily' => 90, 'value_sts' => 85, 'value_sas' => 92]),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'created_at' => now()->subDays(1),
            ],
            [
                'user_id' => $user ? $user->id : 1,
                'student_id' => $student->id,
                'mapel_id' => $mapel->id,
                'fst_id' => $fst->id,
                'action' => 'SYNC_CBT',
                'old_values' => json_encode(['value_sts' => 85]),
                'new_values' => json_encode(['value_sts' => 87.5]),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'CBT-Integration-Client/1.0',
                'created_at' => now()->subHours(5),
            ],
        ];

        foreach ($logs as $log) {
            DB::table('nilai_audit_logs')->insert($log);
        }
    }
}
