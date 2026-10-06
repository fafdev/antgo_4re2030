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
        Schema::create('antgo_re_dates', function (Blueprint $table) {
            $table->string('code')->primary()->unique();
            $table->string('name');
            $table->string('description');
            $table->boolean('inSites')->default(false);
            $table->boolean('inBuildings')->default(false);
            $table->boolean('inProperties')->default(false);
            $table->boolean('inContracts')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antgo_re_dates');
    }
};
