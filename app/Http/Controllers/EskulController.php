<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EskulController extends Controller
{
    public function index()
    {

        return view('meskul.index');
    }
    public function getdata()
    {
        $data = DB::table('m_eskul')->get();
        return response()->json(['data' => $data], 200);
    }
    public function store(Request $request)
    {
        $vl = $request->validate([
            'nama_eskul' => 'required|string'
        ]);
        DB::beginTransaction();
        try {
            DB::table('m_eskul')->insert([
                'nama_eskul' => $vl['nama_eskul'],
                'created_at' => now()
            ]);
            DB::commit();
            return response()->json(['message' => 'Eskul berhasil ditambahkan!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request,$id)
    {
        $vl = $request->validate([
            'nama_eskul' => 'required|string'
        ]);
        DB::beginTransaction();
        try {
            DB::table('m_eskul')->where('id',$id)->update([
                'nama_eskul' => $vl['nama_eskul'],
                'created_at' => now()
            ]);
            DB::commit();
            return response()->json(['message' => 'Eskul berhasil diEdit!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function destroy(Request $request,$id)
    {
        DB::beginTransaction();
        try {
            DB::table('m_eskul')->where('id',$id)->delete();
            DB::commit();
            return response()->json(['message' => 'Eskul berhasil diHapus!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
