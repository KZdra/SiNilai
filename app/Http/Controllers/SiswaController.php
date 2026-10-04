<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Services\DataTableHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class SiswaController extends Controller
{
    public function index()
    {
        $classList = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc')->get();
        $className = null;
        if (Auth::user()->class_id !== null) {
            $className = DB::table('class')->where('id', Auth::user()->class_id)->value('class_name');
        }

        return view('msiswa.index', compact('classList', 'className'));
    }

    public function getData(Request $request)
    {
        $query = DB::table('students as s')
            ->select(
                's.id',
                's.nis',
                's.nisn',
                's.nama',
                's.class_id',
                DB::raw("COALESCE(class.class_name, 'Alumni / Belum Ada Kelas') as class_name"),
                's.jenis_kelamin',
                's.tempat_lahir',
                's.tanggal_lahir',
                's.agama',
                's.pendidikan_sebelumnya',
                's.alamat',
                's.nama_ayah',
                's.nama_ibu',
                's.pekerjaan_ayah',
                's.pekerjaan_ibu',
                's.alamat_orang_tua',
                DB::raw('COALESCE(s.sakit,0) as sakit'),
                DB::raw('COALESCE(s.izin,0) as izin'),
                DB::raw('COALESCE(s.alpa,0) as alpa'),
                's.foto_siswa_path'
            )
            ->leftJoin('class', 's.class_id', '=', 'class.id');

        if (Auth::check() && Auth::user()->role_id != 1 && Auth::user()->class_id !== null) {
            $query->where('s.class_id', Auth::user()->class_id);
        } elseif ($request->filled('class_id')) {
            $query->where('s.class_id', $request->class_id);
        } elseif ($request->filled('class_name')) {
            $query->where('class.class_name', $request->class_name);
        }

        if ($request->filled('search_keyword')) {
            $kw = $request->search_keyword;
            $query->where(function($q) use ($kw) {
                $q->where('s.nama', 'like', "%{$kw}%")
                  ->orWhere('s.nis', 'like', "%{$kw}%")
                  ->orWhere('s.nisn', 'like', "%{$kw}%");
            });
        }

        $searchableColumns = [
            's.nama',
            's.nis',
            's.nisn',
            's.tempat_lahir',
            's.alamat',
            'class.class_name',
        ];

        $orderableColumns = [
            1 => 's.nisn',
            2 => 's.nis',
            3 => 's.nama',
            4 => 'class.class_name',
            5 => 's.jenis_kelamin',
            6 => 's.tempat_lahir',
            7 => 's.tanggal_lahir',
            8 => 's.agama',
            10 => 's.alamat',
            16 => 's.sakit',
            17 => 's.izin',
            18 => 's.alpa',
        ];

        // Default ordering if not ordered
        if (!$request->has('order')) {
            $query->orderBy('s.nama', 'asc');
        }

        return DataTableHelper::process($query, $request, $searchableColumns, $orderableColumns, 's.id');
    }
    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|numeric',
            'nisn' => 'nullable|numeric',
            'student_name' => 'required|string|max:255',
            'class_id' => 'required|integer',
            'jenis_kelamin' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required',
            'agama' => 'required|string',
            'pendidikan_sebelumnya' => 'required|string',
            'alamat' => 'required|string',
            'nama_ibu' => 'required|string',
            'nama_ayah' => 'required|string',
            'pekerjaan_ayah' => 'required|string',
            'pekerjaan_ibu' => 'required|string',
            'alamat_orang_tua' => 'required|string',
            'foto_siswa' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('foto_siswa')) {
            $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($request->student_name));
            $folder = "foto-siswa/{$cleanName}";

            $file = $request->file('foto_siswa');
            $safeExt = in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'webp'])
                ? strtolower($file->getClientOriginalExtension())
                : 'jpg';
            $file_name = time() . '_' . uniqid() . '.' . $safeExt;
            $file_path = $file->storeAs($folder, $file_name, 'public');
        } else {
            $file_name = null;
            $file_path = null;
        }

        try {
            DB::table('students')->insert([
                'nis' => $request->nis,
                'nisn' => $request->nisn,
                'nama' => $request->student_name,
                'class_id' => $request->class_id,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'agama' => $request->agama,
                'pendidikan_sebelumnya' => $request->pendidikan_sebelumnya,
                'alamat' => $request->alamat,
                'nama_ayah' => $request->nama_ayah,
                'nama_ibu' => $request->nama_ibu,
                'pekerjaan_ayah' => $request->pekerjaan_ayah,
                'pekerjaan_ibu' => $request->pekerjaan_ibu,
                'alamat_orang_tua' => $request->alamat_orang_tua,
                'foto_siswa' => $file_name,
                'foto_siswa_path' => $file_path,
                'sakit' => $request->sakit ?? 0,
                'izin' => $request->izin ?? 0,
                'alpa' => $request->alpa ?? 0,
                'created_at' => Carbon::now()
            ]);
            \App\Services\MasterDataCache::clearDashboardCache();
            return response()->json(['message' => 'Siswa berhasil ditambahkan!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nis' => 'required',
            'nisn' => 'nullable|numeric',
            'student_name' => 'required|string|max:255',
            'class_id' => 'required|integer',
            'jenis_kelamin' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required',
            'agama' => 'required|string',
            'pendidikan_sebelumnya' => 'required|string',
            'alamat' => 'required|string',
            'nama_ibu' => 'required|string',
            'nama_ayah' => 'required|string',
            'pekerjaan_ayah' => 'required|string',
            'pekerjaan_ibu' => 'required|string',
            'alamat_orang_tua' => 'required|string',
            'foto_siswa' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);
        $student = DB::table('students')->where('id', $id)->first();
        if (!$student) {
            return response()->json(['message' => 'Siswa tidak ditemukan!'], 404);
        }

        $file_name = $student->foto_siswa;
        $file_path = $student->foto_siswa_path;

        if ($request->hasFile('foto_siswa')) {
            $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($request->student_name));
            $folder = "foto-siswa/{$cleanName}";

            $file = $request->file('foto_siswa');
            $safeExt = in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'webp'])
                ? strtolower($file->getClientOriginalExtension())
                : 'jpg';
            $new_file_name = time() . '_' . uniqid() . '.' . $safeExt;
            $new_file_path = $file->storeAs($folder, $new_file_name, 'public');

            // Hapus foto lama jika ada
            if ($file_path) {
                Storage::disk('public')->delete($file_path);
            }

            $file_name = $new_file_name;
            $file_path = $new_file_path;
        }

        try {
            DB::table('students')->where('id', '=', $id)->update([
                'nis' => $request->nis,
                'nisn' => $request->nisn,
                'nama' => ucwords(strtolower($request->student_name)),
                'class_id' => $request->class_id,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => ucwords(strtolower($request->tempat_lahir)),
                'tanggal_lahir' => $request->tanggal_lahir,
                'agama' => $request->agama,
                'pendidikan_sebelumnya' => $request->pendidikan_sebelumnya,
                'alamat' => $request->alamat,
                'nama_ayah' => ucwords(strtolower($request->nama_ayah)),
                'nama_ibu' => ucwords(strtolower($request->nama_ibu)),
                'pekerjaan_ayah' => $request->pekerjaan_ayah,
                'pekerjaan_ibu' => $request->pekerjaan_ibu,
                'alamat_orang_tua' => $request->alamat_orang_tua,
                'foto_siswa' => $file_name,
                'foto_siswa_path' => $file_path,
                'sakit' => $request->sakit,
                'izin' => $request->izin,
                'alpa' => $request->alpa,
                'updated_at' => Carbon::now()
            ]);
            \App\Services\MasterDataCache::clearDashboardCache();
            return response()->json(['message' => 'Siswa berhasil diUpdate!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function destroy(Request $request, $id)
    {
        $siswa = DB::table('students')->where('id', $id)->first();

        if (!$siswa) {
            return $this->errorResponse('Students not found', 404);
        }

        if ($siswa->foto_siswa_path) {
            Storage::disk('public')->delete($siswa->foto_siswa_path);
        }

        try {
            DB::table('students')->where('id', '=', $id)->delete();
            \App\Services\MasterDataCache::clearDashboardCache();
            return response()->json(['message' => 'Siswa berhasil diHapus!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'nullable|file|mimes:xlsx,xls,csv,txt|max:10240',
            'csv'  => 'nullable|file|mimes:xlsx,xls,csv,txt|max:10240',
            'excel'=> 'nullable|file|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $file = $request->file('file') ?? $request->file('excel') ?? $request->file('csv');

        if (!$file) {
            return response()->json(['message' => 'Berkas Excel (.xlsx / .xls) tidak ditemukan.'], 400);
        }

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $worksheets  = $spreadsheet->getAllSheets();

            $totalImported = 0;
            $totalUpdated  = 0;
            $totalSkipped  = 0;

            DB::beginTransaction();

            foreach ($worksheets as $sheet) {
                $sheetTitle = trim($sheet->getTitle());
                $highestRow = $sheet->getHighestDataRow();

                if ($highestRow < 2) {
                    continue;
                }

                // Resolusi kelas dari nama sheet
                $sheetClass = DB::table('class')
                    ->where('class_name', $sheetTitle)
                    ->orWhere('class_name', str_replace('_', ' ', $sheetTitle))
                    ->orWhere('class_name', str_replace('-', ' ', $sheetTitle))
                    ->first();

                // Jika nama sheet belum cocok, coba cek di cell A2 atau B2
                if (!$sheetClass) {
                    $cellA2 = (string) $sheet->getCell('A2')->getValue();
                    $cellB2 = (string) $sheet->getCell('B2')->getValue();
                    if (str_contains($cellA2, 'Kelas:')) {
                        $parsedName = trim(str_replace('Kelas:', '', $cellA2));
                        $sheetClass = DB::table('class')->where('class_name', $parsedName)->first();
                    } elseif (str_contains($cellB2, 'Kelas:')) {
                        $parsedName = trim(str_replace('Kelas:', '', $cellB2));
                        $sheetClass = DB::table('class')->where('class_name', $parsedName)->first();
                    }
                }

                // Cari baris header secara dinamis (antara baris 1 s.d 4)
                $highestCol    = $sheet->getHighestColumn();
                $highestColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

                $headerRow = 1;
                $colMap = [];

                for ($r = 1; $r <= min(5, $highestRow); $r++) {
                    $foundKey = false;
                    $tempMap = [];
                    for ($c = 1; $c <= $highestColIdx; $c++) {
                        $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                        $header = strtolower(trim((string) $sheet->getCell("{$letter}{$r}")->getValue()));

                        if (str_contains($header, 'nisn')) {
                            $tempMap['nisn'] = $letter;
                            $foundKey = true;
                        } elseif ($header === 'nis' || str_starts_with($header, 'nis ')) {
                            $tempMap['nis'] = $letter;
                            $foundKey = true;
                        } elseif (str_contains($header, 'nama')) {
                            $tempMap['nama'] = $letter;
                            $foundKey = true;
                        } elseif ($header === 'kelas' || str_contains($header, 'rombel')) {
                            $tempMap['kelas'] = $letter;
                        } elseif ($header === 'l/p' || str_contains($header, 'jenis kelamin') || $header === 'jk') {
                            $tempMap['jk'] = $letter;
                        } elseif (str_contains($header, 'tempat lahir')) {
                            $tempMap['tempat_lahir'] = $letter;
                        } elseif (str_contains($header, 'tanggal lahir')) {
                            $tempMap['tanggal_lahir'] = $letter;
                        } elseif (str_contains($header, 'agama')) {
                            $tempMap['agama'] = $letter;
                        } elseif (str_contains($header, 'pendidikan')) {
                            $tempMap['pendidikan'] = $letter;
                        } elseif (str_contains($header, 'alamat peserta didik') || $header === 'alamat siswa' || $header === 'alamat') {
                            $tempMap['alamat'] = $letter;
                        } elseif (str_contains($header, 'nama ayah')) {
                            $tempMap['nama_ayah'] = $letter;
                        } elseif (str_contains($header, 'nama ibu')) {
                            $tempMap['nama_ibu'] = $letter;
                        } elseif (str_contains($header, 'pekerjaan ayah')) {
                            $tempMap['pekerjaan_ayah'] = $letter;
                        } elseif (str_contains($header, 'pekerjaan ibu')) {
                            $tempMap['pekerjaan_ibu'] = $letter;
                        } elseif (str_contains($header, 'alamat orang tua')) {
                            $tempMap['alamat_orang_tua'] = $letter;
                        } elseif ($header === 's' || str_contains($header, 'sakit')) {
                            $tempMap['sakit'] = $letter;
                        } elseif ($header === 'i' || str_contains($header, 'izin')) {
                            $tempMap['izin'] = $letter;
                        } elseif ($header === 'a' || str_contains($header, 'alpa') || str_contains($header, 'alpha')) {
                            $tempMap['alpa'] = $letter;
                        }
                    }

                    if ($foundKey && isset($tempMap['nis']) && isset($tempMap['nama'])) {
                        $headerRow = $r;
                        $colMap = $tempMap;
                        break;
                    }
                }

                // Fallback default kolom jika tidak terpetakan lengkap
                $colNis         = $colMap['nis']          ?? 'B';
                $colNisn        = $colMap['nisn']         ?? 'C';
                $colNama        = $colMap['nama']         ?? 'D';
                $colKelas       = $colMap['kelas']        ?? 'E';
                $colJk          = $colMap['jk']           ?? 'F';
                $colTempatLahir = $colMap['tempat_lahir'] ?? 'G';
                $colTglLahir    = $colMap['tanggal_lahir']?? 'H';
                $colAgama       = $colMap['agama']        ?? 'I';
                $colPendidikan  = $colMap['pendidikan']   ?? 'J';
                $colAlamat      = $colMap['alamat']       ?? 'K';
                $colNamaAyah    = $colMap['nama_ayah']    ?? 'L';
                $colNamaIbu     = $colMap['nama_ibu']     ?? 'M';
                $colPekAyah     = $colMap['pekerjaan_ayah']?? 'N';
                $colPekIbu      = $colMap['pekerjaan_ibu'] ?? 'O';
                $colAlmOrtu     = $colMap['alamat_orang_tua'] ?? 'P';
                $colSakit       = $colMap['sakit']        ?? 'Q';
                $colIzin        = $colMap['izin']         ?? 'R';
                $colAlpa        = $colMap['alpa']         ?? 'S';

                for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
                    $cellNis = $sheet->getCell("{$colNis}{$row}");
                    $nis = trim((string) ($cellNis->getFormattedValue() ?: $cellNis->getValue()));

                    $cellNisn = $sheet->getCell("{$colNisn}{$row}");
                    $nisn = trim((string) ($cellNisn->getFormattedValue() ?: $cellNisn->getValue()));

                    $nama = trim((string) $sheet->getCell("{$colNama}{$row}")->getValue());

                    // Lewati baris jika NIS dan Nama kosong
                    if (empty($nis) && empty($nama)) {
                        continue;
                    }

                    if (empty($nis)) {
                        $totalSkipped++;
                        continue;
                    }

                    $classNameVal = trim((string) $sheet->getCell("{$colKelas}{$row}")->getValue());
                    $className    = !empty($classNameVal) ? $classNameVal : ($sheetClass ? $sheetClass->class_name : '');

                    $jenis_kelamin        = trim((string) $sheet->getCell("{$colJk}{$row}")->getValue());
                    $tempat_lahir         = trim((string) $sheet->getCell("{$colTempatLahir}{$row}")->getValue());
                    $agama                = trim((string) $sheet->getCell("{$colAgama}{$row}")->getValue());
                    $pendidikan_sebelumnya= trim((string) $sheet->getCell("{$colPendidikan}{$row}")->getValue());
                    $alamat               = trim((string) $sheet->getCell("{$colAlamat}{$row}")->getValue());
                    $nama_ayah            = trim((string) $sheet->getCell("{$colNamaAyah}{$row}")->getValue());
                    $nama_ibu             = trim((string) $sheet->getCell("{$colNamaIbu}{$row}")->getValue());
                    $pekerjaan_ayah       = trim((string) $sheet->getCell("{$colPekAyah}{$row}")->getValue());
                    $pekerjaan_ibu        = trim((string) $sheet->getCell("{$colPekIbu}{$row}")->getValue());
                    $alamat_orang_tua     = trim((string) $sheet->getCell("{$colAlmOrtu}{$row}")->getValue());

                    $rawSakit = $sheet->getCell("{$colSakit}{$row}")->getValue();
                    $rawIzin  = $sheet->getCell("{$colIzin}{$row}")->getValue();
                    $rawAlpa  = $sheet->getCell("{$colAlpa}{$row}")->getValue();

                    $sakit = (is_null($rawSakit) || $rawSakit === '') ? 0 : (int)$rawSakit;
                    $izin  = (is_null($rawIzin)  || $rawIzin === '')  ? 0 : (int)$rawIzin;
                    $alpa  = (is_null($rawAlpa)  || $rawAlpa === '')  ? 0 : (int)$rawAlpa;

                    // Parsing Tanggal Lahir
                    $tanggal_lahir = null;
                    $tglCell = $sheet->getCell("{$colTglLahir}{$row}");
                    if (!empty($tglCell->getValue())) {
                        if (ExcelDate::isDateTime($tglCell)) {
                            $val = $tglCell->getValue();
                            $tanggal_lahir = Carbon::instance(ExcelDate::excelToDateTimeObject($val))->format('Y-m-d');
                        } else {
                            $rawDate = trim((string) $tglCell->getValue());
                            try {
                                $tanggal_lahir = Carbon::parse($rawDate)->format('Y-m-d');
                            } catch (\Exception $e) {
                                $tanggal_lahir = null;
                            }
                        }
                    }

                    // Cari ID Kelas atau buat baru jika belum ada
                    $classId = $sheetClass ? $sheetClass->id : null;
                    if (!empty($className)) {
                        $cObj = DB::table('class')
                            ->where('class_name', $className)
                            ->orWhere('class_name', 'LIKE', $className)
                            ->first();

                        if ($cObj) {
                            $classId = $cObj->id;
                        } else {
                            $classId = DB::table('class')->insertGetId([
                                'class_name' => $className,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                            ]);
                        }
                    }

                    $studentData = [
                        'nisn'                 => !empty($nisn) ? $nisn : null,
                        'nama'                 => ucwords(strtolower($nama)),
                        'class_id'             => $classId,
                        'jenis_kelamin'        => !empty($jenis_kelamin) ? strtoupper(trim($jenis_kelamin)) : null,
                        'tempat_lahir'         => !empty($tempat_lahir) ? ucwords(strtolower($tempat_lahir)) : null,
                        'tanggal_lahir'        => $tanggal_lahir,
                        'agama'                => !empty($agama) ? strtolower(trim($agama)) : null,
                        'pendidikan_sebelumnya'=> $pendidikan_sebelumnya ?: null,
                        'alamat'               => $alamat ?: null,
                        'nama_ayah'            => !empty($nama_ayah) ? ucwords(strtolower($nama_ayah)) : null,
                        'nama_ibu'             => !empty($nama_ibu) ? ucwords(strtolower($nama_ibu)) : null,
                        'pekerjaan_ayah'       => $pekerjaan_ayah ?: null,
                        'pekerjaan_ibu'        => $pekerjaan_ibu ?: null,
                        'alamat_orang_tua'     => $alamat_orang_tua ?: null,
                        'sakit'                => $sakit,
                        'izin'                 => $izin,
                        'alpa'                 => $alpa,
                        'updated_at'           => Carbon::now(),
                    ];

                    $existingStudent = DB::table('students')->where('nis', $nis)->first();

                    if ($existingStudent) {
                        DB::table('students')->where('id', $existingStudent->id)->update($studentData);
                        $totalUpdated++;
                    } else {
                        $studentData['nis']        = $nis;
                        $studentData['created_at'] = Carbon::now();
                        DB::table('students')->insert($studentData);
                        $totalImported++;
                    }
                }
            }

            DB::commit();

            if ($totalImported === 0 && $totalUpdated === 0 && $totalSkipped === 0) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Tidak ada baris data siswa yang ditemukan pada berkas Excel.',
                ], 422);
            }

            return response()->json([
                'status'  => 'success',
                'message' => "Data siswa berhasil diimpor! ({$totalImported} siswa baru ditambahkan, {$totalUpdated} siswa diperbarui).",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengimpor berkas Excel siswa: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function downloadTemplate(Request $request)
    {
        $classId = $request->input('class_id');
        if (Auth::check() && Auth::user()->role_id != 1 && Auth::user()->class_id !== null) {
            $classId = Auth::user()->class_id;
        }

        if (!empty($classId) && $classId !== 'all') {
            $classes = DB::table('class')->where('id', $classId)->get();
            $className = $classes->first() ? preg_replace('/[^a-zA-Z0-9_-]/', '_', $classes->first()->class_name) : 'Kelas';
            $filename = "Template_Import_Siswa_{$className}.xlsx";
        } else {
            $classes = DB::table('class')->orderBy('class_name', 'asc')->get();
            $filename = "Template_Import_Siswa_Semua_Kelas.xlsx";
        }

        if ($classes->isEmpty()) {
            abort(404, 'Data kelas tidak ditemukan. Silakan tambahkan kelas terlebih dahulu.');
        }

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MultiClassStudentTemplateExport($classes), $filename);
    }

    public function printCover($id, Request $request)
    {
        $student = DB::table('students')->where('id', $id)->first();
        if (!$student) {
            abort(404, 'Data siswa tidak ditemukan.');
        }

        $schoolData = \App\Services\MasterDataCache::getSchoolData();
        $tgl_print = $request->input('tgl_print', now());

        $pdf = Pdf::loadView('docs.cover_identitas', compact('student', 'schoolData', 'tgl_print'));

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $student->nama);
        $fileName = "Cover_Identitas_{$safeName}.pdf";

        return $pdf->stream($fileName);
    }

    public function printCoverClass(Request $request)
    {
        $classId = $request->input('class_id');
        $className = $request->input('class_name');

        if (Auth::check() && Auth::user()->role_id != 1 && Auth::user()->class_id !== null) {
            $classId = Auth::user()->class_id;
        }

        $query = DB::table('students as s')
            ->leftJoin('class as c', 's.class_id', '=', 'c.id')
            ->select('s.*');

        if (!empty($classId)) {
            $query->where('s.class_id', $classId);
        } elseif (!empty($className)) {
            $query->where('c.class_name', $className);
        }

        $students = $query->orderBy('s.nama', 'asc')->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'Tidak ada data siswa.');
        }

        $schoolData = \App\Services\MasterDataCache::getSchoolData();
        $tgl_print = $request->input('tgl_print', now());

        $pdf = Pdf::loadView('docs.cover_identitas_class', compact('students', 'schoolData', 'tgl_print'));

        $label = !empty($className) ? $className : (!empty($classId) ? (DB::table('class')->where('id', $classId)->value('class_name') ?? 'Kelas') : 'Semua_Kelas');
        $safeClass = preg_replace('/[^a-zA-Z0-9_-]/', '_', $label);
        $fileName = "Cover_Identitas_Kelas_{$safeClass}.pdf";

        return $pdf->stream($fileName);
    }
}
