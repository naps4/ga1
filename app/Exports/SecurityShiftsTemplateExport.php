<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SecurityShiftsTemplateExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function title(): string
    {
        return 'Template Shift';
    }

    public function headings(): array
    {
        return ['nama_shift', 'jam_mulai', 'jam_selesai'];
    }

    public function array(): array
    {
        return [
            ['Shift 1 - Pagi', '07:00', '15:00'],
            ['Shift 2 - Sore', '15:00', '23:00'],
            ['Shift 3 - Malam', '23:00', '07:00'],
        ];
    }
}
