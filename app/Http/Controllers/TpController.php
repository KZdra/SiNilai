<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TpController extends Controller
{
    // Master Sections
    public function index()
    {
        $classList = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc')->get();
        $mapelList = DB::table('mata_pelajarans')->select('id', 'nama_mapel')->orderBy('id', 'asc')->get();
        $fstList = DB::table('m_fst_pembelajaran')->select('id', 'fase','semester','tahun_ajaran')->orderBy('id', 'asc')->get();
        return view('mtp.index', compact('mapelList','classList','fstList'));
    }
    public function getdata(Request $r)
    {
        $data = DB::table('m_tp as tp')->select(
            'tp.id',
            'tp.mapel_id',
            'tp.class_id',
            'tp.fst_id',
            'tp.tp_deskripsi'
        )->join('mata_pelajarans as mp', 'tp.mapel_id', '=', 'mp.id')
            ->where('tp.mapel_id', $r->mapel_id)->where('tp.class_id',$r->class_id)->where('tp.fst_id',$r->fst_id)->orderBy('tp.id','asc')->get();
        return response()->json(['data' => $data], 200);
    }
    public function store(Request $r)
    {
        $sangu = $r->validate([
            'mapel_id' => 'required|integer',
            'class_id' => 'required|integer',
            'fst_id' => 'required|integer',
            'tp_deskripsi' => 'required|string'
        ]);
        try {
            DB::table('m_tp')->insert([
                'mapel_id' => $sangu['mapel_id'],
                'class_id' => $sangu['class_id'],
                'fst_id' => $sangu['fst_id'],
                'tp_deskripsi' => $sangu['tp_deskripsi'],
                'created_at' => Carbon::now()

            ]);
            return response()->json(['message' => 'Tujuan Pembelajaran berhasil ditambahkan!'], 201);
        } catch (\Exception $e) {

            return response()->json(['message' => $e->getMessage()], 201);
            // return response()->json(['message' => 'Terjadi Kesalahan Input atau Sistem!'], 201);
        }
    }
    public function update(Request $r, $id)
    {
        $sangu = $r->validate([
            'mapel_id' => 'required|integer',
            'class_id' => 'required|integer',
            'tp_deskripsi' => 'required|string'
        ]);
        try {
            DB::table('m_tp')->where(
                'id',
                '=',
                $id
            )->update([
                'mapel_id' => $sangu['mapel_id'],
                'class_id' => $sangu['class_id'],
                'tp_deskripsi' => $sangu['tp_deskripsi'],
                'updated_at' => Carbon::now()
            ]);
            return response()->json(['message' => 'Tujuan Pembelajaran berhasil diEdit!'], 201);
        } catch (\Exception $e) {

            return response()->json(['message' => $e->getMessage()], 201);
            // return response()->json(['message' => 'Terjadi Kesalahan Input atau Sistem!'], 201);
        }
    }
    public function destroy(Request $r, $id)
    {
        try {
            DB::table('m_tp')->where(
                'id',
                '=',
                $id
            )->delete();
            return response()->json(['message' => 'Tujuan Pembelajaran berhasil di Hapus!'], 201);
        } catch (\Exception $e) {

            return response()->json(['message' => $e->getMessage()], 201);
            // return response()->json(['message' => 'Terjadi Kesalahan Input atau Sistem!'], 201);
        }
    }
    //End Master Sections

    // Begin The BOSS

    //ENd THe boss
}
