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
        $query = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc');
        if (Auth::user()->role_id != 1 && Auth::user()->class_id !== null) {
            $query->where('id', Auth::user()->class_id);
        }
        $classList = $query->get();
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
        $class_id = $request->class_id;
        $fst_id = $request->fst_id;

        $data = DB::table('students as s')
            ->join('class as c', 's.class_id', '=', 'c.id')
            ->leftJoin('nilai_eskuls as v', function ($join) use ($fst_id) {
                $join->on('s.id', '=', 'v.student_id')
                    ->where('v.fst_id', $fst_id);
            })
            ->leftJoin('m_eskul as es', 'v.eskul_id', '=', 'es.id')
            ->where('s.class_id', $class_id)
            ->orderBy('s.nama', 'asc')
            ->select(
                's.id as student_id',
                's.nama as student_name',
                'v.id as nilai_eskul_id',
                'es.id as eskul_id',
                'es.nama_eskul',
                'v.nilai_eskul'
            )
            ->get();

        $students = [];
        foreach ($data as $row) {
            if (!isset($students[$row->student_id])) {
                $students[$row->student_id] = [
                    'student_id' => $row->student_id,
                    'student_name' => $row->student_name,
                    'eskuls' => []
                ];
            }
            if ($row->nilai_eskul_id) {
                $students[$row->student_id]['eskuls'][] = [
                    'id' => $row->nilai_eskul_id,
                    'eskul_id' => $row->eskul_id,
                    'nama_eskul' => $row->nama_eskul,
                    'nilai_eskul' => $row->nilai_eskul
                ];
            }
        }

        return response()->json(['data' => array_values($students)], 200);
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
    public function storeBulk(Request $request)
    {
        $class_id = $request->input('class_id');
        $fst_id = $request->input('fst_id');
        $students = $request->input('students', []); // format: { student_id: [ {eskul_id: 1, nilai_eskul: 'Baik'}, ... ] }

        DB::beginTransaction();
        try {
            $studentIds = array_keys($students);
            if (count($studentIds) > 0) {
                // Delete existing records for these students in this semester to overwrite them cleanly
                DB::table('nilai_eskuls')
                    ->whereIn('student_id', $studentIds)
                    ->where('fst_id', $fst_id)
                    ->delete();
            }

            $inserts = [];
            $now = Carbon::now();
            foreach ($students as $student_id => $eskuls) {
                foreach ($eskuls as $eskul) {
                    if (!empty($eskul['eskul_id']) && !empty($eskul['nilai_eskul'])) {
                        $inserts[] = [
                            'student_id' => $student_id,
                            'eskul_id' => $eskul['eskul_id'],
                            'fst_id' => $fst_id,
                            'nilai_eskul' => $eskul['nilai_eskul'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }

            if (count($inserts) > 0) {
                DB::table('nilai_eskuls')->insert($inserts);
            }

            DB::commit();
            return response()->json(['message' => 'Simpan Penilaian Ekstrakurikuler Sukses!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
