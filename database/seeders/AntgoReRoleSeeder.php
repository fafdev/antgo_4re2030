<?php

namespace Database\Seeders;

use App\Models\antgo_re_role;
use Illuminate\Database\Seeder;

class AntgoReRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        antgo_re_role::query()->upsert([
            [
                'name' => 'Manager',
                'description' => 'Role with site and building management permissions',
                'inSites' => true,
                'inBuildings' => true,
                'inProperties' => false,
                'inContracts' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'PropertyAnalyst',
                'description' => 'Role focused on property analysis',
                'inSites' => false,
                'inBuildings' => false,
                'inProperties' => true,
                'inContracts' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'ContractSupervisor',
                'description' => 'Role with contract supervision permissions',
                'inSites' => false,
                'inBuildings' => false,
                'inProperties' => false,
                'inContracts' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['name'], ['description', 'inSites', 'inBuildings', 'inProperties', 'inContracts', 'updated_at']);
    }
}
