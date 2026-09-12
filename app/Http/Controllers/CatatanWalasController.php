<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CatatanWalasController extends Controller
{
    /**
     * Display the index page for Catatan & Presensi Wali Kelas.
     */
    public function index()
    {
        $query = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc');

        if (Auth::user()->role_id != 1 && Auth::user()->class_id !== null) {
            $query->where('id', Auth::user()->class_id);
        }
        $classList = $query->get();

        $fstList = DB::table('m_fst_pembelajaran')
            ->select('id', 'fase', 'semester', 'tahun_ajaran', 'ta', 'is_locked')
            ->orderBy('id', 'asc')
            ->get();

        $className = null;
        if (Auth::user()->class_id !== null) {
            $className = DB::table('class')->where('id', Auth::user()->class_id)->value('class_name');
        }

        return view('catatanwalas.index', compact('classList', 'fstList', 'className'));
    }

    /**
     * Get students attendance and notes for a specific class and semester.
     */
    public function getData(Request $request)
    {
        $request->validate([
            'class_id' => 'required|integer',
            'fst_id'   => 'required|integer',
        ]);

        $classId = (int) $request->class_id;
        $fstId   = (int) $request->fst_id;

        $fst = DB::table('m_fst_pembelajaran')->where('id', $fstId)->first();
        $isLocked = $fst ? (bool) $fst->is_locked : false;

        $students = DB::table('students as s')
            ->leftJoin('catatan_walikelas as cw', function ($join) use ($fstId) {
                $join->on('s.id', '=', 'cw.student_id')
                     ->where('cw.fst_id', '=', $fstId);
            })
            ->where('s.class_id', $classId)
            ->select(
                's.id as student_id',
                's.nama as student_name',
                's.nis',
                's.nisn',
                'cw.id as record_id',
                DB::raw('COALESCE(cw.sakit, s.sakit, 0) as sakit'),
                DB::raw('COALESCE(cw.izin, s.izin, 0) as izin'),
                DB::raw('COALESCE(cw.alpa, s.alpa, 0) as alpa'),
                'cw.catatan',
                'cw.status_kenaikan',
                'cw.verification_token'
            )
            ->orderBy('s.nama', 'asc')
            ->get();

        return response()->json([
            'status'    => 'success',
            'is_locked' => $isLocked,
            'data'      => $students,
        ]);
    }

    /**
     * Store or update single student's attendance and note.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id'      => 'required|integer',
            'class_id'        => 'required|integer',
            'fst_id'          => 'required|integer',
            'sakit'           => 'nullable|integer|min:0',
            'izin'            => 'nullable|integer|min:0',
            'alpa'            => 'nullable|integer|min:0',
            'catatan'         => 'nullable|string',
            'status_kenaikan' => 'nullable|string|max:100',
        ]);

        $fst = DB::table('m_fst_pembelajaran')->where('id', $request->fst_id)->first();
        if ($fst && $fst->is_locked) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Semester ini telah dikunci oleh kurikulum. Data tidak dapat diubah.',
            ], 403);
        }

        $existing = DB::table('catatan_walikelas')
            ->where('student_id', $request->student_id)
            ->where('fst_id', $request->fst_id)
            ->first();

        $token = $existing ? ($existing->verification_token ?: Str::random(32)) : Str::random(32);

        $sakit = (int) $request->input('sakit', 0);
        $izin  = (int) $request->input('izin', 0);
        $alpa  = (int) $request->input('alpa', 0);

        DB::beginTransaction();
        try {
            DB::table('catatan_walikelas')->updateOrInsert(
                [
                    'student_id' => $request->student_id,
                    'fst_id'     => $request->fst_id,
                ],
                [
                    'class_id'           => $request->class_id,
                    'sakit'              => $sakit,
                    'izin'               => $izin,
                    'alpa'               => $alpa,
                    'catatan'            => $request->catatan,
                    'status_kenaikan'    => $request->status_kenaikan,
                    'verification_token' => $token,
                    'updated_at'         => Carbon::now(),
                    'created_at'         => Carbon::now(),
                ]
            );

            // Synchronize to student table for legacy backward compatibility
            DB::table('students')->where('id', $request->student_id)->update([
                'sakit' => $sakit,
                'izin'  => $izin,
                'alpa'  => $alpa,
            ]);

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Catatan & presensi siswa berhasil disimpan!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk store or update attendance and notes for all students in class.
     */
    public function storeBulk(Request $request)
    {
        $request->validate([
            'class_id' => 'required|integer',
            'fst_id'   => 'required|integer',
            'items'    => 'required|array|min:1',
            'items.*.student_id'      => 'required|integer',
            'items.*.sakit'           => 'nullable|integer|min:0',
            'items.*.izin'            => 'nullable|integer|min:0',
            'items.*.alpa'            => 'nullable|integer|min:0',
            'items.*.catatan'         => 'nullable|string',
            'items.*.status_kenaikan' => 'nullable|string|max:100',
        ]);

        $fst = DB::table('m_fst_pembelajaran')->where('id', $request->fst_id)->first();
        if ($fst && $fst->is_locked) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Semester ini telah dikunci oleh kurikulum. Data tidak dapat diubah.',
            ], 403);
        }

        DB::beginTransaction();
        try {
            $savedCount = 0;
            foreach ($request->input('items') as $item) {
                $studentId = (int) $item['student_id'];
                $sakit     = isset($item['sakit']) ? (int) $item['sakit'] : 0;
                $izin      = isset($item['izin']) ? (int) $item['izin'] : 0;
                $alpa      = isset($item['alpa']) ? (int) $item['alpa'] : 0;
                $catatan   = $item['catatan'] ?? null;
                $statusKen = $item['status_kenaikan'] ?? null;

                $existing = DB::table('catatan_walikelas')
                    ->where('student_id', $studentId)
                    ->where('fst_id', $request->fst_id)
                    ->first();

                $token = $existing ? ($existing->verification_token ?: Str::random(32)) : Str::random(32);

                DB::table('catatan_walikelas')->updateOrInsert(
                    [
                        'student_id' => $studentId,
                        'fst_id'     => $request->fst_id,
                    ],
                    [
                        'class_id'           => $request->class_id,
                        'sakit'              => $sakit,
                        'izin'               => $izin,
                        'alpa'               => $alpa,
                        'catatan'            => $catatan,
                        'status_kenaikan'    => $statusKen,
                        'verification_token' => $token,
                        'updated_at'         => Carbon::now(),
                        'created_at'         => Carbon::now(),
                    ]
                );

                DB::table('students')->where('id', $studentId)->update([
                    'sakit' => $sakit,
                    'izin'  => $izin,
                    'alpa'  => $alpa,
                ]);

                $savedCount++;
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Berhasil menyimpan catatan dan presensi untuk {$savedCount} siswa!",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan data bulk: ' . $e->getMessage(),
            ], 500);
        }
    }
}
