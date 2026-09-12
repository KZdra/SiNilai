<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SiswaUserSeeder extends Seeder
{
    /**
     * Seed user accounts for students (role_id = 3) for testing portal.
     */
    public function run(): void
    {
        $students = DB::table('students')->limit(15)->get();

        foreach ($students as $student) {
            // Username gunakan NISN jika ada, atau NIS
            $username = !empty($student->nisn) ? $student->nisn : $student->nis;
            
            // Cek apakah akun user sudah ada
            $exists = DB::table('users')->where('username', $username)->orWhere('student_id', $student->id)->first();

            if (!$exists) {
                DB::table('users')->insert([
                    'name' => $student->nama,
                    'username' => $username,
                    'email' => strtolower(str_replace(' ', '', $username)) . '@siswa.sekolah.id',
                    'password' => Hash::make('siswa123'),
                    'role_id' => 3, // Role Siswa
                    'class_id' => $student->class_id,
                    'student_id' => $student->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
