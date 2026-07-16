<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table("roles")->truncate();
        Schema::enableForeignKeyConstraints();
        $role = [
            ['role_name' => 'admin', 'created_at' => now()],
            ['role_name' => 'guru', 'created_at' => now()],
        ];

        DB::table('roles')->insert($role);
    }
}
