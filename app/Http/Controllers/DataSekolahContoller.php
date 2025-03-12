<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataSekolahContoller extends Controller
{
    public function index(){
        $sekolah = DB::table('data_sekolah')->select(
            'id',
            'nama_sekolah',
            'npsn',
            'nss',
            'alamat_sekolah',
            'kode_pos',
            'desa_kelurahan',
            'kecamatan',
            'kabupaten_kota',
            'provinsi',
            'website',
            'email',
            'nama_kepala_sekolah',
            'nip_kepala_sekolah',
        )->first();
        return view('mdsekolah.index',compact('sekolah'));
    }
    public function store(Request $r)
{
    $id = 1;
    try {
        DB::table('data_sekolah')->updateOrInsert(
            ['id' => $r->id ? $r->id : $id],
            [
                'nama_sekolah' => $r->nama_sekolah,
                'npsn' => $r->npsn,
                'nss' => $r->nss,
                'alamat_sekolah' => $r->alamat_sekolah,
                'kode_pos' => $r->kode_pos,
                'desa_kelurahan' => $r->desa_kelurahan,
                'kecamatan' => $r->kecamatan,
                'kabupaten_kota' => $r->kabupaten_kota,
                'provinsi' => $r->provinsi,
                'website' => $r->website,
                'email' => $r->email,
                'nama_kepala_sekolah' => $r->nama_kepala_sekolah,
                'nip_kepala_sekolah' => $r->nip_kepala_sekolah === '' ? null : $r->nip_kepala_sekolah,
            ]
        );
        return response()->json(['message' => 'Data Sekolah Berhasil Di Atur!'], 201);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Terjadi Kesalahan Server!'], 500);
    }
}

    public function destroy(Request $r ,$id){
        try {
            DB::table('data_sekolah')->where('id',$id)->delete();
            return response()->json(['message' => 'Data Sekolah Berhasil Di Hapus!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
