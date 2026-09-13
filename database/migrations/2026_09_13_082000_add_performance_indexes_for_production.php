<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Optimasi tabel students (Pencarian login NISN / NIS dan filter kelas)
        Schema::table('students', function (Blueprint $table) {
            $table->index('nis', 'students_nis_index');
            $table->index('nisn', 'students_nisn_index');
            $table->index(['class_id', 'nama'], 'students_class_nama_index');
        });

        // 2. Optimasi tabel values (Query gabungan nilai sumatif, rekap rapor, dan ekspor leger)
        Schema::table('values', function (Blueprint $table) {
            $table->index(['class_id', 'mapel_id', 'fst_id'], 'values_class_mapel_fst_index');
            $table->index(['student_id', 'fst_id', 'mapel_id'], 'values_student_fst_mapel_index');
            $table->index(['student_id', 'class_id', 'fst_id'], 'values_student_class_fst_index');
        });

        // 3. Optimasi tabel tpsiswas (Tujuan Pembelajaran siswa per semester dan mapel)
        Schema::table('tpsiswas', function (Blueprint $table) {
            $table->index(['siswa_id', 'fst_id', 'mapel_id'], 'tpsiswas_siswa_fst_mapel_index');
            $table->index(['class_id', 'fst_id', 'mapel_id'], 'tpsiswas_class_fst_mapel_index');
        });

        // 4. Optimasi tabel catatan_walikelas (Rekap absensi & catatan per siswa/semester)
        Schema::table('catatan_walikelas', function (Blueprint $table) {
            $table->index(['student_id', 'class_id', 'fst_id'], 'catatan_walas_student_class_fst_index');
        });

        // 5. Optimasi tabel m_tp (Pencarian master TP per mapel & fst)
        Schema::table('m_tp', function (Blueprint $table) {
            $table->index(['mapel_id', 'fst_id'], 'm_tp_mapel_fst_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_nis_index');
            $table->dropIndex('students_nisn_index');
            $table->dropIndex('students_class_nama_index');
        });

        Schema::table('values', function (Blueprint $table) {
            $table->dropIndex('values_class_mapel_fst_index');
            $table->dropIndex('values_student_fst_mapel_index');
            $table->dropIndex('values_student_class_fst_index');
        });

        Schema::table('tpsiswas', function (Blueprint $table) {
            $table->dropIndex('tpsiswas_siswa_fst_mapel_index');
            $table->dropIndex('tpsiswas_class_fst_mapel_index');
        });

        Schema::table('catatan_walikelas', function (Blueprint $table) {
            $table->dropIndex('catatan_walas_student_class_fst_index');
        });

        Schema::table('m_tp', function (Blueprint $table) {
            $table->dropIndex('m_tp_mapel_fst_index');
        });
    }
};
