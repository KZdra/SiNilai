<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class RaportStatusController extends Controller
{
    /**
     * Dapatkan status alur persetujuan rapor untuk kelas & FST tertentu
     */
    public function getStatus(Request $request)
    {
        $classId = (int) $request->input('class_id');
        $fstId   = (int) $request->input('fst_id');

        if (!$classId || !$fstId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Parameter class_id dan fst_id wajib disertakan.',
            ], 422);
        }

        $record = DB::table('raport_statuses as r')
            ->leftJoin('users as s', 'r.submitted_by', '=', 's.id')
            ->leftJoin('users as v', 'r.verified_by', '=', 'v.id')
            ->leftJoin('users as a', 'r.approved_by', '=', 'a.id')
            ->where('r.class_id', $classId)
            ->where('r.fst_id', $fstId)
            ->select([
                'r.id',
                'r.class_id',
                'r.fst_id',
                'r.status',
                'r.notes',
                'r.submitted_at',
                's.name as submitter_name',
                'r.verified_at',
                'v.name as verifier_name',
                'r.approved_at',
                'a.name as approver_name',
            ])
            ->first();

        if (!$record) {
            return response()->json([
                'status' => 'success',
                'data'   => [
                    'status'         => 'draft',
                    'status_label'   => 'Draft (Masih Input)',
                    'badge_class'    => 'badge-secondary',
                    'is_locked'      => false,
                    'can_edit_nilai' => true,
                    'submitter_name' => null,
                    'verifier_name'  => null,
                    'approver_name'  => null,
                ],
            ]);
        }

        $labels = [
            'draft'           => ['label' => 'Draft (Masih Input)', 'badge' => 'badge-secondary', 'locked' => false],
            'submitted'       => ['label' => 'Diajukan (Menunggu Verifikasi)', 'badge' => 'badge-info', 'locked' => false],
            'verified'        => ['label' => 'Diverifikasi Wali Kelas', 'badge' => 'badge-warning text-dark', 'locked' => true],
            'approved_locked' => ['label' => 'Disahkan & Dikunci Kepala Sekolah', 'badge' => 'badge-success', 'locked' => true],
        ];

        $meta = $labels[$record->status] ?? $labels['draft'];

        $userRole = Auth::user() ? Auth::user()->role_id : null;
        $canEdit = ($userRole == 1) ? true : !$meta['locked'];

        return response()->json([
            'status' => 'success',
            'data'   => array_merge((array) $record, [
                'status_label'   => $meta['label'],
                'badge_class'    => $meta['badge'],
                'is_locked'      => $meta['locked'],
                'can_edit_nilai' => $canEdit,
            ]),
        ]);
    }

    /**
     * Guru Mapel / Wali Kelas: Ajukan Nilai (draft -> submitted)
     */
    public function submitRaport(Request $request)
    {
        $classId = (int) $request->input('class_id');
        $fstId   = (int) $request->input('fst_id');
        $notes   = $request->input('notes');

        $user = Auth::user();
        if ($user->role_id != 1 && $user->class_id != $classId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak untuk mengajukan rekap nilai kelas ini.',
            ], 403);
        }

        DB::table('raport_statuses')->updateOrInsert(
            ['class_id' => $classId, 'fst_id' => $fstId],
            [
                'status'       => 'submitted',
                'submitted_by' => Auth::id(),
                'submitted_at' => now(),
                'notes'        => $notes,
                'updated_at'   => now(),
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Rekap nilai rapor kelas berhasil diajukan untuk verifikasi wali kelas.',
        ]);
    }

    /**
     * Wali Kelas / Kurikulum: Verifikasi Nilai (submitted -> verified)
     */
    public function verifyRaport(Request $request)
    {
        $classId = (int) $request->input('class_id');
        $fstId   = (int) $request->input('fst_id');
        $notes   = $request->input('notes');

        $user = Auth::user();
        if ($user->role_id != 1 && $user->class_id != $classId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Anda tidak memiliki hak untuk memverifikasi nilai kelas ini.',
            ], 403);
        }

        DB::table('raport_statuses')->updateOrInsert(
            ['class_id' => $classId, 'fst_id' => $fstId],
            [
                'status'      => 'verified',
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'notes'       => $notes,
                'updated_at'  => now(),
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Rapor kelas telah diverifikasi oleh Wali Kelas.',
        ]);
    }

    /**
     * Kepala Sekolah / Admin: Sahkan & Kunci Rapor (verified -> approved_locked)
     */
    public function approveAndLock(Request $request)
    {
        $classId = (int) $request->input('class_id');
        $fstId   = (int) $request->input('fst_id');
        $notes   = $request->input('notes');

        $user = Auth::user();
        if ($user->role_id != 1) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Hanya Kepala Sekolah / Administrator yang dapat mengesahkan & mengunci rapor.',
            ], 403);
        }

        DB::table('raport_statuses')->updateOrInsert(
            ['class_id' => $classId, 'fst_id' => $fstId],
            [
                'status'      => 'approved_locked',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'notes'       => $notes,
                'updated_at'  => now(),
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Rapor kelas telah disahkan dan dikunci secara permanen!',
        ]);
    }

    /**
     * Administrator: Buka Kunci Rapor (Unlock) jika diperlukan revisi darurat
     */
    public function unlockRaport(Request $request)
    {
        $classId = (int) $request->input('class_id');
        $fstId   = (int) $request->input('fst_id');

        $user = Auth::user();
        if ($user->role_id != 1) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Hanya Administrator yang memiliki wewenang membuka kuncian rapor.',
            ], 403);
        }

        DB::table('raport_statuses')->where('class_id', $classId)->where('fst_id', $fstId)->update([
            'status'     => 'draft',
            'notes'      => 'Kuncian dibuka kembali oleh Administrator (' . now()->format('d/m/Y H:i') . ')',
            'updated_at' => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Kuncian rapor berhasil dibuka kembali ke status Draft.',
        ]);
    }
}
