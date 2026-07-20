<?php

namespace App\Exports;

use App\Models\SecurityCheckpointLog;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SecurityCheckpointExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $dateFrom;
    protected $dateTo;
    protected $plant;
    protected $shift;
    protected $status;

    public function __construct($filters = [])
    {
        $this->dateFrom = $filters['date_from'] ?? null;
        $this->dateTo = $filters['date_to'] ?? null;
        $this->plant = $filters['plant'] ?? null;
        $this->shift = $filters['shift'] ?? null;
        $this->status = $filters['status'] ?? null;
    }

    public function title(): string
    {
        return 'Log Checkpoint Security';
    }

    public function collection()
    {
        $query = SecurityCheckpointLog::with('user', 'checkpoint');

        if ($this->dateFrom) {
            $query->whereDate('checked_at', '>=', $this->dateFrom);
        }
        if ($this->dateTo) {
            $query->whereDate('checked_at', '<=', $this->dateTo);
        }
        if ($this->plant) {
            $query->whereHas('checkpoint', function($q) {
                $q->where('plant', $this->plant);
            });
        }
        if ($this->shift) {
            $query->where('shift', $this->shift);
        }
        if ($this->status) {
            $query->where('status', $this->status);
        }

        $logs = $query->latest('checked_at')->get();

        $data = collect();
        $no = 1;

        foreach ($logs as $log) {
            $data->push([
                'No' => $no++,
                'Petugas' => $log->user->name ?? '-',
                'Shift' => $log->shift,
                'Tanggal' => $log->checked_at->format('Y-m-d'),
                'Jam' => $log->checked_at->format('H:i:s'),
                'Plant' => $log->checkpoint ? $log->checkpoint->plant : '-',
                'Area' => $log->checkpoint ? $log->checkpoint->area_name : 'Eviden Report',
                'Kode Barcode' => $log->checkpoint ? $log->checkpoint->code : '-',
                'Durasi Check' => $log->duration_minutes . ' Menit',
                'Status' => $log->status,
                'Laporan Temuan' => $log->notes ?? '-',
                'Selfie Bukti' => $log->selfie_path ? asset('storage/' . $log->selfie_path) : '-',
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            ['REKAPITULASI MONITORING CHECK POINT SECURITY (FPU GA)'],
            ['Periode: ' . ($this->dateFrom ?? 'Semua') . ' s/d ' . ($this->dateTo ?? 'Semua')],
            ['Tanggal Ekspor: ' . now()->format('d M Y H:i:s')],
            [],
            [
                'No',
                'Nama Petugas',
                'Jadwal Shift',
                'Tanggal Pengecekan',
                'Jam Pengecekan',
                'Plant',
                'Check Point Area',
                'Kode Barcode',
                'Durasi',
                'Status Area',
                'Laporan/Temuan',
                'Link Bukti Selfie'
            ]
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Styling headers
        $sheet->mergeCells('A1:L1');
        $sheet->mergeCells('A2:L2');
        $sheet->mergeCells('A3:L3');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2:A3')->getFont()->setItalic(true)->setSize(10);

        // Header Row (Row 5)
        $sheet->getStyle('A5:L5')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A5:L5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2563EB'); // Theme blue
        $sheet->getStyle('A5:L5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Gridlines & alignment for content
        $highestRow = $sheet->getHighestRow();
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ];

        if ($highestRow >= 5) {
            $sheet->getStyle('A5:L' . $highestRow)->applyFromArray($styleArray);
            $sheet->getStyle('A5:L' . $highestRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            
            // Align center columns
            for ($row = 6; $row <= $highestRow; $row++) {
                $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $row . ':F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('H' . $row . ':I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        return [];
    }
}
