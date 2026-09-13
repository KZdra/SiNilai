<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Facades\DB;

class MultiClassTemplateNilaiExport implements WithMultipleSheets
{
    protected $classes;
    protected $mapel;
    protected $fst;
    protected $includeTpSheet;

    public function __construct($classes, $mapel, $fst, $includeTpSheet = true)
    {
        $this->classes        = $classes;
        $this->mapel          = $mapel;
        $this->fst            = $fst;
        $this->includeTpSheet = $includeTpSheet;
    }

    /**
     * Kembalikan array sheet untuk masing-masing kelas.
     * Sheet paling awal adalah Tujuan Pembelajaran (TP) karena TP digunakan di semua kelas.
     *
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        // 1. Sheet paling awal: Daftar TP (Tujuan Pembelajaran)
        if ($this->includeTpSheet && $this->mapel && $this->fst) {
            $existingTps = DB::table('m_tp')
                ->where('mapel_id', $this->mapel->id)
                ->where('fst_id', $this->fst->id)
                ->orderBy('id', 'asc')
                ->get()
                ->unique('tp_deskripsi')
                ->values();

            $sheets[] = new TpTemplateExport(null, $this->mapel, $this->fst, $existingTps, 'Daftar TP');
        }

        // 2. Sheet per kelas untuk nilai siswa
        foreach ($this->classes as $class) {
            $sheets[] = new ClassSheetExport($class, $this->mapel, $this->fst);
        }

        return $sheets;
    }
}
