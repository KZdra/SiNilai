<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    /**
     * Tampilkan daftar file backup yang tersimpan di private storage.
     */
    public function index()
    {
        $backupFiles = [];
        $totalSizeBytes = 0;

        if (Storage::disk('local')->exists('backups')) {
            $files = Storage::disk('local')->files('backups');
            foreach ($files as $file) {
                if (!str_ends_with(strtolower($file), '.sql')) {
                    continue;
                }

                $size = Storage::disk('local')->size($file);
                $lastModified = Storage::disk('local')->lastModified($file);
                $totalSizeBytes += $size;

                $backupFiles[] = [
                    'filename'   => basename($file),
                    'path'       => $file,
                    'size'       => $this->formatBytes($size),
                    'size_bytes' => $size,
                    'created_at' => Carbon::createFromTimestamp($lastModified)->translatedFormat('d F Y H:i:s'),
                    'timestamp'  => $lastModified,
                ];
            }

            // Urutkan backup terbaru di atas
            usort($backupFiles, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
        }

        $stats = [
            'total_files' => count($backupFiles),
            'total_size'  => $this->formatBytes($totalSizeBytes),
            'last_backup' => !empty($backupFiles) ? $backupFiles[0]['created_at'] : 'Belum pernah',
        ];

        return view('backup.index', compact('backupFiles', 'stats'));
    }

    /**
     * Buat file backup database baru berformat .sql dan simpan ke private storage.
     */
    public function store(Request $request)
    {
        // Tingkatkan batas waktu eksekusi untuk database besar
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        try {
            $filename = 'sinilai_backup_' . date('Y-m-d_H-i-s') . '.sql';
            $backupPath = 'backups/' . $filename;

            // Pastikan folder backups di storage private tersedia
            if (!Storage::disk('local')->exists('backups')) {
                Storage::disk('local')->makeDirectory('backups');
            }

            $pdo = DB::connection()->getPdo();
            $tables = [];
            $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
            while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            // Gunakan stream php://temp untuk menghemat memori
            $tempStream = fopen('php://temp', 'w+');

            $dbName = config('database.connections.mysql.database', 'sinilai');
            $user = Auth::check() ? Auth::user()->name : 'System Administrator';

            // Header SQL
            fwrite($tempStream, "-- ========================================================\n");
            fwrite($tempStream, "-- SiNilai Database Backup File (.sql)\n");
            fwrite($tempStream, "-- Tanggal & Waktu : " . date('Y-m-d H:i:s') . "\n");
            fwrite($tempStream, "-- Basis Data      : {$dbName}\n");
            fwrite($tempStream, "-- Dicadangkan Oleh: {$user}\n");
            fwrite($tempStream, "-- Lokasi File     : Storage Private (storage/app/private/backups)\n");
            fwrite($tempStream, "-- ========================================================\n\n");
            fwrite($tempStream, "SET FOREIGN_KEY_CHECKS = 0;\n");
            fwrite($tempStream, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
            fwrite($tempStream, "SET time_zone = '+00:00';\n");
            fwrite($tempStream, "SET NAMES utf8mb4;\n\n");

            foreach ($tables as $table) {
                // Skema Tabel
                fwrite($tempStream, "-- --------------------------------------------------------\n");
                fwrite($tempStream, "-- Struktur tabel untuk `{$table}`\n");
                fwrite($tempStream, "-- --------------------------------------------------------\n");
                fwrite($tempStream, "DROP TABLE IF EXISTS `{$table}`;\n");

                $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
                $createSql = $createStmt['Create Table'] ?? null;
                if ($createSql) {
                    fwrite($tempStream, $createSql . ";\n\n");
                }

                // Data Tabel
                fwrite($tempStream, "-- Data untuk tabel `{$table}`\n");
                $dataStmt = $pdo->query("SELECT * FROM `{$table}`");
                $rows = [];
                $batchSize = 100;
                $lastRow = null;

                while ($row = $dataStmt->fetch(\PDO::FETCH_ASSOC)) {
                    $lastRow = $row;
                    $escapedValues = array_map(function ($val) use ($pdo) {
                        if (is_null($val)) return 'NULL';
                        return $pdo->quote($val);
                    }, array_values($row));

                    $rows[] = "(" . implode(', ', $escapedValues) . ")";

                    if (count($rows) >= $batchSize) {
                        $columns = array_map(fn($col) => "`{$col}`", array_keys($row));
                        $insertSql = "INSERT INTO `{$table}` (" . implode(', ', $columns) . ") VALUES \n" . implode(",\n", $rows) . ";\n";
                        fwrite($tempStream, $insertSql);
                        $rows = [];
                    }
                }

                if (!empty($rows) && $lastRow !== null) {
                    $columns = array_map(fn($col) => "`{$col}`", array_keys($lastRow));
                    $insertSql = "INSERT INTO `{$table}` (" . implode(', ', $columns) . ") VALUES \n" . implode(",\n", $rows) . ";\n";
                    fwrite($tempStream, $insertSql);
                }

                fwrite($tempStream, "\n");
            }

            fwrite($tempStream, "SET FOREIGN_KEY_CHECKS = 1;\n");
            rewind($tempStream);

            // Simpan ke private disk storage
            Storage::disk('local')->put($backupPath, $tempStream);
            fclose($tempStream);

            $fileSize = Storage::disk('local')->size($backupPath);

            Log::info("Database backup created: {$filename} ({$fileSize} bytes) by user " . Auth::id());

            if ($request->ajax()) {
                return response()->json([
                    'status'       => 'success',
                    'message'      => "Backup database berhasil dibuat: {$filename}",
                    'filename'     => $filename,
                    'size'         => $this->formatBytes($fileSize),
                    'download_url' => route('backup.download', $filename),
                ]);
            }

            return redirect()->route('backup.index')->with('success', "Backup database berhasil dibuat: {$filename}");

        } catch (\Exception $e) {
            Log::error("Database backup failed: " . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Gagal membuat backup database: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->route('backup.index')->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    /**
     * Unduh file backup database (.sql) dari private storage.
     */
    public function download($filename)
    {
        $filename = basename($filename);
        if (!str_ends_with(strtolower($filename), '.sql')) {
            abort(400, 'Format file tidak valid.');
        }

        $path = 'backups/' . $filename;
        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'File backup tidak ditemukan di storage server.');
        }

        return Storage::disk('local')->download($path, $filename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Hapus file backup dari server.
     */
    public function destroy($filename)
    {
        $filename = basename($filename);
        $path = 'backups/' . $filename;

        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
            return response()->json([
                'status'  => 'success',
                'message' => "File backup {$filename} berhasil dihapus dari server.",
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'File backup tidak ditemukan di server.',
        ], 404);
    }

    /**
     * Format byte menjadi satuan yang mudah dibaca.
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
