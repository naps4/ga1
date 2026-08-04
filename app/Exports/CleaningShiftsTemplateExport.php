<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CleaningShiftsTemplateExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function title(): string
    {
        return 'Template Shift Kebersihan';
    }

    public function headings(): array
    {
        return ['nama_shift', 'jam_mulai', 'jam_selesai'];
    }

    public function array(): array
    {
        return [
            ['Shift 1 - Pagi', '06:00', '14:00'],
            ['Shift 2 - Siang/Sore', '14:00', '22:00'],
        ];
    }
}
