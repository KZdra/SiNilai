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

class ClassSheetExport implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    protected $class;
    protected $mapel;
    protected $fst;

    public function __construct($class, $mapel, $fst)
    {
        $this->class = $class;
        $this->mapel = $mapel;
        $this->fst   = $fst;
    }

    public function view(): View
    {
        // Ambil data siswa di kelas ini
        $students = DB::table('students')
            ->where('class_id', $this->class->id)
            ->orderBy('nama', 'asc')
            ->get();

        // Ambil nilai yang sudah ada (jika ada) untuk pre-fill
        $existingValues = [];
        if ($this->mapel && $this->fst) {
            $vals = DB::table('values')
                ->where('class_id', $this->class->id)
                ->where('mapel_id', $this->mapel->id)
                ->where('fst_id', $this->fst->id)
                ->get();

            foreach ($vals as $v) {
                $existingValues[$v->student_id] = $v;
            }
        }

        return view('docs.template_sheet_per_class', [
            'class'          => $this->class,
            'mapel'          => $this->mapel,
            'fst'            => $this->fst,
            'students'       => $students,
            'existingValues' => $existingValues,
        ]);
    }

    public function title(): string
    {
        // Bersihkan nama sheet agar valid di Excel (maksimal 31 karakter, tanpa karakter ilegal)
        $cleanName = str_replace(['\\', '/', '?', '*', ':', '[', ']'], '_', $this->class->class_name);
        return substr($cleanName, 0, 31);
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // 1. Judul Utama (Baris 1)
        $sheet->getStyle('A1:Q1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 13,
                'color' => ['argb' => 'FF0F172A'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF1F5F9'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 2. Info Mata Pelajaran, Kelas, Periode (Baris 2)
        $sheet->getStyle('A2:Q2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
                'color' => ['argb' => 'FF1E293B'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 3. Petunjuk (Baris 3)
        $sheet->getStyle('A3:Q3')->applyFromArray([
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

        // 4. Header Kolom Identitas (A4:E4) -> Dark Navy Slate
        $sheet->getStyle('A4:E4')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 10,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E293B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 5. Header Kolom Sumatif 1 s.d 10 (F4:O4) -> Primary Royal Blue
        $sheet->getStyle('F4:O4')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 10,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF2563EB'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 6. Header Kolom STS & SAS (P4:Q4) -> Teal Emerald
        $sheet->getStyle('P4:Q4')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 10,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0F766E'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 7. Border & Alignment Seluruh Tabel
        if ($highestRow >= 4) {
            $sheet->getStyle("A4:Q{$highestRow}")->applyFromArray([
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

            // Set kolom NIS (B) dan NISN (C) sebagai teks agar angka 0 di depan tidak hilang
            $sheet->getStyle("B5:C{$highestRow}")->getNumberFormat()->setFormatCode('@');
        }

        // Atur tinggi baris agar tidak berdempetan
        $sheet->getRowDimension(1)->setRowHeight(26);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getRowDimension(4)->setRowHeight(26);

        return [];
    }
}
