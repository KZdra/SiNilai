<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassController extends Controller
{
    public function index()
    {
        return view('mkelas.index');
    }

    public function getData(Request $request)
    {
        $query = DB::table('class')->select('id', 'class_name');
        if (!$request->has('order')) {
            $query->orderBy('class_name', 'asc');
        }
        return \App\Services\DataTableHelper::process($query, $request, ['class_name'], [1 => 'class_name'], 'id');
    }
    public function store(Request $request)
    {
        $request->validate([
            'class_name' => 'required|string|max:255'
        ]);

        try {
            DB::table('class')->insert([
                'class_name'=> $request->class_name,
                'created_at'=> Carbon::now()
            ]);
            \App\Services\MasterDataCache::clearClasses();
            return response()->json(['message' => 'Kelas berhasil ditambahkan!'],201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()],500);
        }
    }
    public function update(Request $request,$id)
    {
        $request->validate([
            'class_name' => 'required|string|max:255'
        ]);

        try {
            DB::table('class')->where('id','=',$id)->update([
                'class_name'=> $request->class_name,
                'updated_at'=> Carbon::now()
            ]);
            \App\Services\MasterDataCache::clearClasses($id);
            return response()->json(['message' => 'Kelas berhasil diUpdate!'],201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()],500);
        }
    }
    public function destroy(Request $request, $id)
    {
        try {
            $class = DB::table('class')->where('id', $id)->first();
            if (!$class) {
                return response()->json(['message' => 'Data kelas tidak ditemukan!'], 404);
            }

            // 1. Cek apakah ada siswa aktif yang terdaftar di kelas ini
            $studentCount = DB::table('students')->where('class_id', $id)->count();
            if ($studentCount > 0) {
                return response()->json([
                    'message' => "Kelas '{$class->class_name}' tidak dapat dihapus karena masih memiliki {$studentCount} siswa terdaftar. Silakan mutasikan atau pindahkan siswa terlebih dahulu."
                ], 422);
            }

            // 2. Cek apakah ada data nilai yang tersimpan untuk kelas ini
            if (DB::table('values')->where('class_id', $id)->exists()) {
                return response()->json([
                    'message' => "Kelas '{$class->class_name}' tidak dapat dihapus karena memiliki riwayat nilai siswa."
                ], 422);
            }

            // 3. Cek apakah kelas ini sudah dipetakan ke mata pelajaran
            if (DB::table('mapel_class_fst')->where('class_id', $id)->exists()) {
                return response()->json([
                    'message' => "Kelas '{$class->class_name}' tidak dapat dihapus karena masih terdaftar di menu Mapping Mapel."
                ], 422);
            }

            // 4. Cek apakah ada akun guru / wali kelas yang ditugaskan di kelas ini
            if (DB::table('users')->where('class_id', $id)->exists()) {
                return response()->json([
                    'message' => "Kelas '{$class->class_name}' tidak dapat dihapus karena masih ditugaskan kepada akun Wali Kelas."
                ], 422);
            }

            // 5. Cek apakah ada catatan walikelas atau status rapor
            if (DB::table('catatan_walikelas')->where('class_id', $id)->exists() || DB::table('raport_statuses')->where('class_id', $id)->exists()) {
                return response()->json([
                    'message' => "Kelas '{$class->class_name}' tidak dapat dihapus karena memiliki rekam jejak catatan rapor."
                ], 422);
            }

            DB::table('class')->where('id', '=', $id)->delete();
            \App\Services\MasterDataCache::clearClasses($id);
            return response()->json(['message' => "Kelas '{$class->class_name}' berhasil dihapus!"], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
