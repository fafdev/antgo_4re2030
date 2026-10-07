<?php

namespace Database\Seeders;

use App\Models\antgo_re_contact;
use Illuminate\Database\Seeder;

class AntgoReContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        antgo_re_contact::factory(10)->create();
    }
}
