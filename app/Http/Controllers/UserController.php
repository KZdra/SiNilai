<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\DataTableHelper;

class UserController extends Controller
{
    public function index()
    {
        $classList = DB::table('class')->select('id', 'class_name')->orderBy('class_name', 'asc')->get();
        $rolesList = DB::table('roles')->whereIn('id', [1, 2])->select('id', 'role_name')->orderBy('id', 'asc')->get();

        // Summary counters
        $countAdmin = DB::table('users')->where('role_id', 1)->count();
        $countGuru = DB::table('users')->where('role_id', 2)->count();
        $countWalas = DB::table('users')->where('role_id', 2)->whereNotNull('class_id')->count();
        $countSiswaUser = DB::table('users')->where('role_id', 3)->count();
        $totalStudents = DB::table('students')->count();

        return view('users.index', compact(
            'classList',
            'rolesList',
            'countAdmin',
            'countGuru',
            'countWalas',
            'countSiswaUser',
            'totalStudents'
        ));
    }

    // ── Data Guru & Administrator ─────────────────────────────────
    public function getData(Request $request)
    {
        $query = DB::table('users as u')->select(
            'u.id',
            'u.class_id',
            'c.class_name',
            'u.role_id',
            'r.role_name',
            'u.username',
            'u.name',
            DB::raw('COALESCE(u.nip, "-") AS nip'),
            'u.email'
        )
            ->leftJoin('roles as r', 'u.role_id', '=', 'r.id')
            ->leftJoin('class as c', 'u.class_id', '=', 'c.id')
            ->whereIn('u.role_id', [1, 2]);

        if ($request->filled('role_id')) {
            $query->where('u.role_id', $request->role_id);
        }
        if ($request->filled('class_id')) {
            $query->where('u.class_id', $request->class_id);
        }

        $searchableColumns = [
            'u.username',
            'u.name',
            'u.nip',
            'u.email',
            'c.class_name',
            'r.role_name',
        ];

        $orderableColumns = [
            0 => 'u.username',
            1 => 'u.name',
            2 => 'u.nip',
            3 => 'c.class_name',
            4 => 'u.email',
            5 => 'r.role_name',
        ];

        if (!$request->has('order')) {
            $query->orderBy('u.role_id', 'asc')->orderBy('u.name', 'asc');
        }

        return DataTableHelper::process($query, $request, $searchableColumns, $orderableColumns, 'u.id');
    }

    // ── Data Portal Siswa (Tampilkan Semua Siswa, Baik Aktif Maupun Belum) ──
    public function getDataSiswa(Request $request)
    {
        $query = DB::table('students as s')
            ->leftJoin('class as c', 's.class_id', '=', 'c.id')
            ->leftJoin('users as u', function ($join) {
                $join->on('s.id', '=', 'u.student_id')
                    ->where('u.role_id', 3);
            })
            ->select(
                's.id as student_id',
                's.nama as student_name',
                's.nisn',
                's.nis',
                's.class_id',
                'c.class_name',
                'u.id as user_id',
                DB::raw('COALESCE(u.username, s.nisn, s.nis, "-") as username'),
                DB::raw('COALESCE(u.email, "-") as email'),
                DB::raw('CASE WHEN u.id IS NOT NULL THEN 1 ELSE 0 END as is_active'),
                'u.created_at'
            );

        if ($request->filled('class_id')) {
            $query->where('s.class_id', $request->class_id);
        }

        if ($request->filled('status')) {
            if ($request->status === '1') {
                $query->whereNotNull('u.id');
            } elseif ($request->status === '0') {
                $query->whereNull('u.id');
            }
        }

        $searchable = [
            's.nama',
            's.nisn',
            's.nis',
            'u.username',
            'u.email',
            'c.class_name'
        ];

        $orderable = [
            1 => 's.nisn',
            2 => 's.nama',
            3 => 'c.class_name',
            4 => 'u.email',
            5 => 'is_active',
        ];

        if (!$request->has('order')) {
            $query->orderBy('c.class_name', 'asc')->orderBy('s.nama', 'asc');
        }

        // Hitung statistik real-time sesuai filter kelas
        $statsQuery = DB::table('students as s')
            ->leftJoin('users as u', function ($join) {
                $join->on('s.id', '=', 'u.student_id')
                    ->where('u.role_id', 3);
            });
        if ($request->filled('class_id')) {
            $statsQuery->where('s.class_id', $request->class_id);
        }
        $totalClassStudents = (clone $statsQuery)->count('s.id');
        $activeCount = (clone $statsQuery)->whereNotNull('u.id')->count('s.id');
        $inactiveCount = max(0, $totalClassStudents - $activeCount);

        $extra = [
            'total_students' => $totalClassStudents,
            'active_count' => $activeCount,
            'inactive_count' => $inactiveCount,
        ];

        return DataTableHelper::process($query, $request, $searchable, $orderable, 's.id', null, $extra);
    }

