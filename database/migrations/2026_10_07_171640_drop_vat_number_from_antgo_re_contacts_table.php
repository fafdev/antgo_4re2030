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
        if (Schema::hasColumn('antgo_re_contacts', 'vatNumber')) {
            DB::statement('DROP INDEX IF EXISTS antgo_re_contacts_vatnumber_unique');

            Schema::table('antgo_re_contacts', function (Blueprint $table): void {
                $table->dropColumn('vatNumber');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('antgo_re_contacts', 'vatNumber')) {
            Schema::table('antgo_re_contacts', function (Blueprint $table): void {
                $table->string('vatNumber')->nullable()->unique();
            });
        }
    }
};
