<?php

namespace App\Imports;

use App\Models\SecurityAssignment;
use App\Models\SecurityShift;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SecurityAssignmentsImport implements ToModel, WithHeadingRow
{
    public $skippedRows = [];
    protected $currentRow = 1;

    public function model(array $row)
    {
        $this->currentRow++;
        
        $email = $row['email_petugas'] ?? null;
        $name = $row['nama_petugas'] ?? null;

        // 1. Cari Petugas Security
        $user = null;
        if (!empty($email)) {
            $user = User::where('email', trim($email))->first();
        }
        
        if (!$user && !empty($name)) {
            $user = User::where('role', 'security')
                ->where('name', 'like', '%' . trim($name) . '%')
                ->first();
        }

        if (!$user) {
            $this->skippedRows[] = [
                'row' => $this->currentRow,
                'petugas' => (!empty($email) ? $email : $name) ?: '(Kosong)',
                'reason' => 'Akun petugas security tidak ditemukan di database.'
            ];
            return null;
        }

        // 2. Cari Shift Kerja
        $shift = null;
        if (!empty($row['nama_shift'])) {
            $shift = $this->findShiftByName($row['nama_shift']);
            if (!$shift) {
                $this->skippedRows[] = [
                    'row' => $this->currentRow,
                    'petugas' => $user->name,
                    'reason' => 'Shift "' . $row['nama_shift'] . '" tidak terdaftar.'
                ];
                return null;
            }
        }

        // 3. Resolve Tanggal Tugas
        $assignedDate = $row['tanggal_tugas'] ?? null;
        if ($assignedDate) {
            if (is_numeric($assignedDate)) {
                $assignedDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($assignedDate)->format('Y-m-d');
            } else {
                try {
                    $assignedDate = Carbon::parse(trim($assignedDate))->format('Y-m-d');
                } catch (\Exception $e) {
                    $assignedDate = null;
                }
            }
        } else {
            $assignedDate = null;
        }

        // 4. Cek Duplikasi Penugasan
        $query = SecurityAssignment::where('user_id', $user->id);
        if ($assignedDate) {
            $query->whereDate('assigned_date', $assignedDate);
        } else {
            $query->whereNull('assigned_date');
        }

        if ($query->exists()) {
            $this->skippedRows[] = [
                'row' => $this->currentRow,
                'petugas' => $user->name,
                'reason' => $assignedDate 
                    ? 'Sudah ditugaskan pada tanggal ' . $assignedDate
                    : 'Sudah memiliki penempatan permanen.'
            ];
            return null;
        }

        return new SecurityAssignment([
            'user_id'       => $user->id,
            'shift_id'      => $shift ? $shift->id : null,
            'plant'         => !empty($row['penempatan_area']) ? trim($row['penempatan_area']) : null,
            'assigned_date' => $assignedDate,
            'notes'         => $row['catatan'] ?? null,
        ]);
    }

    /**
     * Pencarian Shift Kerja dengan toleransi penulisan (fuzzy / keyword mapping)
     */
    protected function findShiftByName($inputName)
    {
        $inputClean = strtolower(trim($inputName));
        if (empty($inputClean)) {
            return null;
        }

        // 1. Coba exact match / case insensitive
        $shifts = SecurityShift::all();
        foreach ($shifts as $s) {
            if (strtolower(trim($s->name)) === $inputClean) {
                return $s;
            }
        }

        // 2. Pemetaan toleransi kata kunci (Pagi, Sore/Siang, Malam/Malem)
        foreach ($shifts as $s) {
            $dbNameLower = strtolower($s->name);
            
            // Shift 1 / Pagi
            if (str_contains($inputClean, 'pagi') || str_contains($inputClean, '1')) {
                if (str_contains($dbNameLower, 'pagi') || str_contains($dbNameLower, '1')) {
                    return $s;
                }
            }
            
            // Shift 2 / Sore / Siang
            if (str_contains($inputClean, 'sore') || str_contains($inputClean, 'siang') || str_contains($inputClean, '2')) {
                if (str_contains($dbNameLower, 'sore') || str_contains($dbNameLower, '2')) {
                    return $s;
                }
            }
            
            // Shift 3 / Malam / Malem
            if (str_contains($inputClean, 'malam') || str_contains($inputClean, 'malem') || str_contains($inputClean, '3')) {
                if (str_contains($dbNameLower, 'malam') || str_contains($dbNameLower, '3')) {
                    return $s;
                }
            }
        }

        return null;
    }
}
