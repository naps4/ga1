<?php

namespace Database\Seeders;

use App\Models\Plant;
use App\Models\Department;
use App\Models\AssetCategory;
use App\Models\Location;
use Illuminate\Database\Seeder;

class GaAssetMasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Plants
        $plants = [
            ['code' => '01', 'name' => 'Plant A', 'description' => 'Fasilitas Manufaktur & Kantor Plant A'],
            ['code' => '02', 'name' => 'Plant B', 'description' => 'Fasilitas Manufaktur & Kantor Plant B'],
            ['code' => '00', 'name' => 'Umum / Mobile (Lintas Plant)', 'description' => 'Aset bergerak & kendaraan operasional lintas plant'],
        ];

        foreach ($plants as $p) {
            Plant::updateOrCreate(['code' => $p['code']], $p);
        }

        // 2. Seed Departments & Aliases Mapping Dictionary
        $departments = [
            [
                'code' => 'GA',
                'name' => 'HRGA & Office',
                'aliases' => ['GA', 'HRGA', 'OFFICE HRGA A', 'OFFICE GA', 'GA.B', 'GA.A', 'OFC', 'HRGA & Office', 'Office', 'Office Plant B', 'OFFICE Plant A']
            ],
            [
                'code' => 'PROD',
                'name' => 'Production',
                'aliases' => ['PROD', 'PRODUKSI', 'PRODUCTION A', 'PRODUCTION', 'PROD.B', 'Produksi', 'Produksi (Plant A)', 'Produksi (Plant B)']
            ],
            [
                'code' => 'WH',
                'name' => 'PPIC & Warehouse / Supply Chain',
                'aliases' => ['WH', 'WAREHOUSE', 'Supply Chain', 'PPIC', 'WH.B', 'WH0724', 'Warehouse', 'PPIC-WH', 'PPIC-WH Plant A', 'Area PPIC-WH Plant B', 'Ruang Admin PPIC-WH Plant B']
            ],
            [
                'code' => 'QC',
                'name' => 'Quality Control',
                'aliases' => ['QC', 'QC0324', 'QAQC', 'QC LAB']
            ],
            [
                'code' => 'QA',
                'name' => 'Quality Assurance',
                'aliases' => ['QA', 'QA A']
            ],
            [
                'code' => 'RnD',
                'name' => 'Research & Development',
                'aliases' => ['RnD', 'R&D', 'RnD3', 'R & D', 'R&D B']
            ],
            [
                'code' => 'FAT',
                'name' => 'Finance, Accounting & Tax',
                'aliases' => ['FAT', 'Dep FAT']
            ],
            [
                'code' => 'PC',
                'name' => 'Procurement',
                'aliases' => ['PC', 'Procurement', 'PROCUREMENT', 'Purchas', 'Dep Procurement', 'Purchasing']
            ],
        ];

        $deptMap = [];
        foreach ($departments as $d) {
            $dept = Department::updateOrCreate(['code' => $d['code']], $d);
            $deptMap[$d['code']] = $dept->id;
        }

        // 3. Seed Asset Categories
        $categories = [
            ['code' => '01', 'name' => 'Elektronik', 'default_useful_life_years' => 4],
            ['code' => '02', 'name' => 'Peralatan / Furniture', 'default_useful_life_years' => 4],
            ['code' => '03', 'name' => 'Kendaraan', 'default_useful_life_years' => 5],
            ['code' => '04', 'name' => 'Mesin & Alat Ukur', 'default_useful_life_years' => 5],
        ];

        foreach ($categories as $c) {
            AssetCategory::updateOrCreate(['code' => $c['code']], $c);
        }

        // 4. Seed Specific Locations (Lampiran C CM-03/GA/002.C.01)
        $locations = [
            // Plant A
            ['plant_code' => '01', 'dept_code' => null,   'room_name' => 'Area Umum'],
            ['plant_code' => '01', 'dept_code' => 'QA',   'room_name' => 'QA'],
            ['plant_code' => '01', 'dept_code' => 'QC',   'room_name' => 'QC LAB'],
            ['plant_code' => '01', 'dept_code' => 'GA',   'room_name' => 'OFFICE Plant A'],
            ['plant_code' => '01', 'dept_code' => 'PROD', 'room_name' => 'Produksi Plant A - Ruang SPV'],
            ['plant_code' => '01', 'dept_code' => 'PROD', 'room_name' => 'Produksi Plant A - Ruang Primer'],
            ['plant_code' => '01', 'dept_code' => 'PROD', 'room_name' => 'Produksi Plant A - Ruang Sekunder'],
            ['plant_code' => '01', 'dept_code' => 'WH',   'room_name' => 'PPIC-WH Plant A'],
            ['plant_code' => '01', 'dept_code' => 'RnD',  'room_name' => 'R & D'],
            ['plant_code' => '01', 'dept_code' => 'GA',   'room_name' => 'Ruang Meeting'],

            // Plant B
            ['plant_code' => '02', 'dept_code' => null,   'room_name' => 'Area Umum'],
            ['plant_code' => '02', 'dept_code' => 'GA',   'room_name' => 'OFFICE Plant B - Umum'],
            ['plant_code' => '02', 'dept_code' => 'PC',   'room_name' => 'OFFICE Plant B - Dep Procurement'],
            ['plant_code' => '02', 'dept_code' => 'FAT',  'room_name' => 'OFFICE Plant B - Dep FAT'],
            ['plant_code' => '02', 'dept_code' => 'WH',   'room_name' => 'Ruang Admin PPIC-WH Plant B'],
            ['plant_code' => '02', 'dept_code' => 'WH',   'room_name' => 'Area PPIC-WH Plant B'],
            ['plant_code' => '02', 'dept_code' => 'PROD', 'room_name' => 'Produksi Plant B - Ruang SPV'],
            ['plant_code' => '02', 'dept_code' => 'PROD', 'room_name' => 'Produksi Plant B - Ruang Primer'],
            ['plant_code' => '02', 'dept_code' => 'PROD', 'room_name' => 'Produksi Plant B - Ruang Primer OT'],
            ['plant_code' => '02', 'dept_code' => 'PROD', 'room_name' => 'Produksi Plant B - Ruang Sekunder'],
        ];

        foreach ($locations as $loc) {
            Location::updateOrCreate(
                [
                    'plant_code' => $loc['plant_code'],
                    'room_name'  => $loc['room_name'],
                ],
                [
                    'department_id' => $deptMap[$loc['dept_code']] ?? null,
                    'doc_reference' => 'CM-03/GA/002.C.01',
                ]
            );
        }
    }
}

