<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;



class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'GuruEx',
                'email' => 'admin@x.com',
                'password' => Hash::make('admin'),
                'created_at' => now(),
            ]
        ];

        DB::table('users')->insert($users);

        $this->call(ClassSeeder::class);
        $this->call(MapelSeeder::class);
    }
}
