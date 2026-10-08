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
        Schema::create('antgo_re_contacts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique()->index();
            $table->enum('type', ['Persona', 'Empresa']);
            $table->string('taxId')->unique();
            $table->string('formatedName');
            $table->string('name')->nullable();
            $table->string('middleName')->nullable();
            $table->string('lastName')->nullable();
            $table->string('companyName')->nullable();
            $table->enum('gender', ['Hombre', 'Mujer', 'Other'])->nullable();
            $table->date('birthDate')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobilePhone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antgo_re_contacts');
    }
};
