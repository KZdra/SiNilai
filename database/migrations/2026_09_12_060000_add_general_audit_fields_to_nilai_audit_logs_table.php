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
        Schema::table('nilai_audit_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->nullable()->change();
            $table->unsignedBigInteger('mapel_id')->nullable()->change();
            $table->unsignedBigInteger('fst_id')->nullable()->change();

            if (!Schema::hasColumn('nilai_audit_logs', 'field')) {
                $table->string('field', 50)->nullable()->after('action');
            }
            if (!Schema::hasColumn('nilai_audit_logs', 'old_value')) {
                $table->text('old_value')->nullable()->after('field');
            }
            if (!Schema::hasColumn('nilai_audit_logs', 'new_value')) {
                $table->text('new_value')->nullable()->after('old_value');
            }
            if (!Schema::hasColumn('nilai_audit_logs', 'reason')) {
                $table->text('reason')->nullable()->after('new_value');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilai_audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('nilai_audit_logs', 'field')) {
                $table->dropColumn(['field', 'old_value', 'new_value', 'reason']);
            }
            $table->unsignedBigInteger('student_id')->nullable(false)->change();
            $table->unsignedBigInteger('mapel_id')->nullable(false)->change();
            $table->unsignedBigInteger('fst_id')->nullable(false)->change();
        });
    }
};
