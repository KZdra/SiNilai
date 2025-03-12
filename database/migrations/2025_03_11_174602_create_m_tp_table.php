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
        Schema::create('m_tp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mapel_id');
            $table->text('tp1')->nullable();
            $table->text('tp2')->nullable();
            $table->text('tp3')->nullable();
            $table->text('tp4')->nullable();
            $table->text('tp5')->nullable();
            $table->timestamps();
            $table->foreign('mapel_id')->references('id')->on('mata_pelajarans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_tp');
    }
};
