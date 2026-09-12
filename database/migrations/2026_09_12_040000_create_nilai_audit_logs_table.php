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
        Schema::create('nilai_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('mapel_id')->nullable();
            $table->unsignedBigInteger('fst_id')->nullable();
            $table->string('action', 50); // INPUT_BARU, UPDATE, DELETE, IMPORT_CSV, SYNC_CBT, KENAIKAN_KELAS
            $table->string('field', 50)->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('reason')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('mapel_id')->references('id')->on('mata_pelajarans')->onDelete('cascade');
            $table->foreign('fst_id')->references('id')->on('m_fst_pembelajaran')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai_audit_logs');
    }
};
