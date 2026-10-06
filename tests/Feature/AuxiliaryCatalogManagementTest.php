<?php

use App\Models\antgo_re_characteristic;
use App\Models\antgo_re_equipment;
use App\Models\antgo_re_infrastructure;
use App\Models\antgo_re_measure;
use App\Models\User;
use Illuminate\Http\UploadedFile;

test('guest cannot access auxiliary catalogs', function () {
    $this->get(route('characteristics.index'))->assertRedirect(route('login'));
    $this->get(route('equipments.index'))->assertRedirect(route('login'));
    $this->get(route('infrastructures.index'))->assertRedirect(route('login'));
    $this->get(route('measures.index'))->assertRedirect(route('login'));
});

test('authenticated user can download auxiliary csv templates', function () {
    $actor = User::factory()->create();

    $this->actingAs($actor)->get(route('characteristics.template-csv'))->assertOk();
    $this->actingAs($actor)->get(route('equipments.template-csv'))->assertOk();
    $this->actingAs($actor)->get(route('infrastructures.template-csv'))->assertOk();
    $this->actingAs($actor)->get(route('measures.template-csv'))->assertOk();
});

test('characteristics csv import creates records and skips duplicates', function () {
    $actor = User::factory()->create();
    antgo_re_characteristic::query()->create([
        'description' => 'Fire resistance',
        'inSites' => true,
        'inBuildings' => true,
        'inProperties' => false,
    ]);

    $csv = implode("\n", [
        'description,inSites,inBuildings,inProperties',
        'Fire resistance,1,1,0',
        'Natural ventilation,1,0,1',
    ]);

    $response = $this->actingAs($actor)->post(route('characteristics.import-csv'), [
        'csv_file' => UploadedFile::fake()->createWithContent('characteristics.csv', $csv),
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('characteristics.index'));
    expect(antgo_re_characteristic::query()->where('description', 'Fire resistance')->count())->toBe(1);
    expect(antgo_re_characteristic::query()->where('description', 'Natural ventilation')->exists())->toBeTrue();
});

test('equipments csv import creates records and skips duplicates', function () {
    $actor = User::factory()->create();
    antgo_re_equipment::query()->create([
        'description' => 'HVAC system',
        'inSites' => false,
        'inBuildings' => true,
        'inProperties' => true,
    ]);

    $csv = implode("\n", [
        'description,inSites,inBuildings,inProperties',
        'HVAC system,0,1,1',
        'Water pump,1,1,0',
    ]);

    $response = $this->actingAs($actor)->post(route('equipments.import-csv'), [
        'csv_file' => UploadedFile::fake()->createWithContent('equipments.csv', $csv),
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('equipments.index'));
    expect(antgo_re_equipment::query()->where('description', 'HVAC system')->count())->toBe(1);
    expect(antgo_re_equipment::query()->where('description', 'Water pump')->exists())->toBeTrue();
});

test('infrastructures csv import creates records and skips duplicates', function () {
    $actor = User::factory()->create();
    antgo_re_infrastructure::query()->create([
        'description' => 'Water network',
        'inSites' => true,
        'inBuildings' => true,
        'inProperties' => true,
    ]);

    $csv = implode("\n", [
        'description,inSites,inBuildings,inProperties',
        'Water network,1,1,1',
        'Solar generation line,1,0,1',
    ]);

    $response = $this->actingAs($actor)->post(route('infrastructures.import-csv'), [
        'csv_file' => UploadedFile::fake()->createWithContent('infrastructures.csv', $csv),
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('infrastructures.index'));
    expect(antgo_re_infrastructure::query()->where('description', 'Water network')->count())->toBe(1);
    expect(antgo_re_infrastructure::query()->where('description', 'Solar generation line')->exists())->toBeTrue();
});

test('measures csv import creates records and skips duplicates', function () {
    $actor = User::factory()->create();
    antgo_re_measure::query()->create([
        'description' => 'Square meter',
    ]);

    $csv = implode("\n", [
        'description',
        'Square meter',
        'Ton',
    ]);

    $response = $this->actingAs($actor)->post(route('measures.import-csv'), [
        'csv_file' => UploadedFile::fake()->createWithContent('measures.csv', $csv),
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('measures.index'));
    expect(antgo_re_measure::query()->where('description', 'Square meter')->count())->toBe(1);
    expect(antgo_re_measure::query()->where('description', 'Ton')->exists())->toBeTrue();
});
