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
    public function getAll()
    {
        $data = DB::table('mata_pelajarans')->get();
        return response()->json(['data' => $data], 200);
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
            DB::table('mata_pelajarans')->where('id', '=',$id)->delete();
            return response()->json(['message' => 'Mapel berhasil diHapus!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
