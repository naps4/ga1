<?php

namespace App\Exports;

use App\Models\WoHandover;
use App\Models\WorkOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class WorkOrderReportExport implements WithMultipleSheets
{
    public function __construct(
        protected int $selectedYear,
        protected string $generatorName,
    ) {}

    public function sheets(): array
    {
        return [
            new WorkOrderSummarySheet($this->selectedYear, $this->generatorName),
            new WorkOrderDetailSheet($this->selectedYear),
            new WorkOrderMaterialSheet($this->selectedYear),
        ];
    }
}

// ════════════════════════════════════════════════════════════
// Sheet 1 – Ringkasan Bulanan
// ════════════════════════════════════════════════════════════
class WorkOrderSummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        protected int $selectedYear,
        protected string $generatorName,
    ) {}

    public function title(): string { return 'Ringkasan Bulanan'; }

    public function headings(): array
    {
        return [
            ['LAPORAN WORK ORDER GA — Tahun ' . $this->selectedYear],
            ['Diekspor oleh: ' . $this->generatorName . '   |   Pada: ' . now()->format('d M Y H:i:s')],
            [],
            ['Periode', 'WO Masuk', 'WO Selesai', 'WO Ditutup (Handover)', 'Kumulatif WO Masuk'],
        ];
    }

    public function collection(): Collection
    {
        $masukMap = WorkOrder::selectRaw('MONTH(created_at) as month, count(*) as count')
            ->whereYear('created_at', $this->selectedYear)->groupBy('month')->pluck('count', 'month');

        $selesaiMap = WorkOrder::selectRaw('MONTH(updated_at) as month, count(*) as count')
            ->where('status', 'Selesai')->whereYear('updated_at', $this->selectedYear)
            ->groupBy('month')->pluck('count', 'month');

        $ditutupMap = WoHandover::selectRaw('MONTH(accepted_by_user_at) as month, count(*) as count')
            ->whereNotNull('accepted_by_user_at')->whereYear('accepted_by_user_at', $this->selectedYear)
            ->groupBy('month')->pluck('count', 'month');

        $rows = collect();
        $running = 0;
        $totMasuk = $totSelesai = $totDitutup = 0;

        foreach (range(1, 12) as $m) {
            $masuk   = (int) ($masukMap[$m]   ?? 0);
            $selesai = (int) ($selesaiMap[$m]  ?? 0);
            $ditutup = (int) ($ditutupMap[$m]  ?? 0);
            $running += $masuk;
            $totMasuk += $masuk; $totSelesai += $selesai; $totDitutup += $ditutup;

            $rows->push([
                Carbon::createFromDate($this->selectedYear, $m, 1)->translatedFormat('F Y'),
                $masuk,
                $selesai,
                $ditutup,
                $running,
            ]);
        }

        // Baris total
        $rows->push(['TOTAL', $totMasuk, $totSelesai, $totDitutup, $running]);

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        // Merge judul
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');

        return [
            1  => ['font' => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FF1E3A8A']],
                   'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            2  => ['font' => ['size' => 10, 'color' => ['argb' => 'FF475569']],
                   'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            4  => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                   'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1D4ED8']]],
            16 => ['font' => ['bold' => true],
                   'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDBEAFE']]],
        ];
    }
}

