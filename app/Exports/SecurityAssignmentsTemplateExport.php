<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SecurityAssignmentsTemplateExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function title(): string
    {
        return 'Template Penjadwalan';
    }

    public function headings(): array
    {
        return [
            'email_petugas',
            'nama_petugas',
            'nama_shift',
            'penempatan_area',
            'tanggal_tugas',
            'catatan'
        ];
    }

    public function array(): array
    {
        return [
            ['security@ga-system.com', 'Satpam A', 'Shift 1 - Pagi', 'Plant A', '2026-07-20', 'Contoh Jadwal Harian'],
            ['security2@ga-system.com', 'Satpam B', '', 'Plant B', '', 'Contoh Penempatan Permanen & Shift Fleksibel (kosongkan tanggal & shift)'],
        ];
    }
}
