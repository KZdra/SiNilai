<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use App\Http\Controllers\NilaiAkhirController;

class PortalSiswaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dapatkan siswa yang terikat dengan user login
     */
    private function getLoggedInStudent()
    {
        $user = Auth::user();

        // Jika user adalah siswa
        if ($user->student_id) {
            return DB::table('students')->where('id', $user->student_id)->first();
        }

        // Cari berdasarkan NISN atau NIS jika student_id belum diisi di users
        $student = DB::table('students')
            ->where('nisn', $user->username)
            ->orWhere('nis', $user->username)
            ->first();

        return $student;
    }

    /**
     * Dapatkan kelas siswa pada semester/FST tertentu (historical)
     * Mengambil dari catatan_walikelas -> values -> p5_penilaian -> fallback ke students.class_id
     */
    private function getHistoricalClass($studentId, $fstId, $currentClassId)
    {
        $historicalClassId = null;

        if ($fstId) {
            // Cek di catatan walikelas untuk semester ini
            $historicalClassId = DB::table('catatan_walikelas')
                ->where('student_id', $studentId)
                ->where('fst_id', $fstId)
                ->whereNotNull('class_id')
                ->value('class_id');

            // Jika belum ada catatan walikelas, cek dari riwayat nilai mapel
            if (!$historicalClassId) {
                $historicalClassId = DB::table('values')
                    ->where('student_id', $studentId)
                    ->where('fst_id', $fstId)
                    ->whereNotNull('class_id')
                    ->value('class_id');
            }

            // Jika belum ada, cek dari penilaian P5
            if (!$historicalClassId) {
                $historicalClassId = DB::table('p5_penilaian as pp')
                    ->join('p5_projek as pr', 'pp.projek_id', '=', 'pr.id')
                    ->where('pp.student_id', $studentId)
                    ->where('pr.fst_id', $fstId)
                    ->value('pr.class_id');
            }
        }

        $classId = $historicalClassId ?: $currentClassId;
        return DB::table('class')->where('id', $classId)->first();
    }

    /**
     * Dashboard Portal Siswa & Orang Tua
     */
    public function dashboard(Request $request)
    {
        $student = $this->getLoggedInStudent();
        if (!$student) {
            return view('portal.no_student');
        }

        $fstList = DB::table('m_fst_pembelajaran')->orderBy('id', 'asc')->get();
        $activeFst = $request->filled('fst_id')
            ? DB::table('m_fst_pembelajaran')->where('id', $request->fst_id)->first()
            : $fstList->last();

        // Kelas aktif saat ini dan kelas historis untuk semester terpilih
        $currentClass = DB::table('class')->where('id', $student->class_id)->first();
        $class = $this->getHistoricalClass($student->id, $activeFst ? $activeFst->id : null, $student->class_id);

        $walas = $class ? DB::table('users')->where('class_id', $class->id)->where('role_id', 2)->first() : null;

        // Presensi & Catatan Walas
        $catatanWalas = null;
        if ($activeFst) {
            $catatanWalas = DB::table('catatan_walikelas')
                ->where('student_id', $student->id)
                ->where('fst_id', $activeFst->id)
                ->first();
        }

        // Nilai Mapel
        $mapelScores = [];
        $avgScore = 0;
        if ($activeFst) {
            $scores = DB::table('values as v')
                ->join('mata_pelajarans as m', 'v.mapel_id', '=', 'm.id')
                ->where('v.student_id', $student->id)
                ->where('v.fst_id', $activeFst->id)
                ->select('m.nama_mapel', 'v.value_daily', 'v.value_sts', 'v.value_sas')
                ->get();

            $total = 0;
            $count = 0;
            foreach ($scores as $sc) {
                $daily = $sc->value_daily ?: 0;
                $sts = $sc->value_sts ?: 0;
                $sas = $sc->value_sas ?: 0;
                $final = round(($daily + $sts + $sas) / 3, 1);
                $mapelScores[] = [
                    'nama_mapel' => $sc->nama_mapel,
                    'daily' => $sc->value_daily,
                    'sts' => $sc->value_sts,
                    'sas' => $sc->value_sas,
                    'final' => $final,
                ];
                $total += $final;
                $count++;
            }
            $avgScore = $count > 0 ? round($total / $count, 1) : 0;
        }

        // Projek P5 (Mencari projek di kelas historis ataupun yang dinilai untuk siswa ini)
        $p5List = [];
        if ($activeFst) {
            $targetClassId = $class ? $class->id : $student->class_id;
            $p5List = DB::table('p5_projek as p')
                ->where('p.fst_id', $activeFst->id)
                ->where(function ($q) use ($targetClassId, $student) {
                    if ($targetClassId) {
                        $q->where('p.class_id', $targetClassId);
                    }
                    $q->orWhereExists(function ($sub) use ($student) {
                        $sub->select(DB::raw(1))
                            ->from('p5_penilaian as pp')
                            ->whereColumn('pp.projek_id', 'p.id')
                            ->where('pp.student_id', $student->id);
                    });
                })
                ->distinct()
                ->get();
        }

        return view('portal.dashboard', compact(
            'student',
            'class',
            'currentClass',
            'fstList',
            'activeFst',
            'walas',
            'catatanWalas',
            'mapelScores',
            'avgScore',
            'p5List'
        ));
    }

    /**
     * Halaman Transkrip Nilai Akademik Lengkap Siswa
     */
    public function nilai(Request $request)
    {
        $student = $this->getLoggedInStudent();
        if (!$student) {
            return redirect()->route('portal.dashboard');
        }

        $fstList = DB::table('m_fst_pembelajaran')->orderBy('id', 'asc')->get();
        $activeFst = $request->filled('fst_id')
            ? DB::table('m_fst_pembelajaran')->where('id', $request->fst_id)->first()
            : $fstList->last();

        $currentClass = DB::table('class')->where('id', $student->class_id)->first();
        $class = $this->getHistoricalClass($student->id, $activeFst ? $activeFst->id : null, $student->class_id);

        $scores = [];
        if ($activeFst) {
            $scores = DB::table('values as v')
                ->join('mata_pelajarans as m', 'v.mapel_id', '=', 'm.id')
                ->where('v.student_id', $student->id)
                ->where('v.fst_id', $activeFst->id)
                ->select(
                    'm.nama_mapel',
                    'v.value_daily',
                    'v.value_daily_2',
                    'v.value_daily_3',
                    'v.value_daily_4',
                    'v.value_daily_5',
                    'v.value_sts',
                    'v.value_sas'
                )
                ->get();
        }

        // Nilai Eskul
        $eskulScores = [];
        if ($activeFst) {
            $eskulScores = DB::table('nilai_eskuls as ne')
                ->join('m_eskul as me', 'ne.eskul_id', '=', 'me.id')
                ->where('ne.student_id', $student->id)
                ->where('ne.fst_id', $activeFst->id)
                ->select('me.nama_eskul', 'ne.nilai_eskul')
                ->get();
        }

        return view('portal.nilai', compact('student', 'class', 'currentClass', 'fstList', 'activeFst', 'scores', 'eskulScores'));
    }

    /**
     * Download Berkas PDF E-Raport Siswa Langsung
     */
    public function downloadRaport(Request $request)
    {
        $student = $this->getLoggedInStudent();
        if (!$student) {
            abort(403, 'Siswa tidak teridentifikasi.');
        }

        $fstId = $request->input('fst_id');
        $fst = $fstId ? DB::table('m_fst_pembelajaran')->where('id', $fstId)->first() : DB::table('m_fst_pembelajaran')->orderBy('id', 'desc')->first();
        if (!$fst) {
            abort(404, 'Data periode FST pembelajaran tidak ditemukan.');
        }

        $class = $this->getHistoricalClass($student->id, $fst->id, $student->class_id);
        $classId = $class ? $class->id : $student->class_id;

        $tgl_print = $request->input('tgl_print', now()->toDateString());
        $type = $request->input('type', 'all'); // default rapor lengkap (cover + nilai)

        $nilaiController = app(NilaiAkhirController::class);
        $res = $nilaiController->generateSingleRaportPdf($student->id, $classId, $fst->id, $type, $tgl_print, null);

        $pdfPath = $res['pdf_path'];
        $cleanStudentName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $student->nama);
        $filename = "E-Raport_{$cleanStudentName}_{$res['concated']}.pdf";

        $fullPath = Storage::disk('public')->path($pdfPath);
        if (!file_exists($fullPath)) {
            abort(500, 'Berkas PDF gagal dibuat.');
        }

        return response()->download($fullPath, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Halaman Capaian Projek P5 Siswa
     */
    public function p5(Request $request)
    {
        $student = $this->getLoggedInStudent();
        if (!$student) {
            return redirect()->route('portal.dashboard');
        }

        $fstList = DB::table('m_fst_pembelajaran')->orderBy('id', 'asc')->get();
        $activeFst = $request->filled('fst_id')
            ? DB::table('m_fst_pembelajaran')->where('id', $request->fst_id)->first()
            : $fstList->last();

        $currentClass = DB::table('class')->where('id', $student->class_id)->first();
        $class = $this->getHistoricalClass($student->id, $activeFst ? $activeFst->id : null, $student->class_id);

        $projeks = [];
        if ($activeFst) {
            $targetClassId = $class ? $class->id : $student->class_id;
            $projeksRaw = DB::table('p5_projek as p')
                ->where('p.fst_id', $activeFst->id)
                ->where(function ($q) use ($targetClassId, $student) {
                    if ($targetClassId) {
                        $q->where('p.class_id', $targetClassId);
                    }
                    $q->orWhereExists(function ($sub) use ($student) {
                        $sub->select(DB::raw(1))
                            ->from('p5_penilaian as pp')
                            ->whereColumn('pp.projek_id', 'p.id')
                            ->where('pp.student_id', $student->id);
                    });
                })
                ->distinct()
                ->get();

            foreach ($projeksRaw as $prj) {
                $targets = DB::table('p5_projek_subelemen as ps')
                    ->join('p5_subelemen as s', 'ps.subelemen_id', '=', 's.id')
                    ->join('p5_elemen as e', 's.elemen_id', '=', 'e.id')
                    ->join('p5_dimensi as d', 'e.dimensi_id', '=', 'd.id')
                    ->where('ps.projek_id', $prj->id)
                    ->select('s.nama_subelemen', 's.capaian_fase', 'e.nama_elemen', 'd.nama_dimensi', 's.id as sub_id')
                    ->get();

                $ratings = DB::table('p5_penilaian')
                    ->where('projek_id', $prj->id)
                    ->where('student_id', $student->id)
                    ->get()
                    ->keyBy('subelemen_id');

                $catatan = $ratings->first() ? $ratings->first()->catatan_proses : null;

                $projeks[] = [
                    'projek' => $prj,
                    'targets' => $targets,
                    'ratings' => $ratings,
                    'catatan' => $catatan
                ];
            }
        }

        return view('portal.p5', compact('student', 'class', 'currentClass', 'fstList', 'activeFst', 'projeks'));
    }

    /**
     * Generate Massal Akun Login Siswa oleh Admin
     */
    public function generateStudentAccounts()
    {
        if (Auth::user()->role_id != 1) {
            return response()->json(['message' => 'Hanya Admin yang dapat membuat akun siswa.'], 403);
        }

        $students = DB::table('students')->get();
        $count = 0;

        foreach ($students as $std) {
            $username = !empty($std->nisn) ? $std->nisn : $std->nis;
            $exists = DB::table('users')->where('username', $username)->orWhere('student_id', $std->id)->first();

            if (!$exists) {
                DB::table('users')->insert([
                    'name' => $std->nama,
                    'username' => $username,
                    'email' => strtolower(str_replace(' ', '', $username)) . '@siswa.sekolah.id',
                    'password' => Hash::make('siswa123'),
                    'role_id' => 3, // Role Siswa
                    'class_id' => $std->class_id,
                    'student_id' => $std->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
            }
        }

        return response()->json(['message' => "Berhasil men-generate {$count} akun login siswa baru (Default password: siswa123)."]);
    }

    /**
     * Tampilkan halaman ganti password mandiri siswa
     */
    public function password()
    {
        $student = $this->getLoggedInStudent();
        if (!$student) {
            return redirect()->route('portal.dashboard');
        }

        $class = DB::table('class')->where('id', $student->class_id)->first();

        return view('portal.password', compact('student', 'class'));
    }

    /**
     * Proses update password akun siswa
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        // Cek apakah password lama sesuai
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai.'])->withInput();
        }

        // Update password baru
        DB::table('users')->where('id', $user->id)->update([
            'password' => Hash::make($request->password),
            'updated_at' => now(),
        ]);

        return redirect()->route('portal.password')->with('success', 'Password akun Anda berhasil diperbarui! Silakan gunakan password baru untuk login berikutnya.');
    }
}
