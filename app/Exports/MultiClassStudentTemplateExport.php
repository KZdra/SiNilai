<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MultiClassStudentTemplateExport implements WithMultipleSheets
{
    protected $classes;

    public function __construct($classes)
    {
        $this->classes = $classes;
    }

    /**
     * Kembalikan array sheet untuk masing-masing kelas.
     *
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->classes as $class) {
            $sheets[] = new StudentClassSheetExport($class);
        }

        return $sheets;
    }
}
