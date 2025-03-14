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
        Schema::create('nilai_eskuls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('eskul_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('fst_id');
            $table->string('nilai_eskul');
            $table->timestamps();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('fst_id')->references('id')->on('m_fst_pembelajaran')->onDelete('cascade');
            $table->foreign('eskul_id')->references('id')->on('m_eskul')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_eskuls');
    }
};
