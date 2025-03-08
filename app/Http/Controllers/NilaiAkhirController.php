<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class NilaiAkhirController extends Controller
{
    public function getStudentAllScores($classId = null, $student_id = null)
    {
        // Ambil semua mata pelajaran untuk generate kolom dinamis
        $mapels = DB::table('mata_pelajarans')->get();

        // Generate kolom dinamis untuk setiap mata pelajaran
        $columns = [];
        foreach ($mapels as $mapel) {
            $columns[] = "ROUND(COALESCE(AVG(CASE WHEN v.mapel_id = {$mapel->id} THEN
                (COALESCE(v.value_daily, 0) + COALESCE(v.value_sts, 0) + COALESCE(v.value_sas, 0)) / 3 END), 0), 2) AS `{$mapel->nama_mapel}`";
        }

        // Tambahkan kolom rata-rata semua nilai
        $columns[] = "ROUND(COALESCE(AVG(
            (COALESCE(v.value_daily, 0) + COALESCE(v.value_sts, 0) + COALESCE(v.value_sas, 0)) / 3
        ), 0), 2) AS avg_nilai_semua_mapel";

        // Buat query dasar
        $query = "
            SELECT
                s.id AS student_id,
                s.class_id,
                s.nama AS student_name,
                c.class_name,
                " . implode(', ', $columns) . "
            FROM students AS s
            JOIN class AS c ON s.class_id = c.id
            LEFT JOIN `values` AS v ON s.id = v.student_id
            LEFT JOIN mata_pelajarans AS mp ON v.mapel_id = mp.id
        ";

        // Filter berdasarkan class_id jika diberikan
        if ($classId) {
            $query .= " WHERE s.class_id = :classId AND s.id = :studentId";
        }

        $query .= " GROUP BY s.id, s.nama, c.class_name ORDER BY c.class_name, s.nama";
// dd($query);
        // Jalankan query
        $data = DB::select($query, $classId ? ['classId' => $classId, 'studentId'=> $student_id] : []);
        return $data;
    }

    public function getStudentAvgScores($classId = null)
    {
        $columns = [];
        // Tambahkan kolom rata-rata semua nilai
        $columns[] = "ROUND(COALESCE(AVG(
            (COALESCE(v.value_daily, 0) + COALESCE(v.value_sts, 0) + COALESCE(v.value_sas, 0)) / 3
        ), 0), 2) AS avg_nilai_semua_mapel";

        // Buat query dasar
        $query = "
            SELECT
                s.id AS student_id,
                s.class_id,
                s.nama AS student_name,
                c.class_name,
                " . implode(', ', $columns) . "
            FROM students AS s
            JOIN class AS c ON s.class_id = c.id
            LEFT JOIN `values` AS v ON s.id = v.student_id
        ";

        // Filter berdasarkan class_id jika diberikan
        if ($classId) {
            $query .= " WHERE s.class_id = :classId";
        }

        $query .= " GROUP BY s.id, s.nama, c.class_name ORDER BY c.class_name, s.nama";

        // Jalankan query
        return DB::select($query, $classId ? ['classId' => $classId] : []);
    }

    public function index()
    {
        $classList = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc')->get();

        return view('nilaiakhir.index', compact('classList'));
    }
    public function detailNilaiAkhir(Request $request){
        $data = $this->getStudentAllScores($request->class_id,$request->student_id);
        $formattedStudents = [];
        foreach ($data as $student) {
            $studentArray = (array) $student;

            // Informasi siswa
            $studentInfo = [
                'student_id' => $studentArray['student_id'],
                'class_id' => $studentArray['class_id'],
                'student_name' => $studentArray['student_name'],
                'class_name' => $studentArray['class_name'],
                'avg_nilai_semua_mapel' => $studentArray['avg_nilai_semua_mapel'],
            ];

            // Nilai mata pelajaran (otomatis tanpa hardcoding)
            $mapelScores = array_diff_key($studentArray, $studentInfo);

            // Gabungkan semua ke dalam satu array
            $formattedStudents[] = array_merge($studentInfo, ['nilai_per_mapel' => $mapelScores]);
        }

        return view('nilaiakhir.detail',compact('formattedStudents'));
    }
    // Api Sections
    public function getStudentAllAverages(Request $request)
    {
        $students = $this->getStudentAllScores($request->class_id, $request->student_id); // Ambil data berdasarkan filter class_id (jika ada)

        $formattedStudents = [];

        foreach ($students as $student) {
            $studentArray = (array) $student;

            // Informasi siswa
            $studentInfo = [
                'student_id' => $studentArray['student_id'],
                'class_id' => $studentArray['class_id'],
                'student_name' => $studentArray['student_name'],
                'class_name' => $studentArray['class_name'],
                'avg_nilai_semua_mapel' => $studentArray['avg_nilai_semua_mapel'],
            ];

            // Nilai mata pelajaran (otomatis tanpa hardcoding)
            $mapelScores = array_diff_key($studentArray, $studentInfo);

            // Gabungkan semua ke dalam satu array
            $formattedStudents[] = array_merge($studentInfo, ['nilai_per_mapel' => $mapelScores]);
        }

        return response()->json($formattedStudents);
    }
    public function getAllStudentAveragesOnly(Request $request)
    {
        $data = $this->getStudentAvgScores($request->class_id);
        return response()->json(["data" => $data], 200);
    }

    public function exportPDF(Request $request)
    {

        $students = $this->getStudentAllScores($request->class_id, $request->student_id); // Ambil data berdasarkan filter class_id (jika ada)

        $formattedStudents = [];

        foreach ($students as $student) {
            $studentArray = (array) $student;

            // Informasi siswa
            $studentInfo = [
                'student_id' => $studentArray['student_id'],
                'class_id' => $studentArray['class_id'],
                'student_name' => $studentArray['student_name'],
                'class_name' => $studentArray['class_name'],
                'avg_nilai_semua_mapel' => $studentArray['avg_nilai_semua_mapel'],
            ];

            // Nilai mata pelajaran (otomatis tanpa hardcoding)
            $mapelScores = array_diff_key($studentArray, $studentInfo);

            // Gabungkan semua ke dalam satu array
            $formattedStudents[] = array_merge($studentInfo, ['nilai_per_mapel' => $mapelScores]);
        }
        // return response()->json($formattedStudents);

        $pdf = Pdf::loadView('docs.nilai',compact('formattedStudents'));
        return $pdf->stream('Raport_'.$formattedStudents[0]['student_name'].'.pdf');
    }
}
