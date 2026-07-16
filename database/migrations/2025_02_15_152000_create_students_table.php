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
        Schema::create('students', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nis');
            $table->string('nisn')->nullable();
            $table->string('nama');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->enum('jenis_kelamin',['L','P'])->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama')->nullable();
            $table->string('pendidikan_sebelumnya')->nullable();
            $table->text('alamat')->nullable();
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('pekerjaan_ayah')->nullable();
            $table->string('pekerjaan_ibu')->nullable();
            $table->text('alamat_orang_tua')->nullable();
            $table->integer('sakit')->nullable(0);
            $table->integer('izin')->nullable(0);
            $table->integer('alpa')->nullable(0);
            $table->string('foto_siswa')->nullable();
            $table->string('foto_siswa_path')->nullable();
            $table->timestamps();
            $table->foreign('class_id')->references('id')->on('class')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
