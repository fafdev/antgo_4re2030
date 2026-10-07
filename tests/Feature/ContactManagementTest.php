<?php

use App\Models\antgo_re_contact;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

test('guest cannot access contacts management pages', function () {
    $this->get(route('contacts.index'))->assertRedirect(route('login'));
    $this->get(route('contacts.template-csv'))->assertRedirect(route('login'));
});

test('store contact assigns persona type from nif and builds formatted name', function () {
    $actor = User::factory()->create();

    $response = $this
        ->actingAs($actor)
        ->post(route('contacts.store'), [
            'taxId' => '12345678Z',
            'name' => 'Ana',
            'middleName' => 'María',
            'lastName' => 'López',
            'companyName' => 'Should be ignored',
            'gender' => 'Mujer',
            'birthDate' => '1990-02-10',
            'email' => 'ana@example.com',
            'phone' => '+34111111111',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('contacts.index'));

    $contact = antgo_re_contact::query()->where('taxId', '12345678Z')->first();

    expect($contact)->not->toBeNull();
    expect($contact?->code)->toMatch('/^CT-\d{7}$/');
    expect($contact?->type)->toBe('Persona');
    expect($contact?->formatedName)->toBe('Ana María López');
    expect($contact?->companyName)->toBeNull();
});

test('store contact assigns empresa type from cif and clears persona-only fields', function () {
    $actor = User::factory()->create();

    $response = $this
        ->actingAs($actor)
        ->post(route('contacts.store'), [
            'taxId' => 'A58818501',
            'name' => 'Ignored',
            'middleName' => 'Ignored',
            'lastName' => 'Ignored',
            'companyName' => 'Empresa Ejemplo S.L.',
            'gender' => 'Other',
            'birthDate' => '1980-01-01',
            'email' => 'empresa@example.com',
            'phone' => '+34999999999',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('contacts.index'));

    $contact = antgo_re_contact::query()->where('taxId', 'A58818501')->first();

    expect($contact)->not->toBeNull();
    expect($contact?->code)->toMatch('/^CT-\d{7}$/');
    expect($contact?->type)->toBe('Empresa');
    expect($contact?->formatedName)->toBe('Empresa Ejemplo S.L.');
    expect($contact?->name)->toBeNull();
    expect($contact?->middleName)->toBeNull();
    expect($contact?->lastName)->toBeNull();
    expect($contact?->gender)->toBeNull();
    expect($contact?->birthDate)->toBeNull();
});

test('store contact validates spanish tax id format', function () {
    $actor = User::factory()->create();

    $response = $this
        ->actingAs($actor)
        ->from(route('contacts.index'))
        ->post(route('contacts.store'), [
            'taxId' => 'INVALID',
            'name' => 'Name',
            'lastName' => 'Last',
        ]);

    $response
        ->assertSessionHasErrors('taxId')
        ->assertRedirect(route('contacts.index'));
});

test('csv import creates contacts and skips duplicated keys', function () {
    $actor = User::factory()->create();

    antgo_re_contact::query()->create([
        'id' => (string) Str::uuid(),
        'code' => 'EXISTING_001',
        'type' => 'Persona',
        'taxId' => '12345678Z',
        'formatedName' => 'Existing Contact',
        'name' => 'Existing',
        'middleName' => null,
        'lastName' => 'Contact',
        'companyName' => null,
        'gender' => null,
        'birthDate' => null,
        'email' => null,
        'phone' => null,
    ]);

    $csv = implode("\n", [
        'taxId,name,middleName,lastName,companyName,gender,birthDate,email,phone',
        '12345678Z,Ana,,López,,Mujer,1990-01-15,ana@example.com,+34111111111',
        'A58818501,,,,Empresa Demo S.L.,,,empresa@example.com,+34999999999',
    ]);

    $response = $this->actingAs($actor)->post(route('contacts.import-csv'), [
        'csv_file' => UploadedFile::fake()->createWithContent('contacts.csv', $csv),
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('contacts.index'));

    expect(antgo_re_contact::query()->where('taxId', '12345678Z')->count())->toBe(1);
    expect(antgo_re_contact::query()->where('taxId', 'A58818501')->exists())->toBeTrue();
    expect(antgo_re_contact::query()->where('taxId', 'A58818501')->value('code'))->toMatch('/^CT-\d{7}$/');
});

test('contact type cannot be changed during update', function () {
    $actor = User::factory()->create();

    $contact = antgo_re_contact::query()->create([
        'id' => (string) Str::uuid(),
        'code' => 'LOCKED_TYPE_001',
        'type' => 'Persona',
        'taxId' => '12345678Z',
        'formatedName' => 'Ana López',
        'name' => 'Ana',
        'middleName' => null,
        'lastName' => 'López',
        'companyName' => null,
        'gender' => null,
        'birthDate' => null,
        'email' => null,
        'phone' => null,
    ]);

    $response = $this
        ->actingAs($actor)
        ->from(route('contacts.edit', $contact))
        ->put(route('contacts.update', $contact), [
            'taxId' => 'A58818501',
            'companyName' => 'Empresa Demo S.L.',
            'email' => 'persona@example.com',
        ]);

    $response
        ->assertSessionHasErrors('taxId')
        ->assertRedirect(route('contacts.edit', $contact));
});

test('contact code is incremental and readonly on create', function () {
    $actor = User::factory()->create();

    $this->actingAs($actor)->post(route('contacts.store'), [
        'code' => 'CT-9999999',
        'taxId' => '12345678Z',
        'name' => 'Ana',
        'lastName' => 'López',
    ])->assertSessionHasNoErrors();

    $this->actingAs($actor)->post(route('contacts.store'), [
        'taxId' => 'A58818501',
        'companyName' => 'Empresa Demo S.L.',
    ])->assertSessionHasNoErrors();

    $createdContacts = antgo_re_contact::query()
        ->whereIn('taxId', ['12345678Z', 'A58818501'])
        ->orderBy('created_at')
        ->get();

    expect($createdContacts)->toHaveCount(2);

    $firstCode = $createdContacts[0]->code;
    $secondCode = $createdContacts[1]->code;

    expect($firstCode)->toMatch('/^CT-\d{7}$/');
    expect($secondCode)->toMatch('/^CT-\d{7}$/');
    expect($firstCode)->not->toBe('CT-9999999');

    $firstNumber = (int) str_replace('CT-', '', $firstCode);
    $secondNumber = (int) str_replace('CT-', '', $secondCode);

    expect($secondNumber)->toBe($firstNumber + 1);
});

test('store contact persists multiple addresses', function () {
    $actor = User::factory()->create();

    $response = $this
        ->actingAs($actor)
        ->post(route('contacts.store'), [
            'taxId' => '00000000T',
            'name' => 'Marta',
            'lastName' => 'Sanz',
            'addresses' => [
                [
                    'street' => 'Calle Mayor',
                    'number' => '10',
                    'city' => 'Madrid',
                    'postal_code' => '28013',
                    'country' => 'España',
                    'is_primary' => true,
                ],
                [
                    'street' => 'Avenida Diagonal',
                    'number' => '300',
                    'city' => 'Barcelona',
                    'postal_code' => '08013',
                    'country' => 'España',
                    'is_primary' => false,
                ],
            ],
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('contacts.index'));

    $contact = antgo_re_contact::query()->where('taxId', '00000000T')->firstOrFail();

    expect($contact->addresses()->count())->toBe(2);
    expect($contact->addresses()->where('city', 'Madrid')->exists())->toBeTrue();
    expect($contact->addresses()->where('city', 'Barcelona')->exists())->toBeTrue();
    expect($contact->addresses()->where('is_primary', true)->count())->toBe(1);
});

test('update contact replaces addresses collection', function () {
    $actor = User::factory()->create();

    $contact = antgo_re_contact::query()->create([
        'id' => (string) Str::uuid(),
        'code' => 'CT-0001000',
        'type' => 'Persona',
        'taxId' => '00000001R',
        'formatedName' => 'Marta Sanz',
        'name' => 'Marta',
        'middleName' => null,
        'lastName' => 'Sanz',
        'companyName' => null,
        'gender' => null,
        'birthDate' => null,
        'email' => null,
        'phone' => null,
    ]);

    $contact->addresses()->createMany([
        [
            'street' => 'Old street',
            'city' => 'Sevilla',
            'postal_code' => '41001',
            'country' => 'España',
        ],
        [
            'street' => 'Another old street',
            'city' => 'Valencia',
            'postal_code' => '46001',
            'country' => 'España',
        ],
    ]);

    $response = $this
        ->actingAs($actor)
        ->put(route('contacts.update', $contact), [
            'taxId' => '00000001R',
            'name' => 'Marta',
            'lastName' => 'Sanz',
            'addresses' => [
                [
                    'street' => 'New street',
                    'number' => '8',
                    'city' => 'Bilbao',
                    'postal_code' => '48001',
                    'country' => 'España',
                    'is_primary' => true,
                ],
            ],
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('contacts.index'));

    $contact->refresh();

    expect($contact->addresses()->count())->toBe(1);
    expect($contact->addresses()->where('city', 'Bilbao')->exists())->toBeTrue();
    expect($contact->addresses()->where('city', 'Sevilla')->exists())->toBeFalse();
    expect($contact->addresses()->where('is_primary', true)->count())->toBe(1);
});

test('update contact keeps existing address id when provided', function () {
    $actor = User::factory()->create();

    $contact = antgo_re_contact::query()->create([
        'id' => (string) Str::uuid(),
        'code' => 'CT-0001001',
        'type' => 'Persona',
        'taxId' => '00000002W',
        'formatedName' => 'Laura Martín',
        'name' => 'Laura',
        'middleName' => null,
        'lastName' => 'Martín',
        'companyName' => null,
        'gender' => null,
        'birthDate' => null,
        'email' => null,
        'phone' => null,
    ]);

    $existingAddress = $contact->addresses()->create([
        'street' => 'Old street',
        'number' => '1',
        'city' => 'Madrid',
        'postal_code' => '28001',
        'country' => 'España',
    ]);

    $response = $this
        ->actingAs($actor)
        ->put(route('contacts.update', $contact), [
            'taxId' => '00000002W',
            'name' => 'Laura',
            'lastName' => 'Martín',
            'addresses' => [
                [
                    'id' => $existingAddress->id,
                    'street' => 'Updated street',
                    'number' => '99',
                    'city' => 'Madrid',
                    'postal_code' => '28001',
                    'country' => 'España',
                    'is_primary' => true,
                ],
            ],
        ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('contacts.index'));

    $contact->refresh();
    $persistedAddress = $contact->addresses()->first();

    expect($contact->addresses()->count())->toBe(1);
    expect($persistedAddress?->id)->toBe($existingAddress->id);
    expect($persistedAddress?->street)->toBe('Updated street');
    expect($persistedAddress?->number)->toBe('99');
    expect($persistedAddress?->is_primary)->toBeTrue();
});

test('contact cannot have multiple primary addresses', function () {
    $actor = User::factory()->create();

    $response = $this
        ->actingAs($actor)
        ->from(route('contacts.index'))
        ->post(route('contacts.store'), [
            'taxId' => '00000003A',
            'name' => 'Raul',
            'lastName' => 'Díaz',
            'addresses' => [
                [
                    'street' => 'Street one',
                    'city' => 'Madrid',
                    'is_primary' => true,
                ],
                [
                    'street' => 'Street two',
                    'city' => 'Barcelona',
                    'is_primary' => true,
                ],
            ],
        ]);

    $response
        ->assertSessionHasErrors('addresses')
        ->assertRedirect(route('contacts.index'));
});
