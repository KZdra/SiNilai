<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\DataTableHelper;

class UserController extends Controller
{
    public function index()
    {
        $classList = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc')->get();
        $rolesList = DB::table('roles')->whereIn('id', [1, 2])->select('id', 'role_name')->orderBy('id', 'asc')->get();

        // Summary counters
        $countAdmin = DB::table('users')->where('role_id', 1)->count();
        $countGuru = DB::table('users')->where('role_id', 2)->count();
        $countWalas = DB::table('users')->where('role_id', 2)->whereNotNull('class_id')->count();
        $countSiswaUser = DB::table('users')->where('role_id', 3)->count();
        $totalStudents = DB::table('students')->count();

        return view('users.index', compact(
            'classList',
            'rolesList',
            'countAdmin',
            'countGuru',
            'countWalas',
            'countSiswaUser',
            'totalStudents'
        ));
    }

    // ── Data Guru & Administrator ─────────────────────────────────
    public function getData(Request $request)
    {
        $query = DB::table('users as u')->select(
            'u.id',
            'u.class_id',
            'c.class_name',
            'u.role_id',
            'r.role_name',
            'u.username',
            'u.name',
            DB::raw('COALESCE(u.nip, "-") AS nip'),
            'u.email'
        )
            ->leftJoin('roles as r', 'u.role_id', '=', 'r.id')
            ->leftJoin('class as c', 'u.class_id', '=', 'c.id')
            ->whereIn('u.role_id', [1, 2]);

        if ($request->filled('role_id')) {
            $query->where('u.role_id', $request->role_id);
        }
        if ($request->filled('class_id')) {
            $query->where('u.class_id', $request->class_id);
        }

        $searchableColumns = [
            'u.username',
            'u.name',
            'u.nip',
            'u.email',
            'c.class_name',
            'r.role_name',
        ];

        $orderableColumns = [
            0 => 'u.username',
            1 => 'u.name',
            2 => 'u.nip',
            3 => 'c.class_name',
            4 => 'u.email',
            5 => 'r.role_name',
        ];

        if (!$request->has('order')) {
            $query->orderBy('u.role_id', 'asc')->orderBy('u.name', 'asc');
        }

        return DataTableHelper::process($query, $request, $searchableColumns, $orderableColumns, 'u.id');
    }

    // ── Data Portal Siswa (Tampilkan Semua Siswa, Baik Aktif Maupun Belum) ──
    public function getDataSiswa(Request $request)
    {
        $query = DB::table('students as s')
            ->leftJoin('class as c', 's.class_id', '=', 'c.id')
            ->leftJoin('users as u', function ($join) {
                $join->on('s.id', '=', 'u.student_id')
                    ->where('u.role_id', 3);
            })
            ->select(
                's.id as student_id',
                's.nama as student_name',
                's.nisn',
                's.nis',
                's.class_id',
                'c.class_name',
                'u.id as user_id',
                DB::raw('COALESCE(u.username, s.nisn, s.nis, "-") as username'),
                DB::raw('COALESCE(u.email, "-") as email'),
                DB::raw('CASE WHEN u.id IS NOT NULL THEN 1 ELSE 0 END as is_active'),
                'u.created_at'
            );

        if ($request->filled('class_id')) {
            $query->where('s.class_id', $request->class_id);
        }

        if ($request->filled('status')) {
            if ($request->status === '1') {
                $query->whereNotNull('u.id');
            } elseif ($request->status === '0') {
                $query->whereNull('u.id');
            }
        }

        $searchable = [
            's.nama',
            's.nisn',
            's.nis',
            'u.username',
            'u.email',
            'c.class_name'
        ];

        $orderable = [
            1 => 's.nisn',
            2 => 's.nama',
            3 => 'c.class_name',
            4 => 'u.email',
            5 => 'is_active',
        ];

        if (!$request->has('order')) {
            $query->orderBy('c.class_name', 'asc')->orderBy('s.nama', 'asc');
        }

        // Hitung statistik real-time sesuai filter kelas
        $statsQuery = DB::table('students as s')
            ->leftJoin('users as u', function ($join) {
                $join->on('s.id', '=', 'u.student_id')
                    ->where('u.role_id', 3);
            });
        if ($request->filled('class_id')) {
            $statsQuery->where('s.class_id', $request->class_id);
        }
        $totalClassStudents = (clone $statsQuery)->count('s.id');
        $activeCount = (clone $statsQuery)->whereNotNull('u.id')->count('s.id');
        $inactiveCount = max(0, $totalClassStudents - $activeCount);

        $extra = [
            'total_students' => $totalClassStudents,
            'active_count' => $activeCount,
            'inactive_count' => $inactiveCount,
        ];

        return DataTableHelper::process($query, $request, $searchable, $orderable, 's.id', null, $extra);
    }

