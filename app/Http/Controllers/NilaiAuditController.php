<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NilaiAuditController extends Controller
{
    /**
     * Tampilkan Halaman Audit Log Perubahan Nilai
     */
    public function index()
    {
        $mapelList = DB::table('mata_pelajarans')->select('id', 'nama_mapel')->orderBy('nama_mapel')->get();
        return view('nilai.audit_logs', compact('mapelList'));
    }

    public function getData(Request $request)
    {
        $query = DB::table('nilai_audit_logs as a')
            ->leftJoin('users as u', 'a.user_id', '=', 'u.id')
            ->leftJoin('students as s', 'a.student_id', '=', 's.id')
            ->leftJoin('mata_pelajarans as m', 'a.mapel_id', '=', 'm.id')
            ->leftJoin('m_fst_pembelajaran as f', 'a.fst_id', '=', 'f.id')
            ->select(
                'a.*',
                'u.name as user_name',
                's.nama as student_name',
                's.nis as student_nis',
                'm.nama_mapel',
                'f.fase',
                'f.semester',
                'f.tahun_ajaran'
            );

        if ($request->filled('action')) {
            $query->where('a.action', $request->action);
        }
        if ($request->filled('mapel_id')) {
            $query->where('a.mapel_id', $request->mapel_id);
        }
        if ($request->filled('keyword')) {
            $kw = $request->keyword;
            $query->where(function ($q) use ($kw) {
                $q->where('s.nama', 'like', "%{$kw}%")
                  ->orWhere('s.nis', 'like', "%{$kw}%")
                  ->orWhere('u.name', 'like', "%{$kw}%");
            });
        }
        if ($request->filled('start_date')) {
            $query->whereDate('a.created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('a.created_at', '<=', $request->end_date);
        }

        $searchableColumns = [
            's.nama',
            's.nis',
            'u.name',
            'm.nama_mapel',
            'a.action',
        ];

        $orderableColumns = [
            1 => 'a.created_at',
            2 => 'u.name',
            3 => 'a.action',
            4 => 's.nama',
            5 => 'm.nama_mapel',
            6 => 'f.fase',
        ];

        if (!$request->has('order')) {
            $query->orderBy('a.id', 'desc');
        }

        return \App\Services\DataTableHelper::process($query, $request, $searchableColumns, $orderableColumns, 'a.id');
    }
}
