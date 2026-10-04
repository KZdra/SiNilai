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
        // Pastikan tidak ada data duplikat sebelum menambahkan unique constraint
        DB::statement("
            DELETE v1 FROM `values` v1
            INNER JOIN `values` v2 
            WHERE v1.id > v2.id 
              AND v1.student_id = v2.student_id 
              AND v1.mapel_id = v2.mapel_id 
              AND v1.fst_id = v2.fst_id
        ");

        Schema::table('values', function (Blueprint $table) {
            $table->unique(['student_id', 'mapel_id', 'fst_id'], 'uq_values_student_mapel_fst');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('values', function (Blueprint $table) {
            $table->dropUnique('uq_values_student_mapel_fst');
        });
    }
};
