<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'student_id')) {
                $table->unsignedBigInteger('student_id')->nullable()->after('class_id');
                $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            }
        });

        // Pastikan role siswa tersedia di tabel roles
        if (Schema::hasTable('roles')) {
            $siswaRole = DB::table('roles')->where('id', 3)->first();
            if (!$siswaRole) {
                DB::table('roles')->insert([
                    'id' => 3,
                    'role_name' => 'siswa',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'student_id')) {
                $table->dropForeign(['student_id']);
                $table->dropColumn('student_id');
            }
        });

        DB::table('roles')->where('id', 3)->delete();
    }
};
