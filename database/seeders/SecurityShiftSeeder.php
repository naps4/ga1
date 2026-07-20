<?php

namespace Database\Seeders;

use App\Models\SecurityShift;
use Illuminate\Database\Seeder;

class SecurityShiftSeeder extends Seeder
{
    public function run(): void
    {
        SecurityShift::updateOrCreate(
            ['name' => 'Shift 1 - Pagi'],
            ['start_time' => '07:00:00', 'end_time' => '15:00:00']
        );

        SecurityShift::updateOrCreate(
            ['name' => 'Shift 2 - Sore'],
            ['start_time' => '15:00:00', 'end_time' => '23:00:00']
        );

        SecurityShift::updateOrCreate(
            ['name' => 'Shift 3 - Malam'],
            ['start_time' => '23:00:00', 'end_time' => '07:00:00']
        );
    }
}
