<?php

namespace App\Http\Controllers;

use App\Exports\NilaiAkhirExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\MasterDataCache;

class NilaiAkhirController extends Controller
{
    /**
     * Dapatkan class_id siswa secara historis (khususnya untuk siswa yang sudah lulus / naik kelas)
     */
    private function resolveHistoricalClassId($studentId, $fstId = null, $fallbackClassId = null)
    {
        if ($fallbackClassId) {
            return $fallbackClassId;
        }

        if (!$studentId) {
            return null;
        }

        // 1. Cek dari values semester ini
        if ($fstId) {
            $classId = DB::table('values')
                ->where('student_id', $studentId)
                ->where('fst_id', $fstId)
                ->whereNotNull('class_id')
                ->value('class_id');

            if ($classId) return $classId;

            // 2. Cek dari catatan_walikelas semester ini
            $classId = DB::table('catatan_walikelas')
                ->where('student_id', $studentId)
                ->where('fst_id', $fstId)
                ->whereNotNull('class_id')
                ->value('class_id');

            if ($classId) return $classId;

            // 3. Cek dari tpsiswas semester ini
            $classId = DB::table('tpsiswas')
                ->where('siswa_id', $studentId)
                ->where('fst_id', $fstId)
                ->whereNotNull('class_id')
                ->value('class_id');

            if ($classId) return $classId;
        }

        // 4. Cek kelas aktif saat ini dari students
        $currentClassId = DB::table('students')->where('id', $studentId)->value('class_id');
        if ($currentClassId) return $currentClassId;

        // 5. Jika siswa sudah lulus / alumni (class_id null), ambil kelas terakhir yang tercatat di values / catatan_walikelas
        $lastClassId = DB::table('values')
            ->where('student_id', $studentId)
            ->whereNotNull('class_id')
            ->orderBy('fst_id', 'desc')
            ->value('class_id');

        if ($lastClassId) return $lastClassId;

        return DB::table('catatan_walikelas')
            ->where('student_id', $studentId)
            ->whereNotNull('class_id')
            ->orderBy('fst_id', 'desc')
            ->value('class_id');
    }

