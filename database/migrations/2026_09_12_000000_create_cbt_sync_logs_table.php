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
        Schema::create('cbt_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('sync_type', 20)->default('pull'); // 'pull' or 'push'
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('mapel_id')->nullable();
            $table->unsignedBigInteger('fst_id')->nullable();
            $table->string('target_field', 50)->default('value_sts');
            $table->integer('total_synced')->default(0);
            $table->integer('total_failed')->default(0);
            $table->string('status', 20)->default('success'); // 'success', 'partial', 'failed'
            $table->text('message')->nullable();
            $table->longText('details_json')->nullable();
            $table->timestamps();

            $table->index(['class_id', 'mapel_id', 'fst_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cbt_sync_logs');
    }
};
