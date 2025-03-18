<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FstController extends Controller
{
    public function index()
    {
        return view('mfst.index');
    }
    public function getData(Request $request)
    {
        $data = DB::table('m_fst_pembelajaran')->select('id', 'fase', 'semester', 'tahun_ajaran','ta')->get();
        return response()->json(['data' => $data], 200);
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
                'fase' => $bagong['fase'],
                'semester' => $bagong['semester'],
                'tahun_ajaran' => $bagong['tahun_ajaran'],
                'ta' => $bagong['ta'],
                'created_at' => Carbon::now()
            ]);
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
            'tahun_ajaran' => 'required|string'
        ]);
        try {
            DB::table('m_fst_pembelajaran')->where('id', $id)->update([
                'fase' => $bagong['fase'],
                'semester' => $bagong['semester'],
                'tahun_ajaran' => $bagong['tahun_ajaran'],
                'ta' => $bagong['ta'],
                'updated_at' => Carbon::now()
            ]);
            return response()->json(['message' => 'Data berhasil diUpdate!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function destroy(Request $r, $id)
    {
        try {
            DB::table('m_fst_pembelajaran')->where('id', '=', $id)->delete();
            return response()->json(['message' => 'Data berhasil di Hapus!'], 201);
        } catch (\Exception $e) {

            return response()->json(['message' => $e->getMessage()], 201);
            // return response()->json(['message' => 'Terjadi Kesalahan Input atau Sistem!'], 201);
        }
    }
}
