<?php

use App\Models\antgo_re_admin;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('guest cannot access user management page', function () {
    $response = $this->get(route('users.index'));

    $response->assertRedirect(route('login'));
});

test('user can be created with admin role', function () {
    $actor = User::factory()->create();
    antgo_re_admin::query()->create([
        'role' => 'manager',
        'description' => 'Manager role',
    ]);

    $response = $this
        ->actingAs($actor)
        ->post(route('users.store'), [
            'name' => 'CSV User',
            'email' => 'csv-user@example.com',
            'password' => 'password123',
            'admin_role' => 'manager',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('users.index'));

    $createdUser = User::query()->where('email', 'csv-user@example.com')->first();

    expect($createdUser)->not->toBeNull();
    expect($createdUser?->name)->toBe('CSV User');
    expect($createdUser?->admin_role)->toBe('manager');
});

test('csv import creates only new users and skips existing emails', function () {
    $actor = User::factory()->create();
    User::factory()->create([
        'email' => 'existing@example.com',
    ]);
    antgo_re_admin::query()->create([
        'role' => 'manager',
        'description' => 'Manager role',
    ]);

    $csv = implode("\n", [
        'name,email,password,admin_role',
        'Existing User,existing@example.com,password123,manager',
        'New User,new-user@example.com,password123,manager',
    ]);

    $file = UploadedFile::fake()->createWithContent('users.csv', $csv);

    $response = $this
        ->actingAs($actor)
        ->post(route('users.import-csv'), [
            'csv_file' => $file,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('users.index'));

    expect(User::query()->where('email', 'existing@example.com')->count())->toBe(1);
    expect(User::query()->where('email', 'new-user@example.com')->exists())->toBeTrue();
});

test('csv import validates required header columns', function () {
    $actor = User::factory()->create();

    $csv = implode("\n", [
        'name,email',
        'Invalid User,invalid@example.com',
    ]);

    $file = UploadedFile::fake()->createWithContent('invalid-users.csv', $csv);

    $response = $this
        ->actingAs($actor)
        ->from(route('users.index'))
        ->post(route('users.import-csv'), [
            'csv_file' => $file,
        ]);

    $response
        ->assertSessionHasErrors('csv_file')
        ->assertRedirect(route('users.index'));
});
