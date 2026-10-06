<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AntgoReAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \DB::table('antgo_re_admins')->upsert([
            [
                'role' => 'super_admin',
                'description' => 'Super Administrator with full access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role' => 'admin',
                'description' => 'Administrator with limited access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role' => 'user',
                'description' => 'User with basic access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role' => 'guest',
                'description' => 'Guest with minimal access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role' => 'manager',
                'description' => 'Manager with supervisory access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'role' => 'finance',
                'description' => 'Finance with financial management access',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ], ['role'], ['description', 'updated_at']);
    }
}