// ════════════════════════════════════════════════════════════
// Sheet 2 – Detail Work Order
// ════════════════════════════════════════════════════════════
class WorkOrderDetailSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(protected int $selectedYear) {}

    public function title(): string { return 'Detail Work Order'; }

    public function headings(): array
    {
        return [[
            'No', 'ID WO', 'Tanggal Pengajuan', 'Pemohon', 'Departemen',
            'Deskripsi Pekerjaan', 'Teknisi Ditugaskan', 'Status',
            'Hasil Pengecekan', 'Kebutuhan Teknisi', 'Butuh Material (FPU)',
            'Status FPU', 'Laporan Akhir', 'Tanggal Selesai', 'Tanggal Handover',
        ]];
    }

    public function collection(): Collection
    {
        $workOrders = WorkOrder::with(['user', 'assignedEngineer', 'fpuRequest', 'handover'])
            ->whereYear('created_at', $this->selectedYear)->oldest()->get();

        return $workOrders->map(function ($wo, $idx) {
            $tanggalSelesai = $wo->status === 'Selesai'
                ? $wo->updated_at->format('d/m/Y H:i') : '-';
            $tanggalHandover = ($wo->handover && $wo->handover->accepted_by_user_at)
                ? Carbon::parse($wo->handover->accepted_by_user_at)->format('d/m/Y H:i') : '-';

            return [
                $idx + 1,
                'WO-' . str_pad($wo->id, 5, '0', STR_PAD_LEFT),
                $wo->created_at->format('d/m/Y H:i'),
                $wo->user->name ?? '-',
                $wo->department ?? '-',
                $wo->description,
                $wo->assignedEngineer->name ?? 'Belum ditugaskan',
                $wo->status,
                $wo->check_result ?? '-',
                $wo->engineer_needs ?? '-',
                $wo->fpuRequest ? 'Ya' : 'Tidak',
                $wo->fpuRequest->status_approval ?? '-',
                $wo->engineer_report ?? '-',
                $tanggalSelesai,
                $tanggalHandover,
            ];
        });
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF334155']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            ],
        ];
    }
}

// ════════════════════════════════════════════════════════════
// Sheet 3 – Detail Material (FPU)
// ════════════════════════════════════════════════════════════
class WorkOrderMaterialSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(protected int $selectedYear) {}

    public function title(): string { return 'Detail Material (FPU)'; }

    public function headings(): array
    {
        return [[
            'No', 'ID WO', 'Pemohon', 'Teknisi', 'Status WO',
            'Prioritas FPU', 'Jenis', 'Kategori', 'Pelaksana', 'Status FPU',
            'Nama Material', 'Spesifikasi', 'Qty', 'Satuan', 'Keterangan',
        ]];
    }

    public function collection(): Collection
    {
        $workOrders = WorkOrder::with(['user', 'assignedEngineer', 'fpuRequest.materials'])
            ->whereYear('created_at', $this->selectedYear)
            ->whereHas('fpuRequest')
            ->oldest()
            ->get();

        $rows = collect();
        $no = 1;

        foreach ($workOrders as $wo) {
            $fpu = $wo->fpuRequest;
            if (!$fpu || $fpu->materials->isEmpty()) {
                // WO ada FPU tapi tidak ada material item
                $rows->push([
                    $no++,
                    'WO-' . str_pad($wo->id, 5, '0', STR_PAD_LEFT),
                    $wo->user->name ?? '-',
                    $wo->assignedEngineer->name ?? '-',
                    $wo->status,
                    $fpu->priority ?? '-',
                    $fpu->type ?? '-',
                    $fpu->category ?? '-',
                    $fpu->executor_type ?? '-',
                    $fpu->status_approval ?? '-',
                    '(Belum ada item material)', '-', '-', '-', '-',
                ]);
                continue;
            }

            foreach ($fpu->materials as $mat) {
                $rows->push([
                    $no++,
                    'WO-' . str_pad($wo->id, 5, '0', STR_PAD_LEFT),
                    $wo->user->name ?? '-',
                    $wo->assignedEngineer->name ?? '-',
                    $wo->status,
                    $fpu->priority ?? '-',
                    $fpu->type ?? '-',
                    $fpu->category ?? '-',
                    $fpu->executor_type ?? '-',
                    $fpu->status_approval ?? '-',
                    $mat->material_name,
                    $mat->specification ?? '-',
                    $mat->qty,
                    $mat->unit ?? '-',
                    $mat->notes ?? '-',
                ]);
            }
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF92400E']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            ],
        ];
    }
}
