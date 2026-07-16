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
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        $studentsPerClass = DB::table('class')
            ->leftJoin('students', 'students.class_id', '=', 'class.id')
            ->select('class.class_name', DB::raw('count(students.id) as student_count'))
            ->groupBy('class.id', 'class.class_name')
            ->get();
        $classNames = $studentsPerClass->pluck('class_name');
        $studentCounts = $studentsPerClass->pluck('student_count');
        $classId = Auth::user()->class_id;
        $roleId = Auth::user()->role_id;

        if ($classId !== null) {
            $allstudentCounts = DB::table('students')->where('class_id', $classId)->count();
        } else {
            $allstudentCounts = DB::table('students')->count();
        }
        $allMapelCounts = DB::table('mata_pelajarans')->count();

        // Analytics: Top 5 Siswa dengan Rata-rata Tertinggi
        $whereClause = ($roleId != 1 && $classId !== null) ? "WHERE s.class_id = " . intval($classId) : "";
        $topStudentsQuery = "
            SELECT 
                s.nama AS student_name, 
                c.class_name,
                ROUND(AVG((COALESCE(v.value_daily, 0) + COALESCE(v.value_daily_2, 0) + COALESCE(v.value_daily_3, 0) + COALESCE(v.value_daily_4, 0) + COALESCE(v.value_daily_5, 0) + COALESCE(v.value_daily_6, 0) + COALESCE(v.value_daily_7, 0) + COALESCE(v.value_daily_8, 0) + COALESCE(v.value_daily_9, 0) + COALESCE(v.value_daily_10, 0) + COALESCE(v.value_sts, 0) + COALESCE(v.value_sas, 0)) / 12), 2) AS average_score
            FROM students AS s
            JOIN class AS c ON s.class_id = c.id
            LEFT JOIN `values` AS v ON s.id = v.student_id
            $whereClause
            GROUP BY s.id, s.nama, c.class_name
            ORDER BY average_score DESC
            LIMIT 5
        ";
        $topStudents = DB::select($topStudentsQuery);

        return view('home', compact('classNames', 'studentCounts', 'allstudentCounts', 'allMapelCounts', 'topStudents'));
    }
}
