<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TpController extends Controller
{
    // Master Sections
    public function index()
    {
        $classList = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc')->get();
        $mapelList = DB::table('mata_pelajarans')->select('id', 'nama_mapel')->orderBy('id', 'asc')->get();
        $fstList = DB::table('m_fst_pembelajaran')->select('id', 'fase', 'semester', 'tahun_ajaran')->orderBy('id', 'asc')->get();
        $className = null;
        if (Auth::user()->class_id !== null) {
            $className = DB::table('class')->where('id', Auth::user()->class_id)->value('class_name');
        }
        return view('mtp.index', compact('mapelList', 'classList', 'fstList', 'className'));
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
            ->where('tp.mapel_id', $r->mapel_id)->where('tp.class_id', $r->class_id)->where('tp.fst_id', $r->fst_id)->orderBy('tp.id', 'asc')->get();
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

            return response()->json(['message' => $e->getMessage()], 500);
            // return response()->json(['message' => 'Terjadi Kesalahan Input atau Sistem!'], 500);
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

            return response()->json(['message' => $e->getMessage()], 500);
            // return response()->json(['message' => 'Terjadi Kesalahan Input atau Sistem!'], 500);
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

            return response()->json(['message' => $e->getMessage()], 500);
            // return response()->json(['message' => 'Terjadi Kesalahan Input atau Sistem!'], 500);
        }
    }
    //End Master Sections
    public function indexFormatif()
    {
        $classList = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc')->get();
        $mapelList = DB::table('mata_pelajarans')->select('id', 'nama_mapel')->orderBy('id', 'asc')->get();
        $fstList = DB::table('m_fst_pembelajaran')->select('id', 'fase', 'semester', 'tahun_ajaran')->orderBy('id', 'asc')->get();
        $className = null;
        if (Auth::user()->class_id !== null) {
            $className = DB::table('class')->where('id', Auth::user()->class_id)->value('class_name');
        }
        return view('formatif.index', compact('mapelList', 'classList', 'fstList', 'className'));
    }
    // Begin The BOSS
    public function getDataFormatif(Request $request)
    {
        //
        $mapel_id = $request->mapel_id;
        $class_id = $request->class_id;
        $fst_id = $request->fst_id;
        //
        $data = DB::table('students as s')
            ->join('class as c', 's.class_id', '=', 'c.id')
            ->leftJoin('tpsiswas as v', function ($join) use ($mapel_id, $fst_id) {
                $join->on('s.id', '=', 'v.siswa_id')
                    ->where('v.mapel_id', $mapel_id)
                    ->where('v.fst_id', $fst_id);
            })
            ->leftJoin('mata_pelajarans as mp', 'v.mapel_id', '=', 'mp.id')
            ->leftJoin('m_tp as tp', 'v.tp_id', '=', 'tp.id')
            ->where('s.class_id', $class_id)
            ->orderBy('s.nama', 'asc')
            ->select(
                's.id as student_id',
                's.nama as student_name',
                'v.id as tps_id',
                'v.tp_id',
                'tp.tp_deskripsi',
                'v.kktp',
                'v.tampilkan',
            )
            ->groupBy('s.id', 's.nama', 'v.id', 'v.tp_id', 'v.kktp', 'v.tampilkan')
            ->get();
        $result = [];

        foreach ($data as $item) {
            $id = $item->student_id;

            if (!isset($result[$id])) {
                $result[$id] = [
                    "student_id" => $item->student_id,
                    "student_name" => $item->student_name,
                    "tp_isFill"=>false,
                    "Hasil_Tp_tinggi" => [],
                    "Hasil_Tp_kurang" => [],
                ];
            }

            $result[$id]["idtp" . $item->tp_id] = $item->tp_id;
            $result[$id]["idtps" . $item->tps_id] = $item->tps_id;
            $result[$id]["kktp_tp" . $item->tp_id] = $item->kktp;
            $result[$id]["tampilkan_tp" . $item->tp_id] = $item->tampilkan;
            $result[$id]["tp_deskripsi" . $item->tp_id] = $item->tp_deskripsi;

            // Tambahkan ke Hasil_Tp_tinggi jika kktp = 1 dan tampilkan = 1
            if ($item->kktp == 1 && $item->tampilkan == 1) {
                $result[$id]["tp_isFill"] = true;
                $result[$id]["Hasil_Tp_tinggi"][] = $item->tp_deskripsi;
            }

            // Tambahkan ke Hasil_Tp_kurang jika kktp = 0 dan tampilkan = 1
            if ($item->kktp == 0 && $item->tampilkan == 1) {
                $result[$id]["tp_isFill"] = true;
                $result[$id]["Hasil_Tp_kurang"][] = $item->tp_deskripsi;
            }
        }

        // Ubah array hasil tinggi dan kurang menjadi string dipisahkan koma
        foreach ($result as &$student) {
            $student["tp_isFill"]= $student["tp_isFill"];
            $student["Hasil_Tp_tinggi"] = $student["student_name"] . " Menunjukan Pemahaman Dalam " . implode(", ", $student["Hasil_Tp_tinggi"]);
            $student["Hasil_Tp_kurang"] = $student["student_name"] . " Membutuhkan Bimbingan Dalam " . implode(", ", $student["Hasil_Tp_kurang"]);
        }
        return response()->json(['data' => array_values($result)], 200);
    }
    public function getTPList(Request $request)
    {
        //
        $mapel_id = $request->mapel_id;
        $class_id = $request->class_id;
        $fst_id = $request->fst_id;
        $student_id = $request->student_id;
        //
        $data = DB::table('m_tp AS tp')
            ->select(
                'tp.id',
                'tps.id as tps_id',
                'tp.tp_deskripsi',
                DB::raw('COALESCE(tps.kktp,0) as kktp'),
                DB::raw('COALESCE(tps.tampilkan,0) as tampilkan'),

            )
            ->leftJoin('tpsiswas AS tps', function ($join) use ($student_id) {
                $join->on('tp.id', '=', 'tps.tp_id')
                    ->where('tps.siswa_id', '=', $student_id); // Only filter tps for siswa_id = 1
            })
            ->where('tp.mapel_id', $mapel_id)
            ->where('tp.class_id', $class_id)
            ->where('tp.fst_id', $fst_id)
            ->get();

        return response()->json(['data' => $data], 200);
    }
    public function storeFormatif(Request $request)
    {
        $fst_id = $request->input('fst_id');
        $mapel_id = $request->input('mapel_id');
        $student_id = $request->input('student_id');
        $class_id = $request->input('class_id');
        $tp_list = $request->input('tp_list');
        DB::beginTransaction();

        try {
            foreach ($tp_list as $tp) {
                $tp['tps_id'] = ($tp['tps_id'] === "null") ? null : $tp['tps_id'];
                if ($tp['tps_id'] === null) {
                    DB::table('tpsiswas')->insert([
                        'siswa_id' => $student_id,
                        'class_id' => $class_id,
                        'mapel_id' => $mapel_id,
                        'fst_id' => $fst_id,
                        'tp_id' => $tp['id'],
                        'kktp' => $tp['kktp'],
                        'tampilkan' => $tp['tampilkan'],
                        'created_at' => Carbon::now(),
                    ]);
                } else {
                    // Perform updateOrInsert when tps_id is not null
                    DB::table('tpsiswas')->where('id', $tp['tps_id'])->update(
                        [
                            'siswa_id' => $student_id,
                            'class_id' => $class_id,
                            'mapel_id' => $mapel_id,
                            'fst_id' => $fst_id,
                            'tp_id' => $tp['id'],
                            'kktp' => $tp['kktp'],
                            'tampilkan' => $tp['tampilkan'],
                            'updated_at' => Carbon::now()
                        ]
                    );
                }
            }
            DB::commit();
            return response()->json(['message' => 'Input Nilai Formatif Sukses!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function destroyFormatif(Request $request,$id)
    {
        $fst_id = $request->input('fst_id');
        $mapel_id = $request->input('mapel_id');
        $student_id = $id;
        $class_id = $request->input('class_id');
        DB::beginTransaction();
        try {

            DB::table('tpsiswas')->where('siswa_id', $student_id)->where('class_id', $class_id)
                ->where('mapel_id', $mapel_id)
                ->where('fst_id', $fst_id)->delete();
            DB::commit();
            return response()->json(['message' => 'Input Nilai Formatif Sukses!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    //ENd THe boss
}
