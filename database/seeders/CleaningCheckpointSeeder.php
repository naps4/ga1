<?php

namespace Database\Seeders;

use App\Models\CleaningCheckpoint;
use Illuminate\Database\Seeder;

class CleaningCheckpointSeeder extends Seeder
{
    public function run(): void
    {
        $checkpoints = [
            [
                'plant' => 'Plant A',
                'sub_area' => 'Toilet Utam',
                'area_name' => 'Washtafel & Cermin',
                'code' => 'CP-CLN-A-WASHTAFEL',
                'target_duration' => 5,
            ],
            [
                'plant' => 'Plant A',
                'sub_area' => 'Area Kantor',
                'area_name' => 'Lobby & Resepsionis',
                'code' => 'CP-CLN-A-LOBBY',
                'target_duration' => 10,
            ],
            [
                'plant' => 'Plant B',
                'sub_area' => 'Kantin',
                'area_name' => 'Area Meja Makan & Pantry',
                'code' => 'CP-CLN-B-PANTRY',
                'target_duration' => 15,
            ],
        ];

        foreach ($checkpoints as $cp) {
            CleaningCheckpoint::updateOrCreate(
                ['code' => $cp['code']],
                $cp
            );
        }
    }
}
