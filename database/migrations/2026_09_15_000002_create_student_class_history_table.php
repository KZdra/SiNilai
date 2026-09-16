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
        Schema::create('student_class_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('fst_id');
            $table->enum('status', ['aktif', 'naik_kelas', 'tinggal_kelas', 'lulus', 'mutasi'])->default('aktif');
            $table->string('tahun_ajaran', 20)->nullable();
            $table->string('semester', 20)->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('class')->onDelete('cascade');
            $table->foreign('fst_id')->references('id')->on('m_fst_pembelajaran')->onDelete('cascade');

            $table->unique(['student_id', 'fst_id'], 'uq_student_fst');
            $table->index(['class_id', 'fst_id'], 'idx_class_fst');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_class_history');
    }
};
