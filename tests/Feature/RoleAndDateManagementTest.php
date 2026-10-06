<?php

use App\Models\antgo_re_date;
use App\Models\antgo_re_role;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('guest cannot access roles or dates management pages', function () {
    $this->get(route('roles.index'))->assertRedirect(route('login'));
    $this->get(route('dates.index'))->assertRedirect(route('login'));
    $this->get(route('roles.template-csv'))->assertRedirect(route('login'));
    $this->get(route('dates.template-csv'))->assertRedirect(route('login'));
});

test('user can import roles from csv while skipping duplicated names', function () {
    $actor = User::factory()->create();
    antgo_re_role::query()->create([
        'name' => 'Manager',
        'description' => 'Existing manager role',
        'inSites' => true,
        'inBuildings' => false,
        'inProperties' => false,
        'inContracts' => false,
    ]);

    $csv = implode("\n", [
        'name,description,inSites,inBuildings,inProperties,inContracts',
        'Manager,Duplicate manager,1,0,0,0',
        'Analyst,Data analyst role,1,1,0,1',
    ]);

    $file = UploadedFile::fake()->createWithContent('roles.csv', $csv);

    $response = $this
        ->actingAs($actor)
        ->post(route('roles.import-csv'), [
            'csv_file' => $file,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('roles.index'));

    expect(antgo_re_role::query()->where('name', 'Manager')->count())->toBe(1);

    $newRole = antgo_re_role::query()->where('name', 'Analyst')->first();

    expect($newRole)->not->toBeNull();
    expect($newRole?->description)->toBe('Data analyst role');
    expect($newRole?->inSites)->toBeTrue();
    expect($newRole?->inBuildings)->toBeTrue();
    expect($newRole?->inContracts)->toBeTrue();
});

test('user can import dates from csv while skipping duplicated codes', function () {
    $actor = User::factory()->create();
    antgo_re_date::query()->create([
        'code' => 'START_DATE',
        'name' => 'Start date',
        'description' => 'Existing record',
        'inSites' => false,
        'inBuildings' => false,
        'inProperties' => false,
        'inContracts' => false,
    ]);

    $csv = implode("\n", [
        'code,name,description,inSites,inBuildings,inProperties,inContracts',
        'START_DATE,Duplicate,Duplicated row,0,0,0,0',
        'REVIEW_DATE,Review date,Review milestone,1,0,1,0',
    ]);

    $file = UploadedFile::fake()->createWithContent('dates.csv', $csv);

    $response = $this
        ->actingAs($actor)
        ->post(route('dates.import-csv'), [
            'csv_file' => $file,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dates.index'));

    expect(antgo_re_date::query()->where('code', 'START_DATE')->count())->toBe(1);

    $newDate = antgo_re_date::query()->where('code', 'REVIEW_DATE')->first();

    expect($newDate)->not->toBeNull();
    expect($newDate?->name)->toBe('Review date');
    expect($newDate?->inSites)->toBeTrue();
    expect($newDate?->inProperties)->toBeTrue();
});

test('dates csv import validates required header columns', function () {
    $actor = User::factory()->create();

    $csv = implode("\n", [
        'code,name',
        'MISSING_DESCRIPTION,Date without description',
    ]);

    $file = UploadedFile::fake()->createWithContent('invalid-dates.csv', $csv);

    $response = $this
        ->actingAs($actor)
        ->from(route('dates.index'))
        ->post(route('dates.import-csv'), [
            'csv_file' => $file,
        ]);

    $response
        ->assertSessionHasErrors('csv_file')
        ->assertRedirect(route('dates.index'));
});

test('authenticated user can download roles csv template', function () {
    $actor = User::factory()->create();

    $response = $this
        ->actingAs($actor)
        ->get(route('roles.template-csv'));

    $response->assertOk();
    $this->assertStringContainsString(
        'roles-template.csv',
        (string) $response->headers->get('content-disposition'),
    );
    expect($response->streamedContent())->toContain(
        'name,description,inSites,inBuildings,inProperties,inContracts',
    );
});

test('authenticated user can download dates csv template', function () {
    $actor = User::factory()->create();

    $response = $this
        ->actingAs($actor)
        ->get(route('dates.template-csv'));

    $response->assertOk();
    $this->assertStringContainsString(
        'dates-template.csv',
        (string) $response->headers->get('content-disposition'),
    );
    expect($response->streamedContent())->toContain(
        'code,name,description,inSites,inBuildings,inProperties,inContracts',
    );
});
