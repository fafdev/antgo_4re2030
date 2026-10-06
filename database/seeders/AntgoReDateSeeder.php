<?php

namespace Database\Seeders;

use App\Models\antgo_re_date;
use Illuminate\Database\Seeder;

class AntgoReDateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        antgo_re_date::query()->upsert([
            [
                'code' => 'START_DATE',
                'name' => 'Start date',
                'description' => 'Initial date for lifecycle tracking',
                'inSites' => true,
                'inBuildings' => true,
                'inProperties' => true,
                'inContracts' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'REVIEW_DATE',
                'name' => 'Review date',
                'description' => 'Periodic review milestone',
                'inSites' => true,
                'inBuildings' => false,
                'inProperties' => true,
                'inContracts' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'END_DATE',
                'name' => 'End date',
                'description' => 'Closure date for process completion',
                'inSites' => false,
                'inBuildings' => false,
                'inProperties' => true,
                'inContracts' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['code'], ['name', 'description', 'inSites', 'inBuildings', 'inProperties', 'inContracts', 'updated_at']);
    }
}
