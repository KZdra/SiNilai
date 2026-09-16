<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RaportExplorerController extends Controller
{
    /**
     * Pastikan hanya Administrator (role_id = 1) yang dapat mengakses Penjelajah Arsip Raport.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->checkAdmin();
            return $next($request);
        });
    }

    /**
     * Helper proteksi akses khusus Administrator.
     */
    private function checkAdmin(): void
    {
        if (!Auth::check() || Auth::user()->role_id != 1) {
            abort(403, 'Akses ditolak. Penjelajah Arsip Raport hanya dapat diakses oleh Administrator.');
        }
    }

    /**
     * Tampilkan halaman utama penjelajah arsip raport.
     */
    public function index()
    {
        $this->checkAdmin();

        $totalFiles = 0;
        $totalSizeBytes = 0;

        if (Storage::disk('public')->exists('raport')) {
            $allFiles = Storage::disk('public')->allFiles('raport');
            foreach ($allFiles as $f) {
                if (str_ends_with(strtolower($f), '.pdf')) {
                    $totalFiles++;
                    $totalSizeBytes += Storage::disk('public')->size($f);
                }
            }
        }

        $stats = [
            'total_files' => $totalFiles,
            'total_size'  => $this->formatBytes($totalSizeBytes),
        ];

        return view('raport_explorer.index', compact('stats'));
    }

    /**
     * Kembalikan data struktur folder dan berkas PDF dalam format JSON untuk jsTree.
     */
    public function getTreeData()
    {
        $this->checkAdmin();

        $disk = Storage::disk('public');
        if (!$disk->exists('raport')) {
            $disk->makeDirectory('raport');
        }

        $children = $this->buildDirectoryTree('raport');

        $rootNode = [
            'id'       => 'root_raport',
            'text'     => 'Arsip Raport (storage/raport)',
            'icon'     => 'fas fa-archive text-primary',
            'state'    => ['opened' => true],
            'type'     => 'root',
            'data'     => [
                'is_file' => false,
                'path'    => 'raport',
                'name'    => 'Arsip Raport',
            ],
            'children' => $children,
        ];

        return response()->json([$rootNode]);
    }

    /**
     * Bangun struktur pohon direktori rekursif untuk jsTree.
     */
    private function buildDirectoryTree(string $directory): array
    {
        $disk = Storage::disk('public');
        $nodes = [];

        // 1. Baca semua sub-direktori
        $directories = $disk->directories($directory);
        foreach ($directories as $dir) {
            $folderName = basename($dir);
            $subChildren = $this->buildDirectoryTree($dir);

            // Icon kustom berdasarkan level folder
            $icon = ($directory === 'raport')
                ? 'fas fa-school text-indigo'
                : 'fas fa-folder text-warning';

            $nodes[] = [
                'id'       => 'dir_' . md5($dir),
                'text'     => $folderName,
                'icon'     => $icon,
                'state'    => ['opened' => true],
                'type'     => 'folder',
                'data'     => [
                    'is_file'    => false,
                    'path'       => $dir,
                    'name'       => $folderName,
                    'item_count' => count($subChildren),
                ],
                'children' => $subChildren,
            ];
        }

        // 2. Baca semua berkas PDF pada direktori saat ini
        $files = $disk->files($directory);
        foreach ($files as $file) {
            if (!str_ends_with(strtolower($file), '.pdf')) {
                continue;
            }

            $fileName = basename($file);
            $fileSize = $disk->size($file);
            $lastMod  = $disk->lastModified($file);
            $publicUrl = asset('storage/' . $file);

            $cleanStudentName = pathinfo($fileName, PATHINFO_FILENAME);
            $cleanStudentName = str_replace(['_', '-'], ' ', $cleanStudentName);

            $nodes[] = [
                'id'   => 'file_' . md5($file),
                'text' => $fileName,
                'icon' => 'fas fa-file-pdf text-danger',
                'type' => 'pdf',
                'data' => [
                    'is_file'      => true,
                    'filename'     => $fileName,
                    'student_name' => $cleanStudentName,
                    'path'         => $file,
                    'size'         => $this->formatBytes($fileSize),
                    'size_bytes'   => $fileSize,
                    'modified_at'  => Carbon::createFromTimestamp($lastMod)->translatedFormat('d F Y H:i:s'),
                    'url'          => $publicUrl,
                    'download_url' => route('raport_explorer.download', ['path' => $file]),
                ],
            ];
        }

        return $nodes;
    }

    /**
     * Unduh file rapor PDF.
     */
    public function download(Request $request)
    {
        $this->checkAdmin();

        $path = $request->query('path');
        if (!$path || !str_starts_with($path, 'raport/') || !str_ends_with(strtolower($path), '.pdf')) {
            abort(400, 'Jalur file tidak valid.');
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($path)) {
            abort(404, 'File rapor tidak ditemukan di storage.');
        }

        return response()->download($disk->path($path), basename($path));
    }

    /**
     * Hapus berkas rapor dari storage (Khusus Administrator).
     */
    public function destroy(Request $request)
    {
        if (Auth::user()->role_id != 1) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Hanya Administrator yang memiliki akses untuk menghapus arsip rapor.',
            ], 403);
        }

        $path = $request->input('path');
        if (!$path || !str_starts_with($path, 'raport/') || !str_ends_with(strtolower($path), '.pdf')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Jalur file rapor tidak valid.',
            ], 400);
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($path)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Berkas rapor tidak ditemukan di storage server.',
            ], 404);
        }

        $disk->delete($path);

        return response()->json([
            'status'  => 'success',
            'message' => 'Berkas rapor ' . basename($path) . ' berhasil dihapus dari storage.',
        ]);
    }

    /**
     * Format byte menjadi teks kapasitas yang mudah dipahami.
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Tampilkan halaman Export Massal Rapor ke Server khusus Administrator.
     */
    public function bulkExportView()
    {
        $this->checkAdmin();

        $classes = DB::table('class')->orderBy('class_name', 'asc')->get();
        $fstList = \App\Services\MasterDataCache::getAllFst();

        return view('raport_explorer.bulk_export', compact('classes', 'fstList'));
    }

    /**
     * Endpoint API pemrosesan export rapor bertahap (chunking) per kelas & per siswa.
     */
    public function bulkExportChunk(Request $request)
    {
        $this->checkAdmin();

        $classId   = (int) $request->input('class_id');
        $fstId     = (int) $request->input('fst_id');
        $type      = $request->input('type', 'all');
        $tglPrint  = $request->input('tgl_print', now()->toDateString());
        $keputusan = $request->input('keputusan', null);
        $offset    = (int) $request->input('offset', 0);
        $limit     = (int) $request->input('limit', 4);

        $class = DB::table('class')->where('id', $classId)->first();
        if (!$class) {
            return response()->json(['status' => 'error', 'message' => "Kelas ID {$classId} tidak ditemukan."], 404);
        }

        $students = DB::table('students')->where('class_id', $classId)->orderBy('nama', 'asc')->get();
        if ($students->isEmpty() && \Illuminate\Support\Facades\Schema::hasTable('student_class_history')) {
            $students = DB::table('student_class_history as h')
                ->join('students as s', 'h.student_id', '=', 's.id')
                ->where('h.class_id', $classId)
                ->where('h.fst_id', $fstId)
                ->select('s.*')
                ->orderBy('s.nama', 'asc')
                ->get();
        }

        $totalStudents = $students->count();
        if ($totalStudents === 0) {
            return response()->json([
                'status'       => 'empty',
                'class_id'     => $classId,
                'class_name'   => $class->class_name,
                'total'        => 0,
                'processed'    => 0,
                'is_complete'  => true,
                'message'      => "Kelas {$class->class_name} tidak memiliki siswa aktif.",
            ]);
        }

        $slice = $students->slice($offset, $limit);
        $rendered = [];
        $nilaiAkhirController = app(\App\Http\Controllers\NilaiAkhirController::class);

        foreach ($slice as $std) {
            try {
                $res = $nilaiAkhirController->generateSingleRaportPdf($std->id, $classId, $fstId, $type, $tglPrint, $keputusan);
                $rendered[] = [
                    'id'   => $std->id,
                    'nama' => $std->nama,
                    'path' => $res['pdf_path'],
                ];
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Bulk export error for student ID {$std->id}: " . $e->getMessage());
            }
        }

        $nextOffset = $offset + $slice->count();
        $isComplete = ($nextOffset >= $totalStudents);

        return response()->json([
            'status'      => 'success',
            'class_id'    => $classId,
            'class_name'  => $class->class_name,
            'processed'   => count($rendered),
            'offset'      => $offset,
            'next_offset' => $nextOffset,
            'total'       => $totalStudents,
            'is_complete' => $isComplete,
            'percent'     => (int) round(($nextOffset / $totalStudents) * 100),
            'items'       => $rendered,
        ]);
    }
}
