<?php

namespace Database\Seeders;

use App\Models\antgo_re_equipment;
use Illuminate\Database\Seeder;

class AntgoReEquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            [
                'description' => 'HVAC system',
                'inSites' => false,
                'inBuildings' => true,
                'inProperties' => true,
            ],
            [
                'description' => 'Electrical panel',
                'inSites' => false,
                'inBuildings' => true,
                'inProperties' => true,
            ],
            [
                'description' => 'Security cameras',
                'inSites' => true,
                'inBuildings' => true,
                'inProperties' => false,
            ],
        ];

        foreach ($rows as $row) {
            antgo_re_equipment::query()->updateOrCreate(
                ['description' => $row['description']],
                [
                    'inSites' => $row['inSites'],
                    'inBuildings' => $row['inBuildings'],
                    'inProperties' => $row['inProperties'],
                ],
            );
        }
    }
}
