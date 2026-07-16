<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MapelMappingController extends Controller
{
    public function index(Request $request)
    {
        // Get all FSTs
        $fsts = DB::table('m_fst_pembelajaran')
            ->orderBy('tahun_ajaran', 'desc')
            ->orderBy('semester', 'desc')
            ->get();

        // Determine active FST. If user selects one, use that. Otherwise use the first one (most recent).
        $selectedFstId = $request->get('fst_id');
        if (!$selectedFstId && $fsts->count() > 0) {
            $selectedFstId = $fsts->first()->id;
        }

        // Get all Classes
        $classes = DB::table('class')->orderBy('class_name', 'asc')->get();
        
        $selectedClassId = $request->get('class_id');
        if (!$selectedClassId && $classes->count() > 0) {
            $selectedClassId = $classes->first()->id;
        }

        // Get all Mapels
        $mapels = DB::table('mata_pelajarans')->orderBy('nama_mapel', 'asc')->get();

        // Get existing mappings for the selected class and FST
        $mappings = [];
        if ($selectedFstId && $selectedClassId) {
            $mappingsData = DB::table('mapel_class_fst')
                ->where('fst_id', $selectedFstId)
                ->where('class_id', $selectedClassId)
                ->get();
            
            foreach ($mappingsData as $m) {
                $mappings[$m->mapel_id] = $m->is_active;
            }
        }

        // For "Copy From Previous" feature
        $otherFsts = $fsts->where('id', '!=', $selectedFstId);

        return view('mapel_mapping.index', compact(
            'fsts', 'selectedFstId', 
            'classes', 'selectedClassId', 
            'mapels', 'mappings',
            'otherFsts'
        ));
    }

    public function toggleMapel(Request $request)
    {
        $request->validate([
            'mapel_id' => 'required|integer',
            'class_id' => 'required|integer',
            'fst_id' => 'required|integer',
            'is_active' => 'required|boolean'
        ]);

        try {
            DB::table('mapel_class_fst')->updateOrInsert(
                [
                    'mapel_id' => $request->mapel_id,
                    'class_id' => $request->class_id,
                    'fst_id' => $request->fst_id,
                ],
                [
                    'is_active' => $request->is_active,
                    'updated_at' => Carbon::now()
                ]
            );

            return response()->json(['message' => 'Status mapel berhasil diperbarui.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function copyFromPrevious(Request $request)
    {
        $request->validate([
            'source_fst_id' => 'required|integer',
            'target_fst_id' => 'required|integer',
        ]);

        if ($request->source_fst_id == $request->target_fst_id) {
            return response()->json(['message' => 'Semester asal dan tujuan tidak boleh sama.'], 400);
        }

        try {
            // Hapus mapping yang ada di target FST jika ada, atau timpa?
            // Kita akan hapus semua mapping di target FST agar bersih (opsional) atau updateOrInsert.
            // Pilihan terbaik: copy yang dari source ke target.
            
            $sourceMappings = DB::table('mapel_class_fst')
                ->where('fst_id', $request->source_fst_id)
                ->get();

            if ($sourceMappings->isEmpty()) {
                return response()->json(['message' => 'Tidak ada data mapping di semester sumber.'], 404);
            }

            foreach ($sourceMappings as $mapping) {
                DB::table('mapel_class_fst')->updateOrInsert(
                    [
                        'mapel_id' => $mapping->mapel_id,
                        'class_id' => $mapping->class_id,
                        'fst_id' => $request->target_fst_id,
                    ],
                    [
                        'is_active' => $mapping->is_active,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]
                );
            }

            return response()->json(['message' => 'Berhasil menyalin pengaturan mata pelajaran.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function activateAll(Request $request)
    {
        $request->validate([
            'class_id' => 'required|integer',
            'fst_id' => 'required|integer',
        ]);

        try {
            $mapels = DB::table('mata_pelajarans')->get();
            foreach ($mapels as $mapel) {
                DB::table('mapel_class_fst')->updateOrInsert(
                    [
                        'mapel_id' => $mapel->id,
                        'class_id' => $request->class_id,
                        'fst_id' => $request->fst_id,
                    ],
                    [
                        'is_active' => 1,
                        'updated_at' => Carbon::now()
                    ]
                );
            }
            return response()->json(['message' => 'Semua mata pelajaran berhasil diaktifkan.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
