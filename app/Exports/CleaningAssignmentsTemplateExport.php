<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CleaningAssignmentsTemplateExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function title(): string
    {
        return 'Template Penjadwalan Kebersihan';
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
            ['cleaning@ga-system.com', 'Petugas Kebersihan A', 'Shift 1 - Pagi', 'Plant A', '2026-08-04', 'Contoh Jadwal Harian'],
            ['cleaning2@ga-system.com', 'Petugas Kebersihan B', '', 'Plant B', '', 'Contoh Penempatan Permanen & Shift Fleksibel'],
        ];
    }
}
