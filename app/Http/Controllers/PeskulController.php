<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class PeskulController extends Controller
{
    public function index()
    {
        $classList = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc')->get();
        $eskulList = DB::table('m_eskul')->select('id', 'nama_eskul')->orderBy('id', 'asc')->get();
        $fstList = DB::table('m_fst_pembelajaran')->select('id', 'fase', 'semester', 'tahun_ajaran', 'ta')->orderBy('id', 'asc')->get();
        $className = null;
        if (Auth::user()->class_id !== null) {
            $className = DB::table('class')->where('id', Auth::user()->class_id)->value('class_name');
        }
        return view('peskul.index', compact('eskulList', 'classList', 'fstList', 'className'));
    }
    public function getdata(Request $request)
    {
        //
        $eskul_id = $request->eskul_id;
        $class_id = $request->class_id;
        $fst_id = $request->fst_id;
        //
        $data = DB::table('students as s')
            ->join('class as c', 's.class_id', '=', 'c.id')
            ->leftJoin('nilai_eskuls as v', function ($join) use ($eskul_id, $fst_id) {
                $join->on('s.id', '=', 'v.student_id')
                    ->where('v.eskul_id', $eskul_id)
                    ->where('v.fst_id', $fst_id);
            })
            ->leftJoin('m_eskul as es', 'v.eskul_id', '=', 'es.id')
            ->where('s.class_id', $class_id)
            ->orderBy('s.nama', 'asc')
            ->select(
                's.id as student_id',
                's.nama as student_name',
                'es.id as eskul_id',
                'v.id as nilai_eskul_id',
                'v.nilai_eskul'
            )
            ->groupBy('s.id', 's.nama', 'v.id', 'es.id', 'v.nilai_eskul')
            ->get();

        return response()->json(['data' => $data], 200);
    }
    public function store(Request $request)
    {
        $fst_id = $request->input('fst_id');
        $eskul_id = $request->input('eskul_id');
        $student_id = $request->input('student_id');
        $nilai = $request->input('nilai');
        DB::beginTransaction();
        try {
            DB::table('nilai_eskuls')->insert([
                'student_id' => $student_id,
                'eskul_id' => $eskul_id,
                'fst_id' => $fst_id,
                'nilai_eskul' => $nilai,
                'created_at' => Carbon::now(),
            ]);

            DB::commit();
            return response()->json(['message' => 'Input Nilai Eskul Sukses!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $student_id = $request->input('student_id');
        $nilai = $request->input('nilai');
        DB::beginTransaction();
        try {
            DB::table('nilai_eskuls')->where('id', $id)->where('student_id', $student_id)->update([
                'nilai_eskul' => $nilai,
                'updated_at' => Carbon::now(),
            ]);

            DB::commit();
            return response()->json(['message' => 'Update Nilai Eskul Sukses!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function destroy(Request $request, $id)
    {
        $student_id = $request->input('student_id');
        DB::beginTransaction();
        try {
            DB::table('nilai_eskuls')->where('id', $id)->where('student_id', $student_id)->delete();
            DB::commit();
            return response()->json(['message' => 'Hapus Nilai Eskul Sukses!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
