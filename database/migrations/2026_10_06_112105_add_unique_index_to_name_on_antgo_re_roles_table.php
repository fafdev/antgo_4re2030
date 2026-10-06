<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $duplicatedNames = DB::table('antgo_re_roles')
            ->select('name')
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('name');

        foreach ($duplicatedNames as $name) {
            $ids = DB::table('antgo_re_roles')
                ->where('name', $name)
                ->orderBy('id')
                ->pluck('id')
                ->all();

            $idsToDelete = array_slice($ids, 1);

            if ($idsToDelete !== []) {
                DB::table('antgo_re_roles')
                    ->whereIn('id', $idsToDelete)
                    ->delete();
            }
        }

        Schema::table('antgo_re_roles', function (Blueprint $table) {
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('antgo_re_roles', function (Blueprint $table) {
            $table->dropUnique('antgo_re_roles_name_unique');
        });
    }
};
