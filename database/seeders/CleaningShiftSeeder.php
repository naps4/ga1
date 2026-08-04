<?php

namespace Database\Seeders;

use App\Models\CleaningShift;
use Illuminate\Database\Seeder;

class CleaningShiftSeeder extends Seeder
{
    public function run(): void
    {
        CleaningShift::updateOrCreate(
            ['name' => 'Shift 1 - Pagi'],
            ['start_time' => '06:00:00', 'end_time' => '14:00:00']
        );

        CleaningShift::updateOrCreate(
            ['name' => 'Shift 2 - Siang/Sore'],
            ['start_time' => '14:00:00', 'end_time' => '22:00:00']
        );
    }
}
