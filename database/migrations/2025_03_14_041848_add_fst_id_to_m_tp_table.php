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
        Schema::table('m_tp', function (Blueprint $table) {
            $table->unsignedBigInteger('fst_id')->after('mapel_id')->nullable();
            $table->unsignedBigInteger('class_id')->after('fst_id')->nullable();
            $table->foreign('class_id')->references('id')->on('class')->onDelete('cascade');
            $table->foreign('fst_id')->references('id')->on('m_fst_pembelajaran')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_tp', function (Blueprint $table) {
            //
        });
    }
};
