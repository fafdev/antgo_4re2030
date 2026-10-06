<?php

namespace Database\Seeders;

use App\Models\antgo_re_characteristic;
use Illuminate\Database\Seeder;

class AntgoReCharacteristicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            [
                'description' => 'Fire resistance',
                'inSites' => true,
                'inBuildings' => true,
                'inProperties' => false,
            ],
            [
                'description' => 'Energy efficiency',
                'inSites' => false,
                'inBuildings' => true,
                'inProperties' => true,
            ],
            [
                'description' => 'Accessibility',
                'inSites' => true,
                'inBuildings' => true,
                'inProperties' => true,
            ],
        ];

        foreach ($rows as $row) {
            antgo_re_characteristic::query()->updateOrCreate(
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
