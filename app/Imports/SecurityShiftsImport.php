<?php

namespace App\Imports;

use App\Models\SecurityShift;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SecurityShiftsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Memerlukan header: nama_shift, jam_mulai, jam_selesai
        if (empty($row['nama_shift'])) {
            return null;
        }

        $startTime = $row['jam_mulai'] ?? null;
        $endTime = $row['jam_selesai'] ?? null;

        if (is_numeric($startTime)) {
            $startTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($startTime)->format('H:i');
        }
        if (is_numeric($endTime)) {
            $endTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($endTime)->format('H:i');
        }

        // Pastikan format aman
        $startTime = $startTime ? date('H:i', strtotime($startTime)) : null;
        $endTime = $endTime ? date('H:i', strtotime($endTime)) : null;

        if (!$startTime || !$endTime) {
            return null; // Skip jika jam mulai/selesai tidak valid
        }

        // Cek duplikasi nama shift
        $exists = SecurityShift::where('name', $row['nama_shift'])->exists();
        if ($exists) {
            return null; // Skip jika sudah ada
        }

        return new SecurityShift([
            'name'       => $row['nama_shift'],
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ]);
    }
}
