<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use chillerlan\QRCode\QRCode;

class P5Controller extends Controller
{
    /**
     * Halaman Utama Modul P5 - Daftar Projek
     */
    public function index(Request $request)
    {
        if (!\App\Models\Setting::isModuleEnabled('p5', true) && Auth::user()->role_id != 1) {
            abort(403, 'Modul Projek P5 sedang dinonaktifkan oleh Administrator.');
        }

        $query = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc');
        if (Auth::user()->role_id != 1 && Auth::user()->class_id !== null) {
            $query->where('id', Auth::user()->class_id);
        }
        $classList = $query->get();

        $fstList = DB::table('m_fst_pembelajaran')
            ->select('id', 'fase', 'semester', 'tahun_ajaran', 'ta', 'is_locked')
            ->orderBy('id', 'asc')
            ->get();

        $selectedClassId = $request->class_id ?: ($classList->first() ? $classList->first()->id : null);
        $selectedFstId = $request->fst_id ?: ($fstList->first() ? $fstList->first()->id : null);

        $projekList = [];
        if ($selectedClassId && $selectedFstId) {
            $projekList = DB::table('p5_projek as p')
                ->leftJoin('class as c', 'p.class_id', '=', 'c.id')
                ->leftJoin('m_fst_pembelajaran as f', 'p.fst_id', '=', 'f.id')
                ->leftJoin('users as u', 'p.fasilitator_id', '=', 'u.id')
                ->where('p.class_id', $selectedClassId)
                ->where('p.fst_id', $selectedFstId)
                ->select(
                    'p.*',
                    'c.class_name',
                    'f.fase',
                    'f.semester',
                    'f.tahun_ajaran',
                    'u.name as fasilitator_name',
                    DB::raw('(SELECT COUNT(*) FROM p5_projek_subelemen WHERE projek_id = p.id) as subelemen_count'),
                    DB::raw('(SELECT COUNT(DISTINCT student_id) FROM p5_penilaian WHERE projek_id = p.id) as students_assessed_count')
                )
                ->orderBy('p.id', 'desc')
                ->get();
        }

        // Ambil daftar master dimensi & subelemen untuk modal tambah projek
        $dimensiMaster = DB::table('p5_dimensi')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($dim) {
                $elemen = DB::table('p5_elemen')->where('dimensi_id', $dim->id)->get()->map(function ($el) {
                    $el->subelemen = DB::table('p5_subelemen')->where('elemen_id', $el->id)->get();
                    return $el;
                });
                $dim->elemen = $elemen;
                return $dim;
            });

        $teachers = DB::table('users')->where('role_id', '<=', 2)->select('id', 'name')->orderBy('name')->get();

