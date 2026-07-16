<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'username'=>'admin',
                'name' => 'AdminSekolah',
                'email' => 'admin@x.com',
                'role_id' => 1,
                'nip' => null,
                'password' => Hash::make('admin'),
                'created_at' => now(),
            ],
            [
                'username'=>'walikelas1',
                'name' => 'WaliKelas1',
                'email' => 'walikelas1@x.com',
                'nip' => '12382837',
                'role_id' => 2,
                'password' => Hash::make('walikelas1'),
                'created_at' => now(),
            ],
            [
                'username'=>'walikelas2',
                'name' => 'WaliKelas2',
                'email' => 'walikelas2@x.com',
                'nip' => '12398432',
                'role_id' => 2,
                'password' => Hash::make('walikelas2'),
                'created_at' => now(),
            ],

        ];
        DB::table('users')->insert($users);
    }
}
