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
        // 1. Tabel Dimensi Profil Pelajar Pancasila
        Schema::create('p5_dimensi', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique();
            $table->string('nama_dimensi');
            $table->timestamps();
        });

        // 2. Tabel Elemen
        Schema::create('p5_elemen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dimensi_id');
            $table->string('nama_elemen');
            $table->timestamps();

            $table->foreign('dimensi_id')->references('id')->on('p5_dimensi')->onDelete('cascade');
        });

        // 3. Tabel Subelemen & Capaian Fase
        Schema::create('p5_subelemen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('elemen_id');
            $table->string('nama_subelemen');
            $table->text('capaian_fase')->nullable(); // Deskripsi capaian akhir fase
            $table->timestamps();

            $table->foreign('elemen_id')->references('id')->on('p5_elemen')->onDelete('cascade');
        });

        // 4. Tabel Projek P5
        Schema::create('p5_projek', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('fst_id');
            $table->string('tema'); // e.g. Gaya Hidup Berkelanjutan, Kearifan Lokal, Kewirausahaan, dll.
            $table->string('nama_projek');
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('fasilitator_id')->nullable(); // User / Guru
            $table->timestamps();

            $table->foreign('class_id')->references('id')->on('class')->onDelete('cascade');
            $table->foreign('fst_id')->references('id')->on('m_fst_pembelajaran')->onDelete('cascade');
        });

        // 5. Tabel Target Subelemen Projek (Pivot)
        Schema::create('p5_projek_subelemen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('projek_id');
            $table->unsignedBigInteger('subelemen_id');
            $table->timestamps();

            $table->foreign('projek_id')->references('id')->on('p5_projek')->onDelete('cascade');
            $table->foreign('subelemen_id')->references('id')->on('p5_subelemen')->onDelete('cascade');
            $table->unique(['projek_id', 'subelemen_id']);
        });

        // 6. Tabel Penilaian P5 Siswa
        Schema::create('p5_penilaian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('projek_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subelemen_id');
            $table->enum('predikat', ['MB', 'SB', 'BSH', 'SAB'])->default('BSH'); // Mulai, Sedang, Sesuai Harapan, Sangat Berkembang
            $table->text('catatan_proses')->nullable(); // Catatan fasilitator projek untuk siswa ini
            $table->timestamps();

            $table->foreign('projek_id')->references('id')->on('p5_projek')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('subelemen_id')->references('id')->on('p5_subelemen')->onDelete('cascade');
            $table->unique(['projek_id', 'student_id', 'subelemen_id'], 'p5_penilaian_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('p5_penilaian');
        Schema::dropIfExists('p5_projek_subelemen');
        Schema::dropIfExists('p5_projek');
        Schema::dropIfExists('p5_subelemen');
        Schema::dropIfExists('p5_elemen');
        Schema::dropIfExists('p5_dimensi');
    }
};
