<?php

namespace App\Console\Commands;

use App\Services\AssetEtlMigratorService;
use Illuminate\Console\Command;

class MigrateGaAssetsCommand extends Command
{
    protected $signature = 'ga:migrate-assets';
    protected $description = 'Migrasikan dan bersihkan 4 file Excel Aset GA ke database Master';

    public function handle(AssetEtlMigratorService $migrator): int
    {
        $this->info('Memulai proses ETL & Data Cleansing Aset GA PT Herbatech Innopharma Industry...');

        $startTime = microtime(true);
        $stats = $migrator->migrateAll();
        $duration = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info('=====================================================');
        $this->info('        HASIL MIGRASI ASET GA BERHASIL              ');
        $this->info('=====================================================');
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Aset UTAMA (Data All Aset)', $stats['utama_count']],
                ['Aset TERPISAH (Bulk)', $stats['terpisah_count']],
                ['Aset BERGERAK (Kendaraan)', $stats['bergerak_count']],
                ['Aset Diperkaya dari Plant A', $stats['plant_a_enriched']],
                ['Aset Diperkaya dari Plant B', $stats['plant_b_enriched']],
                ['Resolusi Overlap Plant A & B', $stats['overlap_resolved']],
                ['Aset Yatim Plant A Diinjeksi', $stats['orphan_plant_a']],
                ['Aset Yatim Plant B Diinjeksi', $stats['orphan_plant_b']],
                ['Sub-Ruangan Lampiran C Terpetakan', $stats['locations_mapped']],
                ['TOTAL ASET DI MASTER', $stats['total_assets']],
                ['TOTAL VALUASI PEROLEHAN (Rp)', number_format($stats['total_valuation'], 0, ',', '.')],
                ['Waktu Eksekusi (detik)', $duration . ' s'],
            ]
        );

        return Command::SUCCESS;
    }
}

