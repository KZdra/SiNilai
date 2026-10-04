<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class KenaikanKelasController extends Controller
{
    /**
     * Halaman Utama Kenaikan Kelas / Pemindahan Rombel Massal
     */
    public function index(Request $request)
    {
        $classList = DB::table('class')->orderBy('class_name', 'asc')->get();
        $sourceClassId = $request->source_class_id;

        $students = collect([]);
        $sourceClass = null;

        if ($sourceClassId) {
            $sourceClass = DB::table('class')->where('id', $sourceClassId)->first();
            $students = DB::table('students')
                ->where('class_id', $sourceClassId)
                ->orderBy('nama', 'asc')
                ->get();
        }

        $actionType = $request->action_type ?: 'promote';
        $autoMap = $this->buildAutoPromotionMap();

        return view('admin.kenaikan_kelas', compact('classList', 'sourceClassId', 'sourceClass', 'students', 'actionType', 'autoMap'));
    }

    /**
     * Membangun pemetaan kenaikan kelas otomatis satu sekolah secara berjenjang
     */
    public function buildAutoPromotionMap()
    {
        $classList = DB::table('class')->orderBy('class_name', 'asc')->get();
        $mapping = [];

        // Eager count jumlah siswa per kelas dalam 1 query (mencegah N+1 query)
        $studentCounts = DB::table('students')
            ->select('class_id', DB::raw('count(*) as total'))
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        foreach ($classList as $cls) {
            $name = trim($cls->class_name);
            $studentCount = $studentCounts[$cls->id] ?? 0;

            // Cek jika kelas XII / 12 -> Lulus / Alumni
            if (preg_match('/^(XII|12)\b/i', $name)) {
                $mapping[] = [
                    'source_class_id' => $cls->id,
                    'source_class_name' => $cls->class_name,
                    'level' => 12,
                    'target_class_id' => null,
                    'target_class_name' => 'Alumni / Lulus',
                    'action_type' => 'graduate',
                    'student_count' => $studentCount,
                ];
            }
            // Cek jika kelas XI / 11 -> Naik ke XII
            elseif (preg_match('/^XI\b/i', $name)) {
                $targetName = preg_replace('/^XI\b/i', 'XII', $name);
                $target = $classList->first(fn($c) => strcasecmp(trim($c->class_name), trim($targetName)) === 0);
                $mapping[] = [
                    'source_class_id' => $cls->id,
                    'source_class_name' => $cls->class_name,
                    'level' => 11,
                    'target_class_id' => $target ? $target->id : null,
                    'target_class_name' => $target ? $target->class_name : 'Tidak Ditemukan',
                    'action_type' => $target ? 'promote' : 'unmatched',
                    'student_count' => $studentCount,
                ];
            }
            // Cek jika kelas X / 10 -> Naik ke XI
            elseif (preg_match('/^X\b/i', $name)) {
                $targetName = preg_replace('/^X\b/i', 'XI', $name);
                $target = $classList->first(fn($c) => strcasecmp(trim($c->class_name), trim($targetName)) === 0);
                $mapping[] = [
                    'source_class_id' => $cls->id,
                    'source_class_name' => $cls->class_name,
                    'level' => 10,
                    'target_class_id' => $target ? $target->id : null,
                    'target_class_name' => $target ? $target->class_name : 'Tidak Ditemukan',
                    'action_type' => $target ? 'promote' : 'unmatched',
                    'student_count' => $studentCount,
                ];
            }
        }

        // Urutkan mapping: tingkat 12 dulu, baru 11, baru 10 (SOP Top-Down)
        usort($mapping, function ($a, $b) {
            return $b['level'] <=> $a['level'];
        });

        return $mapping;
    }

    /**
     * Proses Tutup Tahun Ajaran: Promosi Berjenjang Otomatis Seluruh Sekolah
     */
    public function tutupTahunAjaran(Request $request)
    {
        $skipTinggalKelas = $request->boolean('skip_tinggal_kelas', true);
        $mapping = $this->buildAutoPromotionMap();

        DB::beginTransaction();
        try {
            $totalGraduated = 0;
            $totalPromoted = 0;
            $details = [];

            $activeFst = DB::table('m_fst_pembelajaran')->where('is_locked', false)->orderBy('id', 'desc')->first()
                      ?? DB::table('m_fst_pembelajaran')->orderBy('id', 'desc')->first();

            // Siswa yang berstatus 'Tinggal Kelas' di catatan walikelas pada semester aktif (BUG-03 fix)
            $retainedStudentIds = [];
            if ($skipTinggalKelas && $activeFst) {
                $retainedStudentIds = DB::table('catatan_walikelas')
                    ->where('fst_id', $activeFst->id)
                    ->where(function($q) {
                        $q->where('status_kenaikan', 'like', '%tinggal%')
                          ->orWhere('status_kenaikan', 'like', '%tidak naik%');
                    })
                    ->pluck('student_id')
                    ->toArray();
            }

            // Eksekusi secara Top-Down (XII -> XI -> X) untuk menghindari benturan rombel
            foreach ($mapping as $map) {
                if ($map['action_type'] === 'unmatched') {
                    continue;
                }

                // 1. Simpan Riwayat Rombel Siswa ke student_class_history sebelum class_id diubah
                $studentsInClass = DB::table('students')->where('class_id', $map['source_class_id'])->get();
                foreach ($studentsInClass as $std) {
                    $isRetained = in_array($std->id, $retainedStudentIds);
                    $histStatus = 'naik_kelas';
                    if ($map['action_type'] === 'graduate') {
                        $histStatus = 'lulus';
                    } elseif ($isRetained) {
                        $histStatus = 'tinggal_kelas';
                    }

                    if ($activeFst) {
                        DB::table('student_class_history')->updateOrInsert(
                            ['student_id' => $std->id, 'fst_id' => $activeFst->id],
                            [
                                'class_id'     => $map['source_class_id'],
                                'status'       => $histStatus,
                                'tahun_ajaran' => $activeFst->tahun_ajaran ?? null,
                                'semester'     => $activeFst->semester ?? null,
                                'keterangan'   => "Tutup TA: {$map['source_class_name']} ➔ " . ($map['target_class_name'] ?? 'Lulus'),
                                'created_at'   => now(),
                                'updated_at'   => now(),
                            ]
                        );
                    }
                }

                // 2. Eksekusi perpindahan kelas
                $query = DB::table('students')->where('class_id', $map['source_class_id']);

                if ($skipTinggalKelas && count($retainedStudentIds) > 0) {
                    $query->whereNotIn('id', $retainedStudentIds);
                }

                $affectedStudentIds = (clone $query)->pluck('id')->toArray();

                if ($map['action_type'] === 'graduate') {
                    $count = $query->update([
                        'class_id'    => null,
                        'status'      => 'lulus',
                        'tahun_lulus' => date('Y'),
                        'updated_at'  => now(),
                    ]);
                    if (!empty($affectedStudentIds)) {
                        DB::table('users')->whereIn('student_id', $affectedStudentIds)->update([
                            'class_id'   => null,
                            'updated_at' => now(),
                        ]);
                    }
                    $totalGraduated += $count;
                } else {
                    $count = $query->update([
                        'class_id'   => $map['target_class_id'],
                        'status'     => 'aktif',
                        'updated_at' => now(),
                    ]);
                    if (!empty($affectedStudentIds)) {
                        DB::table('users')->whereIn('student_id', $affectedStudentIds)->update([
                            'class_id'   => $map['target_class_id'],
                            'updated_at' => now(),
                        ]);
                    }
                    $totalPromoted += $count;
                }

                $details[] = "{$map['source_class_name']} ➔ {$map['target_class_name']}: {$count} siswa";
            }

            $totalRetained = count($retainedStudentIds);

            // 1. Kunci Otomatis FST Lama yang masih aktif (is_locked = false)
            $lockedFstCount = DB::table('m_fst_pembelajaran')
                ->where('is_locked', false)
                ->update([
                    'is_locked'  => true,
                    'locked_at'   => now(),
                    'locked_by'   => Auth::id(),
                    'updated_at'  => now(),
                ]);

            // 2. Otomatis Generate FST Baru (Tahun Ajaran Baru - Semester Ganjil / I)
            $latestFst = DB::table('m_fst_pembelajaran')->orderBy('id', 'desc')->first();
            $nextTahunAjaran = null;
            $nextTa = null;

            if ($latestFst && preg_match('/^(\d{4})\/(\d{4})$/', trim($latestFst->tahun_ajaran), $matches)) {
                $startYear = (int) $matches[1] + 1;
                $endYear = (int) $matches[2] + 1;
                $nextTahunAjaran = "{$startYear}/{$endYear}";
                $nextTa = substr((string)$startYear, -2) . substr((string)$endYear, -2);
            } else {
                $currYear = (int) date('Y');
                $nextTahunAjaran = "{$currYear}/" . ($currYear + 1);
                $nextTa = substr((string)$currYear, -2) . substr((string)($currYear + 1), -2);
            }

            // Ambil semua fase kurikulum yang aktif digunakan di sekolah (default E dan F)
            $fases = DB::table('m_fst_pembelajaran')->distinct()->pluck('fase')->filter()->toArray();
            if (empty($fases)) {
                $fases = ['E', 'F'];
            }

            $createdFstNames = [];
            foreach ($fases as $fase) {
                // Cek apakah FST Semester I tahun ajaran baru sudah pernah dibuat
                $exists = DB::table('m_fst_pembelajaran')
                    ->where('fase', $fase)
                    ->where('tahun_ajaran', $nextTahunAjaran)
                    ->where(function($q) {
                        $q->where('semester', 'like', '%1%')
                          ->orWhere('semester', 'like', '%Ganjil%')
                          ->orWhere('semester', 'like', '%Satu%');
                    })
                    ->exists();

                if (!$exists) {
                    $newFstId = DB::table('m_fst_pembelajaran')->insertGetId([
                        'fase'         => $fase,
                        'semester'     => 'I (Satu)',
                        'tahun_ajaran' => $nextTahunAjaran,
                        'ta'           => 'tengah',
                        'is_locked'    => false,
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                    $createdFstNames[] = "Fase {$fase} (Sem. I - {$nextTahunAjaran})";
                }
            }

            $fstInfoMsg = !empty($createdFstNames)
                ? "FST baru berhasil dibuat: " . implode(', ', $createdFstNames) . ". Semester sebelumnya telah dikunci otomatis."
                : "Semester sebelumnya telah dikunci otomatis.";

            // Audit Trail ke database
            DB::table('nilai_audit_logs')->insert([
                'user_id' => Auth::id(),
                'action' => 'KENAIKAN_KELAS',
                'field' => 'tutup_tahun_ajaran',
                'old_value' => 'Rombel Seluruh Sekolah',
                'new_value' => "Tahun Ajaran Baru {$nextTahunAjaran}",
                'reason' => "Tutup Tahun Ajaran Massal: {$totalGraduated} siswa lulus, {$totalPromoted} siswa naik kelas, {$totalRetained} siswa tinggal kelas. {$fstInfoMsg}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Proses Tutup Tahun Ajaran berhasil! {$totalGraduated} siswa lulus, {$totalPromoted} siswa naik kelas. {$fstInfoMsg}",
                'total_graduated'   => $totalGraduated,
                'total_promoted'    => $totalPromoted,
                'total_retained'    => $totalRetained,
                'locked_fst_count'  => $lockedFstCount,
                'new_academic_year' => $nextTahunAjaran,
                'created_fsts'      => $createdFstNames,
                'details'           => $details,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memproses tutup tahun ajaran: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint AJAX untuk mengambil siswa berdasarkan kelas asal
     */
    public function getStudents(Request $request)
    {
        $classId = $request->class_id;
        if (!$classId) {
            return response()->json(['students' => []]);
        }

        $students = DB::table('students')
            ->where('class_id', $classId)
            ->select('id', 'nama', 'nis', 'nisn', 'jenis_kelamin', 'foto_siswa_path')
            ->orderBy('nama', 'asc')
            ->get();

        return response()->json(['students' => $students]);
    }

    /**
     * Proses Kenaikan Kelas / Kelulusan Massal
     */
    public function promote(Request $request)
    {
        $request->validate([
            'source_class_id' => 'required|integer|exists:class,id',
            'action_type' => 'required|in:promote,graduate',
            'target_class_id' => 'required_if:action_type,promote|nullable|integer|exists:class,id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer|exists:students,id',
        ]);

        if ($request->action_type === 'promote' && $request->source_class_id == $request->target_class_id) {
            return response()->json(['message' => 'Kelas tujuan tidak boleh sama dengan kelas asal.'], 422);
        }

        DB::beginTransaction();
        try {
            $activeFst = DB::table('m_fst_pembelajaran')->where('is_locked', false)->orderBy('id', 'desc')->first()
                      ?? DB::table('m_fst_pembelajaran')->orderBy('id', 'desc')->first();

            $targetClassId = $request->action_type === 'promote' ? $request->target_class_id : null;
            $sourceClass = DB::table('class')->where('id', $request->source_class_id)->first();
            $targetClass = $targetClassId ? DB::table('class')->where('id', $targetClassId)->first() : null;

            // Catat riwayat rombel ke student_class_history
            if ($activeFst) {
                $histStatus = $request->action_type === 'graduate' ? 'lulus' : 'naik_kelas';
                foreach ($request->student_ids as $stdId) {
                    DB::table('student_class_history')->updateOrInsert(
                        ['student_id' => $stdId, 'fst_id' => $activeFst->id],
                        [
                            'class_id'     => $request->source_class_id,
                            'status'       => $histStatus,
                            'tahun_ajaran' => $activeFst->tahun_ajaran ?? null,
                            'semester'     => $activeFst->semester ?? null,
                            'keterangan'   => "Mutasi/Kenaikan Manual: " . ($sourceClass->class_name ?? '-') . " ➔ " . ($targetClass->class_name ?? 'Alumni'),
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]
                    );
                }
            }

            $updateData = [
                'class_id'   => $targetClassId,
                'status'     => $request->action_type === 'graduate' ? 'lulus' : 'aktif',
                'updated_at' => now(),
            ];
            if ($request->action_type === 'graduate') {
                $updateData['tahun_lulus'] = date('Y');
            }

            $count = DB::table('students')
                ->whereIn('id', $request->student_ids)
                ->where('class_id', $request->source_class_id)
                ->update($updateData);

            // Sinkronkan class_id pada akun pengguna siswa (BUG-04 fix)
            DB::table('users')
                ->whereIn('student_id', $request->student_ids)
                ->update([
                    'class_id'   => $targetClassId,
                    'updated_at' => now(),
                ]);

            $targetName = $targetClass ? $targetClass->class_name : 'Alumni / Lulus';
            $sourceName = $sourceClass ? $sourceClass->class_name : "ID {$request->source_class_id}";

            // Catat log perubahan ke nilai_audit_logs untuk audit trail di database
            DB::table('nilai_audit_logs')->insert([
                'user_id' => Auth::id(),
                'action' => 'KENAIKAN_KELAS',
                'field' => 'class_id',
                'old_value' => $sourceName,
                'new_value' => $targetName,
                'reason' => "Proses pemindahan/kenaikan {$count} siswa dari kelas {$sourceName} ke {$targetName}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Berhasil memproses {$count} siswa dari {$sourceName} ke {$targetName}!",
                'count' => $count,
                'target_name' => $targetName,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memproses kenaikan kelas: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper Backfill: Inisialisasi riwayat rombel (student_class_history) dari data yang ada di sistem
     */
    public static function backfillClassHistory(): int
    {
        $inserted = 0;
        $activeFst = DB::table('m_fst_pembelajaran')->where('is_locked', false)->orderBy('id', 'desc')->first()
                  ?? DB::table('m_fst_pembelajaran')->orderBy('id', 'desc')->first();

        // 1. Dari siswa yang saat ini memiliki class_id aktif
        if ($activeFst) {
            $activeStudents = DB::table('students')->whereNotNull('class_id')->get();
            foreach ($activeStudents as $std) {
                $exists = DB::table('student_class_history')
                    ->where('student_id', $std->id)
                    ->where('fst_id', $activeFst->id)
                    ->exists();

                if (!$exists) {
                    DB::table('student_class_history')->insert([
                        'student_id'   => $std->id,
                        'class_id'     => $std->class_id,
                        'fst_id'       => $activeFst->id,
                        'status'       => 'aktif',
                        'tahun_ajaran' => $activeFst->tahun_ajaran ?? null,
                        'semester'     => $activeFst->semester ?? null,
                        'keterangan'   => 'Inisialisasi otomatis rombel aktif',
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                    $inserted++;
                }
            }
        }

        // 2. Dari data historis values terdahulu
        $historicalValues = DB::table('values')
            ->select('student_id', 'class_id', 'fst_id')
            ->whereNotNull('student_id')
            ->whereNotNull('class_id')
            ->whereNotNull('fst_id')
            ->distinct()
            ->get();

        foreach ($historicalValues as $hv) {
            $exists = DB::table('student_class_history')
                ->where('student_id', $hv->student_id)
                ->where('fst_id', $hv->fst_id)
                ->exists();

            if (!$exists) {
                $fst = DB::table('m_fst_pembelajaran')->where('id', $hv->fst_id)->first();
                DB::table('student_class_history')->insert([
                    'student_id'   => $hv->student_id,
                    'class_id'     => $hv->class_id,
                    'fst_id'       => $hv->fst_id,
                    'status'       => 'aktif',
                    'tahun_ajaran' => $fst ? $fst->tahun_ajaran : null,
                    'semester'     => $fst ? $fst->semester : null,
                    'keterangan'   => 'Inisialisasi dari arsip nilai',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $inserted++;
            }
        }

        return $inserted;
    }
}
