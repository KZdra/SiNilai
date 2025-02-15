<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        return view('home',compact('classNames','studentCounts'));
    }
}
