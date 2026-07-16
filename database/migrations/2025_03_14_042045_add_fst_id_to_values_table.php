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
        Schema::table('values', function (Blueprint $table) {
            $table->unsignedBigInteger('fst_id')->after('class_id')->nullable();
            $table->foreign('fst_id')->references('id')->on('m_fst_pembelajaran')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('values', function (Blueprint $table) {
            //
        });
    }
};
