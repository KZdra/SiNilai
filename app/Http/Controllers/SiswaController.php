<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SiswaController extends Controller
{
    public function index()
    {
        $data = DB::table('students as s')
            ->select(
                's.id',
                's.nis',
                's.nisn',
                's.nama',
                's.class_id',
                'class.class_name',
                'class.id as class_id',
                's.jenis_kelamin',
                's.tempat_lahir',
                's.tanggal_lahir',
                's.agama',
                's.pendidikan_sebelumnya',
                's.alamat',
                's.nama_ayah',
                's.nama_ibu',
                's.pekerjaan_ayah',
                's.pekerjaan_ibu',
                's.alamat_orang_tua',
                DB::raw('COALESCE(s.sakit,0) as sakit'),
                DB::raw('COALESCE(s.izin,0) as izin'),
                DB::raw('COALESCE(s.alpa,0) as alpa'),
                's.foto_siswa_path'
            )
            ->leftJoin('class', 's.class_id', '=', 'class.id')
            ->orderBy('s.nama', 'asc')
            ->get();
        $classList = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc')->get();
        $className = null;
        if(Auth::user()->class_id !== null){
            $className = DB::table('class')->where('id',Auth::user()->class_id)->value('class_name');
        }

        return view('msiswa.index', compact('data', 'classList','className'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'nis' => 'required|numeric',
            'nis' => 'required|integer',
            'student_name' => 'required|string|max:255',
            'class_id' => 'required|integer',
            'jenis_kelamin' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required',
            'agama' => 'required|string',
            'pendidikan_sebelumnya' => 'required|string',
            'alamat' => 'required|string',
            'nama_ibu' => 'required|string',
            'nama_ayah' => 'required|string',
            'pekerjaan_ayah' => 'required|string',
            'pekerjaan_ibu' => 'required|string',
            'alamat_orang_tua' => 'required|string',
        ]);

        if ($request->hasFile('foto_siswa')) {
            $student_name = str_replace(' ', '_', strtolower($request->student_name)); // Format nama
            $folder = "foto-siswa/{$student_name}"; // Path penyimpanan

            $file = $request->file('foto_siswa');
            $file_name = $student_name . '_' . $file->getClientOriginalName(); // Buat nama unik
            $file_path = $file->storeAs($folder, $file_name, 'public'); // Simpan di storage

        } else {
            $file_name = null;
            $file_path = null;
        }

        try {
            DB::table('students')->insert([
                'nis' => $request->nis,
                'nama' => $request->student_name,
                'class_id' => $request->class_id,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'agama' => $request->agama,
                'pendidikan_sebelumnya' => $request->pendidikan_sebelumnya,
                'alamat' => $request->alamat,
                'nama_ayah' => $request->nama_ayah,
                'nama_ibu' => $request->nama_ibu,
                'pekerjaan_ayah' => $request->pekerjaan_ayah,
                'pekerjaan_ibu' => $request->pekerjaan_ibu,
                'alamat_orang_tua' => $request->alamat_orang_tua,
                'foto_siswa' => $file_name,
                'foto_siswa_path' => $file_path,
                'sakit' => $request->sakit ?? 0,
                'izin' => $request->izin ?? 0,
                'alpa' => $request->alpa ?? 0,
                'created_at' => Carbon::now()
            ]);
            return response()->json(['message' => 'Siswa berhasil ditambahkan!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nis' => 'required|numeric',
            'nis' => 'required|integer',
            'student_name' => 'required|string|max:255',
            'class_id' => 'required|integer',
            'jenis_kelamin' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required',
            'agama' => 'required|string',
            'pendidikan_sebelumnya' => 'required|string',
            'alamat' => 'required|string',
            'nama_ibu' => 'required|string',
            'nama_ayah' => 'required|string',
            'pekerjaan_ayah' => 'required|string',
            'pekerjaan_ibu' => 'required|string',
            'alamat_orang_tua' => 'required|string',
        ]);
        $student = DB::table('students')->where('id', $id)->first();
        if (!$student) {
            return response()->json(['message' => 'Siswa tidak ditemukan!'], 404);
        }

        $file_name = $student->foto_siswa;
        $file_path = $student->foto_siswa_path;

        if ($request->hasFile('foto_siswa')) {
            $student_name = str_replace(' ', '_', strtolower($request->student_name)); // Format nama
            $folder = "foto-siswa/{$student_name}"; // Path penyimpanan

            $file = $request->file('foto_siswa');
            $new_file_name = $student_name . '_' . $file->getClientOriginalName(); // Buat nama unik
            $new_file_path = $file->storeAs($folder, $new_file_name, 'public'); // Simpan di storage

            // Hapus foto lama jika ada
            if ($file_path) {
                Storage::disk('public')->delete($file_path);
            }

            $file_name = $new_file_name;
            $file_path = $new_file_path;
        }

        try {
            DB::table('students')->where('id', '=', $id)->update([
                'nis' => $request->nis,
                'nama' => $request->student_name,
                'class_id' => $request->class_id,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'agama' => $request->agama,
                'pendidikan_sebelumnya' => $request->pendidikan_sebelumnya,
                'alamat' => $request->alamat,
                'nama_ayah' => $request->nama_ayah,
                'nama_ibu' => $request->nama_ibu,
                'pekerjaan_ayah' => $request->pekerjaan_ayah,
                'pekerjaan_ibu' => $request->pekerjaan_ibu,
                'alamat_orang_tua' => $request->alamat_orang_tua,
                'foto_siswa' => $file_name,
                'foto_siswa_path' => $file_path,
                'sakit' => $request->sakit,
                'izin' => $request->izin,
                'alpa' => $request->alpa,
                'updated_at' => Carbon::now()
            ]);
            return response()->json(['message' => 'Siswa berhasil diUpdate!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    public function destroy(Request $request, $id)
    {
        $siswa = DB::table('students')->where('id', $id)->first();

        if (!$siswa) {
            return $this->errorResponse('Students not found', 404);
        }

        if ($siswa->foto_siswa_path) {
            Storage::disk('public')->delete($siswa->foto_siswa_path);
        }

        try {
            DB::table('students')->where('id', '=', $id)->delete();
            return response()->json(['message' => 'Siswa berhasil diHapus!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv' => 'required|mimes:csv,txt|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $file = $request->file('csv');
        $csvData = array_map('str_getcsv', file($file));

        if (count($csvData) <= 1) {
            return response()->json(['message' => 'File CSV kosong atau tidak valid.'], 400);
        }

        $header = array_shift($csvData); // Ambil header
        foreach ($csvData as $row) {
            if (count($row) >= 2) { // Pastikan minimal ada NIS & Nama
                $nis = $row[0];
                $nisn = $row[1];
                $nama = $row[2];
                $className = $row[3] ?? null;
                $jenis_kelamin = $row[4] ?? null;
                $tempat_lahir = $row[5] ?? null;
                $tanggal_lahir = $row[6] ?? null;
                $agama = $row[7] ?? null;
                $pendidikan_sebelumnya = $row[8] ?? null;
                $alamat = $row[9] ?? null;
                $nama_ayah = $row[10] ?? null;
                $nama_ibu = $row[11] ?? null;
                $pekerjaan_ayah = $row[12] ?? null;
                $pekerjaan_ibu = $row[13] ?? null;
                $alamat_orang_tua = $row[14] ?? null;
                $sakit =  $row[15] === '' || $row[15] === null ? 0 : $row[15];
                $izin = $row[16] ?? 0;
                $alpa = $row[17] ?? 0;

                $classId = null;

                if (!empty($className)) {
                    // Jika nama kelas ada, cari ID kelasnya
                    $classId = DB::table('class')->where('class_name', 'LIKE', $className)->value('id');

                    if (!$classId) {
                        // Jika kelas tidak ditemukan, buat baru
                        $classId = DB::table('class')->insertGetId([
                            'class_name' => $className,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                }

                // Insert atau update siswa
                DB::table('students')->updateOrInsert(
                    ['nis' => $nis], // Cek berdasarkan NIS
                    [
                        'nisn' => $nisn,
                        'nama' => $nama,
                        'class_id' => $classId, // NULL jika tidak ada kelas
                        'jenis_kelamin' => $jenis_kelamin,
                        'tempat_lahir' => $tempat_lahir,
                        'tanggal_lahir' => $tanggal_lahir,
                        'agama' => $agama,
                        'pendidikan_sebelumnya' => $pendidikan_sebelumnya,
                        'alamat' => $alamat,
                        'nama_ayah' => $nama_ayah,
                        'nama_ibu' => $nama_ibu,
                        'pekerjaan_ayah' => $pekerjaan_ayah,
                        'pekerjaan_ibu' => $pekerjaan_ibu,
                        'alamat_orang_tua' => $alamat_orang_tua,
                        'sakit' => $sakit,
                        'izin' => $izin,
                        'alpa' => $alpa,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]
                );
            }
        }

        return response()->json(['message' => 'Data siswa berhasil diimport!']);
    }

    public function downloadTemplate()
    {
        return response()->download(public_path('down/Template_InputSiswa.xlsx'));
    }
}
