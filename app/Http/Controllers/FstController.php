<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FstController extends Controller
{
    public function index()
    {
        return view('mfst.index');
    }
    public function getData(Request $request)
    {
        $query = DB::table('m_fst_pembelajaran')->select('id', 'fase', 'semester', 'tahun_ajaran','ta', 'is_locked', 'locked_at');
        if (!$request->has('order')) {
            $query->orderBy('id', 'asc');
        }
        $searchableColumns = ['fase', 'semester', 'tahun_ajaran', 'ta'];
        $orderableColumns = [
            1 => 'fase',
            2 => 'semester',
            3 => 'tahun_ajaran',
            4 => 'ta',
            5 => 'is_locked',
        ];
        return \App\Services\DataTableHelper::process($query, $request, $searchableColumns, $orderableColumns, 'id');
    }
    public function store(Request $request)
    {
        $bagong = $request->validate([
            'fase' => 'required|string',
            'semester' => 'required|string',
            'tahun_ajaran' => 'required|string',
            'ta'=>'required|string'
        ]);
        try {
            DB::table('m_fst_pembelajaran')->insert([
                'fase' => ucwords($bagong['fase']),
                'semester' => $bagong['semester'],
                'tahun_ajaran' => $bagong['tahun_ajaran'],
                'ta' => $bagong['ta'],
                'created_at' => Carbon::now()
            ]);
            \App\Services\MasterDataCache::clearFst();
            return response()->json(['message' => 'Data berhasil ditambahkan!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $bagong = $request->validate([
            'fase' => 'required|string',
            'semester' => 'required|string',
            'tahun_ajaran' => 'required|string',
            'ta' => 'required|string'
        ]);
        try {
            DB::table('m_fst_pembelajaran')->where('id', $id)->update([
                'fase' => $bagong['fase'],
                'semester' => $bagong['semester'],
                'tahun_ajaran' => $bagong['tahun_ajaran'],
                'ta' => $bagong['ta'],
                'updated_at' => Carbon::now()
            ]);
            \App\Services\MasterDataCache::clearFst($id);
            return response()->json(['message' => 'Data berhasil diUpdate!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function destroy(Request $r, $id)
    {
        try {
            DB::table('m_fst_pembelajaran')->where('id', '=', $id)->delete();
            \App\Services\MasterDataCache::clearFst($id);
            return response()->json(['message' => 'Data berhasil di Hapus!'], 201);
        } catch (\Exception $e) {

            return response()->json(['message' => $e->getMessage()], 201);
            // return response()->json(['message' => 'Terjadi Kesalahan Input atau Sistem!'], 201);
        }
    }

    /**
     * Toggle lock status for a semester (Admin only).
     */
    public function toggleLock(Request $request, $id)
    {
        if (Auth::user()->role_id != 1) {
            return response()->json(['message' => 'Hanya Admin yang dapat mengunci / membuka semester.'], 403);
        }

        $fst = DB::table('m_fst_pembelajaran')->where('id', $id)->first();
        if (!$fst) {
            return response()->json(['message' => 'Data semester tidak ditemukan.'], 404);
        }

        $newLock = !$fst->is_locked;
        DB::table('m_fst_pembelajaran')->where('id', $id)->update([
            'is_locked'  => $newLock,
            'locked_at'  => $newLock ? Carbon::now() : null,
            'locked_by'  => $newLock ? Auth::id() : null,
            'updated_at' => Carbon::now(),
        ]);
        \App\Services\MasterDataCache::clearFst($id);

        $statusText = $newLock ? 'dikunci (Read-Only)' : 'dibuka kembali';
        return response()->json([
            'status'    => 'success',
            'message'   => "Semester {$fst->tahun_ajaran} berhasil {$statusText}!",
            'is_locked' => $newLock,
        ]);
    }
}
