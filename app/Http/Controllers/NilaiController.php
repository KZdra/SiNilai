<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class NilaiController extends Controller
{
    public function index()
    {
        $classList = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc')->get();

        return view('nilai.index', compact('classList'));
    }
    public function getData(Request $request)
    {
        $id = $request->class_id;
        $data = DB::table('students as s')->select(
            's.id as student_id',
            's.nama as student_name',
            'c.class_name',
            'v.id as value_id',
            'v.value_daily',
            'v.value_sts',
            'v.value_sas',
            DB::raw('ROUND((COALESCE(v.value_daily, 0) + COALESCE(v.value_sts, 0) + COALESCE(v.value_sas, 0)) / 3, 2) as average_value')
        )
            ->Join('class as c', 's.class_id', '=', 'c.id')
            ->leftJoin('values as v', 's.id', '=', 'v.student_id')
            ->where('s.class_id', '=', $id)
            ->orderBy('s.nama', 'asc')
            ->get();
        return response()->json(['data' => $data], 200);
    }
    public function store(Request $request)
    {

        $request->validate([
            'student_id' => 'required|integer',
            'value_daily' => 'required|numeric|max_digits:3|max:100',
            'value_sts' => 'required|numeric|max_digits:3|max:100',
            'value_sas' => 'required|numeric|max_digits:3|max:100',
        ]);

        try {
            DB::table('values')->insert([
                'student_id' => $request->student_id,
                'value_daily' => $request->value_daily,
                'value_sts' => $request->value_sts,
                'value_sas' => $request->value_sas,
                'created_at' => Carbon::now()
            ]);
            return response()->json(['message' => 'Nilai berhasil ditambahkan!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {

        $request->validate([
            'student_id' => 'required|integer',
            'value_daily' => 'required|integer|max_digits:3',
            'value_sts' => 'required|integer|max_digits:3',
            'value_sas' => 'required|integer|max_digits:3',
        ]);

        try {
            DB::table('values')->where('id', '=', $id)->where('student_id', '=', $request->student_id)->update([
                'value_daily' => $request->value_daily,
                'value_sts' => $request->value_sts,
                'value_sas' => $request->value_sas,
                'updated_at' => Carbon::now()
            ]);
            return response()->json(['message' => 'Nilai berhasil diEdit!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            DB::table('values')->where('id', '=', $id)->delete();
            return response()->json(['message' => 'Nilai berhasil diHapus!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function downloadTemplate()
    {
        return response()->download(public_path('down/Template_InputNilaiSiswa.xlsx'));
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv' => 'required|mimes:csv,txt|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $file = $request->file('csv');
        $csvData = array_map('str_getcsv', file($file));

        if (count($csvData) <= 1) {
            return response()->json(['message' => 'File CSV kosong atau tidak valid.'], 400);
        }

        $header = array_shift($csvData); // Ambil header
        try {
            DB::beginTransaction();
            foreach ($csvData as $row) {
                if (count($row) >= 2) {
                    $nis = $row[0];
                    $nama = $row[1];
                    $className = $row[2] ?? null;
                    $value_daily = $row[3] ?? null;
                    $value_sts = $row[4] ?? null;
                    $value_sas = $row[5] ?? null;
                    $studentId = DB::table('students')->where('nis', '=', $nis)->where('nama', 'like', "%{$nama}%")->value('id');
                    DB::table('values')->updateOrInsert(
                        ['student_id' => $studentId],
                        [
                            'value_daily' => $value_daily,
                            'value_sts' => $value_sts,
                            'value_sas' => $value_sas,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]
                    );
                }
            }
            DB::commit();
            return response()->json(['message' => 'Nilai siswa berhasil diimport!'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function exportPDF(Request $request)
    {
        $s_id= $request->student_id;
        $v_id= $request->value_id;
        $data = DB::table('students as s')->select(
            's.id as student_id',
            's.nis as student_nis',
            's.nama as student_name',
            'c.class_name',
            'v.id as value_id',
            'v.value_daily',
            'v.value_sts',
            'v.value_sas',
            DB::raw('ROUND((COALESCE(v.value_daily, 0) + COALESCE(v.value_sts, 0) + COALESCE(v.value_sas, 0)) / 3, 2) as average_value')
        )
            ->Join('class as c', 's.class_id', '=', 'c.id')
            ->leftJoin('values as v', 's.id', '=', 'v.student_id')
            ->where('v.id', '=', $v_id)
            ->where('s.id', '=', $s_id)
            ->get();
        $pdf = Pdf::loadView('docs.nilai', compact('data'));
        return $pdf->stream('Nilai_'.$data[0]->student_name.'.pdf');
    }
}
