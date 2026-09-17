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

class StudentClassSheetExport implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    protected $class;

    public function __construct($class)
    {
        $this->class = $class;
    }

    public function view(): View
    {
        $students = DB::table('students')
            ->where('class_id', $this->class->id)
            ->orderBy('nama', 'asc')
            ->get();

        return view('docs.template_siswa_sheet', [
            'class'    => $this->class,
            'students' => $students,
        ]);
    }

    public function title(): string
    {
        // Maksimal 31 karakter, hilangkan karakter dilarang oleh Excel
        $cleanName = str_replace(['\\', '/', '?', '*', ':', '[', ']'], '_', $this->class->class_name);
        return substr($cleanName, 0, 31);
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // 1. Judul Utama (Baris 1)
        $sheet->getStyle('A1:S1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 12,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0F172A'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 2. Info Baris 2
        $sheet->getStyle('A2:S2')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 10,
                'color' => ['argb' => 'FF1E293B'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF1F5F9'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 3. Header Kolom (Baris 3)
        $sheet->getStyle('A3:S3')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 10,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'alignment' => [
                'vertical'   => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // 4. Border & Alignment Seluruh Baris Data (Baris 4 s.d selesai)
        if ($highestRow >= 4) {
            $sheet->getStyle("A3:S{$highestRow}")->applyFromArray([
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

            // Set kolom NIS (B) dan NISN (C) sebagai teks murni agar 0 di depan tidak terpotong
            $sheet->getStyle("B4:C{$highestRow}")->getNumberFormat()->setFormatCode('@');
        }

        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getRowDimension(3)->setRowHeight(26);

        return [];
    }
}
