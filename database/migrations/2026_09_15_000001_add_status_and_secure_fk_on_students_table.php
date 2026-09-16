<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'status')) {
                $table->enum('status', ['aktif', 'lulus', 'mutasi', 'keluar'])->default('aktif')->after('nama');
            }
            if (!Schema::hasColumn('students', 'tahun_lulus')) {
                $table->string('tahun_lulus', 10)->nullable()->after('status');
            }
        });

        // Update status for any existing students where class_id is null
        DB::table('students')->whereNull('class_id')->update(['status' => 'lulus']);

        // Aman-kan foreign key dari cascade delete menjadi set null
        try {
            Schema::table('students', function (Blueprint $table) {
                // Drop foreign key lama (jika ada)
                $table->dropForeign(['class_id']);
            });

            Schema::table('students', function (Blueprint $table) {
                // Pasang foreign key baru dengan onDelete('set null')
                $table->foreign('class_id')->references('id')->on('class')->onDelete('set null');
            });
        } catch (\Exception $e) {
            // Log or ignore if running on engines that don't support dropping named FK directly
            \Illuminate\Support\Facades\Log::warning('Foreign key modification note: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'tahun_lulus')) {
                $table->dropColumn('tahun_lulus');
            }
            if (Schema::hasColumn('students', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
