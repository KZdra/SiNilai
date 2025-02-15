<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;


class SiswaController extends Controller
{
    public function index()
    {
        $data = DB::table('students')
            ->select('students.id', 'students.nis', 'students.nama', 'students.class_id', 'class.class_name', 'class.id as class_id')
            ->leftJoin('class', 'students.class_id', '=', 'class.id')
            ->orderBy('students.nama', 'asc')
            ->get();
        $classList = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc')->get();

        return view('msiswa.index', compact('data', 'classList'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|integer',
            'student_name' => 'required|string|max:255',
            'class_id' => 'required|integer'
        ]);

        try {
            DB::table('students')->insert([
                'nis' => $request->nis,
                'nama' => $request->student_name,
                'class_id' => $request->class_id,
                'created_at' => Carbon::now()
            ]);
            return response()->json(['message' => 'Siswa berhasil ditambahkan!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nis' => 'required|integer',
            'student_name' => 'required|string|max:255',
            'class_id' => 'required|integer'
        ]);
        try {
            DB::table('students')->where('id', '=', $id)->update([
                'nis' => $request->nis,
                'nama' => $request->student_name,
                'class_id' => $request->class_id,
                'updated_at' => Carbon::now()
            ]);
            return response()->json(['message' => 'Siswa berhasil diUpdate!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function destroy(Request $request, $id)
    {
        try {
            DB::table('students')->where('id', '=', $id)->delete();
            return response()->json(['message' => 'Siswa berhasil diHapus!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
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

        foreach ($csvData as $row) {
            if (count($row) >= 2) { // Pastikan minimal ada NIS & Nama
                $nis = $row[0];
                $nama = $row[1];
                $className = $row[2] ?? null;

                $classId = null;

                if (!empty($className)) {
                    // Jika nama kelas ada, cari ID kelasnya
                    $classId = DB::table('class')->where('class_name','LIKE', $className)->value('id');

                    if (!$classId) {
                        // Jika kelas tidak ditemukan, buat baru
                        $classId = DB::table('class')->insertGetId([
                            'class_name' => $className,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                }

                // Insert atau update siswa
                DB::table('students')->updateOrInsert(
                    ['nis' => $nis], // Cek berdasarkan NIS
                    [
                        'nama' => $nama,
                        'class_id' => $classId, // NULL jika tidak ada kelas
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]
                );
            }
        }

        return response()->json(['message' => 'Data siswa berhasil diimport!']);
    }

    public function downloadTemplate(){
        return response()->download(public_path('down/Template_InputSiswa.xlsx'));
    }
}
