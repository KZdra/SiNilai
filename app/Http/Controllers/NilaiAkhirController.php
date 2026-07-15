<?php

namespace App\Http\Controllers;

use App\Exports\NilaiAkhirExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class NilaiAkhirController extends Controller
{
    public function getStudentAllScores($classId = null, $student_id = null, $fstId = null)
    {
        // Ambil semua mata pelajaran untuk generate kolom dinamis
        $mapels = DB::table('mata_pelajarans')->get();

        // Generate kolom dinamis untuk setiap mata pelajaran
        $columns = [];
        foreach ($mapels as $mapel) {
            $columns[] = "ROUND(COALESCE(AVG(CASE WHEN v.mapel_id = {$mapel->id} THEN
                (COALESCE(v.value_daily, 0) + COALESCE(v.value_daily_2, 0) + COALESCE(v.value_daily_3, 0) + COALESCE(v.value_daily_4, 0) + COALESCE(v.value_daily_5, 0) + COALESCE(v.value_daily_6, 0) + COALESCE(v.value_daily_7, 0) + COALESCE(v.value_daily_8, 0) + COALESCE(v.value_daily_9, 0) + COALESCE(v.value_daily_10, 0) + COALESCE(v.value_sts, 0) + COALESCE(v.value_sas, 0)) / 12 END), 0), 2) AS `{$mapel->nama_mapel}`";
        }

        // Tambahkan kolom rata-rata semua nilai
        $columns[] = "ROUND(COALESCE(AVG(
            (COALESCE(v.value_daily, 0) + COALESCE(v.value_daily_2, 0) + COALESCE(v.value_daily_3, 0) + COALESCE(v.value_daily_4, 0) + COALESCE(v.value_daily_5, 0) + COALESCE(v.value_daily_6, 0) + COALESCE(v.value_daily_7, 0) + COALESCE(v.value_daily_8, 0) + COALESCE(v.value_daily_9, 0) + COALESCE(v.value_daily_10, 0) + COALESCE(v.value_sts, 0) + COALESCE(v.value_sas, 0)) / 12), 0), 2) AS avg_nilai_semua_mapel";

        // Buat query dasar
        $query = "
            SELECT
                s.id AS student_id,
                s.class_id,
                s.nama AS student_name,
                s.nis AS student_nis,
                s.foto_siswa_path,
                s.sakit,
                s.alpa,
                s.izin,
                c.class_name,
                " . implode(', ', $columns) . "
            FROM students AS s
            JOIN class AS c ON s.class_id = c.id
            LEFT JOIN `values` AS v ON s.id = v.student_id AND v.fst_id =:fstId
            LEFT JOIN mata_pelajarans AS mp ON v.mapel_id = mp.id
        ";

        // Filter berdasarkan class_id jika diberikan
        if ($classId) {
            $query .= " WHERE s.class_id = :classId AND s.id = :studentId ";
        }

        $query .= " GROUP BY s.id, s.nama, c.class_name ORDER BY c.class_name, s.nama";
        // dd($query);
        // Jalankan query
        $data = DB::select($query, $classId ? ['classId' => $classId, 'studentId' => $student_id, 'fstId' => $fstId] : []);
        return $data;
    }
    public function getStudentsAllScores($classId = null,  $fstId = null)
    {
        // Ambil semua mata pelajaran untuk generate kolom dinamis
        $mapels = DB::table('mata_pelajarans')->get();

        // Generate kolom dinamis untuk setiap mata pelajaran
        $columns = [];
        foreach ($mapels as $mapel) {
            $columns[] = "ROUND(COALESCE(AVG(CASE WHEN v.mapel_id = {$mapel->id} THEN
                (COALESCE(v.value_daily, 0) + COALESCE(v.value_daily_2, 0) + COALESCE(v.value_daily_3, 0) + COALESCE(v.value_daily_4, 0) + COALESCE(v.value_daily_5, 0) + COALESCE(v.value_daily_6, 0) + COALESCE(v.value_daily_7, 0) + COALESCE(v.value_daily_8, 0) + COALESCE(v.value_daily_9, 0) + COALESCE(v.value_daily_10, 0) + COALESCE(v.value_sts, 0) + COALESCE(v.value_sas, 0)) / 12 END), 0), 2) AS `{$mapel->nama_mapel}`";
        }

        // Buat query dasar
        $query = "
            SELECT
                s.nama AS student_name,
                s.nis AS student_nis,
                c.class_name,
                " . implode(', ', $columns) . "
            FROM students AS s
            JOIN class AS c ON s.class_id = c.id
            LEFT JOIN `values` AS v ON s.id = v.student_id AND v.fst_id =:fstId
            LEFT JOIN mata_pelajarans AS mp ON v.mapel_id = mp.id
        ";

        // Filter berdasarkan class_id jika diberikan
        if ($classId) {
            $query .= " WHERE s.class_id = :classId";
        }

        $query .= " GROUP BY s.id, s.nama, c.class_name ORDER BY c.class_name, s.nama";
        // dd($query);
        // Jalankan query
        $data = DB::select($query, $classId ? ['classId' => $classId, 'fstId' => $fstId] : []);
        return $data;
    }

    public function getStudentAllTp($classId = null, $student_id = null, $fstId = null)
    {
        // Ambil data siswa berdasarkan student_id (hanya satu siswa)
        $student = DB::table('students')
            ->where('id', $student_id)
            ->first();

        // Jika siswa tidak ditemukan, return response kosong
        if (!$student) {
            return response()->json(["message" => "Siswa tidak ditemukan"], 404);
        }

        // Ambil semua mata pelajaran yang ada
        $mapels = DB::table('mata_pelajarans')->get();

        // Inisialisasi hasil untuk 1 siswa
        $result = [
            "mapel" => []
        ];

        foreach ($mapels as $mapel) {
            // Ambil TP untuk mata pelajaran tertentu
            $data = DB::table('tpsiswas as v')
                ->leftJoin('m_tp as tp', 'v.tp_id', '=', 'tp.id')
                ->where('v.siswa_id', $student_id)
                ->where('v.class_id', $classId)
                ->where('v.fst_id', $fstId)
                ->where('v.mapel_id', $mapel->id)
                ->select(
                    'v.tp_id',
                    'tp.tp_deskripsi',
                    'v.kktp',
                    'v.tampilkan'
                )
                ->get();

            // Inisialisasi data mapel, meskipun tidak ada TP
            $result["mapel"][$mapel->nama_mapel] = [
                "Hasil_Tp_tinggi" => [],
                "Hasil_Tp_kurang" => []
            ];

            foreach ($data as $item) {
                // Tambahkan TP tinggi jika kktp = 1 dan tampilkan = 1
                if ($item->kktp == 1 && $item->tampilkan == 1) {
                    $result["mapel"][$mapel->nama_mapel]["Hasil_Tp_tinggi"][] = $item->tp_deskripsi;
                }

                // Tambahkan TP kurang jika kktp = 0 dan tampilkan = 1
                if ($item->kktp == 0 && $item->tampilkan == 1) {
                    $result["mapel"][$mapel->nama_mapel]["Hasil_Tp_kurang"][] = $item->tp_deskripsi;
                }
            }
        }

        // Ubah array hasil tinggi dan kurang menjadi string dipisahkan koma
        foreach ($result["mapel"] as $mapel => &$tp) {
            $tp["Hasil_Tp_tinggi"] = !empty($tp["Hasil_Tp_tinggi"])
                ? $student->nama . " Menunjukkan Pemahaman Dalam " . implode(", ", $tp["Hasil_Tp_tinggi"])
                : $student->nama . " Belum Memiliki TP dengan Pemahaman Tinggi.";

            $tp["Hasil_Tp_kurang"] = !empty($tp["Hasil_Tp_kurang"])
                ? $student->nama . " Membutuhkan Bimbingan Dalam " . implode(", ", $tp["Hasil_Tp_kurang"])
                : $student->nama . " Tidak Memiliki TP yang Membutuhkan Bimbingan.";
        }

        // return response()->json(
        return  $result;
    }




    public function getStudentAvgScores($classId = null, $fstId = null)
    {
        $columns = [];

        $columns[] = "ROUND(COALESCE(AVG(
            (COALESCE(v.value_daily, 0) + COALESCE(v.value_daily_2, 0) + COALESCE(v.value_daily_3, 0) + COALESCE(v.value_daily_4, 0) + COALESCE(v.value_daily_5, 0) + COALESCE(v.value_daily_6, 0) + COALESCE(v.value_daily_7, 0) + COALESCE(v.value_daily_8, 0) + COALESCE(v.value_daily_9, 0) + COALESCE(v.value_daily_10, 0) + COALESCE(v.value_sts, 0) + COALESCE(v.value_sas, 0)) / 12
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
            LEFT JOIN `values` AS v ON s.id = v.student_id AND v.fst_id =:fstId
        ";

        // Filter berdasarkan class_id jika diberikan
        if ($classId) {
            $query .= " WHERE s.class_id = :classId ";
        }

        $query .= " GROUP BY s.id, s.nama, c.class_name ORDER BY c.class_name, s.nama";

        // Jalankan query
        return DB::select($query, $classId ? ['classId' => $classId, 'fstId' => $fstId] : []);
    }

    public function index()
    {
        $query = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc');
        
        // Role Management: Wali Kelas / Guru hanya melihat kelasnya
        if (Auth::user()->role_id != 1 && Auth::user()->class_id !== null) {
            $query->where('id', Auth::user()->class_id);
        }
        $classList = $query->get();

        $fstList = DB::table('m_fst_pembelajaran')->select('id', 'fase', 'semester', 'tahun_ajaran', 'ta')->orderBy('id', 'asc')->get();

        $className = null;
        if (Auth::user()->class_id !== null) {
            $className = DB::table('class')->where('id', Auth::user()->class_id)->value('class_name');
        }
        return view('nilaiakhir.index', compact('classList', 'className', 'fstList'));
    }
    public function detailNilaiAkhir(Request $request)
    {
        $data = $this->getStudentAllScores($request->class_id, $request->student_id, $request->fst_id);
        $formattedStudents = [];
        foreach ($data as $student) {
            $studentArray = (array) $student;

            // Informasi siswa
            $studentInfo = [
                'student_id' => $studentArray['student_id'],
                'class_id' => $studentArray['class_id'],
                'student_name' => $studentArray['student_name'],
                'class_name' => $studentArray['class_name'],
                'foto_siswa_path' => $studentArray['foto_siswa_path'],
                'student_nis' => $studentArray['student_nis'],
                'sakit' => $studentArray['sakit'],
                'izin' => $studentArray['izin'],
                'alpa' => $studentArray['alpa'],
                'avg_nilai_semua_mapel' => $studentArray['avg_nilai_semua_mapel'],
            ];

            // Nilai mata pelajaran (otomatis tanpa hardcoding)
            $mapelScores = array_diff_key($studentArray, $studentInfo);

            // Gabungkan semua ke dalam satu array
            $formattedStudents[] = array_merge($studentInfo, ['nilai_per_mapel' => $mapelScores]);
        }
        return view('nilaiakhir.detail', compact('formattedStudents'));
    }
    // Api Sections
    public function getStudentAllAverages(Request $request)
    {
        $students = $this->getStudentAllScores($request->class_id, $request->student_id, $request->fst_id); // Ambil data berdasarkan filter class_id (jika ada)
        $formattedStudents = [];

        foreach ($students as $student) {
            $studentArray = (array) $student;

            // Informasi siswa
            $studentInfo = [
                'student_id' => $studentArray['student_id'],
                'class_id' => $studentArray['class_id'],
                'student_name' => $studentArray['student_name'],
                'class_name' => $studentArray['class_name'],
                'student_nis' => $studentArray['student_nis'],
                'sakit' => $studentArray['sakit'],
                'izin' => $studentArray['izin'],
                'alpa' => $studentArray['alpa'],
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
        $data = $this->getStudentAvgScores($request->class_id, $request->fst_id);
        return response()->json(["data" => $data], 200);
    }

    public function exportPDF(Request $request)
    {
        $students = $this->getStudentAllScores($request->class_id, $request->student_id, $request->fst_id); // Ambil data berdasarkan filter class_id (jika ada)
        $studentsTP = $this->getStudentAllTp($request->class_id, $request->student_id, $request->fst_id); // Ambil data berdasarkan filter class_id (jika ada)
        $fst = DB::table('m_fst_pembelajaran')->select('fase', 'semester', 'tahun_ajaran', 'ta')->where('id', $request->fst_id)->first();
        $schoolData = DB::table('data_sekolah')->select('nama_sekolah', 'alamat_sekolah', 'nama_kepala_sekolah', 'nip_kepala_sekolah')->first();
        $studentEskul = DB::table('nilai_eskuls as ns')->select('ns.id', 'ns.nilai_eskul', 'ms.nama_eskul')->join('m_eskul as ms','ns.eskul_id','=','ms.id')
        ->where('ns.student_id',$request->student_id)->where('ns.fst_id',$request->fst_id)->get();
        $formattedStudents = [];
        $tgl_print = $request->tgl_print;
        $keputusan = $request->keputusan;
        foreach ($students as $student) {
            $studentArray = (array) $student;

            // Informasi siswa
            $studentInfo = [
                'student_id' => $studentArray['student_id'],
                'class_id' => $studentArray['class_id'],
                'student_name' => $studentArray['student_name'],
                'class_name' => $studentArray['class_name'],
                'avg_nilai_semua_mapel' => $studentArray['avg_nilai_semua_mapel'],
                'student_nis' => $studentArray['student_nis'],
                'sakit' => $studentArray['sakit'],
                'izin' => $studentArray['izin'],
                'alpa' => $studentArray['alpa'],
                'foto_siswa_path' => $studentArray['foto_siswa_path'],
            ];

            // Nilai mata pelajaran (otomatis tanpa hardcoding)
            $mapelScores = array_diff_key($studentArray, $studentInfo);

            // Gabungkan semua ke dalam satu array
            $formattedStudents[] = array_merge($studentInfo, ['nilai_per_mapel' => $mapelScores, 'school_data' => $schoolData, 'TP' => $studentsTP["mapel"], 'fst' => $fst,'eskul'=>$studentEskul]);
        }
        // return response()->json($formattedStudents);
        // dd($formattedStudents);

        $pdf = Pdf::loadView('docs.nilai', compact('formattedStudents', 'tgl_print', 'keputusan'));
        $concated = ucwords($formattedStudents[0]['fst']->fase) . '-' . str_replace(' ', '', $formattedStudents[0]['fst']->semester) . '-' . $formattedStudents[0]['fst']->ta;
        $pdfPath = 'raport/' . str_replace(' ','_',$formattedStudents[0]['class_name']) . '/' . $concated . '/' . str_replace(' ','_',$formattedStudents[0]['student_name']). '.pdf';

        // return $pdf->stream('Raport_' . $formattedStudents[0]['student_name'] . '.pdf');
        // return view('docs.nilai',compact('formattedStudents'));

        Storage::disk('public')->put($pdfPath, $pdf->output());

        return response()->json(['pdf_url' => asset('storage/' . $pdfPath)]);
    }

    public function ExportNilaiAkhirExcel(Request $request)
    {
        $students = $this->getStudentsAllScores($request->class_id,$request->fst_id); // Ambil data berdasarkan filter class_id (jika ada)
        $className= DB::table('class')->select('class_name')->where('id', $request->class_id)->first();
        $formattedStudents = [];

        foreach ($students as $student) {
            $studentArray = (array) $student;

            // Informasi siswa
            $studentInfo = [
                'student_name' => $studentArray['student_name'],
                'class_name' => $studentArray['class_name'],
                'student_nis' => $studentArray['student_nis'],
            ];

            // Nilai mata pelajaran (otomatis tanpa hardcoding)
            $mapelScores = array_diff_key($studentArray, $studentInfo);

            // Gabungkan semua ke dalam satu array
            $formattedStudents[] = array_merge($studentInfo, ['nilai_per_mapel' => $mapelScores]);
        }


        // dd($formattedStudents);
        // return view('docs.nilaiakhir',compact('formattedStudents'));
        
        return Excel::download(new NilaiAkhirExport($formattedStudents),"Nilai_Akhir_$className->class_name.xlsx");
    }

    public function exportRankingExcel(Request $request)
    {
        $data = $this->getStudentAvgScores($request->class_id, $request->fst_id);
        
        // Convert to array and sort descending by average
        $students = (array) $data;
        usort($students, function ($a, $b) {
            return $b->avg_nilai_semua_mapel <=> $a->avg_nilai_semua_mapel;
        });

        $className = DB::table('class')->where('id', $request->class_id)->value('class_name');
        
        return Excel::download(new \App\Exports\RankingExport($students), "Ranking_Siswa_{$className}.xlsx");
    }
}
