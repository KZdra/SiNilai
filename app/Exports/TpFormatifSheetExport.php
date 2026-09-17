<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TpFormatifSheetExport implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    protected $class;
    protected $mapel;
    protected $fst;
    protected $tps;

    public function __construct($class, $mapel, $fst, $tps = [])
    {
        $this->class = $class;
        $this->mapel = $mapel;
        $this->fst   = $fst;
        $this->tps   = $tps;
    }

    public function view(): View
    {
        // Ambil data siswa di kelas ini
        $students = DB::table('students')
            ->where('class_id', $this->class->id)
            ->orderBy('nama', 'asc')
            ->get();

        // Ambil data tpsiswas yang sudah ada
        $existingTpsiswas = [];
        if ($this->mapel && $this->fst) {
            $rows = DB::table('tpsiswas')
                ->where('class_id', $this->class->id)
                ->where('mapel_id', $this->mapel->id)
                ->where('fst_id', $this->fst->id)
                ->get();

            foreach ($rows as $r) {
                $existingTpsiswas[$r->siswa_id][$r->tp_id] = $r;
            }
        }

        return view('docs.template_sheet_formatif', [
            'class'            => $this->class,
            'mapel'            => $this->mapel,
            'fst'              => $this->fst,
            'students'         => $students,
            'tps'              => $this->tps,
            'existingTpsiswas' => $existingTpsiswas,
        ]);
    }

    public function title(): string
    {
        $cleanName = str_replace(['\\', '/', '?', '*', ':', '[', ']'], '_', $this->class->class_name);
        return substr("TP_" . $cleanName, 0, 31);
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();

        // 1. Judul Utama (Baris 1)
        $sheet->getStyle("A1:{$highestCol}1")->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 12,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF047857'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 2. Info Baris 2
        $sheet->getStyle("A2:{$highestCol}2")->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 10,
                'color' => ['argb' => 'FF1E293B'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 3. Petunjuk Baris 3
        $sheet->getStyle("A3:{$highestCol}3")->applyFromArray([
            'font' => [
                'italic' => true,
                'size'   => 9,
                'color'  => ['argb' => 'FF92400E'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFEF3C7'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 4. Header Kolom (Baris 4)
        $sheet->getStyle("A4:{$highestCol}4")->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 10,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 5. Border seluruh isi tabel
        if ($highestRow >= 4) {
            $sheet->getStyle("A4:{$highestCol}{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FF94A3B8'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Set kolom NIS (B) dan NISN (C) sebagai format teks
            $sheet->getStyle("B5:C{$highestRow}")->getNumberFormat()->setFormatCode('@');
        }

        $sheet->getRowDimension(1)->setRowHeight(26);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(26);

        return [];
    }
}
