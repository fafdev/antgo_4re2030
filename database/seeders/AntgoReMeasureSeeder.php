<?php

namespace Database\Seeders;

use App\Models\antgo_re_measure;
use Illuminate\Database\Seeder;

class AntgoReMeasureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Square meter', 'Linear meter', 'Cubic meter'] as $description) {
            antgo_re_measure::query()->updateOrCreate(
                ['description' => $description],
                ['description' => $description],
            );
        }
    }
}
