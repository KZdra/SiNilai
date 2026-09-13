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

        return view('home', compact('classNames', 'studentCounts', 'allstudentCounts', 'allMapelCounts', 'topStudents'));
    }
}
