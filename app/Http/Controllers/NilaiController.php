<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class NilaiController extends Controller
{
    public function index()
    {
        $query = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc');
        
        // Role Management: Wali Kelas / Guru hanya melihat kelasnya
        if (Auth::user()->role_id != 1 && Auth::user()->class_id !== null) {
            $query->where('id', Auth::user()->class_id);
        }
        $classList = $query->get();

        $mapelList = DB::table('mata_pelajarans')->select('id', 'nama_mapel')->orderBy('id', 'asc')->get();
        $fstList = DB::table('m_fst_pembelajaran')->select('id', 'fase', 'semester', 'tahun_ajaran','ta')->orderBy('id', 'asc')->get();
        $className = null;
        if (Auth::user()->class_id !== null) {
            $className = DB::table('class')->where('id', Auth::user()->class_id)->value('class_name');
        }
        return view('nilai.index', compact('classList', 'mapelList', 'fstList','className'));
    }
    public function getData(Request $request)
    {
        $id = $request->class_id;
        $mp_id = $request->mapel_id;
        $fst_id = $request->fst_id;
        $data = DB::table('students as s')->select(
            's.id as student_id',
            's.nama as student_name',
            'c.class_name',
            'v.id as value_id',
            'mp.nama_mapel',
            'v.mapel_id as mapel_id',
            'v.value_daily',
            'v.value_daily_2',
            'v.value_daily_3',
            'v.value_daily_4',
            'v.value_daily_5',
            'v.value_daily_6',
            'v.value_daily_7',
            'v.value_daily_8',
            'v.value_daily_9',
            'v.value_daily_10',
            'v.value_sts',
            'v.value_sas',
            DB::raw('ROUND(
                (
                    COALESCE(
                        (COALESCE(v.value_daily, 0) + COALESCE(v.value_daily_2, 0) + COALESCE(v.value_daily_3, 0) + COALESCE(v.value_daily_4, 0) + COALESCE(v.value_daily_5, 0) + COALESCE(v.value_daily_6, 0) + COALESCE(v.value_daily_7, 0) + COALESCE(v.value_daily_8, 0) + COALESCE(v.value_daily_9, 0) + COALESCE(v.value_daily_10, 0))
                        / 
                        NULLIF(
                            IF(v.value_daily IS NOT NULL, 1, 0) + IF(v.value_daily_2 IS NOT NULL, 1, 0) + IF(v.value_daily_3 IS NOT NULL, 1, 0) + IF(v.value_daily_4 IS NOT NULL, 1, 0) + IF(v.value_daily_5 IS NOT NULL, 1, 0) + IF(v.value_daily_6 IS NOT NULL, 1, 0) + IF(v.value_daily_7 IS NOT NULL, 1, 0) + IF(v.value_daily_8 IS NOT NULL, 1, 0) + IF(v.value_daily_9 IS NOT NULL, 1, 0) + IF(v.value_daily_10 IS NOT NULL, 1, 0),
                            0
                        ), 0
                    )
                    +
                    COALESCE(
                        (COALESCE(v.value_sts, 0) + COALESCE(v.value_sas, 0))
                        /
                        NULLIF(IF(v.value_sts IS NOT NULL, 1, 0) + IF(v.value_sas IS NOT NULL, 1, 0), 0), 0
                    )
                )
                /
                NULLIF(
                    IF(COALESCE(v.value_daily, v.value_daily_2, v.value_daily_3, v.value_daily_4, v.value_daily_5, v.value_daily_6, v.value_daily_7, v.value_daily_8, v.value_daily_9, v.value_daily_10) IS NOT NULL, 1, 0)
                    +
                    IF(COALESCE(v.value_sts, v.value_sas) IS NOT NULL, 1, 0), 
                    0
                )
            , 2) as average_value')
        )
            ->Join('class as c', 's.class_id', '=', 'c.id')
            ->leftJoin('values as v', function ($join) use ($mp_id,$fst_id) {
                $join->on('s.id', '=', 'v.student_id')->where('v.mapel_id', '=', $mp_id)->where('v.fst_id', '=', $fst_id);
            })->leftJoin('mata_pelajarans as mp', 'v.mapel_id', '=', 'mp.id')
            ->where('s.class_id', '=', $id)
            ->orderBy('s.nama', 'asc')
            ->get();
        return response()->json(['data' => $data], 200);
    }

    public function getMapel(Request $request)
    {
        $class_id = $request->class_id;
        $fst_id = $request->fst_id;

        $mapels = DB::table('mapel_class_fst')
            ->join('mata_pelajarans', 'mapel_class_fst.mapel_id', '=', 'mata_pelajarans.id')
            ->where('mapel_class_fst.class_id', $class_id)
            ->where('mapel_class_fst.fst_id', $fst_id)
            ->where('mapel_class_fst.is_active', 1)
            ->select('mata_pelajarans.id', 'mata_pelajarans.nama_mapel')
            ->orderBy('mata_pelajarans.nama_mapel', 'asc')
            ->get();

        return response()->json($mapels);
    }
    public function store(Request $request)
    {

        $request->validate([
            'student_id' => 'required|integer',
            'value_daily' => 'nullable|max:100',
            'value_daily_2' => 'nullable|max:100',
            'value_sts' => 'nullable|max:100',
            'value_sas' => 'nullable|max:100',
        ]);

        if ($this->isSemesterLocked($request->fst_id)) {
            return response()->json(['message' => 'Semester ini telah dikunci oleh Kurikulum. Nilai tidak dapat diubah.'], 403);
        }

        DB::beginTransaction();
        try {
            DB::table('values')->insert([
                'class_id'=> $request->class_id,
                'mapel_id' => $request->mapel_id,
                'fst_id' => $request->fst_id,
                'student_id' => $request->student_id,
                'value_daily' => $request->value_daily ,
                'value_daily_2' => $request->value_daily_2 ,
                'value_daily_3' => $request->value_daily_3 ,
                'value_daily_4' => $request->value_daily_4 ,
                'value_daily_5' => $request->value_daily_5 ,
                'value_daily_6' => $request->value_daily_6 ,
                'value_daily_7' => $request->value_daily_7 ,
                'value_daily_8' => $request->value_daily_8 ,
                'value_daily_9' => $request->value_daily_9 ,
                'value_daily_10' => $request->value_daily_10 ,
                'value_sts' => $request->value_sts ,
                'value_sas' => $request->value_sas ,
                'created_at' => Carbon::now()
            ]);

            \App\Services\NilaiAuditService::log(
                $request->student_id,
                $request->mapel_id,
                $request->fst_id,
                'INPUT_BARU',
                null,
                [
                    'value_daily' => $request->value_daily,
                    'value_daily_2' => $request->value_daily_2,
                    'value_sts' => $request->value_sts,
                    'value_sas' => $request->value_sas,
                ]
            );

            DB::commit();
            return response()->json(['message' => 'Nilai berhasil ditambahkan!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {

        $request->validate([
            'student_id' => 'required|integer',
            'value_daily' => 'nullable',
            'value_daily_2' => 'nullable',
            'value_sts' => 'nullable|max:100',
            'value_sas' => 'nullable|max:100',
        ]);

        if ($this->isSemesterLocked($request->fst_id)) {
            return response()->json(['message' => 'Semester ini telah dikunci oleh Kurikulum. Nilai tidak dapat diubah.'], 403);
        }

        DB::beginTransaction();
        try {
            $existing = DB::table('values')->where('id', '=', $id)->first();
            $oldVals = $existing ? [
                'value_daily' => $existing->value_daily,
                'value_daily_2' => $existing->value_daily_2,
                'value_sts' => $existing->value_sts,
                'value_sas' => $existing->value_sas,
            ] : null;

            DB::table('values')->where('id', '=', $id)->where('student_id', '=', $request->student_id)->where('mapel_id', '=', $request->mapel_id)->where('fst_id', $request->fst_id)->where('mapel_id', '=', $request->mapel_id)->where('class_id', $request->class_id)->update([
                'value_daily' => $request->value_daily,
                'value_daily_2' => $request->value_daily_2,
                'value_daily_3' => $request->value_daily_3,
                'value_daily_4' => $request->value_daily_4,
                'value_daily_5' => $request->value_daily_5,
                'value_daily_6' => $request->value_daily_6,
                'value_daily_7' => $request->value_daily_7,
                'value_daily_8' => $request->value_daily_8,
                'value_daily_9' => $request->value_daily_9,
                'value_daily_10' => $request->value_daily_10,
                'value_sts' => $request->value_sts,
                'value_sas' => $request->value_sas,
                'updated_at' => Carbon::now()
            ]);

            \App\Services\NilaiAuditService::log(
                $request->student_id,
                $request->mapel_id,
                $request->fst_id,
                'UPDATE',
                $oldVals,
                [
                    'value_daily' => $request->value_daily,
                    'value_daily_2' => $request->value_daily_2,
                    'value_sts' => $request->value_sts,
                    'value_sas' => $request->value_sas,
                ]
            );

            DB::commit();
            return response()->json(['message' => 'Nilai berhasil diEdit!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $val = DB::table('values')->where('id', '=', $id)->first();
            if ($val) {
                \App\Services\NilaiAuditService::log(
                    $val->student_id,
                    $val->mapel_id,
                    $val->fst_id,
                    'DELETE',
                    (array)$val,
                    null
                );
            }
            DB::table('values')->where('id', '=', $id)->delete();
            DB::commit();
            return response()->json(['message' => 'Nilai berhasil diHapus!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function downloadTemplate()
    {
        return response()->download(public_path('down/Template_InputNilaiSiswa.xlsx'));
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv' => 'required|mimes:csv,txt|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        if ($this->isSemesterLocked($request->fst_id)) {
            return response()->json(['message' => 'Semester ini telah dikunci oleh Kurikulum. Nilai tidak dapat diubah.'], 403);
        }

        $file = $request->file('csv');
        $csvData = array_map('str_getcsv', file($file));

        if (count($csvData) <= 1) {
            return response()->json(['message' => 'File CSV kosong atau tidak valid.'], 400);
        }

        $header = array_shift($csvData); // Ambil header
        try {
            DB::beginTransaction();
            foreach ($csvData as $row) {
                if (count($row) >= 2) {
                    $nis = $row[0];
                    $nama = $row[1];
                    $className = $row[2] ?? null;
                    $value_daily = $row[3] ?? null;
                    $value_daily_2 = $row[4] ?? null;
                    $value_daily_3 = $row[5] ?? null;
                    $value_daily_4 = $row[6] ?? null;
                    $value_daily_5 = $row[7] ?? null;
                    $value_daily_6 = $row[8] ?? null;
                    $value_daily_7 = $row[9] ?? null;
                    $value_daily_8 = $row[10] ?? null;
                    $value_daily_9 = $row[11] ?? null;
                    $value_daily_10 = $row[12] ?? null;
                    $value_sts = $row[13] ?? null;
                    $value_sas = $row[14] ?? null;
                    $studentId = DB::table('students')->where('nis', '=', $nis)->where('nama', 'like', "%{$nama}%")->value('id');
                    DB::table('values')->updateOrInsert(
                        ['student_id' => $studentId],
                        [
                            'class_id'=> $request->class_id,
                            'mapel_id' => $request->mapel_id,
                            'fst_id' => $request->fst_id,
                            'value_daily' => $value_daily,
                            'value_daily_2' => $value_daily_2,
                            'value_daily_3' => $value_daily_3,
                            'value_daily_4' => $value_daily_4,
                            'value_daily_5' => $value_daily_5,
                            'value_daily_6' => $value_daily_6,
                            'value_daily_7' => $value_daily_7,
                            'value_daily_8' => $value_daily_8,
                            'value_daily_9' => $value_daily_9,
                            'value_daily_10' => $value_daily_10,
                            'value_sts' => $value_sts,
                            'value_sas' => $value_sas,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]
                    );

                    if ($studentId) {
                        \App\Services\NilaiAuditService::log(
                            $studentId,
                            $request->mapel_id,
                            $request->fst_id,
                            'IMPORT_CSV',
                            null,
                            ['value_daily' => $value_daily, 'value_sts' => $value_sts, 'value_sas' => $value_sas]
                        );
                    }
                }
            }
            DB::commit();
            return response()->json(['message' => 'Nilai siswa berhasil diimport!'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Test connection to CBT server.
     */
    public function testCbtConnection()
    {
        $cbtUrl = config('services.cbt.url', env('CBT_API_URL', 'http://localhost:8001/api/v1'));
        $cbtKey = config('services.cbt.key', env('CBT_API_KEY'));

        $cleanUrl = rtrim($cbtUrl, '/');
        $startTime = microtime(true);

        try {
            // Attempt to hit health/ping or the root API endpoint with a 4-second timeout
            $testEndpoints = [$cleanUrl . '/ping', $cleanUrl . '/health', $cleanUrl . '/classes', $cleanUrl];
            $connected = false;
            $statusCode = 0;
            $lastError = null;

            foreach ($testEndpoints as $endpoint) {
                try {
                    $response = Http::withToken($cbtKey)->timeout(4)->get($endpoint);
                    if ($response->status() < 500) {
                        $connected = true;
                        $statusCode = $response->status();
                        break;
                    }
                } catch (\Exception $ex) {
                    $lastError = $ex->getMessage();
                }
            }

            $latencyMs = round((microtime(true) - $startTime) * 1000, 2);

            if ($connected) {
                return response()->json([
                    'status' => 'online',
                    'message' => 'Terhubung ke server CBT (' . $latencyMs . ' ms)',
                    'cbt_url' => $cleanUrl,
                    'latency_ms' => $latencyMs,
                    'http_status' => $statusCode,
                ]);
            }

            return response()->json([
                'status' => 'offline',
                'message' => 'Tidak dapat terhubung ke server CBT di ' . $cleanUrl . '. ' . ($lastError ? 'Error: ' . $lastError : ''),
                'cbt_url' => $cleanUrl,
            ], 503);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'offline',
                'message' => 'Gagal menguji koneksi: ' . $e->getMessage(),
                'cbt_url' => $cleanUrl,
            ], 500);
        }
    }

    /**
     * Pull scores from CBT and save into values table.
     */
    public function syncFromCbt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id'     => 'required|integer',
            'mapel_id'     => 'required|integer',
            'fst_id'       => 'required|integer',
            'target_field' => 'nullable|string',
            'category'     => 'nullable|string',
            'overwrite'    => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Parameter tidak valid',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $classId   = (int) $request->input('class_id');
        $mapelId   = (int) $request->input('mapel_id');
        $fstId     = (int) $request->input('fst_id');
        $overwrite = $request->boolean('overwrite', true);

        if ($this->isSemesterLocked($fstId)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Semester ini telah dikunci oleh Kurikulum. Nilai tidak dapat diubah.',
            ], 403);
        }

        // Determine target field
        $targetField = $request->input('target_field');
        $categoryInput = strtoupper($request->input('category', 'STS'));

        if (empty($targetField)) {
            if ($categoryInput === 'STS') {
                $targetField = 'value_sts';
            } elseif ($categoryInput === 'SAS') {
                $targetField = 'value_sas';
            } elseif (in_array($categoryInput, ['HARIAN', 'UH', 'FORMATIF'])) {
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
                'message' => "Kolom target tidak valid: {$targetField}",
            ], 422);
        }

        $cbtUrl = config('services.cbt.url', env('CBT_API_URL', 'http://localhost:8001/api/v1'));
        $cbtKey = config('services.cbt.key', env('CBT_API_KEY'));
        $cleanUrl = rtrim($cbtUrl, '/');

        $cbtCategory = ($targetField === 'value_sas') ? 'SAS' : (($targetField === 'value_sts') ? 'STS' : 'UH');

        try {
            // Request to CBT API
            $endpoint = $cleanUrl . '/scores/export';
            $response = Http::withToken($cbtKey)->timeout(15)->get($endpoint, [
                'class_id'   => $classId,
                'subject_id' => $mapelId,
                'category'   => $cbtCategory,
                'fst_id'     => $fstId,
            ]);

            if ($response->failed()) {
                // If specific export route fails, try scores list endpoint as fallback
                $fallbackEndpoint = $cleanUrl . '/scores';
                $response = Http::withToken($cbtKey)->timeout(15)->get($fallbackEndpoint, [
                    'class_id'   => $classId,
                    'subject_id' => $mapelId,
                    'category'   => $cbtCategory,
                    'fst_id'     => $fstId,
                ]);

                if ($response->failed()) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Gagal terhubung atau mengambil data dari CBT (' . $response->status() . '): ' . $response->body(),
                    ], 400);
                }
            }

            $responseData = $response->json();
            $cbtScores = $responseData['data'] ?? ($responseData['scores'] ?? []);

            if (empty($cbtScores)) {
                return response()->json([
                    'status'  => 'warning',
                    'message' => 'Tidak ada data nilai ujian yang ditemukan di CBT untuk filter kelas dan mata pelajaran ini.',
                ], 404);
            }

            $students = DB::table('students')
                ->where('class_id', $classId)
                ->select('id', 'nis', 'nisn', 'nama')
                ->get();

            if ($students->isEmpty()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Tidak ada data siswa yang terdaftar di kelas ini pada SiNilai.",
                ], 404);
            }

            $updatedCount  = 0;
            $skippedCount  = 0;
            $unmatched     = [];
            $syncedDetails = [];

            DB::beginTransaction();

            foreach ($cbtScores as $item) {
                $itemNisn = !empty($item['nisn']) ? trim((string) $item['nisn']) : null;
                $itemNis  = !empty($item['nis']) ? trim((string) $item['nis']) : null;
                $itemId   = !empty($item['student_id']) ? (int) $item['student_id'] : null;
                $score    = isset($item['score']) && $item['score'] !== '' ? (float) $item['score'] : (isset($item['final_score']) ? (float)$item['final_score'] : null);

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
                        'name' => $item['student_name'] ?? ($item['nama'] ?? ($itemNis ?? ($itemNisn ?? 'Siswa'))),
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

                    \App\Services\NilaiAuditService::log(
                        $student->id,
                        $mapelId,
                        $fstId,
                        'SYNC_CBT',
                        [$targetField => $existing->{$targetField}],
                        [$targetField => $score]
                    );
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

                    \App\Services\NilaiAuditService::log(
                        $student->id,
                        $mapelId,
                        $fstId,
                        'SYNC_CBT',
                        null,
                        [$targetField => $score]
                    );
                }

                $updatedCount++;
                $syncedDetails[] = [
                    'student_id'   => $student->id,
                    'student_name' => $student->nama,
                    'score'        => $score,
                ];
            }

            // Save log to cbt_sync_logs
            if (Schema::hasTable('cbt_sync_logs')) {
                DB::table('cbt_sync_logs')->insert([
                    'sync_type'    => 'pull',
                    'user_id'      => Auth::id(),
                    'class_id'     => $classId,
                    'mapel_id'     => $mapelId,
                    'fst_id'       => $fstId,
                    'target_field' => $targetField,
                    'total_synced' => $updatedCount,
                    'total_failed' => count($unmatched),
                    'status'       => count($unmatched) > 0 ? (count($syncedDetails) > 0 ? 'partial' : 'failed') : 'success',
                    'message'      => "Berhasil menarik {$updatedCount} nilai {$cbtCategory} dari server CBT ke kolom {$targetField}.",
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

            return response()->json([
                'status'          => 'success',
                'message'         => "Berhasil menarik {$updatedCount} nilai ({$cbtCategory}) dari CBT ke kolom {$targetField}!",
                'target_field'    => $targetField,
                'total_synced'    => $updatedCount,
                'total_skipped'   => $skippedCount,
                'total_unmatched' => count($unmatched),
                'unmatched'       => $unmatched,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem saat menarik nilai CBT: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent CBT sync logs for the current filter.
     */
    public function getCbtSyncLogs(Request $request)
    {
        if (!Schema::hasTable('cbt_sync_logs')) {
            return response()->json(['data' => []]);
        }

        $query = DB::table('cbt_sync_logs as l')
            ->leftJoin('class as c', 'l.class_id', '=', 'c.id')
            ->leftJoin('mata_pelajarans as m', 'l.mapel_id', '=', 'm.id')
            ->leftJoin('users as u', 'l.user_id', '=', 'u.id')
            ->select(
                'l.id',
                'l.sync_type',
                'l.target_field',
                'l.total_synced',
                'l.total_failed',
                'l.status',
                'l.message',
                'l.details_json',
                'l.created_at',
                'c.class_name',
                'm.nama_mapel',
                'u.name as user_name'
            );

        if ($request->filled('class_id')) {
            $query->where('l.class_id', $request->class_id);
        }
        if ($request->filled('mapel_id')) {
            $query->where('l.mapel_id', $request->mapel_id);
        }
        if ($request->filled('fst_id')) {
            $query->where('l.fst_id', $request->fst_id);
        }

        $logs = $query->orderBy('l.created_at', 'desc')->limit(15)->get();

        return response()->json(['data' => $logs]);
    }

    /**
     * Check if a semester is locked.
     */
    protected function isSemesterLocked($fstId)
    {
        if (!$fstId) {
            return false;
        }
        $fst = DB::table('m_fst_pembelajaran')->where('id', $fstId)->first();
        return $fst && (bool) $fst->is_locked;
    }
}
