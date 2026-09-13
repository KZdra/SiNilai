<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\DataTableHelper;

class EskulController extends Controller
{
    public function index()
    {
        return view('meskul.index');
    }

    // ── Master Eskul CRUD ─────────────────────────────────────────
    public function getdata(Request $request)
    {
        $query = DB::table('m_eskul')->select('id', 'nama_eskul');
        if (!$request->has('order')) {
            $query->orderBy('nama_eskul', 'asc');
        }
        return DataTableHelper::process($query, $request, ['nama_eskul'], [1 => 'nama_eskul'], 'id');
    }

    public function store(Request $request)
    {
        $vl = $request->validate([
            'nama_eskul' => 'required|string|max:100'
        ]);

        DB::beginTransaction();
        try {
            DB::table('m_eskul')->insert([
                'nama_eskul' => $vl['nama_eskul'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::commit();
            return response()->json(['message' => 'Ekstrakurikuler berhasil ditambahkan!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $vl = $request->validate([
            'nama_eskul' => 'required|string|max:100'
        ]);

        DB::beginTransaction();
        try {
            DB::table('m_eskul')->where('id', $id)->update([
                'nama_eskul' => $vl['nama_eskul'],
                'updated_at' => now()
            ]);
            DB::commit();
            return response()->json(['message' => 'Ekstrakurikuler berhasil diperbarui!'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Check if eskul is referenced in nilai_eskuls
            $hasScores = DB::table('nilai_eskuls')->where('eskul_id', $id)->exists();
            if ($hasScores) {
                return response()->json(['message' => 'Tidak dapat menghapus ekstrakurikuler karena masih memiliki riwayat penilaian siswa.'], 422);
            }

            DB::table('m_eskul')->where('id', $id)->delete();
            DB::commit();
            return response()->json(['message' => 'Ekstrakurikuler berhasil dihapus!'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ── Master Predikat & Template Narasi CRUD ───────────────────
    public function getPredikatData(Request $request)
    {
        $query = DB::table('m_predikat_eskul')->select('id', 'kode', 'nama', 'badge_color', 'template_narasi', 'urutan', 'is_active');
        if (!$request->has('order')) {
            $query->orderBy('urutan', 'asc');
        }
        return DataTableHelper::process(
            $query,
            $request,
            ['kode', 'nama', 'template_narasi'],
            [1 => 'kode', 2 => 'nama', 3 => 'template_narasi', 4 => 'urutan'],
            'id'
        );
    }

    public function storePredikat(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10',
            'nama' => 'required|string|max:50',
            'badge_color' => 'nullable|string|max:20',
            'template_narasi' => 'required|string',
            'urutan' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            DB::table('m_predikat_eskul')->insert([
                'kode' => strtoupper(trim($validated['kode'])),
                'nama' => trim($validated['nama']),
                'badge_color' => $validated['badge_color'] ?: 'primary',
                'template_narasi' => trim($validated['template_narasi']),
                'urutan' => $validated['urutan'] ?? 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::commit();
            return response()->json(['message' => 'Template predikat berhasil ditambahkan!'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function updatePredikat(Request $request, $id)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10',
            'nama' => 'required|string|max:50',
            'badge_color' => 'nullable|string|max:20',
            'template_narasi' => 'required|string',
            'urutan' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            DB::table('m_predikat_eskul')->where('id', $id)->update([
                'kode' => strtoupper(trim($validated['kode'])),
                'nama' => trim($validated['nama']),
                'badge_color' => $validated['badge_color'] ?: 'primary',
                'template_narasi' => trim($validated['template_narasi']),
                'urutan' => $validated['urutan'] ?? 1,
                'updated_at' => now(),
            ]);
            DB::commit();
            return response()->json(['message' => 'Template predikat berhasil diperbarui!'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroyPredikat(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            DB::table('m_predikat_eskul')->where('id', $id)->delete();
            DB::commit();
            return response()->json(['message' => 'Template predikat berhasil dihapus!'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
