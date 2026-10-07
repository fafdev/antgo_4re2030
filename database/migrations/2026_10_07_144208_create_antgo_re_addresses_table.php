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
        Schema::create('antgo_re_addresses', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('street');
            $table->string('number')->nullable();
            $table->string('building')->nullable();
            $table->string('floor')->nullable();
            $table->string('door')->nullable();
            $table->string('city');
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();

            // Identifica el modelo propietario y su registro
            $table->morphs('addressable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antgo_re_addresses');
    }
};
