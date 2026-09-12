<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CbtSyncController extends Controller
{
    /**
     * Return list of all classes with delta sync support.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function classes(Request $request): JsonResponse
    {
        $query = DB::table('class')
            ->select([
                'id',
                'class_name',
                DB::raw('COALESCE(updated_at, created_at) as updated_at'),
            ]);

        if ($request->filled('since')) {
            try {
                $since = Carbon::parse($request->query('since'))->toDateTimeString();
            } catch (\Exception $e) {
                $since = $request->query('since');
            }

            $query->where(function ($q) use ($since) {
                $q->where('updated_at', '>=', $since)
                    ->orWhere(function ($q2) use ($since) {
                        $q2->whereNull('updated_at')
                            ->where('created_at', '>=', $since);
                    });
            });
        }

        $classes = $query->orderBy('id', 'asc')->get();

        Log::channel('cbt_sync')->info('CBT Sync: Classes retrieved', [
            'count' => $classes->count(),
            'since' => $request->query('since'),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $classes,
        ]);
    }

    /**
     * Return list of students joined with class table, with optional ?class_id=X and ?since=YYYY-MM-DD filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function students(Request $request): JsonResponse
    {
        $genderField = Schema::hasColumn('students', 'gender') ? 's.gender' : 's.jenis_kelamin as gender';

        $query = DB::table('students as s')
            ->join('class as c', 's.class_id', '=', 'c.id')
            ->select(
                's.nis',
                's.nisn',
                's.nama',
                $genderField,
                's.class_id',
                'c.class_name',
                DB::raw('COALESCE(s.updated_at, s.created_at) as updated_at')
            );

        if ($request->filled('class_id')) {
            $query->where('s.class_id', $request->query('class_id'));
        }

        if ($request->filled('since')) {
            try {
                $since = Carbon::parse($request->query('since'))->toDateTimeString();
            } catch (\Exception $e) {
                $since = $request->query('since');
            }

            $query->where(function ($q) use ($since) {
                $q->where('s.updated_at', '>=', $since)
                    ->orWhere(function ($q2) use ($since) {
                        $q2->whereNull('s.updated_at')
                            ->where('s.created_at', '>=', $since);
                    });
            });
        }

        $students = $query->orderBy('s.class_id', 'asc')
            ->orderBy('s.nama', 'asc')
            ->get();

        Log::channel('cbt_sync')->info('CBT Sync: Students retrieved', [
            'count' => $students->count(),
            'class_id' => $request->query('class_id'),
            'since' => $request->query('since'),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $students,
        ]);
    }

    /**
     * Return list of all FST (Fase / Semester / Tahun Ajaran) for CBT Academic Years sync.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function fst(Request $request): JsonResponse
    {
        $query = DB::table('m_fst_pembelajaran')
            ->select([
                'id',
                'fase',
                'semester',
                'tahun_ajaran',
                'ta',
                DB::raw('COALESCE(updated_at, created_at) as updated_at'),
            ]);

        if ($request->filled('since')) {
            try {
                $since = Carbon::parse($request->query('since'))->toDateTimeString();
            } catch (\Exception $e) {
                $since = $request->query('since');
            }

            $query->where(function ($q) use ($since) {
                $q->where('updated_at', '>=', $since)
                    ->orWhere(function ($q2) use ($since) {
                        $q2->whereNull('updated_at')
                            ->where('created_at', '>=', $since);
                    });
            });
        }

        $fsts = $query->orderBy('id', 'asc')->get();

        Log::channel('cbt_sync')->info('CBT Sync: FST retrieved', [
            'count' => $fsts->count(),
            'since' => $request->query('since'),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $fsts,
        ]);
    }

    /**
     * Return list of Mata Pelajaran for CBT Subjects sync with optional class_id, fst_id, and since filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function mapel(Request $request): JsonResponse
    {
        $query = DB::table('mata_pelajarans')
            ->select([
                'id',
                'nama_mapel',
                DB::raw('COALESCE(updated_at, created_at) as updated_at'),
            ]);

        if ($request->filled('class_id') || $request->filled('fst_id')) {
            $query->whereIn('id', function ($sub) use ($request) {
                $sub->select('mapel_id')->from('mapel_class_fst');
                if ($request->filled('class_id')) {
                    $sub->where('class_id', $request->query('class_id'));
                }
                if ($request->filled('fst_id')) {
                    $sub->where('fst_id', $request->query('fst_id'));
                }
                $sub->where('is_active', 1);
            });
        }

        if ($request->filled('since')) {
            try {
                $since = Carbon::parse($request->query('since'))->toDateTimeString();
            } catch (\Exception $e) {
                $since = $request->query('since');
            }

            $query->where(function ($q) use ($since) {
                $q->where('updated_at', '>=', $since)
                    ->orWhere(function ($q2) use ($since) {
                        $q2->whereNull('updated_at')
                            ->where('created_at', '>=', $since);
                    });
            });
        }

        $mapels = $query->orderBy('id', 'asc')->get();

        Log::channel('cbt_sync')->info('CBT Sync: Mapel retrieved', [
            'count' => $mapels->count(),
            'class_id' => $request->query('class_id'),
            'fst_id' => $request->query('fst_id'),
            'since' => $request->query('since'),
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $mapels,
        ]);
    }

    /**
     * Ingest / Push scores from CBT application into SiNilai values table.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeScores(Request $request): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'class_id'       => 'required|integer',
            'mapel_id'       => 'required|integer',
            'fst_id'         => 'required|integer',
            'category'       => 'nullable|string',
            'target_field'   => 'nullable|string',
            'scores'         => 'required|array|min:1',
            'scores.*.score' => 'nullable|numeric|min:0|max:100',
            'overwrite'      => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $classId   = (int) $request->input('class_id');
        $mapelId   = (int) $request->input('mapel_id');
        $fstId     = (int) $request->input('fst_id');
        $overwrite = $request->boolean('overwrite', true);

        // Determine target field
        $targetField = $request->input('target_field');
        if (empty($targetField)) {
            $category = strtoupper($request->input('category', 'STS'));
            if ($category === 'STS') {
                $targetField = 'value_sts';
            } elseif ($category === 'SAS') {
                $targetField = 'value_sas';
            } elseif (in_array($category, ['HARIAN', 'UH', 'FORMATIF'])) {
                $targetField = 'value_daily';
            } else {
                $targetField = 'value_sts';
            }
        }

        $allowedFields = [
            'value_sts', 'value_sas',
            'value_daily', 'value_daily_2', 'value_daily_3', 'value_daily_4', 'value_daily_5',
            'value_daily_6', 'value_daily_7', 'value_daily_8', 'value_daily_9', 'value_daily_10',
        ];

        if (!in_array($targetField, $allowedFields)) {
            return response()->json([
                'status'  => 'error',
                'message' => "Invalid target field: {$targetField}. Allowed: " . implode(', ', $allowedFields),
            ], 422);
        }

        $students = DB::table('students')
            ->where('class_id', $classId)
            ->select('id', 'nis', 'nisn', 'nama')
            ->get();

        if ($students->isEmpty()) {
            return response()->json([
                'status'  => 'error',
                'message' => "Tidak ada data siswa yang ditemukan untuk kelas dengan ID {$classId}.",
            ], 404);
        }

        $updatedCount  = 0;
        $skippedCount  = 0;
        $unmatched     = [];
        $syncedDetails = [];

        DB::beginTransaction();
        try {
            foreach ($request->input('scores') as $item) {
                $itemNisn = !empty($item['nisn']) ? trim((string) $item['nisn']) : null;
                $itemNis  = !empty($item['nis']) ? trim((string) $item['nis']) : null;
                $itemId   = !empty($item['student_id']) ? (int) $item['student_id'] : null;
                $score    = isset($item['score']) && $item['score'] !== '' ? (float) $item['score'] : null;

                $student = $students->first(function ($s) use ($itemNisn, $itemNis, $itemId) {
                    if ($itemId && (int) $s->id === $itemId) {
                        return true;
                    }
                    if ($itemNisn && !empty($s->nisn) && (string) $s->nisn === $itemNisn) {
                        return true;
                    }
                    if ($itemNis && !empty($s->nis) && (string) $s->nis === $itemNis) {
                        return true;
                    }
                    return false;
                });

                if (!$student) {
                    $unmatched[] = [
                        'name' => $item['student_name'] ?? ($itemNis ?? ($itemNisn ?? 'Unknown')),
                        'nis'  => $itemNis,
                        'nisn' => $itemNisn,
                    ];
                    continue;
                }

                $existing = DB::table('values')
                    ->where('student_id', $student->id)
                    ->where('mapel_id', $mapelId)
                    ->where('fst_id', $fstId)
                    ->first();

                if ($existing) {
                    if (!$overwrite && !is_null($existing->{$targetField})) {
                        $skippedCount++;
                        continue;
                    }

                    DB::table('values')
                        ->where('id', $existing->id)
                        ->update([
                            $targetField => $score,
                            'updated_at' => Carbon::now(),
                        ]);
                } else {
                    DB::table('values')->insert([
                        'student_id'   => $student->id,
                        'class_id'     => $classId,
                        'mapel_id'     => $mapelId,
                        'fst_id'       => $fstId,
                        $targetField   => $score,
                        'created_at'   => Carbon::now(),
                        'updated_at'   => Carbon::now(),
                    ]);
                }

                $updatedCount++;
                $syncedDetails[] = [
                    'student_id'   => $student->id,
                    'student_name' => $student->nama,
                    'score'        => $score,
                ];
            }

            if (Schema::hasTable('cbt_sync_logs')) {
                DB::table('cbt_sync_logs')->insert([
                    'sync_type'    => 'push',
                    'user_id'      => null,
                    'class_id'     => $classId,
                    'mapel_id'     => $mapelId,
                    'fst_id'       => $fstId,
                    'target_field' => $targetField,
                    'total_synced' => $updatedCount,
                    'total_failed' => count($unmatched),
                    'status'       => count($unmatched) > 0 ? (count($syncedDetails) > 0 ? 'partial' : 'failed') : 'success',
                    'message'      => "Berhasil memproses {$updatedCount} nilai dari CBT (Push).",
                    'details_json' => json_encode([
                        'synced'    => $syncedDetails,
                        'unmatched' => $unmatched,
                        'skipped'   => $skippedCount,
                    ]),
                    'created_at'   => Carbon::now(),
                    'updated_at'   => Carbon::now(),
                ]);
            }

            DB::commit();

            Log::channel('cbt_sync')->info('CBT Sync: Scores pushed and saved', [
                'class_id'        => $classId,
                'mapel_id'        => $mapelId,
                'fst_id'          => $fstId,
                'target_field'    => $targetField,
                'updated_count'   => $updatedCount,
                'skipped_count'   => $skippedCount,
                'unmatched_count' => count($unmatched),
            ]);

            return response()->json([
                'status'          => 'success',
                'message'         => "Berhasil menyinkronkan {$updatedCount} nilai ke kolom {$targetField}.",
                'target_field'    => $targetField,
                'total_synced'    => $updatedCount,
                'total_skipped'   => $skippedCount,
                'total_unmatched' => count($unmatched),
                'unmatched'       => $unmatched,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('cbt_sync')->error('CBT Sync Error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan nilai: ' . $e->getMessage(),
            ], 500);
        }
    }
}

