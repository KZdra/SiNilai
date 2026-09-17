<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapelController extends Controller
{
    public function index()
    {
        return view('mmapel.index');
    }
    // Api Sections
    public function getAll(Request $request)
    {
        $query = DB::table('mata_pelajarans')->select('id', 'nama_mapel');
        if (!$request->has('order')) {
            $query->orderBy('nama_mapel', 'asc');
        }
        return \App\Services\DataTableHelper::process($query, $request, ['nama_mapel'], [1 => 'nama_mapel'], 'id');
    }
    public function store(Request $request)
    {

        $request->validate([
            'nama_mapel' => 'required|string',
        ]);

        try {
            DB::table('mata_pelajarans')->insert([
                'nama_mapel' => $request->nama_mapel,
                'created_at' => Carbon::now()
            ]);
            return response()->json(['message' => 'Mapel berhasil ditambahkan!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {

        $request->validate([
            'nama_mapel' => 'required|string',
        ]);

        try {
            DB::table('mata_pelajarans')->where('id', $id)->update([
                'nama_mapel' => $request->nama_mapel,
                'updated_at' => Carbon::now()
            ]);
            return response()->json(['message' => 'Mapel berhasil diEdit!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function destroy($id)
    {
        try {
            $mapel = DB::table('mata_pelajarans')->where('id', $id)->first();
            if (!$mapel) {
                return response()->json(['message' => 'Data mata pelajaran tidak ditemukan!'], 404);
            }

            // 1. Cek apakah mata pelajaran ini sedang dipetakan di Mapping Mapel
            if (DB::table('mapel_class_fst')->where('mapel_id', $id)->exists()) {
                return response()->json([
                    'message' => "Mata pelajaran '{$mapel->nama_mapel}' tidak dapat dihapus karena masih aktif pada menu Mapping Mapel."
                ], 422);
            }

            // 2. Cek apakah ada riwayat nilai siswa untuk mapel ini
            if (DB::table('values')->where('mapel_id', $id)->exists()) {
                return response()->json([
                    'message' => "Mata pelajaran '{$mapel->nama_mapel}' tidak dapat dihapus karena memiliki riwayat nilai siswa."
                ], 422);
            }

            // 3. Cek apakah ada Tujuan Pembelajaran (TP) yang terdaftar
            if (DB::table('m_tp')->where('mapel_id', $id)->exists() || DB::table('tpsiswas')->where('mapel_id', $id)->exists()) {
                return response()->json([
                    'message' => "Mata pelajaran '{$mapel->nama_mapel}' tidak dapat dihapus karena masih terhubung dengan data Tujuan Pembelajaran (TP)."
                ], 422);
            }

            DB::table('mata_pelajarans')->where('id', '=', $id)->delete();
            return response()->json(['message' => "Mata pelajaran '{$mapel->nama_mapel}' berhasil dihapus!"], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
