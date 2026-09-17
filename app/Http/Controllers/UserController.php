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
            DB::raw('COALESCE(u.nip, "-") AS nip')
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
            'c.class_name',
            'r.role_name',
        ];

        $orderableColumns = [
            0 => 'u.username',
            1 => 'u.name',
            2 => 'u.nip',
            3 => 'c.class_name',
            4 => 'r.role_name',
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
            'c.class_name'
        ];

        $orderable = [
            1 => 's.nisn',
            2 => 's.nama',
            3 => 'c.class_name',
            4 => 'is_active',
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
            'email' => 'nullable|email|max:150|unique:users,email',
        ]);

        try {
            DB::table('users')->insert([
                'class_id' => !empty($kont['class_id']) ? $kont['class_id'] : null,
                'role_id' => $kont['role_id'],
                'nip' => !empty($kont['nip']) ? $kont['nip'] : null,
                'name' => $kont['nama'],
                'username' => $kont['username'],
                'password' => Hash::make($kont['password']),
                'email' => !empty($kont['email']) ? $kont['email'] : null,
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
            'email' => 'nullable|email|max:150|unique:users,email,' . $id,
            'password' => 'nullable|min:4',
        ]);

        try {
            $data = [
                'class_id' => $request->filled('class_id') ? $request->class_id : null,
                'role_id' => $validated['role_id'],
                'nip' => !empty($validated['nip']) ? $validated['nip'] : null,
                'name' => $validated['nama'],
                'username' => $validated['username'],
                'email' => !empty($validated['email']) ? $validated['email'] : null,
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
        $request->validate([
            'password' => 'required|min:4',
        ]);

        try {
            DB::table('users')->where('id', $id)->update([
                'password' => Hash::make($request->password),
                'updated_at' => Carbon::now(),
            ]);

            return response()->json(['message' => 'Password berhasil direset!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ── Generate Akun Login Portal Siswa Massal ───────────────────
    public function generateSiswaAccounts(Request $request)
    {
        $classId = $request->input('class_id');
        $query = DB::table('students');
        if (!empty($classId)) {
            $query->where('class_id', $classId);
        }
        $students = $query->get();

        if ($students->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada siswa yang ditemukan untuk dibuatkan akun.'
            ], 404);
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
                    'email' => null,
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

        DB::table('users')->insert([
            'name' => $student->nama,
            'username' => $username,
            'email' => null,
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
    // ── Download Template Excel Guru & Walas (Dengan Dropdown Kelas) ──
    public function downloadTemplate()
    {
        $classes = DB::table('class')->orderBy('class_name', 'asc')->pluck('class_name');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ── Sheet 1: Data Wali Kelas & Guru ──
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data_Wali_Kelas');

        // Headers
        $headers = [
            'A1' => 'No',
            'B1' => 'Nama Lengkap & Gelar',
            'C1' => 'Wali Kelas (Pilih Dropdown)',
            'D1' => 'Password (Opsional: Kosong = Sama dg Username)',
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
        $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);
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
                'nama' => 'Budi Santoso, S.Pd',
                'walas' => $classes->first() ?? '- (Bukan Walas)',
                'password' => '', // Kosong = otomatis username "budi" dan password "budi"
            ],
            [
                'no' => 2,
                'nama' => 'Siti Aminah, M.Pd',
                'walas' => $classes->skip(1)->first() ?? '- (Bukan Walas)',
                'password' => '', // Kosong = otomatis username "siti" dan password "siti"
            ],
            [
                'no' => 3,
                'nama' => 'Ahmad Dahlan, M.Pd',
                'walas' => '- (Bukan Walas)',
                'password' => 'guru123', // Admin menentukan password khusus
            ]
        ];

        $rowIdx = 2;
        foreach ($sampleRows as $row) {
            $sheet->setCellValue("A{$rowIdx}", $row['no']);
            $sheet->setCellValue("B{$rowIdx}", $row['nama']);
            $sheet->setCellValue("C{$rowIdx}", $row['walas']);
            $sheet->setCellValue("D{$rowIdx}", $row['password']);
            $rowIdx++;
        }

        // Terapkan Data Validation (Dropdown) dari baris 2 hingga 200 untuk Kolom C (Wali Kelas)
        for ($i = 2; $i <= 200; $i++) {
            $validation = $sheet->getCell("C{$i}")->getDataValidation();
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
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(35);
        $sheet->getColumnDimension('C')->setWidth(28);
        $sheet->getColumnDimension('D')->setWidth(40);

        // Aktifkan kembali Sheet 1 sebagai sheet aktif saat file dibuka
        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Template_Import_Wali_Kelas.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    // ── Export Excel Kredensial Login Wali Kelas / Guru ───────────
    public function exportKredensialWalas(Request $request)
    {
        $type = $request->input('type', 'walas'); // 'walas' atau 'all'

        $query = DB::table('users as u')
            ->leftJoin('class as c', 'u.class_id', '=', 'c.id')
            ->leftJoin('roles as r', 'u.role_id', '=', 'r.id')
            ->select(
                'u.id',
                'u.name',
                'u.username',
                'u.nip',
                'u.role_id',
                'r.role_name',
                'c.class_name'
            );

        if ($type === 'walas') {
            $query->where('u.role_id', 2)->whereNotNull('u.class_id');
            $docTitle = 'DAFTAR KREDENSIAL LOGIN WALI KELAS';
            $filename = 'Kredensial_Login_Wali_Kelas_' . date('Ymd_His') . '.xlsx';
        } else {
            $query->whereIn('u.role_id', [1, 2]);
            $docTitle = 'DAFTAR KREDENSIAL LOGIN GURU & PENDIDIK';
            $filename = 'Kredensial_Login_Guru_Pendidik_' . date('Ymd_His') . '.xlsx';
        }

        $users = $query->orderBy('c.class_name', 'asc')->orderBy('u.name', 'asc')->get();

        $sekolah = \App\Services\MasterDataCache::getSchoolData();
        $namaSekolah = $sekolah->nama_sekolah ?? 'SiNilai - Raport Digital';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Kredensial_Akun');

        // Setup halaman untuk cetak
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

        // Judul Dokumen
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', $docTitle);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0F766E'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', $namaSekolah . '  |  Portal Login: ' . url('/login'));
        $sheet->getStyle('A2')->getFont()->setSize(10)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('555555'));
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:G3');
        $sheet->setCellValue('A3', 'Dicetak pada: ' . Carbon::now()->isoFormat('D MMMM Y, HH:mm') . ' WIB');
        $sheet->getStyle('A3')->getFont()->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('777777'));
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Header Tabel
        $tableHeaders = [
            'A5' => 'NO',
            'B5' => 'NAMA LENGKAP',
            'C5' => 'NIP',
            'D5' => 'WALI KELAS',
            'E5' => 'USERNAME LOGIN',
            'F5' => 'KATA SANDI AWAL',
            'G5' => 'PARAF / TANDA TERIMA',
        ];

        foreach ($tableHeaders as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0F766E'],
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
        $sheet->getStyle('A5:G5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(26);

        // Isi Data Baris
        $row = 6;
        $no = 1;
        foreach ($users as $u) {
            $walasLabel = !empty($u->class_name) ? $u->class_name : '- (Bukan Walas)';

            $sheet->setCellValue("A{$row}", $no);
            $sheet->setCellValue("B{$row}", $u->name);
            $sheet->setCellValueExplicit("C{$row}", !empty($u->nip) ? (string)$u->nip : '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("D{$row}", $walasLabel);
            $sheet->setCellValueExplicit("E{$row}", (string)$u->username, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("F{$row}", 'Sama dg Username');
            $sheet->setCellValue("G{$row}", '');

            $sheet->getRowDimension($row)->setRowHeight(22);
            $row++;
            $no++;
        }

        $lastRow = max(6, $row - 1);

        // Styling Border & Alignment Baris Data
        $dataBorderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle("A6:G{$lastRow}")->applyFromArray($dataBorderStyle);

        $sheet->getStyle("A6:A{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C6:C{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("D6:D{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E6:E{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F6:F{$lastRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("E6:E{$lastRow}")->getFont()->setBold(true);

        // Lebar Kolom
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(32);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(22);
        $sheet->getColumnDimension('F')->setWidth(22);
        $sheet->getColumnDimension('G')->setWidth(24);

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
            $sheet = $spreadsheet->getSheet(0); // Ambil sheet pertama (Data_Wali_Kelas)
            $rows = $sheet->toArray(null, true, true, false);

            if (count($rows) <= 1) {
                return response()->json([
                    'message' => 'Berkas Excel kosong atau tidak memiliki baris data.'
                ], 422);
            }

            // Cek apakah menggunakan format ringkas (Kolom B adalah Nama Lengkap) atau format lama (Kolom B adalah Username)
            $headerColB = strtolower(trim((string)($rows[0][1] ?? '')));
            $isSimplifiedFormat = str_contains($headerColB, 'nama') || !str_contains($headerColB, 'username');

            // Peta nama kelas ke ID
            $classesMap = DB::table('class')->pluck('id', 'class_name')->toArray();

            $inserted = 0;
            $updated = 0;
            $skipped = 0;
            $errors = [];
            $usedUsernames = [];

            // Mulai dari baris ke-2 (index 1 karena index 0 adalah header)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $rowNum = $i + 1;

                if ($isSimplifiedFormat) {
                    // Format Baru: B = Nama Lengkap, C = Wali Kelas, D = Password
                    $nama     = isset($row[1]) ? trim((string)$row[1]) : '';
                    $walasStr = isset($row[2]) ? trim((string)$row[2]) : '';
                    $password = isset($row[3]) ? trim((string)$row[3]) : '';
                    $nip      = null;
                    $roleStr  = 'Guru';

                    if (empty($nama)) {
                        continue;
                    }

                    // Ekstrak nama depan untuk username (hapus titel gelar di depan jika ada)
                    $cleanedNama = trim(preg_replace('/^(dr\.|dra\.|drs\.|prof\.|ir\.|h\.|hj\.)\s+/i', '', $nama));
                    $words = preg_split('/[\s,\.]+/', $cleanedNama, -1, PREG_SPLIT_NO_EMPTY);
                    $firstName = !empty($words[0]) ? strtolower($words[0]) : 'guru';
                    $baseUsername = preg_replace('/[^a-z0-9]/', '', $firstName);
                    if (empty($baseUsername)) {
                        $baseUsername = 'guru';
                    }

                    // Cek apakah guru dengan nama ini sudah ada di database
                    $existingByName = DB::table('users')->where('name', $nama)->where('role_id', 2)->first();
                    if ($existingByName) {
                        $username = $existingByName->username;
                    } else {
                        // Generate username unik
                        $usernameCandidate = $baseUsername;
                        $counter = 2;
                        while (in_array($usernameCandidate, $usedUsernames) || DB::table('users')->where('username', $usernameCandidate)->exists()) {
                            $usernameCandidate = $baseUsername . $counter;
                            $counter++;
                        }
                        $username = $usernameCandidate;
                    }
                    $usedUsernames[] = $username;

                    $email = null;
                } else {
                    // Format Lama: B = Username, C = Nama Lengkap, D = NIP, E = Email, F = Walas, G = Role, H = Password
                    $username = isset($row[1]) ? trim((string)$row[1]) : '';
                    $nama     = isset($row[2]) ? trim((string)$row[2]) : '';
                    $nip      = isset($row[3]) ? trim((string)$row[3]) : '';
                    $email    = isset($row[4]) ? trim((string)$row[4]) : null;
                    $walasStr = isset($row[5]) ? trim((string)$row[5]) : '';
                    $roleStr  = isset($row[6]) ? trim((string)$row[6]) : '';
                    $password = isset($row[7]) ? trim((string)$row[7]) : '';

                    if (empty($username) && empty($nama)) {
                        continue;
                    }

                    if (empty($username) || empty($nama)) {
                        $errors[] = "Baris {$rowNum}: Username dan Nama Lengkap wajib diisi.";
                        $skipped++;
                        continue;
                    }

                    if (empty($email)) {
                        $email = null;
                    }
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

                // Role jelas guru (role_id = 2)
                $roleId = 2;
                if (isset($roleStr) && strcasecmp($roleStr, 'admin') === 0 && $classId === null) {
                    $roleId = 1;
                }

                // Resolusi Password: jika diisi admin pakai password tsb, jika kosong disamakan dengan username
                $rawPass = !empty($password) ? $password : $username;

                // Cek apakah user sudah ada berdasarkan username
                $existingQuery = DB::table('users')->where('username', $username);
                if (!empty($email)) {
                    $existingQuery->orWhere('email', $email);
                }
                $existing = $existingQuery->first();

                if ($existing) {
                    $updatePayload = [
                        'name' => $nama,
                        'nip' => !empty($nip) ? $nip : $existing->nip,
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
