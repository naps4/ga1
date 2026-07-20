<?php

namespace App\Imports;

use App\Models\SecurityShift;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SecurityShiftsImport implements ToModel, WithHeadingRow
{
    public $skippedRows = [];
    protected $currentRow = 1;

    public function model(array $row)
    {
        $this->currentRow++;

        if (empty($row['nama_shift'])) {
            $this->skippedRows[] = [
                'row' => $this->currentRow,
                'shift' => '(Kosong)',
                'reason' => 'Nama shift tidak boleh kosong.'
            ];
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

        $startTime = $startTime ? date('H:i', strtotime($startTime)) : null;
        $endTime = $endTime ? date('H:i', strtotime($endTime)) : null;

        if (!$startTime || !$endTime) {
            $this->skippedRows[] = [
                'row' => $this->currentRow,
                'shift' => $row['nama_shift'],
                'reason' => 'Jam mulai atau jam selesai tidak valid.'
            ];
            return null;
        }

        // Cek duplikasi nama shift
        $exists = SecurityShift::where('name', $row['nama_shift'])->exists();
        if ($exists) {
            $this->skippedRows[] = [
                'row' => $this->currentRow,
                'shift' => $row['nama_shift'],
                'reason' => 'Shift dengan nama ini sudah terdaftar.'
            ];
            return null;
        }

        return new SecurityShift([
            'name'       => $row['nama_shift'],
            'start_time' => $startTime,
            'end_time'   => $endTime,
        ]);
    }
}
