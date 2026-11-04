<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AtkRecapExport implements WithMultipleSheets
{
    public function __construct(
        protected array $data,
        protected $filterRequest
    ) {}

    public function sheets(): array
    {
        return [
            new AtkSummarySheet(
                $this->data['atkByCategory'] ?? [],
                $this->data['atkGrandTotalQty'] ?? 0,
                $this->filterRequest
            ),
            new HouseholdSummarySheet(
                $this->data['householdByCategory'] ?? [],
                $this->data['householdGrandTotalQty'] ?? 0,
                $this->filterRequest
            ),
            new AtkDepartmentSheet(
                $this->data['atkByCategory'] ?? [],
                $this->data['atkDeptList'] ?? [],
                $this->data['atkDeptGrandTotals'] ?? [],
                $this->data['atkGrandTotalQty'] ?? 0,
                $this->filterRequest
            ),
            new HouseholdDepartmentSheet(
                $this->data['householdByCategory'] ?? [],
                $this->data['householdDeptList'] ?? [],
                $this->data['householdDeptGrandTotals'] ?? [],
                $this->data['householdGrandTotalQty'] ?? 0,
                $this->filterRequest
            ),
        ];
    }
}

// ════════════════════════════════════════════════════════════
// Sheet 1: Rekap Total ATK
// ════════════════════════════════════════════════════════════
class AtkSummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        protected array $byCategory,
        protected int $grandTotalQty,
        protected $filterRequest
    ) {}

    public function title(): string { return 'Rekap Total ATK'; }

    public function headings(): array
    {
        $periode = 'Semua Periode';
        if ($this->filterRequest && ($this->filterRequest->filled('start_date') || $this->filterRequest->filled('end_date'))) {
            $start = $this->filterRequest->start_date ? Carbon::parse($this->filterRequest->start_date)->format('d/m/Y') : 'Awal';
            $end = $this->filterRequest->end_date ? Carbon::parse($this->filterRequest->end_date)->format('d/m/Y') : 'Akhir';
            $periode = $start . ' s.d ' . $end;
        }

        return [
            ['REKAPITULASI TOTAL KEBUTUHAN ALAT TULIS KANTOR (ATK)'],
            ['Periode: ' . $periode . '   |   Dicetak pada: ' . now()->translatedFormat('d F Y H:i')],
            [],
            ['No', 'Kategori', 'Nama Barang ATK', 'Satuan', 'Total Kebutuhan (Qty)', 'Frekuensi Pengajuan'],
        ];
    }

    public function collection(): Collection
    {
        $rows = collect();
        $no = 1;

        foreach ($this->byCategory as $catName => $catData) {
            foreach ($catData['items'] as $item) {
                $rows->push([
                    $no++,
                    $catName,
                    $item['name'],
                    $item['unit'],
                    $item['total_qty'],
                    $item['request_count'] . 'x pengajuan',
                ]);
            }
            // Subtotal row
            $rows->push([
                '',
                'SUBTOTAL ' . strtoupper($catName),
                '',
                '',
                $catData['subtotal_qty'],
                '',
            ]);
        }

        // Grand Total row
        $rows->push([
            '',
            'GRAND TOTAL KEBUTUHAN ATK',
            '',
            '',
            $this->grandTotalQty,
            '',
        ]);

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');

        $styles = [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E3A8A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            2 => [
                'font' => ['size' => 10, 'italic' => true, 'color' => ['rgb' => '4B5563']],
            ],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];

        $highestRow = $sheet->getHighestRow();
        for ($r = 5; $r <= $highestRow; $r++) {
            $valB = (string)$sheet->getCell('B' . $r)->getValue();
            if (str_starts_with($valB, 'SUBTOTAL')) {
                $sheet->mergeCells("B{$r}:D{$r}");
                $styles[$r] = [
                    'font' => ['bold' => true, 'color' => ['rgb' => '1E40AF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                ];
            } elseif (str_starts_with($valB, 'GRAND TOTAL')) {
                $sheet->mergeCells("B{$r}:D{$r}");
                $styles[$r] = [
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
                ];
            }
        }

        $sheet->getStyle("A4:F{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        return $styles;
    }
}

// ════════════════════════════════════════════════════════════
// Sheet 2: Rekap Total Rumah Tangga
// ════════════════════════════════════════════════════════════
class HouseholdSummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        protected array $byCategory,
        protected int $grandTotalQty,
        protected $filterRequest
    ) {}

    public function title(): string { return 'Rekap Total Rumah Tangga'; }

    public function headings(): array
    {
        $periode = 'Semua Periode';
        if ($this->filterRequest && ($this->filterRequest->filled('start_date') || $this->filterRequest->filled('end_date'))) {
            $start = $this->filterRequest->start_date ? Carbon::parse($this->filterRequest->start_date)->format('d/m/Y') : 'Awal';
            $end = $this->filterRequest->end_date ? Carbon::parse($this->filterRequest->end_date)->format('d/m/Y') : 'Akhir';
            $periode = $start . ' s.d ' . $end;
        }

        return [
            ['REKAPITULASI TOTAL KEBUTUHAN RUMAH TANGGA (RT)'],
            ['Periode: ' . $periode . '   |   Dicetak pada: ' . now()->translatedFormat('d F Y H:i')],
            [],
            ['No', 'Kategori', 'Nama Barang Rumah Tangga', 'Satuan', 'Total Kebutuhan (Qty)', 'Frekuensi Pengajuan'],
        ];
    }

    public function collection(): Collection
    {
        $rows = collect();
        $no = 1;

        foreach ($this->byCategory as $catName => $catData) {
            foreach ($catData['items'] as $item) {
                $rows->push([
                    $no++,
                    $catName,
                    $item['name'],
                    $item['unit'],
                    $item['total_qty'],
                    $item['request_count'] . 'x pengajuan',
                ]);
            }
            // Subtotal row
            $rows->push([
                '',
                'SUBTOTAL ' . strtoupper($catName),
                '',
                '',
                $catData['subtotal_qty'],
                '',
            ]);
        }

        // Grand Total row
        $rows->push([
            '',
            'GRAND TOTAL KEBUTUHAN RUMAH TANGGA',
            '',
            '',
            $this->grandTotalQty,
            '',
        ]);

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');

        $styles = [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '92400E']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            2 => [
                'font' => ['size' => 10, 'italic' => true, 'color' => ['rgb' => '4B5563']],
            ],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D97706']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];

        $highestRow = $sheet->getHighestRow();
        for ($r = 5; $r <= $highestRow; $r++) {
            $valB = (string)$sheet->getCell('B' . $r)->getValue();
            if (str_starts_with($valB, 'SUBTOTAL')) {
                $sheet->mergeCells("B{$r}:D{$r}");
                $styles[$r] = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'B45309']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                ];
            } elseif (str_starts_with($valB, 'GRAND TOTAL')) {
                $sheet->mergeCells("B{$r}:D{$r}");
                $styles[$r] = [
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B45309']],
                ];
            }
        }

        $sheet->getStyle("A4:F{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        return $styles;
    }
}

