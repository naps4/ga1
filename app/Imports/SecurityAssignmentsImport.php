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
    public function model(array $row)
    {
        // Memerlukan header: email_petugas, nama_petugas, nama_shift, penempatan_area, tanggal_tugas, catatan
        
        // 1. Cari Petugas Security
        $user = null;
        if (!empty($row['email_petugas'])) {
            $user = User::where('email', trim($row['email_petugas']))->first();
        }
        
        if (!$user && !empty($row['nama_petugas'])) {
            $user = User::where('role', 'security')
                ->where('name', 'like', '%' . trim($row['nama_petugas']) . '%')
                ->first();
        }

        if (!$user) {
            return null; // Skip jika user security tidak ditemukan
        }

        // 2. Cari Shift Kerja
        $shift = null;
        if (!empty($row['nama_shift'])) {
            $shift = SecurityShift::where('name', trim($row['nama_shift']))->first();
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
            return null; // Skip jika penugasan sudah ada
        }

        return new SecurityAssignment([
            'user_id'       => $user->id,
            'shift_id'      => $shift ? $shift->id : null,
            'plant'         => !empty($row['penempatan_area']) ? trim($row['penempatan_area']) : null,
            'assigned_date' => $assignedDate,
            'notes'         => $row['catatan'] ?? null,
        ]);
    }
}