        return view('p5.index', compact(
            'classList',
            'fstList',
            'selectedClassId',
            'selectedFstId',
            'projekList',
            'dimensiMaster',
            'teachers'
        ));
    }

    /**
     * Simpan Projek P5 Baru
     */
    public function storeProjek(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:class,id',
            'fst_id' => 'required|exists:m_fst_pembelajaran,id',
            'tema' => 'required|string|max:150',
            'nama_projek' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'subelemen_ids' => 'required|array|min:1',
            'subelemen_ids.*' => 'exists:p5_subelemen,id',
        ]);

        DB::beginTransaction();
        try {
            $projekId = DB::table('p5_projek')->insertGetId([
                'class_id' => $request->class_id,
                'fst_id' => $request->fst_id,
                'tema' => $request->tema,
                'nama_projek' => $request->nama_projek,
                'deskripsi' => $request->deskripsi,
                'fasilitator_id' => $request->fasilitator_id ?: Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($request->subelemen_ids as $subId) {
                DB::table('p5_projek_subelemen')->insert([
                    'projek_id' => $projekId,
                    'subelemen_id' => $subId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Projek P5 berhasil dibuat!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat projek: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Ambil data detail projek untuk modal Edit
     */
    public function editProjek($id)
    {
        $projek = DB::table('p5_projek')->where('id', $id)->first();
        if (!$projek) {
            return response()->json(['message' => 'Projek tidak ditemukan.'], 404);
        }

        $selectedSubIds = DB::table('p5_projek_subelemen')
            ->where('projek_id', $id)
            ->pluck('subelemen_id')
            ->toArray();

        return response()->json([
            'projek' => $projek,
            'subelemen_ids' => $selectedSubIds
        ]);
    }

    /**
     * Update Projek P5
     */
    public function updateProjek(Request $request, $id)
    {
        $request->validate([
            'tema' => 'required|string|max:150',
            'nama_projek' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'subelemen_ids' => 'required|array|min:1',
            'subelemen_ids.*' => 'exists:p5_subelemen,id',
        ]);

        DB::beginTransaction();
        try {
            DB::table('p5_projek')->where('id', $id)->update([
                'tema' => $request->tema,
                'nama_projek' => $request->nama_projek,
                'deskripsi' => $request->deskripsi,
                'fasilitator_id' => $request->fasilitator_id ?: Auth::id(),
                'updated_at' => now(),
            ]);

            // Sync subelemen
            DB::table('p5_projek_subelemen')->where('projek_id', $id)->delete();
            foreach ($request->subelemen_ids as $subId) {
                DB::table('p5_projek_subelemen')->insert([
                    'projek_id' => $id,
                    'subelemen_id' => $subId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Projek P5 berhasil diperbarui!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memperbarui projek: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hapus Projek P5
     */
    public function destroyProjek($id)
    {
        DB::beginTransaction();
        try {
            DB::table('p5_penilaian')->where('projek_id', $id)->delete();
            DB::table('p5_projek_subelemen')->where('projek_id', $id)->delete();
            DB::table('p5_projek')->where('id', $id)->delete();
            DB::commit();
            return response()->json(['message' => 'Projek P5 berhasil dihapus!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Lembar Penilaian Siswa per Projek
     */
    public function penilaian($id)
    {
        $projek = DB::table('p5_projek as p')
            ->join('class as c', 'p.class_id', '=', 'c.id')
            ->join('m_fst_pembelajaran as f', 'p.fst_id', '=', 'f.id')
            ->where('p.id', $id)
            ->select('p.*', 'c.class_name', 'f.fase', 'f.semester', 'f.tahun_ajaran', 'f.is_locked')
            ->first();

        if (!$projek) {
            return redirect()->route('p5.index')->with('error', 'Projek tidak ditemukan.');
        }

        // Ambil target subelemen beserta dimensi & elemennya
        $targetSubelemen = DB::table('p5_projek_subelemen as ps')
            ->join('p5_subelemen as s', 'ps.subelemen_id', '=', 's.id')
            ->join('p5_elemen as e', 's.elemen_id', '=', 'e.id')
            ->join('p5_dimensi as d', 'e.dimensi_id', '=', 'd.id')
            ->where('ps.projek_id', $id)
            ->select(
                's.id as subelemen_id',
                's.nama_subelemen',
                's.capaian_fase',
                'e.nama_elemen',
                'd.kode as dimensi_kode',
                'd.nama_dimensi'
            )
            ->orderBy('d.id', 'asc')
            ->orderBy('e.id', 'asc')
            ->get();

        // Ambil data siswa
        $students = DB::table('students')
            ->where('class_id', $projek->class_id)
            ->orderBy('nama', 'asc')
            ->get();

        // Ambil penilaian yang sudah tersimpan
        $penilaian = DB::table('p5_penilaian')
            ->where('projek_id', $id)
            ->get()
            ->groupBy('student_id');

        return view('p5.penilaian', compact('projek', 'targetSubelemen', 'students', 'penilaian'));
    }

    /**
     * Simpan Bulk Penilaian P5 Siswa
     */
    public function storePenilaian(Request $request, $id)
    {
        $projek = DB::table('p5_projek')->where('id', $id)->first();
        if (!$projek) {
            return response()->json(['message' => 'Projek tidak ditemukan.'], 404);
        }

        // Cek apakah semester terkunci
        $fst = DB::table('m_fst_pembelajaran')->where('id', $projek->fst_id)->first();
        if ($fst && $fst->is_locked) {
            return response()->json(['message' => 'Semester ini telah dikunci oleh Kurikulum. Penilaian P5 tidak dapat diubah.'], 403);
        }

        $ratings = $request->input('ratings', []); // [student_id => [subelemen_id => 'BSH']]
        $catatan = $request->input('catatan', []); // [student_id => 'catatan teks']

        DB::beginTransaction();
        try {
            foreach ($ratings as $studentId => $subs) {
                $studentCatatan = isset($catatan[$studentId]) ? trim($catatan[$studentId]) : null;

                foreach ($subs as $subelemenId => $predikat) {
                    if (in_array($predikat, ['MB', 'SB', 'BSH', 'SAB'])) {
                        DB::table('p5_penilaian')->updateOrInsert(
                            [
                                'projek_id' => $id,
                                'student_id' => $studentId,
                                'subelemen_id' => $subelemenId,
                            ],
                            [
                                'predikat' => $predikat,
                                'catatan_proses' => $studentCatatan,
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            }

            DB::commit();
            return response()->json(['message' => 'Semua penilaian Projek P5 berhasil disimpan!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan penilaian: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cetak Lembar Rapor Projek P5 Resmi (PDF)
     */
    public function cetakRaportP5(Request $request)
    {
        $studentId = $request->student_id;
        $classId = $request->class_id;
        $fstId = $request->fst_id;

        $student = DB::table('students')->where('id', $studentId)->first();
        if (!$student) {
            return response()->json(['message' => 'Data siswa tidak ditemukan.'], 404);
        }

        // Tentukan kelas yang dipakai (menggunakan class_id yang diminta/historis jika ada)
        $targetClassId = $classId ?: $student->class_id;
        $classObj = DB::table('class')->where('id', $targetClassId)->first();
        $student->class_id = $targetClassId;
        $student->class_name = $classObj ? $classObj->class_name : '-';

        $fst = \App\Services\MasterDataCache::getFst($fstId);
        $schoolData = \App\Services\MasterDataCache::getSchoolData();

        // Ambil semua projek P5 di kelas & semester ini (atau yang dinilai untuk siswa ini)
        $projeks = DB::table('p5_projek as p')
            ->leftJoin('users as u', 'p.fasilitator_id', '=', 'u.id')
            ->where('p.fst_id', $fstId)
            ->where(function ($q) use ($targetClassId, $studentId) {
                if ($targetClassId) {
                    $q->where('p.class_id', $targetClassId);
                }
                $q->orWhereExists(function ($sub) use ($studentId) {
                    $sub->select(DB::raw(1))
                        ->from('p5_penilaian as pp')
                        ->whereColumn('pp.projek_id', 'p.id')
                        ->where('pp.student_id', $studentId);
                });
            })
            ->select('p.*', 'u.name as fasilitator_name')
            ->distinct()
            ->get();

        $projekDetails = [];
        foreach ($projeks as $prj) {
            $targets = DB::table('p5_projek_subelemen as ps')
                ->join('p5_subelemen as s', 'ps.subelemen_id', '=', 's.id')
                ->join('p5_elemen as e', 's.elemen_id', '=', 'e.id')
                ->join('p5_dimensi as d', 'e.dimensi_id', '=', 'd.id')
                ->where('ps.projek_id', $prj->id)
                ->select(
                    's.id as subelemen_id',
                    's.nama_subelemen',
                    's.capaian_fase',
                    'e.nama_elemen',
                    'd.nama_dimensi',
                    'd.kode as dimensi_kode'
                )
                ->orderBy('d.id', 'asc')
                ->orderBy('e.id', 'asc')
                ->get();

            // Ambil penilaian siswa pada projek ini
            $ratings = DB::table('p5_penilaian')
                ->where('projek_id', $prj->id)
                ->where('student_id', $studentId)
                ->get()
                ->keyBy('subelemen_id');

            $catatanProses = $ratings->first() ? $ratings->first()->catatan_proses : null;

            $projekDetails[] = [
                'projek' => $prj,
                'targets' => $targets,
                'ratings' => $ratings,
                'catatan_proses' => $catatanProses,
            ];
        }

        // Generate QR Code untuk verifikasi rapor
        $catatanWalas = DB::table('catatan_walikelas')
            ->where('student_id', $studentId)
            ->where('fst_id', $fstId)
            ->first();

        $token = $catatanWalas && $catatanWalas->verification_token ? $catatanWalas->verification_token : \Illuminate\Support\Str::random(32);
        $verifyUrl = route('raport.verify', $token);
        $qrCodeDataUri = null;
        try {
            $qrCodeDataUri = (new QRCode)->render($verifyUrl);
        } catch (\Exception $e) {
            $qrCodeDataUri = null;
        }

        $tgl_print = $request->tgl_print ?: date('d F Y');

        $pdf = Pdf::loadView('docs.raport_p5', compact(
            'student',
            'fst',
            'schoolData',
            'projekDetails',
            'qrCodeDataUri',
            'tgl_print'
        ))->setPaper('a4', 'portrait');

        $cleanTA   = $fst && !empty($fst->tahun_ajaran) ? str_replace(['/', ' '], ['-', '_'], $fst->tahun_ajaran) : 'TA';
        $cleanFase = $fst && !empty($fst->fase) ? 'Fase_' . ucwords($fst->fase) : 'Fase';
        $cleanSem  = $fst && !empty($fst->semester) ? 'Sem_' . preg_replace('/[^a-zA-Z0-9]/', '', $fst->semester) : 'Sem';
        $concated  = "{$cleanTA}_{$cleanFase}_{$cleanSem}";

        $safeClassName   = str_replace(['/', '\\', ' '], '_', $student->class_name);
        $safeStudentName = str_replace(['/', '\\', ' '], '_', $student->nama);
        $pdfPath         = "raport_p5/{$safeClassName}/{$concated}/{$safeStudentName}_P5.pdf";

        Storage::disk('public')->put($pdfPath, $pdf->output());

        return response()->json(['pdf_url' => asset('storage/' . $pdfPath)]);
    }
}