// ════════════════════════════════════════════════════════════
// Sheet 3: Distribusi Departemen ATK
// ════════════════════════════════════════════════════════════
class AtkDepartmentSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        protected array $byCategory,
        protected array $deptList,
        protected array $deptGrandTotals,
        protected int $grandTotalQty,
        protected $filterRequest
    ) {}

    public function title(): string { return 'Distribusi Departemen ATK'; }

    public function headings(): array
    {
        $headerCols = ['No', 'Kategori', 'Nama Barang ATK', 'Satuan'];
        foreach ($this->deptList as $dept) {
            $headerCols[] = $dept;
        }

        $periode = 'Semua Periode';
        if ($this->filterRequest && ($this->filterRequest->filled('start_date') || $this->filterRequest->filled('end_date'))) {
            $start = $this->filterRequest->start_date ? Carbon::parse($this->filterRequest->start_date)->format('d/m/Y') : 'Awal';
            $end = $this->filterRequest->end_date ? Carbon::parse($this->filterRequest->end_date)->format('d/m/Y') : 'Akhir';
            $periode = $start . ' s.d ' . $end;
        }

        return [
            ['MATRIKS DISTRIBUSI KEBUTUHAN ATK PER DEPARTEMEN / DIVISI'],
            ['Periode: ' . $periode . '   |   Dicetak pada: ' . now()->translatedFormat('d F Y H:i')],
            [],
            $headerCols,
        ];
    }

    public function collection(): Collection
    {
        $rows = collect();
        $no = 1;

        foreach ($this->byCategory as $catName => $catData) {
            foreach ($catData['items'] as $item) {
                $row = [
                    $no++,
                    $catName,
                    $item['name'],
                    $item['unit'],
                ];
                foreach ($this->deptList as $dept) {
                    if (!empty($item['dept_display'][$dept])) {
                        $row[] = $item['dept_display'][$dept];
                    } else {
                        $qty = $item['dept_quantities'][$dept] ?? 0;
                        $row[] = $qty > 0 ? ($qty . ' ' . $item['unit']) : '-';
                    }
                }
                $rows->push($row);
            }

            // Subtotal per category
            $subRow = ['', 'SUBTOTAL ' . strtoupper($catName), '', ''];
            foreach ($this->deptList as $dept) {
                $subRow[] = ($catData['dept_subtotals'][$dept] ?? 0) > 0 ? $catData['dept_subtotals'][$dept] : '-';
            }
            $rows->push($subRow);
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $colCount = 4 + count($this->deptList);
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->mergeCells("A2:{$lastColLetter}2");

        $styles = [
            1 => [
                'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '1E3A8A']],
            ],
            2 => [
                'font' => ['size' => 10, 'italic' => true, 'color' => ['rgb' => '4B5563']],
            ],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];

        $highestRow = $sheet->getHighestRow();
        for ($r = 5; $r <= $highestRow; $r++) {
            $valB = (string)$sheet->getCell('B' . $r)->getValue();
            if (str_starts_with($valB, 'SUBTOTAL')) {
                $sheet->mergeCells("B{$r}:D{$r}");
                $styles[$r] = [
                    'font' => ['bold' => true, 'color' => ['rgb' => '1E40AF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
                ];
            }
        }

        $sheet->getStyle("A4:{$lastColLetter}{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        return $styles;
    }
}

// ════════════════════════════════════════════════════════════
// Sheet 4: Distribusi Departemen Rumah Tangga
// ════════════════════════════════════════════════════════════
class HouseholdDepartmentSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(
        protected array $byCategory,
        protected array $deptList,
        protected array $deptGrandTotals,
        protected int $grandTotalQty,
        protected $filterRequest
    ) {}

    public function title(): string { return 'Distribusi Dept Rumah Tangga'; }

    public function headings(): array
    {
        $headerCols = ['No', 'Kategori', 'Nama Barang Rumah Tangga', 'Satuan'];
        foreach ($this->deptList as $dept) {
            $headerCols[] = $dept;
        }

        $periode = 'Semua Periode';
        if ($this->filterRequest && ($this->filterRequest->filled('start_date') || $this->filterRequest->filled('end_date'))) {
            $start = $this->filterRequest->start_date ? Carbon::parse($this->filterRequest->start_date)->format('d/m/Y') : 'Awal';
            $end = $this->filterRequest->end_date ? Carbon::parse($this->filterRequest->end_date)->format('d/m/Y') : 'Akhir';
            $periode = $start . ' s.d ' . $end;
        }

        return [
            ['MATRIKS DISTRIBUSI KEBUTUHAN RUMAH TANGGA (RT) PER DEPARTEMEN / DIVISI'],
            ['Periode: ' . $periode . '   |   Dicetak pada: ' . now()->translatedFormat('d F Y H:i')],
            [],
            $headerCols,
        ];
    }

    public function collection(): Collection
    {
        $rows = collect();
        $no = 1;

        foreach ($this->byCategory as $catName => $catData) {
            foreach ($catData['items'] as $item) {
                $row = [
                    $no++,
                    $catName,
                    $item['name'],
                    $item['unit'],
                ];
                foreach ($this->deptList as $dept) {
                    if (!empty($item['dept_display'][$dept])) {
                        $row[] = $item['dept_display'][$dept];
                    } else {
                        $qty = $item['dept_quantities'][$dept] ?? 0;
                        $row[] = $qty > 0 ? ($qty . ' ' . $item['unit']) : '-';
                    }
                }
                $rows->push($row);
            }

            // Subtotal per category
            $subRow = ['', 'SUBTOTAL ' . strtoupper($catName), '', ''];
            foreach ($this->deptList as $dept) {
                $subRow[] = ($catData['dept_subtotals'][$dept] ?? 0) > 0 ? $catData['dept_subtotals'][$dept] : '-';
            }
            $rows->push($subRow);
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $colCount = 4 + count($this->deptList);
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);

        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->mergeCells("A2:{$lastColLetter}2");

        $styles = [
            1 => [
                'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '92400E']],
            ],
            2 => [
                'font' => ['size' => 10, 'italic' => true, 'color' => ['rgb' => '4B5563']],
            ],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D97706']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];

        $highestRow = $sheet->getHighestRow();
        for ($r = 5; $r <= $highestRow; $r++) {
            $valB = (string)$sheet->getCell('B' . $r)->getValue();
            if (str_starts_with($valB, 'SUBTOTAL')) {
                $sheet->mergeCells("B{$r}:D{$r}");
                $styles[$r] = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'B45309']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                ];
            }
        }

        $sheet->getStyle("A4:{$lastColLetter}{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        return $styles;
    }
}
