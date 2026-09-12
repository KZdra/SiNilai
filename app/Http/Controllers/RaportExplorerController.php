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
     * Tampilkan halaman utama penjelajah arsip raport.
     */
    public function index()
    {
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
        $disk = Storage::disk('public');
        if (!$disk->exists('raport')) {
            $disk->makeDirectory('raport');
        }

        $user = Auth::user();
        $isTeacherOnly = ($user && $user->role_id != 1 && $user->class_id !== null);

        $classNameFilter = null;
        if ($isTeacherOnly && $user) {
            $classRecord = DB::table('class')->where('id', $user->class_id)->first();
            if ($classRecord) {
                $classNameFilter = str_replace(['/', '\\', ' '], '_', $classRecord->class_name);
            }
        }

        $children = $this->buildDirectoryTree('raport', $classNameFilter);

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
    private function buildDirectoryTree(string $directory, ?string $classNameFilter = null): array
    {
        $disk = Storage::disk('public');
        $nodes = [];

        // 1. Baca semua sub-direktori
        $directories = $disk->directories($directory);
        foreach ($directories as $dir) {
            $folderName = basename($dir);

            // Jika ada filter kelas untuk wali kelas pada level pertama di dalam folder raport
            if ($directory === 'raport' && $classNameFilter !== null && $folderName !== $classNameFilter) {
                continue;
            }

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
        $path = $request->query('path');
        if (!$path || !str_starts_with($path, 'raport/') || !str_ends_with(strtolower($path), '.pdf')) {
            abort(400, 'Jalur file tidak valid.');
        }

        $disk = Storage::disk('public');
        if (!$disk->exists($path)) {
            abort(404, 'File rapor tidak ditemukan di storage.');
        }

        return $disk->download($path, basename($path));
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
}
