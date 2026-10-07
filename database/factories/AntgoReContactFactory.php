<?php

namespace Database\Factories;

use App\Models\antgo_re_contact;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<antgo_re_contact>
 */
class AntgoReContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->firstName();
        $middleName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'id' => (string) Str::uuid(),
            'code' => strtoupper(fake()->unique()->bothify('CONTACT_###??')),
            'type' => 'Persona',
            'taxId' => strtoupper(fake()->unique()->regexify('[0-9]{8}[A-Z]')),
            'formatedName' => "{$name} {$middleName} {$lastName}",
            'name' => $name,
            'middleName' => $middleName,
            'lastName' => $lastName,
            'companyName' => null,
            'gender' => fake()->randomElement(['Hombre', 'Mujer', 'Other']),
            'birthDate' => fake()->date(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
        ];
    }
}
