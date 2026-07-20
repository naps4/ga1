<?php

namespace Database\Seeders;

use App\Models\SecurityCheckpoint;
use Illuminate\Database\Seeder;

class SecurityCheckpointSeeder extends Seeder
{
    public function run(): void
    {
        $checkpoints = [
            // Plant A
            ['plant' => 'Plant A', 'area_name' => 'Office', 'code' => 'CP-A-OFFICE', 'target_duration' => 5],
            ['plant' => 'Plant A', 'area_name' => 'QC', 'code' => 'CP-A-QC', 'target_duration' => 5],
            ['plant' => 'Plant A', 'area_name' => 'QA', 'code' => 'CP-A-QA', 'target_duration' => 5],
            ['plant' => 'Plant A', 'area_name' => 'Produksi Primer', 'code' => 'CP-A-PROD-PRIMER', 'target_duration' => 10],
            ['plant' => 'Plant A', 'area_name' => 'Ruang Supervisor', 'code' => 'CP-A-RUANG-SPV', 'target_duration' => 5],
            ['plant' => 'Plant A', 'area_name' => 'Sekunder', 'code' => 'CP-A-SEKUNDER', 'target_duration' => 10],

            // Plant B
            ['plant' => 'Plant B', 'area_name' => 'Office', 'code' => 'CP-B-OFFICE', 'target_duration' => 5],
            ['plant' => 'Plant B', 'area_name' => 'Ruang Meeting', 'code' => 'CP-B-RUANG-MEETING', 'target_duration' => 5],
            ['plant' => 'Plant B', 'area_name' => 'R&D', 'code' => 'CP-B-RD', 'target_duration' => 5],
            ['plant' => 'Plant B', 'area_name' => 'Produksi Primer Pangan', 'code' => 'CP-B-PROD-PANGAN', 'target_duration' => 10],
            ['plant' => 'Plant B', 'area_name' => 'Produksi Primer OT', 'code' => 'CP-B-PROD-OT', 'target_duration' => 10],
            ['plant' => 'Plant B', 'area_name' => 'Ruang Supervisor', 'code' => 'CP-B-RUANG-SPV', 'target_duration' => 5],
            ['plant' => 'Plant B', 'area_name' => 'Sekunder', 'code' => 'CP-B-SEKUNDER', 'target_duration' => 10],
            ['plant' => 'Plant B', 'area_name' => 'Warehouse', 'code' => 'CP-B-WAREHOUSE', 'target_duration' => 10],
        ];

        foreach ($checkpoints as $cp) {
            SecurityCheckpoint::updateOrCreate(
                ['code' => $cp['code']],
                $cp
            );
        }
    }
}
