<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MasterDataCache
{
    const TTL_LONG = 86400; // 24 jam

    /**
     * Ambil data profil sekolah (data_sekolah) dari cache
     */
    public static function getSchoolData()
    {
        return Cache::remember('master_school_data', self::TTL_LONG, function () {
            return DB::table('data_sekolah')->first();
        });
    }

    /**
     * Hapus cache data sekolah saat diupdate
     */
    public static function clearSchoolData(): void
    {
        Cache::forget('master_school_data');
    }

    /**
     * Ambil seluruh daftar FST / semester dari cache
     */
    public static function getAllFst()
    {
        return Cache::remember('master_all_fst', self::TTL_LONG, function () {
            return DB::table('m_fst_pembelajaran')
                ->select('id', 'fase', 'semester', 'tahun_ajaran', 'ta', 'is_locked')
                ->orderBy('id', 'asc')
                ->get();
        });
    }

    /**
     * Ambil detail FST berdasarkan ID dari cache
     */
    public static function getFst($id)
    {
        if (!$id) return null;

        return Cache::remember("master_fst_{$id}", self::TTL_LONG, function () use ($id) {
            return DB::table('m_fst_pembelajaran')
                ->select('id', 'fase', 'semester', 'tahun_ajaran', 'ta', 'is_locked')
                ->where('id', $id)
                ->first();
        });
    }

    /**
     * Hapus seluruh cache FST saat ada penambahan/perubahan
     */
    public static function clearFst($id = null): void
    {
        Cache::forget('master_all_fst');
        if ($id) {
            Cache::forget("master_fst_{$id}");
        }
    }

    /**
     * Ambil seluruh daftar kelas dari cache
     */
    public static function getAllClasses()
    {
        return Cache::remember('master_all_classes', self::TTL_LONG, function () {
            return DB::table('class')
                ->select('id', 'class_name')
                ->orderBy('class_name', 'asc')
                ->get();
        });
    }

    /**
     * Ambil nama kelas berdasarkan ID dari cache
     */
    public static function getClassName($id)
    {
        if (!$id) return null;

        return Cache::remember("master_class_name_{$id}", self::TTL_LONG, function () use ($id) {
            return DB::table('class')->where('id', $id)->value('class_name');
        });
    }

    /**
     * Hapus cache daftar kelas
     */
    public static function clearClasses($id = null): void
    {
        Cache::forget('master_all_classes');
        if ($id) {
            Cache::forget("master_class_name_{$id}");
        }
    }

    /**
     * Hapus seluruh master cache sekaligus
     */
    public static function clearAll(): void
    {
        self::clearSchoolData();
        self::clearFst();
        self::clearClasses();
    }
}
