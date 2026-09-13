<?php

namespace App\Http\Controllers;

use App\Exports\ClassSheetExport;
use App\Exports\MultiClassTemplateNilaiExport;
use App\Services\NilaiAuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class NilaiImportController extends Controller
{
    /**
     * Tampilkan halaman antarmuka upload nilai Excel untuk Guru Mapel / Wali Kelas.
     */
    public function index()
    {
        $classList = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc')->get();
        $mapelList = DB::table('mata_pelajarans')->select('id', 'nama_mapel')->orderBy('nama_mapel', 'asc')->get();
        $fstList   = DB::table('m_fst_pembelajaran')->select('id', 'fase', 'semester', 'tahun_ajaran')->orderBy('id', 'desc')->get();

        return view('nilai.upload_excel', compact('classList', 'mapelList', 'fstList'));
    }

    /**
     * Unduh template Excel (.xlsx).
     * Jika class_id = 'all' atau kosong: menghasilkan workbook multi-sheet (1 sheet per kelas).
     * Jika class_id spesifik: menghasilkan file 1 sheet untuk kelas tersebut.
     */
    public function downloadTemplate(Request $request)
    {
        $request->validate([
            'mapel_id' => 'required|integer',
            'fst_id'   => 'required|integer',
            'class_id' => 'nullable|string',
        ]);

        $mapel = DB::table('mata_pelajarans')->where('id', $request->mapel_id)->first();
        $fst   = DB::table('m_fst_pembelajaran')->where('id', $request->fst_id)->first();

        if (!$mapel || !$fst) {
            abort(404, 'Data Mata Pelajaran atau Periode Semester tidak ditemukan.');
        }

        $cleanMapel = preg_replace('/[^a-zA-Z0-9_-]/', '_', $mapel->nama_mapel);
        $cleanTA    = preg_replace('/[^a-zA-Z0-9_-]/', '_', $fst->tahun_ajaran);

        // Jika memilih satu kelas spesifik
        if ($request->filled('class_id') && $request->class_id !== 'all') {
            $class = DB::table('class')->where('id', $request->class_id)->first();
            if (!$class) {
                abort(404, 'Data Kelas tidak ditemukan.');
            }

            $cleanClass = preg_replace('/[^a-zA-Z0-9_-]/', '_', $class->class_name);
            $filename = "Template_Nilai_{$cleanMapel}_{$cleanClass}_{$cleanTA}.xlsx";

            // Tetap sertakan sheet TP di awal karena guru mapel membutuhkan referensi TP untuk kelas tersebut
            return Excel::download(new MultiClassTemplateNilaiExport([$class], $mapel, $fst, true), $filename);
        }

        // Default: Multi-Sheet untuk SEMUA KELAS (Sheet 0: Daftar TP, Sheet 1..N: Kelas)
        $classes = DB::table('class')->orderBy('class_name', 'asc')->get();
        $filename = "Template_Nilai_{$cleanMapel}_Semua_Kelas_{$cleanTA}.xlsx";

        return Excel::download(new MultiClassTemplateNilaiExport($classes, $mapel, $fst, true), $filename);
    }

    /**
     * Proses import berkas Excel (.xlsx / .xls).
     * Mendukung pembacaan multi-sheet otomatis untuk semua kelas.
     */
    public function importExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
            'mapel_id'   => 'required|integer',
            'fst_id'     => 'required|integer',
            'overwrite'  => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $mapelId   = (int) $request->input('mapel_id');
        $fstId     = (int) $request->input('fst_id');
        $overwrite = $request->boolean('overwrite', true);

        // Cek apakah semester telah dikunci oleh Kurikulum
        if ($this->isSemesterLocked($fstId)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Semester ini telah dikunci oleh Kurikulum. Nilai tidak dapat diubah.',
            ], 403);
        }

        $file = $request->file('excel_file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheets  = $spreadsheet->getAllSheets();

            $totalImportedAll = 0;
            $totalSkippedAll  = 0;
            $sheetSummaries   = [];

            DB::beginTransaction();

            foreach ($worksheets as $sheet) {
                $sheetTitle = trim($sheet->getTitle());

                // Cari kelas berdasarkan nama sheet (cocokkan string eksak atau ganti underscore)
                $class = DB::table('class')
                    ->where('class_name', $sheetTitle)
                    ->orWhere('class_name', str_replace('_', ' ', $sheetTitle))
                    ->orWhere('class_name', str_replace('-', ' ', $sheetTitle))
                    ->first();

                // Jika nama sheet bukan nama kelas, coba baca info kelas dari cell B2 jika ada
                if (!$class) {
                    $cellB2 = (string) $sheet->getCell('D2')->getValue();
                    if (str_contains($cellB2, 'Kelas:')) {
                        $parsedClassName = trim(str_replace('Kelas:', '', $cellB2));
                        $class = DB::table('class')->where('class_name', $parsedClassName)->first();
                    }
                }

                if (!$class) {
                    // Deteksi jika sheet ini merupakan sheet Daftar TP (Tujuan Pembelajaran)
                    if (str_contains(strtolower($sheetTitle), 'tp') || str_contains(strtolower($sheetTitle), 'tujuan')) {
                        $this->syncTpsFromSheet($sheet, $mapelId, $fstId);
                    }
                    // Lewati sheet yang bukan merupakan sheet kelas (misal: Daftar TP, Info/Cover)
                    continue;
                }

                $highestRow = $sheet->getHighestDataRow();
                $sheetImported = 0;
                $sheetSkipped  = 0;

                // Deteksi mapping kolom dari baris 4 (header) secara dinamis jika tersedia
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

                // Default column letters (Sesuai Urutan Web Input: B=NIS, C=NISN, D=Nama, F..O=Sumatif 1..10, P=STS, Q=SAS)
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

                // Data siswa dimulai dari baris ke-5 (baris 1-4 adalah judul, metadata & header)
                for ($row = 5; $row <= $highestRow; $row++) {
                    $nis  = trim((string) $sheet->getCell("{$colNis}{$row}")->getValue());
                    $nisn = trim((string) $sheet->getCell("{$colNisn}{$row}")->getValue());
                    $nama = trim((string) $sheet->getCell("{$colNama}{$row}")->getValue());

                    // Jika baris kosong, lewati
                    if (empty($nis) && empty($nisn) && empty($nama)) {
                        continue;
                    }

                    // Cari siswa berdasarkan NIS atau NISN di kelas tersebut
                    $student = DB::table('students')
                        ->where('class_id', $class->id)
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
                        $sheetSkipped++;
                        continue;
                    }

                    // Ekstrak nilai angka: jika sel tidak diisi, parseNumericValue menghasilkan null
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

                    // Jika semua kolom nilai kosong sama sekali, siswa dilewati (belum dinilai)
                    $allEmpty = is_null($valH1) && is_null($valH2) && is_null($valH3) && is_null($valH4) &&
                                is_null($valH5) && is_null($valH6) && is_null($valH7) && is_null($valH8) &&
                                is_null($valH9) && is_null($valH10) && is_null($valSts) && is_null($valSas);

                    if ($allEmpty) {
                        continue;
                    }

                    // Ambil nilai eksisting di database
                    $existing = DB::table('values')
                        ->where('student_id', $student->id)
                        ->where('mapel_id', $mapelId)
                        ->where('fst_id', $fstId)
                        ->first();

                    // Siapkan data: kolom yang kosong di Excel otomatis menjadi NULL
                    $updateData = [
                        'class_id'        => $class->id,
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

                    // Jika opsi overwrite TIDAK dicentang, pertahankan nilai database lama jika di Excel kosong
                    if (!$overwrite && $existing) {
                        if (is_null($valH1))  $updateData['value_daily']     = $existing->value_daily;
                        if (is_null($valH2))  $updateData['value_daily_2']   = $existing->value_daily_2;
                        if (is_null($valH3))  $updateData['value_daily_3']   = $existing->value_daily_3;
                        if (is_null($valH4))  $updateData['value_daily_4']   = $existing->value_daily_4;
                        if (is_null($valH5))  $updateData['value_daily_5']   = $existing->value_daily_5;
                        if (is_null($valH6))  $updateData['value_daily_6']   = $existing->value_daily_6;
                        if (is_null($valH7))  $updateData['value_daily_7']   = $existing->value_daily_7;
                        if (is_null($valH8))  $updateData['value_daily_8']   = $existing->value_daily_8;
                        if (is_null($valH9))  $updateData['value_daily_9']   = $existing->value_daily_9;
                        if (is_null($valH10)) $updateData['value_daily_10']  = $existing->value_daily_10;
                        if (is_null($valSts)) $updateData['value_sts']       = $existing->value_sts;
                        if (is_null($valSas)) $updateData['value_sas']       = $existing->value_sas;
                    }

                    if ($existing) {
                        DB::table('values')->where('id', $existing->id)->update($updateData);
                    } else {
                        $updateData['student_id'] = $student->id;
                        $updateData['created_at'] = Carbon::now();
                        DB::table('values')->insert($updateData);
                    }

                    // Catat log audit trail mutasi nilai
                    NilaiAuditService::log(
                        $student->id,
                        $mapelId,
                        $fstId,
                        'IMPORT_EXCEL',
                        $existing ? (array)$existing : null,
                        $updateData
                    );

                    $sheetImported++;
                }

                $totalImportedAll += $sheetImported;
                $totalSkippedAll  += $sheetSkipped;

                $sheetSummaries[] = [
                    'sheet_title' => $sheetTitle,
                    'class_name'  => $class->class_name,
                    'imported'    => $sheetImported,
                    'skipped'     => $sheetSkipped,
                ];
            }

            DB::commit();

            if (empty($sheetSummaries)) {
                return response()->json([
                    'status'  => 'warning',
                    'message' => 'Tidak ada sheet kelas yang cocok ditemukan dalam berkas Excel. Pastikan menggunakan format template resmi SiNilai.',
                ], 422);
            }

            return response()->json([
                'status'          => 'success',
                'message'         => "Berhasil mengimpor {$totalImportedAll} nilai siswa dari " . count($sheetSummaries) . " kelas!",
                'total_imported'  => $totalImportedAll,
                'total_skipped'   => $totalSkippedAll,
                'sheet_summaries' => $sheetSummaries,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Excel grade import failed: " . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem saat memproses berkas Excel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Konversi nilai sel Excel menjadi angka desimal/float yang valid (skala 0 - 100).
     */
    private function parseNumericValue($raw): ?float
    {
        if (is_null($raw) || $raw === '') {
            return null;
        }

        // Bersihkan koma desimal Indonesia menjadi titik
        $cleaned = str_replace(',', '.', trim((string)$raw));

        if (!is_numeric($cleaned)) {
            return null;
        }

        $val = (float) $cleaned;
        // Batasi rentang nilai 0 s.d 100
        return max(0, min(100, $val));
    }

    /**
     * Cek apakah status semester pada FST terkunci.
     */
    private function isSemesterLocked($fstId): bool
    {
        if (!$fstId) return false;

        $fst = DB::table('m_fst_pembelajaran')->where('id', $fstId)->first();
        return $fst && isset($fst->is_locked) && (int) $fst->is_locked === 1;
    }

    /**
     * Sinkronisasi Tujuan Pembelajaran (TP) jika ada deskripsi TP pada sheet Daftar TP.
     */
    private function syncTpsFromSheet($sheet, int $mapelId, int $fstId): void
    {
        try {
            $highestRow = $sheet->getHighestDataRow();
            $highestCol = $sheet->getHighestColumn();
            $highestColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

            $tpColLetter = 'C';
            for ($c = 1; $c <= $highestColIdx; $c++) {
                $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                $header = strtolower(trim((string) $sheet->getCell("{$letter}4")->getValue()));
                if (str_contains($header, 'deskripsi') || str_contains($header, 'tujuan') || str_contains($header, 'kompetensi')) {
                    $tpColLetter = $letter;
                    break;
                }
            }

            $descriptions = [];
            for ($row = 5; $row <= $highestRow; $row++) {
                $desc = trim((string) $sheet->getCell("{$tpColLetter}{$row}")->getValue());
                if (!empty($desc) && !str_starts_with($desc, 'Petunjuk:')) {
                    $descriptions[] = $desc;
                }
            }

            if (empty($descriptions)) {
                return;
            }

            $existingTps = DB::table('m_tp')
                ->where('mapel_id', $mapelId)
                ->where('fst_id', $fstId)
                ->pluck('tp_deskripsi')
                ->toArray();

            $now = Carbon::now();
            $toInsert = [];
            foreach ($descriptions as $desc) {
                if (!in_array($desc, $existingTps)) {
                    $toInsert[] = [
                        'mapel_id'     => $mapelId,
                        'fst_id'       => $fstId,
                        'class_id'     => null,
                        'tp_deskripsi' => $desc,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                    $existingTps[] = $desc;
                }
            }

            if (!empty($toInsert)) {
                DB::table('m_tp')->insert($toInsert);
            }
        } catch (\Exception $e) {
            Log::warning("Could not sync TP from sheet in NilaiImportController: " . $e->getMessage());
        }
    }
}
