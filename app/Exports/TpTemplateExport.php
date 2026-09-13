<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TpTemplateExport implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    protected $class;
    protected $mapel;
    protected $fst;
    protected $existingTps;
    protected $customTitle;

    public function __construct($class, $mapel, $fst, $existingTps = [], $customTitle = null)
    {
        $this->class       = $class;
        $this->mapel       = $mapel;
        $this->fst         = $fst;
        $this->existingTps = $existingTps;
        $this->customTitle = $customTitle;
    }

    public function view(): View
    {
        return view('docs.template_tp', [
            'class'       => $this->class,
            'mapel'       => $this->mapel,
            'fst'         => $this->fst,
            'existingTps' => $this->existingTps,
        ]);
    }

    public function title(): string
    {
        if ($this->customTitle) {
            $cleanTitle = str_replace(['\\', '/', '?', '*', ':', '[', ']'], '_', $this->customTitle);
            return substr($cleanTitle, 0, 31);
        }

        $mapelName = $this->mapel ? $this->mapel->nama_mapel : 'TP';
        $cleanName = str_replace(['\\', '/', '?', '*', ':', '[', ']'], '_', $mapelName);
        return substr("TP_" . $cleanName, 0, 31);
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // 1. Judul Utama (Baris 1)
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 13,
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

        // 2. Info Periode, Kelas, Mapel (Baris 2)
        $sheet->getStyle('A2:C2')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 10,
                'color' => ['argb' => 'FF1E293B'],
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFF8FAFC'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 3. Petunjuk Pengisian (Baris 3)
        $sheet->getStyle('A3:C3')->applyFromArray([
            'font' => [
                'italic' => true,
                'size'   => 9,
                'color'  => ['argb' => 'FF475569'],
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
        $sheet->getStyle('A4:C4')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size'  => 10,
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Background header kolom
        $sheet->getStyle('A4')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('B4')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2563EB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getStyle('C4')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF0F766E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // 5. Border dan text alignment untuk seluruh isi baris data (Baris 5 s.d selesai)
        if ($highestRow >= 5) {
            $sheet->getStyle("A5:C{$highestRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FFCBD5E1'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Center align No & Kode TP
            $sheet->getStyle("A5:B{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Left align deskripsi TP dengan text wrap
            $sheet->getStyle("C5:C{$highestRow}")->getAlignment()->setWrapText(true);
        }

        // Set row heights
        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->getRowDimension(2)->setRowHeight(24);
        $sheet->getRowDimension(3)->setRowHeight(22);
        $sheet->getRowDimension(4)->setRowHeight(26);

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(80);

        return [];
    }
}
