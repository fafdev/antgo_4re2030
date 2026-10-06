<?php

namespace Database\Seeders;

use App\Models\antgo_re_infrastructure;
use Illuminate\Database\Seeder;

class AntgoReInfrastructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            [
                'description' => 'Water network',
                'inSites' => true,
                'inBuildings' => true,
                'inProperties' => true,
            ],
            [
                'description' => 'Drainage system',
                'inSites' => true,
                'inBuildings' => true,
                'inProperties' => false,
            ],
            [
                'description' => 'Telecom backbone',
                'inSites' => true,
                'inBuildings' => false,
                'inProperties' => true,
            ],
        ];

        foreach ($rows as $row) {
            antgo_re_infrastructure::query()->updateOrCreate(
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