    public function store(Request $request)
    {
        $kont = $request->validate([
            'class_id' => 'nullable|integer',
            'role_id' => 'required|integer',
            'nip' => 'nullable|string|max:50',
            'nama' => 'required|string|max:150',
            'username' => 'required|string|max:100|unique:users,username',
            'password' => 'required|min:4',
            'email' => 'required|email|max:150|unique:users,email',
        ]);

        try {
            DB::table('users')->insert([
                'class_id' => !empty($kont['class_id']) ? $kont['class_id'] : null,
                'role_id' => $kont['role_id'],
                'nip' => !empty($kont['nip']) ? $kont['nip'] : null,
                'name' => $kont['nama'],
                'username' => $kont['username'],
                'password' => Hash::make($kont['password']),
                'email' => $kont['email'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            return response()->json(['message' => 'Pengguna berhasil ditambahkan!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'class_id' => 'nullable|integer',
            'role_id' => 'required|integer',
            'nip' => 'nullable|string|max:50',
            'nama' => 'required|string|max:150',
            'username' => 'required|string|max:100|unique:users,username,' . $id,
            'email' => 'required|email|max:150|unique:users,email,' . $id,
            'password' => 'nullable|min:4',
        ]);

        try {
            $data = [
                'class_id' => $request->filled('class_id') ? $request->class_id : null,
                'role_id' => $validated['role_id'],
                'nip' => !empty($validated['nip']) ? $validated['nip'] : null,
                'name' => $validated['nama'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'updated_at' => Carbon::now(),
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            DB::table('users')->where('id', $id)->update($data);
            return response()->json(['message' => 'Pengguna berhasil diperbarui!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::table('users')->where('id', $id)->delete();
            return response()->json(['message' => 'Pengguna berhasil dihapus!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ── Reset Password ───────────────────────────────────────────
    public function resetPassword(Request $request, $id)
    {
        try {
            $user = DB::table('users')->where('id', $id)->first();
            if (!$user) {
                return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
            }

            $defaultPass = ($user->role_id == 3) ? 'siswa123' : 'guru123';
            $newPassword = $request->input('password', $defaultPass);

            DB::table('users')->where('id', $id)->update([
                'password' => Hash::make($newPassword),
                'updated_at' => Carbon::now(),
            ]);

            return response()->json([
                'message' => "Password berhasil direset ke: {$newPassword}"
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ── Generate Massal Akun Siswa ────────────────────────────────
    public function generateSiswaAccounts(Request $request)
    {
        $classId = $request->input('class_id');
        $query = DB::table('students');
        if (!empty($classId)) {
            $query->where('class_id', $classId);
        }
        $students = $query->get();

        if ($students->isEmpty()) {
            return response()->json(['message' => 'Tidak ada siswa yang ditemukan untuk digenerate.'], 404);
        }

        $created = 0;
        $skipped = 0;

        foreach ($students as $std) {
            $username = !empty($std->nisn) ? trim($std->nisn) : trim($std->nis);
            if (empty($username)) continue;

            $exists = DB::table('users')
                ->where('username', $username)
                ->orWhere('student_id', $std->id)
                ->first();

            if (!$exists) {
                DB::table('users')->insert([
                    'name' => $std->nama,
                    'username' => $username,
                    'email' => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $username)) . '@siswa.sekolah.id',
                    'password' => Hash::make('siswa123'),
                    'role_id' => 3, // Role Siswa
                    'class_id' => $std->class_id,
                    'student_id' => $std->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        return response()->json([
            'message' => "Proses selesai: {$created} akun login siswa baru berhasil dibuat, {$skipped} siswa sudah memiliki akun sebelumnya. (Password default: siswa123)"
        ], 200);
    }

    // ── Aktifkan Akun Siswa Individu ──────────────────────────────
    public function activateSingleSiswa(Request $request)
    {
        $request->validate([
            'student_id' => 'required|integer|exists:students,id'
        ]);

        $student = DB::table('students')->where('id', $request->student_id)->first();
        if (!$student) {
            return response()->json(['message' => 'Data siswa tidak ditemukan.'], 404);
        }

        $username = !empty($student->nisn) ? trim($student->nisn) : trim($student->nis);
        if (empty($username)) {
            return response()->json(['message' => 'Siswa belum memiliki NISN atau NIS untuk username login.'], 422);
        }

        $exists = DB::table('users')
            ->where('student_id', $student->id)
            ->orWhere('username', $username)
            ->first();

        if ($exists) {
            DB::table('users')->where('id', $exists->id)->update([
                'student_id' => $student->id,
                'class_id' => $student->class_id,
                'role_id' => 3,
                'updated_at' => now(),
            ]);
            return response()->json(['message' => "Akun login untuk {$student->nama} sudah aktif dan telah disinkronkan."], 200);
        }

        $cleanUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $username));
        $email = $cleanUsername . '@siswa.sekolah.id';

        DB::table('users')->insert([
            'name' => $student->nama,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make('siswa123'),
            'role_id' => 3,
            'class_id' => $student->class_id,
            'student_id' => $student->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => "Akun login untuk {$student->nama} berhasil diaktifkan! Username: {$username}, Password: siswa123"
        ], 201);
    }

    // ── Download Template Excel Guru & Walas (Dengan Dropdown Kelas) ──
    public function downloadTemplate()
    {
        $classes = DB::table('class')->orderBy('class_name', 'asc')->pluck('class_name');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ── Sheet 1: Data Guru & Walas ──
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data_Guru_Walas');

        // Headers
        $headers = [
            'A1' => 'No',
            'B1' => 'Username',
            'C1' => 'Nama Lengkap',
            'D1' => 'NIP',
            'E1' => 'Email',
            'F1' => 'Wali Kelas (Pilih Dropdown)',
            'G1' => 'Role (Pilih Dropdown)',
            'H1' => 'Password (Default: guru123)',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style Header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F766E'], // Teal modern
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '0D5C56'],
                ],
            ],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ── Sheet 2: Referensi Daftar Kelas untuk Dropdown ──
        $classSheet = $spreadsheet->createSheet();
        $classSheet->setTitle('Daftar_Kelas');
        $classSheet->setCellValue('A1', 'Daftar Kelas Tersedia');
        $classSheet->setCellValue('A2', '- (Bukan Walas)');

        $r = 3;
        foreach ($classes as $cName) {
            $classSheet->setCellValue("A{$r}", $cName);
            $r++;
        }
        $lastClassRow = max(2, $r - 1);

        $classSheet->getStyle('A1')->getFont()->setBold(true);
        $classSheet->getColumnDimension('A')->setAutoSize(true);

        // Contoh Data (Baris 2 & 3)
        $sampleRows = [
            [
                'no' => 1,
                'username' => 'walikelas1',
                'nama' => 'Wali Kelas Satu, S.Pd',
                'nip' => '198501012010011001',
                'email' => 'walikelas1@icb.sch.id',
                'walas' => $classes->first() ?? '- (Bukan Walas)',
                'role' => 'Guru',
                'password' => 'guru123',
            ],
            [
                'no' => 2,
                'username' => 'guru_matematika',
                'nama' => 'Guru Matematika, M.Pd',
                'nip' => '199002152015022002',
                'email' => 'gurumtk@icb.sch.id',
                'walas' => '- (Bukan Walas)',
                'role' => 'Guru',
                'password' => 'guru123',
            ]
        ];

        $rowIdx = 2;
        foreach ($sampleRows as $row) {
            $sheet->setCellValue("A{$rowIdx}", $row['no']);
            $sheet->setCellValue("B{$rowIdx}", $row['username']);
            $sheet->setCellValue("C{$rowIdx}", $row['nama']);
            $sheet->setCellValue("D{$rowIdx}", $row['nip']);
            $sheet->setCellValue("E{$rowIdx}", $row['email']);
            $sheet->setCellValue("F{$rowIdx}", $row['walas']);
            $sheet->setCellValue("G{$rowIdx}", $row['role']);
            $sheet->setCellValue("H{$rowIdx}", $row['password']);
            $rowIdx++;
        }

        // Terapkan Data Validation (Dropdown) dari baris 2 hingga 200
        for ($i = 2; $i <= 200; $i++) {
            // Dropdown Kolom F (Wali Kelas)
            $validation = $sheet->getCell("F{$i}")->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Pilihan Tidak Valid');
            $validation->setError('Silakan pilih salah satu kelas dari daftar yang tersedia.');
            $validation->setPromptTitle('Pilih Wali Kelas');
            $validation->setPrompt('Pilih kelas binaan guru ini, atau pilih "- (Bukan Walas)".');
            $validation->setFormula1("Daftar_Kelas!\$A\$2:\$A\${$lastClassRow}");

            // Dropdown Kolom G (Role)
            $roleVal = $sheet->getCell("G{$i}")->getDataValidation();
            $roleVal->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $roleVal->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $roleVal->setAllowBlank(false);
            $roleVal->setShowDropDown(true);
            $roleVal->setErrorTitle('Role Tidak Valid');
            $roleVal->setError('Pilih Guru atau Admin.');
            $roleVal->setFormula1('"Guru,Admin"');
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(24);
        $sheet->getColumnDimension('E')->setWidth(28);
        $sheet->getColumnDimension('F')->setWidth(26);
        $sheet->getColumnDimension('G')->setWidth(16);
        $sheet->getColumnDimension('H')->setWidth(25);

        // Aktifkan kembali Sheet 1 sebagai sheet aktif saat file dibuka
        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Template_Import_Guru_Walas.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    // ── Import Akun Guru & Penugasan Wali Kelas dari Excel ─────────
    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [
            'file.required' => 'Berkas Excel wajib diunggah.',
            'file.mimes' => 'Berkas harus berupa file Excel (.xlsx atau .xls).',
            'file.max' => 'Ukuran berkas maksimal 5MB.',
        ]);

        try {
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getSheet(0); // Ambil sheet pertama (Data_Guru_Walas)
            $rows = $sheet->toArray(null, true, true, false);

            if (count($rows) <= 1) {
                return response()->json([
                    'message' => 'Berkas Excel kosong atau tidak memiliki baris data.'
                ], 422);
            }

            // Peta nama kelas ke ID
            $classesMap = DB::table('class')->pluck('id', 'class_name')->toArray();

            $inserted = 0;
            $updated = 0;
            $skipped = 0;
            $errors = [];

            // Mulai dari baris ke-2 (index 1 karena index 0 adalah header)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $rowNum = $i + 1;

                $username = isset($row[1]) ? trim((string)$row[1]) : '';
                $nama     = isset($row[2]) ? trim((string)$row[2]) : '';
                $nip      = isset($row[3]) ? trim((string)$row[3]) : '';
                $email    = isset($row[4]) ? trim((string)$row[4]) : '';
                $walasStr = isset($row[5]) ? trim((string)$row[5]) : '';
                $roleStr  = isset($row[6]) ? trim((string)$row[6]) : '';
                $password = isset($row[7]) ? trim((string)$row[7]) : '';

                // Lewati baris kosong
                if (empty($username) && empty($nama) && empty($email)) {
                    continue;
                }

                if (empty($username) || empty($nama)) {
                    $errors[] = "Baris {$rowNum}: Username dan Nama Lengkap wajib diisi.";
                    $skipped++;
                    continue;
                }

                // Jika email kosong, buatkan format default dari username
                if (empty($email)) {
                    $cleanU = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $username));
                    $email = $cleanU . '@sekolah.id';
                }

                // Resolusi Kelas Binaan Wali Kelas
                $classId = null;
                if (!empty($walasStr) && $walasStr !== '-' && !str_contains(strtolower($walasStr), 'bukan')) {
                    foreach ($classesMap as $cName => $cId) {
                        if (strcasecmp(trim($cName), $walasStr) === 0) {
                            $classId = $cId;
                            break;
                        }
                    }
                }

                // Resolusi Role
                // Jika dijadikan wali kelas, otomatis minimal role Guru (2)
                $roleId = 2;
                if (strcasecmp($roleStr, 'admin') === 0 && $classId === null) {
                    $roleId = 1;
                }

                // Resolusi Password
                $rawPass = !empty($password) ? $password : 'guru123';

                // Cek apakah user sudah ada berdasarkan username atau email
                $existing = DB::table('users')
                    ->where('username', $username)
                    ->orWhere('email', $email)
                    ->first();

                if ($existing) {
                    $updatePayload = [
                        'name' => $nama,
                        'nip' => !empty($nip) ? $nip : null,
                        'email' => $email,
                        'role_id' => $roleId,
                        'class_id' => $classId,
                        'updated_at' => now(),
                    ];

                    if (!empty($password)) {
                        $updatePayload['password'] = Hash::make($rawPass);
                    }

                    // Jika kelas ditetapkan, lepaskan kelas dari guru lain agar tidak tumpang tindih
                    if ($classId !== null) {
                        DB::table('users')->where('class_id', $classId)->where('id', '!=', $existing->id)->update(['class_id' => null]);
                    }

                    DB::table('users')->where('id', $existing->id)->update($updatePayload);
                    $updated++;
                } else {
                    $newId = DB::table('users')->insertGetId([
                        'username' => $username,
                        'name' => $nama,
                        'nip' => !empty($nip) ? $nip : null,
                        'email' => $email,
                        'password' => Hash::make($rawPass),
                        'role_id' => $roleId,
                        'class_id' => $classId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if ($classId !== null) {
                        DB::table('users')->where('class_id', $classId)->where('id', '!=', $newId)->update(['class_id' => null]);
                    }

                    $inserted++;
                }
            }

            \App\Services\MasterDataCache::clearDashboardCache();

            $msg = "Import berhasil! {$inserted} guru/walas baru ditambahkan, {$updated} data diperbarui.";
            if ($skipped > 0) {
                $msg .= " ({$skipped} baris dilewati karena data tidak lengkap).";
            }

            return response()->json([
                'message' => $msg,
                'inserted' => $inserted,
                'updated' => $updated,
                'skipped' => $skipped,
                'errors' => $errors,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses berkas Excel: ' . $e->getMessage()
            ], 500);
        }
    }
}
