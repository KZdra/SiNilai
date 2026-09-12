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
        Schema::table('m_fst_pembelajaran', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('ta');
            $table->timestamp('locked_at')->nullable()->after('is_locked');
            $table->unsignedBigInteger('locked_by')->nullable()->after('locked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_fst_pembelajaran', function (Blueprint $table) {
            $table->dropColumn(['is_locked', 'locked_at', 'locked_by']);
        });
    }
};
