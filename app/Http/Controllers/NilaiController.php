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
use App\Models\Setting;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Exports\MultiClassTemplateNilaiExport;
use App\Services\NilaiAuditService;

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

    public function downloadTemplate(Request $request)
    {
        $user = Auth::user();
        $classId = $request->input('class_id') ?: ($user->class_id ?? null);
        $mapelId = $request->input('mapel_id');
        $fstId   = $request->input('fst_id');

        $class = $classId ? DB::table('class')->where('id', $classId)->first() : null;
        $mapel = $mapelId ? DB::table('mata_pelajarans')->where('id', $mapelId)->first() : null;
        $fst   = $fstId   ? DB::table('m_fst_pembelajaran')->where('id', $fstId)->first() : null;

        // Jika kelas dipilih (atau user adalah wali kelas), unduh template HANYA 1 SHEET khusus kelas tersebut
        if ($class) {
            $classes = [$class];
            $cleanClass = str_replace(['/', '\\'], '_', $class->class_name);
            $cleanMapel = $mapel ? '_' . str_replace(['/', '\\'], '_', $mapel->nama_mapel) : '';
            $filename = "Template_Nilai_{$cleanClass}{$cleanMapel}.xlsx";
        } else {
            // Fallback untuk admin jika belum memilih kelas: semua sheet kelas
            $classes = DB::table('class')->orderBy('id', 'asc')->get();
            $filename = "Template_Nilai_Semua_Kelas.xlsx";
        }

        return Excel::download(new MultiClassTemplateNilaiExport($classes, $mapel, $fst), $filename);
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv'        => 'nullable|file|max:10240',
            'file'       => 'nullable|file|max:10240',
            'excel'      => 'nullable|file|max:10240',
            'excel_file' => 'nullable|file|max:10240',
            'mapel_id'   => 'nullable|integer',
            'fst_id'     => 'nullable|integer',
            'class_id'   => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $file = $request->file('csv') ?? $request->file('file') ?? $request->file('excel') ?? $request->file('excel_file');
        if (!$file) {
            return response()->json(['message' => 'Berkas Excel (.xlsx / .xls) tidak ditemukan.'], 400);
        }

        $user = Auth::user();
        $targetClassId = (int) ($request->input('class_id') ?: ($user->class_id ?? 0));
        $mapelId       = (int) ($request->input('mapel_id') ?? 0);
        $fstId         = (int) ($request->input('fst_id') ?? 0);

        if (!$targetClassId) {
            return response()->json(['message' => 'Silahkan pilih kelas terlebih dahulu sebelum mengimpor nilai.'], 422);
        }

        $targetClass = DB::table('class')->where('id', $targetClassId)->first();
        if (!$targetClass) {
            return response()->json(['message' => 'Kelas yang dipilih tidak valid.'], 404);
        }

        if ($fstId && $this->isSemesterLocked($fstId)) {
            return response()->json(['message' => 'Semester ini telah dikunci oleh Kurikulum. Nilai tidak dapat diubah.'], 403);
        }

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheets  = $spreadsheet->getAllSheets();

            // Tentukan sheet target:
            // 1. Cari sheet dengan nama yang sama dengan nama kelas target (raw maupun sanitized)
            $cleanTargetName = substr(str_replace(['\\', '/', '?', '*', ':', '[', ']'], '_', $targetClass->class_name), 0, 31);
            $targetSheet = null;

            foreach ($worksheets as $ws) {
                $wsTitle = trim($ws->getTitle());
                if (strcasecmp($wsTitle, trim($targetClass->class_name)) === 0 || strcasecmp($wsTitle, trim($cleanTargetName)) === 0) {
                    $targetSheet = $ws;
                    break;
                }
            }

            // 2. Jika file hanya punya 1 sheet (template khusus 1 kelas), langsung gunakan sheet tersebut
            if (!$targetSheet && count($worksheets) === 1) {
                $targetSheet = $worksheets[0];
            }

            // 3. Cari sheet yang judulnya mengandung nama kelas
            if (!$targetSheet) {
                foreach ($worksheets as $ws) {
                    $wsTitle = strtolower(trim($ws->getTitle()));
                    $clsName = strtolower(trim($targetClass->class_name));
                    if (str_contains($wsTitle, $clsName) || str_contains($clsName, $wsTitle)) {
                        $targetSheet = $ws;
                        break;
                    }
                }
            }

            // 4. Periksa header baris 2 (sel F2, B2, E2) jika ada teks nama kelas
            if (!$targetSheet) {
                foreach ($worksheets as $ws) {
                    $cellVal = (string) ($ws->getCell('F2')->getValue() ?? $ws->getCell('B2')->getValue() ?? $ws->getCell('E2')->getValue() ?? '');
                    if (str_contains(strtolower($cellVal), strtolower(trim($targetClass->class_name)))) {
                        $targetSheet = $ws;
                        break;
                    }
                }
            }

            // 5. Fallback ke sheet pertama yang BUKAN sheet TP jika tetap tidak teridentifikasi
            if (!$targetSheet) {
                foreach ($worksheets as $ws) {
                    $wsTitle = strtolower(trim($ws->getTitle()));
                    if (!str_contains($wsTitle, 'tp') && !str_contains($wsTitle, 'tujuan')) {
                        $targetSheet = $ws;
                        break;
                    }
                }
                if (!$targetSheet) {
                    $targetSheet = $worksheets[0];
                }
            }

            $sheet = $targetSheet;
            $totalImported = 0;
            $totalSkipped  = 0;

            // Deteksi mapping kolom dari baris 4
            $colMap = [];
            $highestCol = $sheet->getHighestColumn();
            $highestColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

            for ($c = 1; $c <= $highestColIdx; $c++) {
                $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                $head = strtolower(trim((string) $sheet->getCell("{$letter}4")->getValue()));
                if (str_contains($head, 'nisn')) {
                    $colMap['nisn'] = $letter;
                } elseif (str_contains($head, 'nis')) {
                    $colMap['nis'] = $letter;
                } elseif (str_contains($head, 'nama')) {
                    $colMap['nama'] = $letter;
                } elseif (str_contains($head, 'sts')) {
                    $colMap['sts'] = $letter;
                } elseif (str_contains($head, 'sas')) {
                    $colMap['sas'] = $letter;
                } elseif (preg_match('/(sumatif|harian)\s*10/i', $head)) {
                    $colMap['h10'] = $letter;
                } elseif (preg_match('/(sumatif|harian)\s*1/i', $head)) {
                    $colMap['h1'] = $letter;
                } elseif (preg_match('/(sumatif|harian)\s*2/i', $head)) {
                    $colMap['h2'] = $letter;
                } elseif (preg_match('/(sumatif|harian)\s*3/i', $head)) {
                    $colMap['h3'] = $letter;
                } elseif (preg_match('/(sumatif|harian)\s*4/i', $head)) {
                    $colMap['h4'] = $letter;
                } elseif (preg_match('/(sumatif|harian)\s*5/i', $head)) {
                    $colMap['h5'] = $letter;
                } elseif (preg_match('/(sumatif|harian)\s*6/i', $head)) {
                    $colMap['h6'] = $letter;
                } elseif (preg_match('/(sumatif|harian)\s*7/i', $head)) {
                    $colMap['h7'] = $letter;
                } elseif (preg_match('/(sumatif|harian)\s*8/i', $head)) {
                    $colMap['h8'] = $letter;
                } elseif (preg_match('/(sumatif|harian)\s*9/i', $head)) {
                    $colMap['h9'] = $letter;
                }
            }

            $colNis  = $colMap['nis']  ?? 'B';
            $colNisn = $colMap['nisn'] ?? 'C';
            $colNama = $colMap['nama'] ?? 'D';
            $colH1   = $colMap['h1']   ?? 'F';
            $colH2   = $colMap['h2']   ?? 'G';
            $colH3   = $colMap['h3']   ?? 'H';
            $colH4   = $colMap['h4']   ?? 'I';
            $colH5   = $colMap['h5']   ?? 'J';
            $colH6   = $colMap['h6']   ?? 'K';
            $colH7   = $colMap['h7']   ?? 'L';
            $colH8   = $colMap['h8']   ?? 'M';
            $colH9   = $colMap['h9']   ?? 'N';
            $colH10  = $colMap['h10']  ?? 'O';
            $colSts  = $colMap['sts']  ?? 'P';
            $colSas  = $colMap['sas']  ?? 'Q';

            $highestRow = $sheet->getHighestDataRow();

            DB::beginTransaction();

            for ($row = 5; $row <= $highestRow; $row++) {
                $nis  = trim((string) $sheet->getCell("{$colNis}{$row}")->getValue());
                $nisn = trim((string) $sheet->getCell("{$colNisn}{$row}")->getValue());
                $nama = trim((string) $sheet->getCell("{$colNama}{$row}")->getValue());

                if (empty($nis) && empty($nisn) && empty($nama)) {
                    continue;
                }

                // HANYA COCOKKAN SISWA DI KELAS TARGET ($targetClass->id)
                $student = DB::table('students')
                    ->where('class_id', $targetClass->id)
                    ->where(function ($q) use ($nis, $nisn, $nama) {
                        if (!empty($nis)) {
                            $q->where('nis', $nis);
                        }
                        if (!empty($nisn)) {
                            $q->orWhere('nisn', $nisn);
                        }
                        if (empty($nis) && empty($nisn) && !empty($nama)) {
                            $q->where('nama', 'like', "%{$nama}%");
                        }
                    })
                    ->first();

                if (!$student) {
                    $totalSkipped++;
                    continue;
                }

                $valH1  = $this->parseNumericValue($sheet->getCell("{$colH1}{$row}")->getValue());
                $valH2  = $this->parseNumericValue($sheet->getCell("{$colH2}{$row}")->getValue());
                $valH3  = $this->parseNumericValue($sheet->getCell("{$colH3}{$row}")->getValue());
                $valH4  = $this->parseNumericValue($sheet->getCell("{$colH4}{$row}")->getValue());
                $valH5  = $this->parseNumericValue($sheet->getCell("{$colH5}{$row}")->getValue());
                $valH6  = $this->parseNumericValue($sheet->getCell("{$colH6}{$row}")->getValue());
                $valH7  = $this->parseNumericValue($sheet->getCell("{$colH7}{$row}")->getValue());
                $valH8  = $this->parseNumericValue($sheet->getCell("{$colH8}{$row}")->getValue());
                $valH9  = $this->parseNumericValue($sheet->getCell("{$colH9}{$row}")->getValue());
                $valH10 = $this->parseNumericValue($sheet->getCell("{$colH10}{$row}")->getValue());
                $valSts = $this->parseNumericValue($sheet->getCell("{$colSts}{$row}")->getValue());
                $valSas = $this->parseNumericValue($sheet->getCell("{$colSas}{$row}")->getValue());

                // Jika seluruh nilai kosong, lewati baris siswa ini
                $allEmpty = is_null($valH1) && is_null($valH2) && is_null($valH3) && is_null($valH4) &&
                            is_null($valH5) && is_null($valH6) && is_null($valH7) && is_null($valH8) &&
                            is_null($valH9) && is_null($valH10) && is_null($valSts) && is_null($valSas);

                if ($allEmpty) {
                    continue;
                }

                $existing = DB::table('values')
                    ->where('student_id', $student->id)
                    ->where('mapel_id', $mapelId)
                    ->where('fst_id', $fstId)
                    ->first();

                // Kolom kosong di Excel otomatis disimpan sebagai NULL
                $updateData = [
                    'class_id'        => $targetClass->id,
                    'mapel_id'        => $mapelId,
                    'fst_id'          => $fstId,
                    'value_daily'     => $valH1,
                    'value_daily_2'   => $valH2,
                    'value_daily_3'   => $valH3,
                    'value_daily_4'   => $valH4,
                    'value_daily_5'   => $valH5,
                    'value_daily_6'   => $valH6,
                    'value_daily_7'   => $valH7,
                    'value_daily_8'   => $valH8,
                    'value_daily_9'   => $valH9,
                    'value_daily_10'  => $valH10,
                    'value_sts'       => $valSts,
                    'value_sas'       => $valSas,
                    'updated_at'      => Carbon::now(),
                ];

                if ($existing) {
                    DB::table('values')->where('id', $existing->id)->update($updateData);
                } else {
                    $updateData['student_id'] = $student->id;
                    $updateData['created_at'] = Carbon::now();
                    DB::table('values')->insert($updateData);
                }

                NilaiAuditService::log(
                    $student->id,
                    $mapelId,
                    $fstId,
                    'IMPORT_EXCEL',
                    $existing ? (array)$existing : null,
                    $updateData
                );

                $totalImported++;
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Berhasil mengimpor {$totalImported} data nilai siswa untuk kelas {$targetClass->class_name}!",
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengimpor file Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse cell value to valid float or null.
     */
    private function parseNumericValue($raw): ?float
    {
        if (is_null($raw) || $raw === '') {
            return null;
        }

        $cleaned = str_replace(',', '.', trim((string)$raw));

        if (!is_numeric($cleaned)) {
            return null;
        }

        $val = (float) $cleaned;
        return max(0, min(100, $val));
    }

    /**
     * Test connection to CBT server.
     */
    public function testCbtConnection()
    {
        if (!Setting::isModuleEnabled('cbt_sync', true)) {
            return response()->json([
                'status'  => 'offline',
                'message' => 'Integrasi CBT dinonaktifkan oleh administrator.',
            ], 403);
        }

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
        if (!Setting::isModuleEnabled('cbt_sync', true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Integrasi CBT dinonaktifkan oleh administrator.',
            ], 403);
        }

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
        if (!Setting::isModuleEnabled('cbt_sync', true)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Integrasi CBT dinonaktifkan oleh administrator.',
                'data'    => [],
            ], 403);
        }

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
