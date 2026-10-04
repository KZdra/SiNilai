<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FstController extends Controller
{
    public function index()
    {
        return view('mfst.index');
    }
    public function getData(Request $request)
    {
        $query = DB::table('m_fst_pembelajaran')->select('id', 'fase', 'semester', 'tahun_ajaran','ta', 'is_locked', 'locked_at');
        if (!$request->has('order')) {
            $query->orderBy('id', 'asc');
        }
        $searchableColumns = ['fase', 'semester', 'tahun_ajaran', 'ta'];
        $orderableColumns = [
            1 => 'fase',
            2 => 'semester',
            3 => 'tahun_ajaran',
            4 => 'ta',
            5 => 'is_locked',
        ];
        return \App\Services\DataTableHelper::process($query, $request, $searchableColumns, $orderableColumns, 'id');
    }
    public function store(Request $request)
    {
        $bagong = $request->validate([
            'fase' => 'required|string',
            'semester' => 'required|string',
            'tahun_ajaran' => 'required|string',
            'ta'=>'required|string'
        ]);
        try {
            DB::table('m_fst_pembelajaran')->insert([
                'fase' => ucwords($bagong['fase']),
                'semester' => $bagong['semester'],
                'tahun_ajaran' => $bagong['tahun_ajaran'],
                'ta' => $bagong['ta'],
                'created_at' => Carbon::now()
            ]);
            \App\Services\MasterDataCache::clearFst();
            return response()->json(['message' => 'Data berhasil ditambahkan!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $bagong = $request->validate([
            'fase' => 'required|string',
            'semester' => 'required|string',
            'tahun_ajaran' => 'required|string',
            'ta' => 'required|string'
        ]);
        try {
            DB::table('m_fst_pembelajaran')->where('id', $id)->update([
                'fase' => $bagong['fase'],
                'semester' => $bagong['semester'],
                'tahun_ajaran' => $bagong['tahun_ajaran'],
                'ta' => $bagong['ta'],
                'updated_at' => Carbon::now()
            ]);
            \App\Services\MasterDataCache::clearFst($id);
            return response()->json(['message' => 'Data berhasil diUpdate!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function destroy(Request $r, $id)
    {
        try {
            $fst = DB::table('m_fst_pembelajaran')->where('id', $id)->first();
            if (!$fst) {
                return response()->json(['message' => 'Data periode tidak ditemukan!'], 404);
            }

            // 1. Cek apakah berstatus terkunci
            if ($fst->is_locked == 1) {
                return response()->json([
                    'message' => "Periode ini sedang berstatus Terkunci. Buka kunci terlebih dahulu jika memang ingin melakukan perubahan."
                ], 422);
            }

            // 2. Cek apakah ada data nilai siswa di periode ini
            if (DB::table('values')->where('fst_id', $id)->exists()) {
                return response()->json([
                    'message' => "Periode '{$fst->tahun_ajaran} ({$fst->semester})' tidak dapat dihapus karena sudah memiliki riwayat nilai siswa."
                ], 422);
            }

            // 3. Cek apakah ada data TP atau TP siswa
            if (DB::table('m_tp')->where('fst_id', $id)->exists() || DB::table('tpsiswas')->where('fst_id', $id)->exists()) {
                return response()->json([
                    'message' => "Periode ini tidak dapat dihapus karena masih terhubung dengan Tujuan Pembelajaran (TP)."
                ], 422);
            }

            // 4. Cek apakah ada status cetak rapor atau mapping aktif
            if (DB::table('raport_statuses')->where('fst_id', $id)->exists() || DB::table('mapel_class_fst')->where('fst_id', $id)->exists()) {
                return response()->json([
                    'message' => "Periode ini tidak dapat dihapus karena masih terhubung dengan status rapor atau pemetaan mata pelajaran."
                ], 422);
            }

            DB::table('m_fst_pembelajaran')->where('id', '=', $id)->delete();
            \App\Services\MasterDataCache::clearFst($id);
            return response()->json(['message' => 'Periode berhasil dihapus!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Toggle lock status for a semester (Admin only).
     */
    public function toggleLock(Request $request, $id)
    {
        if (Auth::user()->role_id != 1) {
            return response()->json(['message' => 'Hanya Admin yang dapat mengunci / membuka semester.'], 403);
        }

        $fst = DB::table('m_fst_pembelajaran')->where('id', $id)->first();
        if (!$fst) {
            return response()->json(['message' => 'Data semester tidak ditemukan.'], 404);
        }

        $newLock = !$fst->is_locked;
        DB::table('m_fst_pembelajaran')->where('id', $id)->update([
            'is_locked'  => $newLock,
            'locked_at'  => $newLock ? Carbon::now() : null,
            'locked_by'  => $newLock ? Auth::id() : null,
            'updated_at' => Carbon::now(),
        ]);
        \App\Services\MasterDataCache::clearFst($id);

        $statusText = $newLock ? 'dikunci (Read-Only)' : 'dibuka kembali';
        return response()->json([
            'status'    => 'success',
            'message'   => "Semester {$fst->tahun_ajaran} berhasil {$statusText}!",
            'is_locked' => $newLock,
        ]);
    }

    /**
     * Generate Paket Preset Periode FST & Otomatis Petakan Mata Pelajaran ke Kelas
     */
    public function generatePreset(Request $request)
    {
        if (Auth::user()->role_id != 1) {
            return response()->json(['message' => 'Hanya Administrator yang memiliki akses untuk generate preset.'], 403);
        }

        $request->validate([
            'tahun_ajaran'     => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'paket'            => 'required|in:ganjil,genap,full',
            'auto_map_classes' => 'nullable|boolean',
        ], [
            'tahun_ajaran.regex' => 'Format tahun ajaran harus YYYY/YYYY (contoh: 2024/2025).',
        ]);

        $taInput = trim($request->tahun_ajaran);
        $paket   = $request->paket;
        $autoMap = $request->boolean('auto_map_classes', true);

        // Susun daftar FST berdasarkan paket yang dipilih
        $presets = [];
        if ($paket === 'ganjil' || $paket === 'full') {
            $presets[] = ['fase' => 'E', 'semester' => 'I (Satu)', 'ta' => 'tengah'];
            $presets[] = ['fase' => 'E', 'semester' => 'I (Satu)', 'ta' => 'akhir'];
            $presets[] = ['fase' => 'F', 'semester' => 'I (Satu)', 'ta' => 'tengah'];
            $presets[] = ['fase' => 'F', 'semester' => 'I (Satu)', 'ta' => 'akhir'];
        }
        if ($paket === 'genap' || $paket === 'full') {
            $presets[] = ['fase' => 'E', 'semester' => 'II (Dua)', 'ta' => 'tengah'];
            $presets[] = ['fase' => 'E', 'semester' => 'II (Dua)', 'ta' => 'akhir'];
            $presets[] = ['fase' => 'F', 'semester' => 'II (Dua)', 'ta' => 'tengah'];
            $presets[] = ['fase' => 'F', 'semester' => 'II (Dua)', 'ta' => 'akhir'];
        }

        DB::beginTransaction();
        try {
            $createdCount  = 0;
            $createdFstIds = [];

            foreach ($presets as $item) {
                $existing = DB::table('m_fst_pembelajaran')
                    ->where('fase', $item['fase'])
                    ->where('semester', $item['semester'])
                    ->where('tahun_ajaran', $taInput)
                    ->where('ta', $item['ta'])
                    ->first();

                if (!$existing) {
                    $fstId = DB::table('m_fst_pembelajaran')->insertGetId([
                        'fase'         => $item['fase'],
                        'semester'     => $item['semester'],
                        'tahun_ajaran' => $taInput,
                        'ta'           => $item['ta'],
                        'is_locked'    => 0,
                        'created_at'   => Carbon::now(),
                        'updated_at'   => Carbon::now(),
                    ]);
                    $createdCount++;
                    $createdFstIds[] = ['id' => $fstId, 'fase' => $item['fase']];
                } else {
                    $createdFstIds[] = ['id' => $existing->id, 'fase' => $item['fase']];
                }
            }

            $mappedCount = 0;
            if ($autoMap && !empty($createdFstIds)) {
                $mapels  = DB::table('mata_pelajarans')->pluck('id')->toArray();
                $classes = DB::table('class')->get();

                if (!empty($mapels) && $classes->isNotEmpty()) {
                    foreach ($createdFstIds as $fstInfo) {
                        $fstId = $fstInfo['id'];
                        $fase  = $fstInfo['fase'];

                        foreach ($classes as $cls) {
                            $cName = trim($cls->class_name);
                            // Klasifikasikan rombel:
                            // Fase E untuk tingkat Kelas 10 / X
                            // Fase F untuk tingkat Kelas 11 (XI) & Kelas 12 (XII)
                            $isClassMatch = false;
                            if ($fase === 'E') {
                                if (preg_match('/^(X|10)\b/i', $cName)) {
                                    $isClassMatch = true;
                                }
                            } elseif ($fase === 'F') {
                                if (preg_match('/^(XI|XII|11|12)\b/i', $cName)) {
                                    $isClassMatch = true;
                                }
                            }

                            // Fallback jika format nama kelas custom: kaitkan ke semua kelas
                            if (!preg_match('/^(X|XI|XII|10|11|12)\b/i', $cName)) {
                                $isClassMatch = true;
                            }

                            if ($isClassMatch) {
                                foreach ($mapels as $mId) {
                                    DB::table('mapel_class_fst')->updateOrInsert(
                                        [
                                            'mapel_id' => $mId,
                                            'class_id' => $cls->id,
                                            'fst_id'   => $fstId,
                                        ],
                                        [
                                            'is_active'  => 1,
                                            'updated_at' => Carbon::now(),
                                        ]
                                    );
                                    $mappedCount++;
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();
            \App\Services\MasterDataCache::clearFst();

            $msg = "Berhasil membuat {$createdCount} periode FST baru untuk Tahun Ajaran {$taInput}!";
            if ($autoMap && $mappedCount > 0) {
                $msg .= " Serta otomatis memetakan {$mappedCount} pengaturan mata pelajaran ke rombel kelas yang sesuai.";
            }

            return response()->json([
                'status'        => 'success',
                'message'       => $msg,
                'created_count' => $createdCount,
                'mapped_count'  => $mappedCount,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal generate preset: ' . $e->getMessage()], 500);
        }
    }
}
