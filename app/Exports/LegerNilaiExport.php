<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LegerNilaiExport implements FromView, ShouldAutoSize, WithStyles
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('docs.leger_lengkap', $this->data);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Bold header rows
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true, 'size' => 11]],
            3 => ['font' => ['bold' => true, 'size' => 10]],
            4 => ['font' => ['bold' => true, 'size' => 10]],
        ];
    }
}
