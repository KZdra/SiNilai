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
    public function destroy(Request $request,$id)
    {
        try {
            DB::table('class')->where('id','=',$id)->delete();
            \App\Services\MasterDataCache::clearClasses($id);
            return response()->json(['message' => 'Kelas berhasil diHapus!'],201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()],500);
        }
    }
}
