<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use ZipArchive;

class GenerateClassRaportZipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 menit
    public int $tries = 1;

    protected int $classId;
    protected int $fstId;
    protected string $type;
    protected string $tglPrint;
    protected ?string $keputusan;
    protected ?int $userId;
    protected string $jobKey;

    /**
     * Create a new job instance.
     */
    public function __construct(int $classId, int $fstId, string $type = 'all', string $tglPrint = '', ?string $keputusan = null, ?int $userId = null)
    {
        $this->classId = $classId;
        $this->fstId = $fstId;
        $this->type = $type;
        $this->tglPrint = !empty($tglPrint) ? $tglPrint : now()->toDateString();
        $this->keputusan = $keputusan;
        $this->userId = $userId;
        $this->jobKey = "raport_zip_job_{$this->classId}_{$this->fstId}";
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Cache::put($this->jobKey, [
            'status'     => 'processing',
            'progress'   => 5,
            'message'    => 'Memulai pembuatan file ZIP raport kelas di latar belakang...',
            'file_url'   => null,
            'updated_at' => now()->toDateTimeString(),
        ], 3600);

        try {
            $class = DB::table('class')->where('id', $this->classId)->first();
            $fst = DB::table('m_fst_pembelajaran')->where('id', $this->fstId)->first();

            if (!$class || !$fst) {
                throw new \Exception('Data Kelas atau Periode FST tidak ditemukan.');
            }

            // Ambil daftar siswa (utamakan dari class aktif atau history)
            $students = DB::table('students')->where('class_id', $this->classId)->orderBy('nama', 'asc')->get();
            if ($students->isEmpty() && \Illuminate\Support\Facades\Schema::hasTable('student_class_history')) {
                $students = DB::table('student_class_history as h')
                    ->join('students as s', 'h.student_id', '=', 's.id')
                    ->where('h.class_id', $this->classId)
                    ->where('h.fst_id', $this->fstId)
                    ->select('s.*')
                    ->orderBy('s.nama', 'asc')
                    ->get();
            }

            $totalStudents = $students->count();
            if ($totalStudents === 0) {
                throw new \Exception('Tidak ada data siswa ditemukan untuk kelas ini.');
            }

            // Inisialisasi controller untuk memanggil generateSingleRaportPdf
            $nilaiAkhirController = app(\App\Http\Controllers\NilaiAkhirController::class);

            $pdfFiles = [];
            $processedCount = 0;

            foreach ($students as $student) {
                try {
                    $res = $nilaiAkhirController->generateSingleRaportPdf(
                        $student->id,
                        $this->classId,
                        $this->fstId,
                        $this->type,
                        $this->tglPrint,
                        $this->keputusan
                    );

                    $fullPath = Storage::disk('public')->path($res['pdf_path']);
                    if (file_exists($fullPath)) {
                        $pdfFiles[] = [
                            'path'     => $fullPath,
                            'filename' => basename($res['pdf_path']),
                        ];
                    }
                } catch (\Exception $e) {
                    Log::error("Queue Job: Gagal render raport siswa ID {$student->id}: " . $e->getMessage());
                }

                $processedCount++;
                $progressPercent = 5 + (int) round(($processedCount / $totalStudents) * 80);

                Cache::put($this->jobKey, [
                    'status'     => 'processing',
                    'progress'   => $progressPercent,
                    'message'    => "Sedang merender PDF siswa: {$processedCount} dari {$totalStudents}...",
                    'file_url'   => null,
                    'updated_at' => now()->toDateTimeString(),
                ], 3600);
            }

            if (empty($pdfFiles)) {
                throw new \Exception('Tidak ada berkas PDF yang berhasil dibuat.');
            }

            // Buat File ZIP
            if (!Storage::disk('public')->exists('raport_zip')) {
                Storage::disk('public')->makeDirectory('raport_zip');
            }

            $safeClassName = str_replace(['/', '\\', ' '], '_', $class->class_name);
            $cleanTA = str_replace(['/', ' '], ['-', '_'], $fst->tahun_ajaran ?? 'TA');
            $cleanSem = preg_replace('/[^a-zA-Z0-9]/', '', $fst->semester ?? 'Sem');
            $zipFilename = "Raport_{$safeClassName}_{$cleanTA}_{$cleanSem}.zip";
            $zipRelativePath = "raport_zip/{$zipFilename}";
            $zipFullPath = Storage::disk('public')->path($zipRelativePath);

            $zip = new ZipArchive();
            if ($zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                foreach ($pdfFiles as $item) {
                    $zip->addFile($item['path'], $item['filename']);
                }
                $zip->close();
            } else {
                throw new \Exception('Gagal membuat arsip ZIP pada sistem berkas.');
            }

            Cache::put($this->jobKey, [
                'status'     => 'completed',
                'progress'   => 100,
                'message'    => "File ZIP berhasil dibuat! Total {$processedCount} berkas rapor siswa tersimpan.",
                'file_url'   => url('storage/' . $zipRelativePath),
                'filename'   => $zipFilename,
                'updated_at' => now()->toDateTimeString(),
            ], 3600);

            Log::info("Queue Job: Raport ZIP kelas {$class->class_name} berhasil dibuat.");
        } catch (\Exception $e) {
            Log::error("Queue Job Error: " . $e->getMessage());
            Cache::put($this->jobKey, [
                'status'     => 'failed',
                'progress'   => 0,
                'message'    => 'Gagal memproses pembuatan file ZIP: ' . $e->getMessage(),
                'file_url'   => null,
                'updated_at' => now()->toDateTimeString(),
            ], 3600);
        }
    }
}
