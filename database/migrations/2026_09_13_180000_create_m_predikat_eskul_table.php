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
        Schema::create('m_predikat_eskul', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10); // A, B, C, D
            $table->string('nama', 50); // Sangat Baik, Baik, Cukup, Kurang
            $table->string('badge_color', 20)->default('primary'); // success, primary, warning, secondary, info
            $table->text('template_narasi'); // Template kalimat dengan placeholder {eskul}
            $table->integer('urutan')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default initial master data
        DB::table('m_predikat_eskul')->insert([
            [
                'kode' => 'A',
                'nama' => 'Sangat Baik',
                'badge_color' => 'success',
                'template_narasi' => 'Sangat aktif, berdisiplin tinggi, dan menunjukkan capaian prestasi yang memuaskan dalam kegiatan {eskul}.',
                'urutan' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'B',
                'nama' => 'Baik',
                'badge_color' => 'primary',
                'template_narasi' => 'Aktif, disiplin, dan menunjukkan perkembangan yang positif dalam mengikuti kegiatan {eskul}.',
                'urutan' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'C',
                'nama' => 'Cukup',
                'badge_color' => 'warning',
                'template_narasi' => 'Cukup aktif dalam mengikuti latihan dan kegiatan rutin {eskul}.',
                'urutan' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'D',
                'nama' => 'Kurang',
                'badge_color' => 'secondary',
                'template_narasi' => 'Perlu meningkatkan keaktifan dan kedisiplinan dalam mengikuti kegiatan {eskul}.',
                'urutan' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('m_predikat_eskul');
    }
};