    public function store(Request $request)
    {
        $kont = $request->validate([
            'class_id' => 'nullable|integer',
            'role_id' => 'required|integer',
            'nip' => 'nullable|string|max:50',
            'nama' => 'required|string|max:150',
            'username' => 'required|string|max:100|unique:users,username',
            'password' => 'required|min:4',
            'email' => 'required|email|max:150|unique:users,email',
        ]);

        try {
            DB::table('users')->insert([
                'class_id' => !empty($kont['class_id']) ? $kont['class_id'] : null,
                'role_id' => $kont['role_id'],
                'nip' => !empty($kont['nip']) ? $kont['nip'] : null,
                'name' => $kont['nama'],
                'username' => $kont['username'],
                'password' => Hash::make($kont['password']),
                'email' => $kont['email'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            return response()->json(['message' => 'Pengguna berhasil ditambahkan!'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'class_id' => 'nullable|integer',
            'role_id' => 'required|integer',
            'nip' => 'nullable|string|max:50',
            'nama' => 'required|string|max:150',
            'username' => 'required|string|max:100|unique:users,username,' . $id,
            'email' => 'required|email|max:150|unique:users,email,' . $id,
            'password' => 'nullable|min:4',
        ]);

        try {
            $data = [
                'class_id' => $request->filled('class_id') ? $request->class_id : null,
                'role_id' => $validated['role_id'],
                'nip' => !empty($validated['nip']) ? $validated['nip'] : null,
                'name' => $validated['nama'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'updated_at' => Carbon::now(),
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            DB::table('users')->where('id', $id)->update($data);
            return response()->json(['message' => 'Pengguna berhasil diperbarui!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::table('users')->where('id', $id)->delete();
            return response()->json(['message' => 'Pengguna berhasil dihapus!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ── Reset Password ───────────────────────────────────────────
    public function resetPassword(Request $request, $id)
    {
        try {
            $user = DB::table('users')->where('id', $id)->first();
            if (!$user) {
                return response()->json(['message' => 'Pengguna tidak ditemukan.'], 404);
            }

            $defaultPass = ($user->role_id == 3) ? 'siswa123' : 'guru123';
            $newPassword = $request->input('password', $defaultPass);

            DB::table('users')->where('id', $id)->update([
                'password' => Hash::make($newPassword),
                'updated_at' => Carbon::now(),
            ]);

            return response()->json([
                'message' => "Password berhasil direset ke: {$newPassword}"
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ── Generate Massal Akun Siswa ────────────────────────────────
    public function generateSiswaAccounts(Request $request)
    {
        $classId = $request->input('class_id');
        $query = DB::table('students');
        if (!empty($classId)) {
            $query->where('class_id', $classId);
        }
        $students = $query->get();

        if ($students->isEmpty()) {
            return response()->json(['message' => 'Tidak ada siswa yang ditemukan untuk digenerate.'], 404);
        }

        $created = 0;
        $skipped = 0;

        foreach ($students as $std) {
            $username = !empty($std->nisn) ? trim($std->nisn) : trim($std->nis);
            if (empty($username)) continue;

            $exists = DB::table('users')
                ->where('username', $username)
                ->orWhere('student_id', $std->id)
                ->first();

            if (!$exists) {
                DB::table('users')->insert([
                    'name' => $std->nama,
                    'username' => $username,
                    'email' => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $username)) . '@siswa.sekolah.id',
                    'password' => Hash::make('siswa123'),
                    'role_id' => 3, // Role Siswa
                    'class_id' => $std->class_id,
                    'student_id' => $std->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        return response()->json([
            'message' => "Proses selesai: {$created} akun login siswa baru berhasil dibuat, {$skipped} siswa sudah memiliki akun sebelumnya. (Password default: siswa123)"
        ], 200);
    }

    // ── Aktifkan Akun Siswa Individu ──────────────────────────────
    public function activateSingleSiswa(Request $request)
    {
        $request->validate([
            'student_id' => 'required|integer|exists:students,id'
        ]);

        $student = DB::table('students')->where('id', $request->student_id)->first();
        if (!$student) {
            return response()->json(['message' => 'Data siswa tidak ditemukan.'], 404);
        }

        $username = !empty($student->nisn) ? trim($student->nisn) : trim($student->nis);
        if (empty($username)) {
            return response()->json(['message' => 'Siswa belum memiliki NISN atau NIS untuk username login.'], 422);
        }

        $exists = DB::table('users')
            ->where('student_id', $student->id)
            ->orWhere('username', $username)
            ->first();

        if ($exists) {
            DB::table('users')->where('id', $exists->id)->update([
                'student_id' => $student->id,
                'class_id' => $student->class_id,
                'role_id' => 3,
                'updated_at' => now(),
            ]);
            return response()->json(['message' => "Akun login untuk {$student->nama} sudah aktif dan telah disinkronkan."], 200);
        }

        $cleanUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $username));
        $email = $cleanUsername . '@siswa.sekolah.id';

        DB::table('users')->insert([
            'name' => $student->nama,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make('siswa123'),
            'role_id' => 3,
            'class_id' => $student->class_id,
            'student_id' => $student->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => "Akun login untuk {$student->nama} berhasil diaktifkan! Username: {$username}, Password: siswa123"
        ], 201);
    }
}
