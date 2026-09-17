<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PanduanController extends Controller
{
    public function index()
    {
        $sekolah = DB::table('data_sekolah')->first();
        $totalKelas = DB::table('class')->count();
        $totalSiswa = DB::table('students')->count();
        $totalMapel = DB::table('mata_pelajarans')->count();
        $activeFst = DB::table('m_fst_pembelajaran')->where('is_locked', 0)->first();

        return view('panduan.index', compact('sekolah', 'totalKelas', 'totalSiswa', 'totalMapel', 'activeFst'));
    }
}