    public function getStudentAllScores($classId = null, $student_id = null, $fstId = null)
    {
        $classId = $this->resolveHistoricalClassId($student_id, $fstId, $classId);

        // Ambil mata pelajaran yang aktif untuk kelas dan semester ini
        $mapels = DB::table('mapel_class_fst')
            ->join('mata_pelajarans', 'mapel_class_fst.mapel_id', '=', 'mata_pelajarans.id')
            ->where('mapel_class_fst.class_id', $classId)
            ->where('mapel_class_fst.fst_id', $fstId)
            ->where('mapel_class_fst.is_active', 1)
            ->select('mata_pelajarans.*')
            ->orderBy('mata_pelajarans.nama_mapel', 'asc')
            ->get();

        // Fallback jika mapel_class_fst belum dikaitkan, ambil dari mata pelajaran yang memiliki nilai
        if ($mapels->isEmpty() && $student_id) {
            $mapels = DB::table('values as v')
                ->join('mata_pelajarans as m', 'v.mapel_id', '=', 'm.id')
                ->where('v.student_id', $student_id)
                ->where('v.fst_id', $fstId)
                ->select('m.*')
                ->distinct()
                ->orderBy('m.nama_mapel', 'asc')
                ->get();
        }

        // Generate kolom dinamis untuk setiap mata pelajaran
        $columns = [];
        $activeMapelIds = $mapels->pluck('id')->toArray();
        $inMapelIds = count($activeMapelIds) > 0 ? implode(',', $activeMapelIds) : '0';

        foreach ($mapels as $mapel) {
            $columns[] = "ROUND(COALESCE(AVG(CASE WHEN v.mapel_id = {$mapel->id} THEN
                (
                    COALESCE(
                        (COALESCE(v.value_daily, 0) + COALESCE(v.value_daily_2, 0) + COALESCE(v.value_daily_3, 0) + COALESCE(v.value_daily_4, 0) + COALESCE(v.value_daily_5, 0) + COALESCE(v.value_daily_6, 0) + COALESCE(v.value_daily_7, 0) + COALESCE(v.value_daily_8, 0) + COALESCE(v.value_daily_9, 0) + COALESCE(v.value_daily_10, 0))
                        / 
                        NULLIF(
                            IF(v.value_daily IS NOT NULL, 1, 0) + IF(v.value_daily_2 IS NOT NULL, 1, 0) + IF(v.value_daily_3 IS NOT NULL, 1, 0) + IF(v.value_daily_4 IS NOT NULL, 1, 0) + IF(v.value_daily_5 IS NOT NULL, 1, 0) + IF(v.value_daily_6 IS NOT NULL, 1, 0) + IF(v.value_daily_7 IS NOT NULL, 1, 0) + IF(v.value_daily_8 IS NOT NULL, 1, 0) + IF(v.value_daily_9 IS NOT NULL, 1, 0) + IF(v.value_daily_10 IS NOT NULL, 1, 0),
                            0
                        ), 0
                    )
                    +
                    COALESCE(
                        (COALESCE(v.value_sts, 0) + COALESCE(v.value_sas, 0))
                        /
                        NULLIF(IF(v.value_sts IS NOT NULL, 1, 0) + IF(v.value_sas IS NOT NULL, 1, 0), 0), 0
                    )
                )
                /
                NULLIF(
                    IF(COALESCE(v.value_daily, v.value_daily_2, v.value_daily_3, v.value_daily_4, v.value_daily_5, v.value_daily_6, v.value_daily_7, v.value_daily_8, v.value_daily_9, v.value_daily_10) IS NOT NULL, 1, 0)
                    +
                    IF(COALESCE(v.value_sts, v.value_sas) IS NOT NULL, 1, 0), 
                    0
                )
                END), 0), 2) AS `{$mapel->nama_mapel}`";
        }

        // Tambahkan kolom rata-rata semua nilai
        $columns[] = "ROUND(COALESCE(AVG(
            (
                COALESCE(
                    (COALESCE(v.value_daily, 0) + COALESCE(v.value_daily_2, 0) + COALESCE(v.value_daily_3, 0) + COALESCE(v.value_daily_4, 0) + COALESCE(v.value_daily_5, 0) + COALESCE(v.value_daily_6, 0) + COALESCE(v.value_daily_7, 0) + COALESCE(v.value_daily_8, 0) + COALESCE(v.value_daily_9, 0) + COALESCE(v.value_daily_10, 0))
                    / 
                    NULLIF(
                        IF(v.value_daily IS NOT NULL, 1, 0) + IF(v.value_daily_2 IS NOT NULL, 1, 0) + IF(v.value_daily_3 IS NOT NULL, 1, 0) + IF(v.value_daily_4 IS NOT NULL, 1, 0) + IF(v.value_daily_5 IS NOT NULL, 1, 0) + IF(v.value_daily_6 IS NOT NULL, 1, 0) + IF(v.value_daily_7 IS NOT NULL, 1, 0) + IF(v.value_daily_8 IS NOT NULL, 1, 0) + IF(v.value_daily_9 IS NOT NULL, 1, 0) + IF(v.value_daily_10 IS NOT NULL, 1, 0),
                        0
                    ), 0
                )
                +
                COALESCE(
                    (COALESCE(v.value_sts, 0) + COALESCE(v.value_sas, 0))
                    /
                    NULLIF(IF(v.value_sts IS NOT NULL, 1, 0) + IF(v.value_sas IS NOT NULL, 1, 0), 0), 0
                )
            )
            /
            NULLIF(
                IF(COALESCE(v.value_daily, v.value_daily_2, v.value_daily_3, v.value_daily_4, v.value_daily_5, v.value_daily_6, v.value_daily_7, v.value_daily_8, v.value_daily_9, v.value_daily_10) IS NOT NULL, 1, 0)
                +
                IF(COALESCE(v.value_sts, v.value_sas) IS NOT NULL, 1, 0), 
                0
            )
        ), 0), 2) AS avg_nilai_semua_mapel";

        $selectedClassIdSql = $classId ? "{$classId} AS class_id" : "COALESCE(s.class_id, 0) AS class_id";
        $joinClassSql = $classId ? "LEFT JOIN class AS c ON c.id = {$classId}" : "LEFT JOIN class AS c ON s.class_id = c.id";

        // Buat query dasar
        $query = "
            SELECT
                s.id AS student_id,
                {$selectedClassIdSql},
                s.nama AS student_name,
                s.nis AS student_nis,
                s.foto_siswa_path,
                s.sakit,
                s.alpa,
                s.izin,
                COALESCE(c.class_name, 'Alumni / Lulus') AS class_name,
                " . implode(', ', $columns) . "
            FROM students AS s
            {$joinClassSql}
            LEFT JOIN `values` AS v ON s.id = v.student_id AND v.fst_id =:fstId AND v.mapel_id IN ($inMapelIds)
            LEFT JOIN mata_pelajarans AS mp ON v.mapel_id = mp.id
        ";

        // Filter berdasarkan student_id dan class_id
        $bindings = ['fstId' => $fstId];
        if ($student_id) {
            $query .= " WHERE s.id = :studentId ";
            $bindings['studentId'] = $student_id;
        } elseif ($classId) {
            $query .= " WHERE (s.class_id = :classId OR EXISTS (SELECT 1 FROM `values` val WHERE val.student_id = s.id AND val.class_id = :classIdVal AND val.fst_id = :fstIdVal) OR EXISTS (SELECT 1 FROM catatan_walikelas cw WHERE cw.student_id = s.id AND cw.class_id = :classIdCw AND cw.fst_id = :fstIdCw)) ";
            $bindings['classId'] = $classId;
            $bindings['classIdVal'] = $classId;
            $bindings['fstIdVal'] = $fstId;
            $bindings['classIdCw'] = $classId;
            $bindings['fstIdCw'] = $fstId;
        }

        $query .= " GROUP BY s.id, s.nama, c.class_name ORDER BY c.class_name, s.nama";
        // Jalankan query
        $data = DB::select($query, $bindings);
        return $data;
    }
    public function getStudentsAllScores($classId = null,  $fstId = null)
    {
        // Ambil mata pelajaran yang aktif untuk kelas dan semester ini
        $mapels = DB::table('mapel_class_fst')
            ->join('mata_pelajarans', 'mapel_class_fst.mapel_id', '=', 'mata_pelajarans.id')
            ->where('mapel_class_fst.class_id', $classId)
            ->where('mapel_class_fst.fst_id', $fstId)
            ->where('mapel_class_fst.is_active', 1)
            ->select('mata_pelajarans.*')
            ->orderBy('mata_pelajarans.nama_mapel', 'asc')
            ->get();

        // Generate kolom dinamis untuk setiap mata pelajaran
        $columns = [];
        $activeMapelIds = $mapels->pluck('id')->toArray();
        $inMapelIds = count($activeMapelIds) > 0 ? implode(',', $activeMapelIds) : '0';

        foreach ($mapels as $mapel) {
            $columns[] = "ROUND(COALESCE(AVG(CASE WHEN v.mapel_id = {$mapel->id} THEN
                (
                    COALESCE(
                        (COALESCE(v.value_daily, 0) + COALESCE(v.value_daily_2, 0) + COALESCE(v.value_daily_3, 0) + COALESCE(v.value_daily_4, 0) + COALESCE(v.value_daily_5, 0) + COALESCE(v.value_daily_6, 0) + COALESCE(v.value_daily_7, 0) + COALESCE(v.value_daily_8, 0) + COALESCE(v.value_daily_9, 0) + COALESCE(v.value_daily_10, 0))
                        / 
                        NULLIF(
                            IF(v.value_daily IS NOT NULL, 1, 0) + IF(v.value_daily_2 IS NOT NULL, 1, 0) + IF(v.value_daily_3 IS NOT NULL, 1, 0) + IF(v.value_daily_4 IS NOT NULL, 1, 0) + IF(v.value_daily_5 IS NOT NULL, 1, 0) + IF(v.value_daily_6 IS NOT NULL, 1, 0) + IF(v.value_daily_7 IS NOT NULL, 1, 0) + IF(v.value_daily_8 IS NOT NULL, 1, 0) + IF(v.value_daily_9 IS NOT NULL, 1, 0) + IF(v.value_daily_10 IS NOT NULL, 1, 0),
                            0
                        ), 0
                    )
                    +
                    COALESCE(
                        (COALESCE(v.value_sts, 0) + COALESCE(v.value_sas, 0))
                        /
                        NULLIF(IF(v.value_sts IS NOT NULL, 1, 0) + IF(v.value_sas IS NOT NULL, 1, 0), 0), 0
                    )
                )
                /
                NULLIF(
                    IF(COALESCE(v.value_daily, v.value_daily_2, v.value_daily_3, v.value_daily_4, v.value_daily_5, v.value_daily_6, v.value_daily_7, v.value_daily_8, v.value_daily_9, v.value_daily_10) IS NOT NULL, 1, 0)
                    +
                    IF(COALESCE(v.value_sts, v.value_sas) IS NOT NULL, 1, 0), 
                    0
                )
                END), 0), 2) AS `{$mapel->nama_mapel}`";
        }

        $selectedClassIdSql = $classId ? "{$classId} AS class_id" : "s.class_id";
        $joinClassSql = $classId ? "JOIN class AS c ON c.id = {$classId}" : "JOIN class AS c ON s.class_id = c.id";

        // Buat query dasar
        $query = "
            SELECT
                s.id AS student_id,
                {$selectedClassIdSql},
                s.nama AS student_name,
                s.nis AS student_nis,
                c.class_name,
                " . implode(', ', $columns) . "
            FROM students AS s
            {$joinClassSql}
            LEFT JOIN `values` AS v ON s.id = v.student_id AND v.fst_id =:fstId AND v.mapel_id IN ($inMapelIds)
            LEFT JOIN mata_pelajarans AS mp ON v.mapel_id = mp.id
        ";

        $bindings = $classId ? [
            'classId' => $classId,
            'fstId' => $fstId,
            'classIdVal' => $classId,
            'fstIdVal' => $fstId,
            'classIdCw' => $classId,
            'fstIdCw' => $fstId,
        ] : [];

        // Filter berdasarkan class_id jika diberikan
        if ($classId) {
            $query .= " WHERE (s.class_id = :classId OR EXISTS (SELECT 1 FROM `values` val WHERE val.student_id = s.id AND val.class_id = :classIdVal AND val.fst_id = :fstIdVal) OR EXISTS (SELECT 1 FROM catatan_walikelas cw WHERE cw.student_id = s.id AND cw.class_id = :classIdCw AND cw.fst_id = :fstIdCw))";
        }

        $query .= " GROUP BY s.id, s.nama, c.class_name ORDER BY c.class_name, s.nama";
        // dd($query);
        // Jalankan query
        $data = DB::select($query, $bindings);
        return $data;
    }

    public function getStudentAllTp($classId = null, $student_id = null, $fstId = null)
    {
        $classId = $this->resolveHistoricalClassId($student_id, $fstId, $classId);

        // Ambil data siswa berdasarkan student_id (hanya satu siswa)
        $student = DB::table('students')
            ->where('id', $student_id)
            ->first();

        // Jika siswa tidak ditemukan, return response kosong
        if (!$student) {
            return ["mapel" => []];
        }

        // Ambil mata pelajaran yang aktif untuk kelas dan semester ini
        $mapels = DB::table('mapel_class_fst')
            ->join('mata_pelajarans', 'mapel_class_fst.mapel_id', '=', 'mata_pelajarans.id')
            ->where('mapel_class_fst.class_id', $classId)
            ->where('mapel_class_fst.fst_id', $fstId)
            ->where('mapel_class_fst.is_active', 1)
            ->select('mata_pelajarans.*')
            ->orderBy('mata_pelajarans.nama_mapel', 'asc')
            ->get();

        // Fallback jika mapel_class_fst kosong, ambil dari tpsiswas siswa
        if ($mapels->isEmpty() && $student_id) {
            $mapels = DB::table('tpsiswas as tp')
                ->join('mata_pelajarans as m', 'tp.mapel_id', '=', 'm.id')
                ->where('tp.siswa_id', $student_id)
                ->where('tp.fst_id', $fstId)
                ->select('m.*')
                ->distinct()
                ->orderBy('m.nama_mapel', 'asc')
                ->get();
        }

        // Inisialisasi hasil untuk 1 siswa
        $result = [
            "mapel" => []
        ];

        // Eager fetch semua data TP untuk seluruh mapel siswa sekaligus dalam 1 query tunggal (mencegah N+1 query)
        $mapelIds = $mapels->pluck('id')->toArray();
        $allTpData = collect([]);

        if (!empty($mapelIds)) {
            $tpQuery = DB::table('tpsiswas as v')
                ->leftJoin('m_tp as tp', 'v.tp_id', '=', 'tp.id')
                ->where('v.siswa_id', $student_id)
                ->where('v.fst_id', $fstId)
                ->whereIn('v.mapel_id', $mapelIds);

            if ($classId) {
                $tpQuery->where(function($q) use ($classId) {
                    $q->where('v.class_id', $classId)->orWhereNull('v.class_id');
                });
            }

            $allTpData = $tpQuery->select(
                    'v.mapel_id',
                    'v.tp_id',
                    'tp.tp_deskripsi',
                    'v.kktp',
                    'v.tampilkan'
                )
                ->get()
                ->groupBy('mapel_id');
        }

        foreach ($mapels as $mapel) {
            $data = $allTpData->get($mapel->id, collect([]));

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
            (
                COALESCE(
                    (COALESCE(v.value_daily, 0) + COALESCE(v.value_daily_2, 0) + COALESCE(v.value_daily_3, 0) + COALESCE(v.value_daily_4, 0) + COALESCE(v.value_daily_5, 0) + COALESCE(v.value_daily_6, 0) + COALESCE(v.value_daily_7, 0) + COALESCE(v.value_daily_8, 0) + COALESCE(v.value_daily_9, 0) + COALESCE(v.value_daily_10, 0))
                    / 
                    NULLIF(
                        IF(v.value_daily IS NOT NULL, 1, 0) + IF(v.value_daily_2 IS NOT NULL, 1, 0) + IF(v.value_daily_3 IS NOT NULL, 1, 0) + IF(v.value_daily_4 IS NOT NULL, 1, 0) + IF(v.value_daily_5 IS NOT NULL, 1, 0) + IF(v.value_daily_6 IS NOT NULL, 1, 0) + IF(v.value_daily_7 IS NOT NULL, 1, 0) + IF(v.value_daily_8 IS NOT NULL, 1, 0) + IF(v.value_daily_9 IS NOT NULL, 1, 0) + IF(v.value_daily_10 IS NOT NULL, 1, 0),
                        0
                    ), 0
                )
                +
                COALESCE(
                    (COALESCE(v.value_sts, 0) + COALESCE(v.value_sas, 0))
                    /
                    NULLIF(IF(v.value_sts IS NOT NULL, 1, 0) + IF(v.value_sas IS NOT NULL, 1, 0), 0), 0
                )
            )
            /
            NULLIF(
                IF(COALESCE(v.value_daily, v.value_daily_2, v.value_daily_3, v.value_daily_4, v.value_daily_5, v.value_daily_6, v.value_daily_7, v.value_daily_8, v.value_daily_9, v.value_daily_10) IS NOT NULL, 1, 0)
                +
                IF(COALESCE(v.value_sts, v.value_sas) IS NOT NULL, 1, 0), 
                0
            )
        ), 0), 2) AS avg_nilai_semua_mapel";

        $selectedClassIdSql = $classId ? "{$classId} AS class_id" : "s.class_id";
        $joinClassSql = $classId ? "JOIN class AS c ON c.id = {$classId}" : "JOIN class AS c ON s.class_id = c.id";

        // Buat query dasar
        $query = "
            SELECT
                s.id AS student_id,
                {$selectedClassIdSql},
                s.nama AS student_name,
                s.nis AS student_nis,
                s.nisn AS student_nisn,
                c.class_name,
                " . implode(', ', $columns) . "
            FROM students AS s
            {$joinClassSql}
            LEFT JOIN `values` AS v ON s.id = v.student_id AND v.fst_id =:fstId
        ";

        $bindings = $classId ? [
            'classId' => $classId,
            'fstId' => $fstId,
            'classIdVal' => $classId,
            'fstIdVal' => $fstId,
            'classIdCw' => $classId,
            'fstIdCw' => $fstId,
        ] : [];

        // Filter berdasarkan class_id jika diberikan
        if ($classId) {
            $query .= " WHERE (s.class_id = :classId OR EXISTS (SELECT 1 FROM `values` val WHERE val.student_id = s.id AND val.class_id = :classIdVal AND val.fst_id = :fstIdVal) OR EXISTS (SELECT 1 FROM catatan_walikelas cw WHERE cw.student_id = s.id AND cw.class_id = :classIdCw AND cw.fst_id = :fstIdCw)) ";
        }

        $query .= " GROUP BY s.id, s.nama, s.nis, s.nisn, c.class_name ORDER BY c.class_name, s.nama";

        // Jalankan query
        return DB::select($query, $bindings);
    }

    public function index()
    {
        $query = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc');
        
        // Role Management: Wali Kelas / Guru hanya melihat kelasnya
        if (Auth::user()->role_id != 1 && Auth::user()->class_id !== null) {
            $query->where('id', Auth::user()->class_id);
        }
        $classList = $query->get();

        $fstList = MasterDataCache::getAllFst();

        $className = null;
        if (Auth::user()->class_id !== null) {
            $className = DB::table('class')->where('id', Auth::user()->class_id)->value('class_name');
        }
        return view('nilaiakhir.index', compact('classList', 'className', 'fstList'));
    }
    public function detailNilaiAkhir(Request $request)
    {
        $classId = $this->resolveHistoricalClassId($request->student_id, $request->fst_id, $request->class_id);
        $data = $this->getStudentAllScores($classId, $request->student_id, $request->fst_id);
        $formattedStudents = [];
        foreach ($data as $student) {
            $studentArray = (array) $student;

            // Informasi siswa
            $studentInfo = [
                'student_id' => $studentArray['student_id'],
                'class_id' => $studentArray['class_id'] ?? $classId,
                'student_name' => $studentArray['student_name'],
                'class_name' => $studentArray['class_name'] ?? (DB::table('class')->where('id', $classId)->value('class_name') ?? 'Alumni / Lulus'),
                'foto_siswa_path' => $studentArray['foto_siswa_path'] ?? null,
                'student_nis' => $studentArray['student_nis'],
                'sakit' => $studentArray['sakit'] ?? 0,
                'izin' => $studentArray['izin'] ?? 0,
                'alpa' => $studentArray['alpa'] ?? 0,
                'avg_nilai_semua_mapel' => $studentArray['avg_nilai_semua_mapel'] ?? 0,
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
        $classId = $this->resolveHistoricalClassId($request->student_id, $request->fst_id, $request->class_id);
        $students = $this->getStudentAllScores($classId, $request->student_id, $request->fst_id); // Ambil data berdasarkan filter class_id (jika ada)
        $formattedStudents = [];
        $defaultClassName = $classId ? (DB::table('class')->where('id', $classId)->value('class_name') ?? 'Alumni / Lulus') : 'Alumni / Lulus';

        foreach ($students as $student) {
            $studentArray = (array) $student;

            // Informasi siswa
            $studentInfo = [
                'student_id' => $studentArray['student_id'],
                'class_id' => $studentArray['class_id'] ?? $classId,
                'student_name' => $studentArray['student_name'],
                'class_name' => $studentArray['class_name'] ?? $defaultClassName,
                'student_nis' => $studentArray['student_nis'],
                'sakit' => $studentArray['sakit'] ?? 0,
                'izin' => $studentArray['izin'] ?? 0,
                'alpa' => $studentArray['alpa'] ?? 0,
                'avg_nilai_semua_mapel' => $studentArray['avg_nilai_semua_mapel'] ?? 0,
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

    public function generateSingleRaportPdf($studentId, $classId, $fstId, $type = 'nilai', $tgl_print = null, $keputusan = null)
    {
        $resolvedClassId = $this->resolveHistoricalClassId($studentId, $fstId, $classId);

        $students = $this->getStudentAllScores($resolvedClassId, $studentId, $fstId);
        $studentsTP = $this->getStudentAllTp($resolvedClassId, $studentId, $fstId);
        $fst = MasterDataCache::getFst($fstId);
        $schoolData = MasterDataCache::getSchoolData();
        $studentEskul = DB::table('nilai_eskuls as ns')->select('ns.id', 'ns.nilai_eskul', 'ms.nama_eskul')->join('m_eskul as ms', 'ns.eskul_id', '=', 'ms.id')
            ->where('ns.student_id', $studentId)->where('ns.fst_id', $fstId)->get();

        // Ambil catatan wali kelas, presensi semester, dan status keputusan
        $catatanWalas = DB::table('catatan_walikelas')
            ->where('student_id', $studentId)
            ->where('fst_id', $fstId)
            ->first();

        $token = $catatanWalas ? ($catatanWalas->verification_token ?: \Illuminate\Support\Str::random(32)) : \Illuminate\Support\Str::random(32);

        if (!$catatanWalas) {
            $sakit = isset($students[0]) ? ($students[0]->sakit ?? 0) : 0;
            $izin  = isset($students[0]) ? ($students[0]->izin ?? 0) : 0;
            $alpa  = isset($students[0]) ? ($students[0]->alpa ?? 0) : 0;
            $catatan = null;
            $statusKenaikan = $keputusan;

            $effectiveClassId = $resolvedClassId ?: ($students[0]->class_id ?? 1);

            DB::table('catatan_walikelas')->updateOrInsert(
                [
                    'student_id' => $studentId,
                    'fst_id'     => $fstId,
                ],
                [
                    'class_id'           => $effectiveClassId,
                    'sakit'              => $sakit,
                    'izin'               => $izin,
                    'alpa'               => $alpa,
                    'catatan'            => null,
                    'status_kenaikan'    => $statusKenaikan,
                    'verification_token' => $token,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]
            );
        } else {
            if (!$catatanWalas->verification_token) {
                DB::table('catatan_walikelas')->where('id', $catatanWalas->id)->update(['verification_token' => $token]);
            }
            $sakit = $catatanWalas->sakit;
            $izin  = $catatanWalas->izin;
            $alpa  = $catatanWalas->alpa;
            $catatan = $catatanWalas->catatan;
            $statusKenaikan = $catatanWalas->status_kenaikan ?: $keputusan;
        }

        // Generate QR Code data URI untuk verifikasi publik raport
        $verifyUrl = route('raport.verify', $token);
        $qrCodeDataUri = null;
        try {
            $qrCodeDataUri = (new \chillerlan\QRCode\QRCode)->render($verifyUrl);
        } catch (\Exception $e) {
            $qrCodeDataUri = null;
        }

        $defaultClassName = $resolvedClassId ? (MasterDataCache::getClassName($resolvedClassId) ?? 'Alumni / Lulus') : 'Alumni / Lulus';
        $formattedStudents = [];

        foreach ($students as $student) {
            $studentArray = (array) $student;

            $studentInfo = [
                'student_id'            => $studentArray['student_id'],
                'class_id'              => $studentArray['class_id'] ?? $resolvedClassId,
                'student_name'          => $studentArray['student_name'],
                'class_name'            => $studentArray['class_name'] ?? $defaultClassName,
                'avg_nilai_semua_mapel' => $studentArray['avg_nilai_semua_mapel'] ?? 0,
                'student_nis'           => $studentArray['student_nis'],
                'sakit'                 => $sakit,
                'izin'                  => $izin,
                'alpa'                  => $alpa,
                'catatan'               => $catatan,
                'status_kenaikan'       => $statusKenaikan,
                'qr_code'               => $qrCodeDataUri,
                'verify_url'            => $verifyUrl,
                'foto_siswa_path'       => $studentArray['foto_siswa_path'] ?? null,
            ];

            $mapelScores = array_diff_key($studentArray, $studentInfo);

            $formattedStudents[] = array_merge($studentInfo, [
                'nilai_per_mapel' => $mapelScores,
                'school_data'     => $schoolData,
                'TP'              => is_array($studentsTP) && isset($studentsTP["mapel"]) ? $studentsTP["mapel"] : [],
                'fst'             => $fst,
                'eskul'           => $studentEskul,
            ]);
        }

        if (empty($formattedStudents)) {
            $rawStudent = DB::table('students')->where('id', $studentId)->first();
            $className = MasterDataCache::getClassName($resolvedClassId) ?? 'Alumni / Lulus';
            $formattedStudents[] = [
                'student_id'            => $studentId,
                'class_id'              => $resolvedClassId,
                'student_name'          => $rawStudent ? $rawStudent->nama : 'Siswa',
                'class_name'            => $className,
                'avg_nilai_semua_mapel' => 0,
                'student_nis'           => $rawStudent ? $rawStudent->nis : '-',
                'sakit'                 => $sakit,
                'izin'                  => $izin,
                'alpa'                  => $alpa,
                'catatan'               => $catatan,
                'status_kenaikan'       => $statusKenaikan,
                'qr_code'               => $qrCodeDataUri,
                'verify_url'            => $verifyUrl,
                'foto_siswa_path'       => $rawStudent ? $rawStudent->foto_siswa_path : null,
                'nilai_per_mapel'       => [],
                'school_data'           => $schoolData,
                'TP'                    => [],
                'fst'                   => $fst,
                'eskul'                 => $studentEskul,
            ];
        }

        $rawStudent = DB::table('students')->where('id', $studentId)->first();
        $walas = DB::table('users')->where('class_id', $resolvedClassId)->where('role_id', 2)->first();

        $keputusan = $statusKenaikan;

        $suffix = '';
        if ($type === 'cover') {
            $pdf = Pdf::loadView('docs.cover_identitas', [
                'student' => $rawStudent,
                'schoolData' => $schoolData,
                'tgl_print' => $tgl_print,
            ]);
            $suffix = '_Cover';
        } elseif ($type === 'all') {
            $includeCover = true;
            $rawStudentData = $rawStudent;
            $rawSchoolData = $schoolData;
            $pdf = Pdf::loadView('docs.nilai', compact('formattedStudents', 'tgl_print', 'keputusan', 'walas', 'includeCover', 'rawStudentData', 'rawSchoolData'));
            $suffix = '_Lengkap';
        } else {
            $includeCover = false;
            $pdf = Pdf::loadView('docs.nilai', compact('formattedStudents', 'tgl_print', 'keputusan', 'walas', 'includeCover'));
            $suffix = '';
        }

        $cleanTA   = $fst && !empty($fst->tahun_ajaran) ? str_replace(['/', ' '], ['-', '_'], $fst->tahun_ajaran) : 'TA';
        $cleanFase = $fst && !empty($fst->fase) ? 'Fase_' . ucwords($fst->fase) : 'Fase';
        $cleanSem  = $fst && !empty($fst->semester) ? 'Sem_' . preg_replace('/[^a-zA-Z0-9]/', '', $fst->semester) : 'Sem';
        $concated  = "{$cleanTA}_{$cleanFase}_{$cleanSem}";

        $safeClassName   = str_replace(['/', '\\', ' '], '_', $formattedStudents[0]['class_name']);
        $safeStudentName = str_replace(['/', '\\', ' '], '_', $formattedStudents[0]['student_name']);
        $pdfPath         = "raport/{$safeClassName}/{$concated}/{$safeStudentName}{$suffix}.pdf";

        Storage::disk('public')->put($pdfPath, $pdf->output());

        return [
            'pdf'               => $pdf,
            'pdf_path'          => $pdfPath,
            'safe_student_name' => $safeStudentName,
            'concated'          => $concated,
            'suffix'            => $suffix,
        ];
    }

    public function exportPDF(Request $request)
    {
        $result = $this->generateSingleRaportPdf(
            $request->student_id,
            $request->class_id,
            $request->fst_id,
            $request->input('type', 'nilai'),
            $request->tgl_print,
            $request->keputusan
        );

        $pdfPath = $result['pdf_path'];

        if ($request->has('download')) {
            $filename = "{$result['safe_student_name']}{$result['suffix']}.pdf";
            return response()->download(Storage::disk('public')->path($pdfPath), $filename);
        }

        // Jika diakses langsung sebagai navigasi dokumen di tab browser (misal klik link biasa)
        if ($request->header('Sec-Fetch-Dest') === 'document' || $request->has('direct') || !$request->ajax()) {
            return redirect(url('storage/' . $pdfPath));
        }

        // Default: response JSON untuk AJAX / fetch JavaScript
        return response()->json(['pdf_url' => url('storage/' . $pdfPath)]);
    }

    public function exportServer(Request $request)
    {
        $request->validate([
            'class_id' => 'required|integer',
            'fst_id'   => 'required|integer',
        ]);

        $classId   = $request->class_id;
        $fstId     = $request->fst_id;
        $type      = $request->input('type', 'all');
        $tgl_print = $request->input('tgl_print', now()->toDateString());
        $keputusan = $request->input('keputusan', null);

        $class = DB::table('class')->where('id', $classId)->first();
        if (!$class) {
            return response()->json(['status' => 'error', 'message' => 'Kelas tidak ditemukan.'], 404);
        }

        $students = DB::table('students')->where('class_id', $classId)->orderBy('nama', 'asc')->get();
        if ($students->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Tidak ada siswa pada kelas ini.'], 400);
        }

        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $exportedCount = 0;
        $lastFolder = '';

        foreach ($students as $student) {
            try {
                $res = $this->generateSingleRaportPdf($student->id, $classId, $fstId, $type, $tgl_print, $keputusan);
                $lastFolder = dirname($res['pdf_path']);
                $exportedCount++;
            } catch (\Exception $e) {
                Log::error("Gagal export raport server untuk siswa ID {$student->id}: " . $e->getMessage());
            }
        }

        return response()->json([
            'status'         => 'success',
            'message'        => "Berhasil meng-export {$exportedCount} rapor siswa ke server arsip ({$lastFolder}).",
            'exported_count' => $exportedCount,
            'total_students' => $students->count(),
            'folder'         => $lastFolder,
        ]);
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

    public function exportLegerExcel(Request $request)
    {
        $classId = $request->class_id;
        $fstId = $request->fst_id;

        $class = DB::table('class')->where('id', $classId)->first();
        $className = $class ? $class->class_name : 'Kelas';
        $fst = MasterDataCache::getFst($fstId);
        $schoolData = MasterDataCache::getSchoolData();

        // Wali Kelas
        $walas = DB::table('users')->where('class_id', $classId)->first();
        $walasName = $walas ? $walas->name : null;

        // Mapel aktif
        $mapels = DB::table('mapel_class_fst')
            ->join('mata_pelajarans', 'mapel_class_fst.mapel_id', '=', 'mata_pelajarans.id')
            ->where('mapel_class_fst.class_id', $classId)
            ->where('mapel_class_fst.fst_id', $fstId)
            ->where('mapel_class_fst.is_active', 1)
            ->select('mata_pelajarans.id', 'mata_pelajarans.nama_mapel')
            ->orderBy('mata_pelajarans.nama_mapel', 'asc')
            ->get();

        $mapelNames = $mapels->pluck('nama_mapel')->toArray();

        // Data Siswa
        $studentsRaw = DB::table('students')
            ->where('class_id', $classId)
            ->orderBy('nama', 'asc')
            ->get();

        // Data Presensi & Catatan Walas
        $catatanWalas = DB::table('catatan_walikelas')
            ->where('class_id', $classId)
            ->where('fst_id', $fstId)
            ->get()
            ->keyBy('student_id');

        // Nilai Siswa
        $valuesRaw = DB::table('values')
            ->where('class_id', $classId)
            ->where('fst_id', $fstId)
            ->get();

        // Hitung rata-rata per mapel per siswa
        $studentScores = [];
        foreach ($valuesRaw as $val) {
            $dailyScores = array_filter([
                $val->value_daily, $val->value_daily_2, $val->value_daily_3, $val->value_daily_4, $val->value_daily_5,
                $val->value_daily_6, $val->value_daily_7, $val->value_daily_8, $val->value_daily_9, $val->value_daily_10
            ], function($v) { return !is_null($v); });

            $avgDaily = count($dailyScores) > 0 ? (array_sum($dailyScores) / count($dailyScores)) : null;

            $stsSasScores = array_filter([$val->value_sts, $val->value_sas], function($v) { return !is_null($v); });
            $avgStsSas = count($stsSasScores) > 0 ? (array_sum($stsSasScores) / count($stsSasScores)) : null;

            $parts = array_filter([$avgDaily, $avgStsSas], function($v) { return !is_null($v); });
            $finalScore = count($parts) > 0 ? round(array_sum($parts) / count($parts), 2) : null;

            $studentScores[$val->student_id][$val->mapel_id] = $finalScore;
        }

        // Susun data siswa
        $students = [];
        $mapelColStats = [];
        foreach ($mapelNames as $mpName) {
            $mapelColStats[$mpName] = [];
        }

        foreach ($studentsRaw as $std) {
            $scores = [];
            $sum = 0;
            $count = 0;

            foreach ($mapels as $mp) {
                $sc = $studentScores[$std->id][$mp->id] ?? null;
                $scores[$mp->nama_mapel] = $sc;
                if (!is_null($sc)) {
                    $sum += $sc;
                    $count++;
                    $mapelColStats[$mp->nama_mapel][] = $sc;
                }
            }

            $avg = $count > 0 ? round($sum / $count, 2) : 0;
            $walasInfo = $catatanWalas->get($std->id);

            $students[] = [
                'id' => $std->id,
                'nis' => $std->nis,
                'nisn' => $std->nisn,
                'nama' => $std->nama,
                'jenis_kelamin' => $std->jenis_kelamin,
                'scores' => $scores,
                'total_nilai' => $sum,
                'avg_nilai' => $avg,
                'sakit' => $walasInfo ? $walasInfo->sakit : ($std->sakit ?? 0),
                'izin' => $walasInfo ? $walasInfo->izin : ($std->izin ?? 0),
                'alpa' => $walasInfo ? $walasInfo->alpa : ($std->alpa ?? 0),
                'keputusan' => $walasInfo ? $walasInfo->status_kenaikan : 'Naik Kelas',
                'rank' => 1,
            ];
        }

        // Hitung Ranking
        usort($students, function ($a, $b) {
            return $b['avg_nilai'] <=> $a['avg_nilai'];
        });
        foreach ($students as $idx => &$st) {
            $st['rank'] = $idx + 1;
        }
        unset($st);

        // Urutkan kembali berdasarkan nama untuk kerapian leger
        usort($students, function ($a, $b) {
            return strcmp($a['nama'], $b['nama']);
        });

        // Hitung statistik kelas per mapel
        $stats = ['avg' => [], 'max' => [], 'min' => []];
        foreach ($mapelNames as $mpName) {
            $colValues = $mapelColStats[$mpName];
            if (count($colValues) > 0) {
                $stats['avg'][$mpName] = round(array_sum($colValues) / count($colValues), 2);
                $stats['max'][$mpName] = max($colValues);
                $stats['min'][$mpName] = min($colValues);
            }
        }

        $payload = [
            'schoolData' => $schoolData,
            'className' => $className,
            'fst' => $fst,
            'walasName' => $walasName,
            'mapelNames' => $mapelNames,
            'students' => $students,
            'stats' => $stats,
        ];

        $safeClassName = str_replace(' ', '_', $className);
        return Excel::download(new \App\Exports\LegerNilaiExport($payload), "Leger_Nilai_{$safeClassName}.xlsx");
    }
}
