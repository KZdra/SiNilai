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
        Schema::create('values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->decimal('value_daily',5,2)->nullable()->default(NULL);
            $table->decimal('value_daily_2',5,2)->nullable()->default(NULL);
            $table->decimal('value_daily_3',5,2)->nullable()->default(NULL);
            $table->decimal('value_daily_4',5,2)->nullable()->default(NULL);
            $table->decimal('value_daily_5',5,2)->nullable()->default(NULL);
            $table->decimal('value_daily_6',5,2)->nullable()->default(NULL);
            $table->decimal('value_daily_7',5,2)->nullable()->default(NULL);
            $table->decimal('value_daily_8',5,2)->nullable()->default(NULL);
            $table->decimal('value_daily_9',5,2)->nullable()->default(NULL);
            $table->decimal('value_daily_10',5,2)->nullable()->default(NULL);
            $table->decimal('value_sts',5,2)->nullable()->default(NULL);
            $table->decimal('value_sas',5,2)->nullable()->default(NULL);
            $table->timestamps();
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('values');
    }
};
