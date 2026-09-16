<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        if (Auth::user()->role_id == 3) {
            return redirect()->route('portal.dashboard');
        }

        $classId = Auth::user()->class_id;
        $roleId = Auth::user()->role_id;
        $ver = \App\Services\MasterDataCache::getDashboardVersion();

        // Cache ringkasan statistik siswa & mapel (otomatis terhapus saat data diupdate)
        $stats = \Illuminate\Support\Facades\Cache::remember("dashboard_stats_{$ver}_{$classId}", 120, function () use ($classId) {
            $studentsPerClass = DB::table('class')
                ->leftJoin('students', 'students.class_id', '=', 'class.id')
                ->select('class.class_name', DB::raw('count(students.id) as student_count'))
                ->groupBy('class.id', 'class.class_name')
                ->get();

            $allStudents = ($classId !== null) 
                ? DB::table('students')->where('class_id', $classId)->count() 
                : DB::table('students')->count();

            $allMapels = DB::table('mata_pelajarans')->count();

            return [
                'classNames' => $studentsPerClass->pluck('class_name'),
                'studentCounts' => $studentsPerClass->pluck('student_count'),
                'allstudentCounts' => $allStudents,
                'allMapelCounts' => $allMapels,
            ];
        });

        $classNames = $stats['classNames'];
        $studentCounts = $stats['studentCounts'];
        $allstudentCounts = $stats['allstudentCounts'];
        $allMapelCounts = $stats['allMapelCounts'];

        // Cache Analytics: Top 5 Siswa dengan Rata-rata Tertinggi (otomatis terhapus saat nilai diupdate)
        $topStudents = \Illuminate\Support\Facades\Cache::remember("dashboard_top5_{$ver}_{$roleId}_{$classId}", 120, function () use ($roleId, $classId) {
            $whereClause = ($roleId != 1 && $classId !== null) ? "WHERE s.class_id = " . intval($classId) : "";
            $topStudentsQuery = "
                SELECT 
                    s.nama AS student_name, 
                    c.class_name,
                    ROUND(COALESCE(AVG(
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
            ), 0), 2) AS average_score
                FROM students AS s
                JOIN class AS c ON s.class_id = c.id
                LEFT JOIN `values` AS v ON s.id = v.student_id
                $whereClause
                GROUP BY s.id, s.nama, c.class_name
                ORDER BY average_score DESC
                LIMIT 5
            ";
            return DB::select($topStudentsQuery);
        });

        // Master Data Setup Readiness Checklist (untuk admin saat baru deploy)
        $sekolah = DB::table('data_sekolah')->first();
        $isSekolahComplete = !empty($sekolah) && !empty($sekolah->nama_sekolah) && !empty($sekolah->nama_kepala_sekolah);

        $fstCount = DB::table('m_fst_pembelajaran')->count();
        $classCount = DB::table('class')->count();
        $studentCount = DB::table('students')->count();
        $mapelCount = DB::table('mata_pelajarans')->count();
        $mappingCount = DB::table('mapel_class_fst')->where('is_active', 1)->count();
        $walasCount = DB::table('users')->where('role_id', 2)->whereNotNull('class_id')->count();
        $tpCount = DB::table('m_tp')->count();

        $masterChecklist = [
            [
                'title' => 'Profil & Identitas Sekolah',
                'desc' => $isSekolahComplete ? ($sekolah->nama_sekolah ?? 'Identitas sekolah lengkap') : 'Nama sekolah & Kepala Sekolah belum diisi',
                'is_filled' => $isSekolahComplete,
                'count_label' => $isSekolahComplete ? 'Lengkap' : 'Belum Diisi',
                'route' => route('datasekolah.index'),
                'icon' => 'fas fa-school text-primary',
            ],
            [
                'title' => 'Tahun Ajaran & Semester (FST)',
                'desc' => $fstCount > 0 ? "{$fstCount} Periode FST terdaftar" : 'Periode semester / tahun ajaran aktif belum dibuat',
                'is_filled' => $fstCount > 0,
                'count_label' => $fstCount > 0 ? "{$fstCount} Periode" : 'Belum Diisi',
                'route' => route('mfst.index'),
                'icon' => 'fas fa-calendar-alt text-info',
            ],
            [
                'title' => 'Data Rombongan Belajar (Kelas)',
                'desc' => $classCount > 0 ? "{$classCount} Kelas telah dibuat" : 'Belum ada data kelas yang didaftarkan',
                'is_filled' => $classCount > 0,
                'count_label' => $classCount > 0 ? "{$classCount} Kelas" : 'Belum Diisi',
                'route' => route('class.index'),
                'icon' => 'fas fa-chalkboard text-warning',
            ],
            [
                'title' => 'Data Siswa',
                'desc' => $studentCount > 0 ? "{$studentCount} Siswa terdaftar" : 'Belum ada data siswa diinput atau diimpor',
                'is_filled' => $studentCount > 0,
                'count_label' => $studentCount > 0 ? "{$studentCount} Siswa" : 'Belum Diisi',
                'route' => route('student.index'),
                'icon' => 'fas fa-user-graduate text-success',
            ],
            [
                'title' => 'Mata Pelajaran',
                'desc' => $mapelCount > 0 ? "{$mapelCount} Mata pelajaran terdaftar" : 'Belum ada mata pelajaran yang diinput',
                'is_filled' => $mapelCount > 0,
                'count_label' => $mapelCount > 0 ? "{$mapelCount} Mapel" : 'Belum Diisi',
                'route' => route('mapel.index'),
                'icon' => 'fas fa-book text-secondary',
            ],
            [
                'title' => 'Mapping Mapel ke Kelas',
                'desc' => $mappingCount > 0 ? "{$mappingCount} Relasi mapel diaktifkan di kelas" : 'Mata pelajaran belum dihubungkan ke kelas',
                'is_filled' => $mappingCount > 0,
                'count_label' => $mappingCount > 0 ? "{$mappingCount} Aktif" : 'Belum Diisi',
                'route' => route('mapel_mapping.index'),
                'icon' => 'fas fa-network-wired text-purple',
            ],
            [
                'title' => 'Penugasan Wali Kelas',
                'desc' => $walasCount > 0 ? "{$walasCount} Guru ditugaskan sebagai wali kelas" : 'Belum ada wali kelas yang ditugaskan ke kelas',
                'is_filled' => $walasCount > 0,
                'count_label' => $walasCount > 0 ? "{$walasCount} Walas" : 'Belum Diisi',
                'route' => route('users.index'),
                'icon' => 'fas fa-user-tie text-teal',
            ],
            [
                'title' => 'Tujuan Pembelajaran (TP)',
                'desc' => $tpCount > 0 ? "{$tpCount} TP Kurikulum Merdeka terdaftar" : 'Belum ada rumusan TP untuk asesmen nilai',
                'is_filled' => $tpCount > 0,
                'count_label' => $tpCount > 0 ? "{$tpCount} TP" : 'Belum Diisi',
                'route' => route('mastertp.index'),
                'icon' => 'fas fa-bullseye text-danger',
            ],
        ];

        $filledCount = collect($masterChecklist)->where('is_filled', true)->count();
        $totalMasterItems = count($masterChecklist);
        $unfilledCount = $totalMasterItems - $filledCount;
        $readinessPercent = round(($filledCount / $totalMasterItems) * 100);

        $walasClassName = null;
        if ($classId !== null) {
            $walasClassName = DB::table('class')->where('id', $classId)->value('class_name');
        }

        return view('home', compact(
            'classNames', 'studentCounts', 'allstudentCounts', 'allMapelCounts', 'topStudents',
            'masterChecklist', 'filledCount', 'totalMasterItems', 'unfilledCount', 'readinessPercent',
            'walasClassName'
        ));
    }
}
