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
        ]);

        if ($request->hasFile('foto_siswa')) {
            $student_name = str_replace(' ', '_', strtolower($request->student_name)); // Format nama
            $folder = "foto-siswa/{$student_name}"; // Path penyimpanan

            $file = $request->file('foto_siswa');
            $file_name = $student_name . '_' . $file->getClientOriginalName(); // Buat nama unik
            $file_path = $file->storeAs($folder, $file_name, 'public'); // Simpan di storage

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
        ]);
        $student = DB::table('students')->where('id', $id)->first();
        if (!$student) {
            return response()->json(['message' => 'Siswa tidak ditemukan!'], 404);
        }

        $file_name = $student->foto_siswa;
        $file_path = $student->foto_siswa_path;

        if ($request->hasFile('foto_siswa')) {
            $student_name = str_replace(' ', '_', strtolower($request->student_name)); // Format nama
            $folder = "foto-siswa/{$student_name}"; // Path penyimpanan

            $file = $request->file('foto_siswa');
            $new_file_name = $student_name . '_' . $file->getClientOriginalName(); // Buat nama unik
            $new_file_path = $file->storeAs($folder, $new_file_name, 'public'); // Simpan di storage

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
            $sheet       = $spreadsheet->getActiveSheet();
            $highestRow  = $sheet->getHighestDataRow();

            if ($highestRow < 2) {
                return response()->json(['message' => 'Berkas Excel kosong atau tidak memiliki baris data siswa.'], 400);
            }

            // Cari mapping kolom dari baris ke-1 (header) secara dinamis
            $highestCol    = $sheet->getHighestColumn();
            $highestColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);

            $colMap = [];
            for ($c = 1; $c <= $highestColIdx; $c++) {
                $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
                $header = strtolower(trim((string) $sheet->getCell("{$letter}1")->getValue()));

                if (str_contains($header, 'nisn')) {
                    $colMap['nisn'] = $letter;
                } elseif ($header === 'nis' || str_starts_with($header, 'nis ')) {
                    $colMap['nis'] = $letter;
                } elseif (str_contains($header, 'nama peserta didik') || str_contains($header, 'nama siswa') || $header === 'nama') {
                    $colMap['nama'] = $letter;
                } elseif ($header === 'kelas' || str_contains($header, 'rombel')) {
                    $colMap['kelas'] = $letter;
                } elseif ($header === 'l/p' || str_contains($header, 'jenis kelamin') || $header === 'jk') {
                    $colMap['jk'] = $letter;
                } elseif (str_contains($header, 'tempat lahir')) {
                    $colMap['tempat_lahir'] = $letter;
                } elseif (str_contains($header, 'tanggal lahir')) {
                    $colMap['tanggal_lahir'] = $letter;
                } elseif (str_contains($header, 'agama')) {
                    $colMap['agama'] = $letter;
                } elseif (str_contains($header, 'pendidikan')) {
                    $colMap['pendidikan'] = $letter;
                } elseif (str_contains($header, 'alamat peserta didik') || $header === 'alamat siswa' || $header === 'alamat') {
                    $colMap['alamat'] = $letter;
                } elseif (str_contains($header, 'nama ayah')) {
                    $colMap['nama_ayah'] = $letter;
                } elseif (str_contains($header, 'nama ibu')) {
                    $colMap['nama_ibu'] = $letter;
                } elseif (str_contains($header, 'pekerjaan ayah')) {
                    $colMap['pekerjaan_ayah'] = $letter;
                } elseif (str_contains($header, 'pekerjaan ibu')) {
                    $colMap['pekerjaan_ibu'] = $letter;
                } elseif (str_contains($header, 'alamat orang tua')) {
                    $colMap['alamat_orang_tua'] = $letter;
                } elseif ($header === 's' || str_contains($header, 'sakit')) {
                    $colMap['sakit'] = $letter;
                } elseif ($header === 'i' || str_contains($header, 'izin')) {
                    $colMap['izin'] = $letter;
                } elseif ($header === 'a' || str_contains($header, 'alpa') || str_contains($header, 'alpha')) {
                    $colMap['alpa'] = $letter;
                }
            }

            // Fallback ke posisi kolom standar template (A s.d R sesuai urutan: NIS, NISN, Nama, Kelas, L/P, Tempat Lahir, Tgl Lahir, Agama, Pendidikan, Alamat, Ayah, Ibu, Pek Ayah, Pek Ibu, Alamat Ortu, S, I, A)
            $colNis         = $colMap['nis']          ?? 'A';
            $colNisn        = $colMap['nisn']         ?? 'B';
            $colNama        = $colMap['nama']         ?? 'C';
            $colKelas       = $colMap['kelas']        ?? 'D';
            $colJk          = $colMap['jk']           ?? 'E';
            $colTempatLahir = $colMap['tempat_lahir'] ?? 'F';
            $colTglLahir    = $colMap['tanggal_lahir']?? 'G';
            $colAgama       = $colMap['agama']        ?? 'H';
            $colPendidikan  = $colMap['pendidikan']   ?? 'I';
            $colAlamat      = $colMap['alamat']       ?? 'J';
            $colNamaAyah    = $colMap['nama_ayah']    ?? 'K';
            $colNamaIbu     = $colMap['nama_ibu']     ?? 'L';
            $colPekAyah     = $colMap['pekerjaan_ayah']?? 'M';
            $colPekIbu      = $colMap['pekerjaan_ibu'] ?? 'N';
            $colAlmOrtu     = $colMap['alamat_orang_tua'] ?? 'O';
            $colSakit       = $colMap['sakit']        ?? 'P';
            $colIzin        = $colMap['izin']         ?? 'Q';
            $colAlpa        = $colMap['alpa']         ?? 'R';

            $totalImported = 0;
            $totalUpdated  = 0;
            $totalSkipped  = 0;

            DB::beginTransaction();

            for ($row = 2; $row <= $highestRow; $row++) {
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

                $className            = trim((string) $sheet->getCell("{$colKelas}{$row}")->getValue());
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

                // Parsing Tanggal Lahir (Mendukung tipe tanggal Excel maupun teks tanggal biasa)
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
                $classId = null;
                if (!empty($className)) {
                    $class = DB::table('class')
                        ->where('class_name', $className)
                        ->orWhere('class_name', 'LIKE', $className)
                        ->first();

                    if ($class) {
                        $classId = $class->id;
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

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => "Data siswa berhasil diimpor dari Excel! ({$totalImported} siswa baru ditambahkan, {$totalUpdated} siswa diperbarui).",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengimpor berkas Excel siswa: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        $path = public_path('down/Template_InputSiswa.xlsx');
        if (file_exists($path)) {
            return response()->download($path, 'Template_InputSiswa.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        abort(404, 'Berkas template siswa tidak ditemukan.');
    }

    public function printCover($id, Request $request)
    {
        $student = DB::table('students')->where('id', $id)->first();
        if (!$student) {
            abort(404, 'Data siswa tidak ditemukan.');
        }

        $schoolData = DB::table('data_sekolah')->first();
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

        $schoolData = DB::table('data_sekolah')->first();
        $tgl_print = $request->input('tgl_print', now());

        $pdf = Pdf::loadView('docs.cover_identitas_class', compact('students', 'schoolData', 'tgl_print'));

        $label = !empty($className) ? $className : (!empty($classId) ? (DB::table('class')->where('id', $classId)->value('class_name') ?? 'Kelas') : 'Semua_Kelas');
        $safeClass = preg_replace('/[^a-zA-Z0-9_-]/', '_', $label);
        $fileName = "Cover_Identitas_Kelas_{$safeClass}.pdf";

        return $pdf->stream($fileName);
    }
}
