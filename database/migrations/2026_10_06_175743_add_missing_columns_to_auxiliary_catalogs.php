<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('auxiliary_catalogs', function (Blueprint $table) {
            if (! Schema::hasColumn('antgo_re_characteristics', 'description')) {
                Schema::table('antgo_re_characteristics', function (Blueprint $table): void {
                    $table->string('description')->after('id');
                });
            }

            if (! Schema::hasColumn('antgo_re_characteristics', 'inSites')) {
                Schema::table('antgo_re_characteristics', function (Blueprint $table): void {
                    $table->boolean('inSites')->default(false)->after('description');
                });
            }

            if (! Schema::hasColumn('antgo_re_characteristics', 'inBuildings')) {
                Schema::table('antgo_re_characteristics', function (Blueprint $table): void {
                    $table->boolean('inBuildings')->default(false)->after('inSites');
                });
            }

            if (! Schema::hasColumn('antgo_re_characteristics', 'inProperties')) {
                Schema::table('antgo_re_characteristics', function (Blueprint $table): void {
                    $table->boolean('inProperties')->default(false)->after('inBuildings');
                });
            }

            if (! Schema::hasColumn('antgo_re_equipments', 'description')) {
                Schema::table('antgo_re_equipments', function (Blueprint $table): void {
                    $table->string('description')->after('id');
                });
            }

            if (! Schema::hasColumn('antgo_re_equipments', 'inSites')) {
                Schema::table('antgo_re_equipments', function (Blueprint $table): void {
                    $table->boolean('inSites')->default(false)->after('description');
                });
            }

            if (! Schema::hasColumn('antgo_re_equipments', 'inBuildings')) {
                Schema::table('antgo_re_equipments', function (Blueprint $table): void {
                    $table->boolean('inBuildings')->default(false)->after('inSites');
                });
            }

            if (! Schema::hasColumn('antgo_re_equipments', 'inProperties')) {
                Schema::table('antgo_re_equipments', function (Blueprint $table): void {
                    $table->boolean('inProperties')->default(false)->after('inBuildings');
                });
            }

            if (! Schema::hasColumn('antgo_re_infrastructures', 'description')) {
                Schema::table('antgo_re_infrastructures', function (Blueprint $table): void {
                    $table->string('description')->after('id');
                });
            }

            if (! Schema::hasColumn('antgo_re_infrastructures', 'inSites')) {
                Schema::table('antgo_re_infrastructures', function (Blueprint $table): void {
                    $table->boolean('inSites')->default(false)->after('description');
                });
            }

            if (! Schema::hasColumn('antgo_re_infrastructures', 'inBuildings')) {
                Schema::table('antgo_re_infrastructures', function (Blueprint $table): void {
                    $table->boolean('inBuildings')->default(false)->after('inSites');
                });
            }

            if (! Schema::hasColumn('antgo_re_infrastructures', 'inProperties')) {
                Schema::table('antgo_re_infrastructures', function (Blueprint $table): void {
                    $table->boolean('inProperties')->default(false)->after('inBuildings');
                });
            }

            if (! Schema::hasColumn('antgo_re_measures', 'description')) {
                Schema::table('antgo_re_measures', function (Blueprint $table): void {
                    $table->string('description')->after('id');
                });
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auxiliary_catalogs', function (Blueprint $table) {
            // Backwards-compatible migration; intentionally left empty.
        });
    }
};
