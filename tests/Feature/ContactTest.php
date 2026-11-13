<?php

use App\Models\Contact;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('lista solo los contactos del usuario autenticado', function () {
    $user = User::factory()->create();
    Contact::factory(2)->for($user)->create();
    Contact::factory()->create();
    Sanctum::actingAs($user);

    $this->getJson('/api/v1/contacts')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('muestra el perfil de un contacto propio', function () {
    $user = User::factory()->create();
    $contact = Contact::factory()->for($user)->create(['name' => 'Luis Gómez']);
    Sanctum::actingAs($user);

    $this->getJson("/api/v1/contacts/{$contact->id}")
        ->assertOk()
        ->assertJsonPath('data.name', 'Luis Gómez');
});

test('actualiza un contacto propio', function () {
    $user = User::factory()->create();
    $contact = Contact::factory()->for($user)->create();
    Sanctum::actingAs($user);

    $this->putJson("/api/v1/contacts/{$contact->id}", [
        'name' => 'Nombre nuevo',
        'number_phone' => '3001112233',
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Nombre nuevo');
});

test('no permite actualizar un contacto de otro usuario', function () {
    $owner = User::factory()->create();
    $contact = Contact::factory()->for($owner)->create(['name' => 'Original']);
    Sanctum::actingAs(User::factory()->create());

    $this->putJson("/api/v1/contacts/{$contact->id}", ['name' => 'Cambiado'])
        ->assertForbidden();

    expect($contact->fresh()->name)->toBe('Original');
});

test('no permite repetir el teléfono en la agenda del mismo usuario', function () {
    $user = User::factory()->create();
    Contact::factory()->for($user)->create(['number_phone' => '3104445566']);
    Sanctum::actingAs($user);

    $this->postJson('/api/v1/contacts', [
        'name' => 'Otro contacto',
        'number_phone' => '3104445566',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['number_phone']);
});

test('elimina un contacto propio con borrado lógico', function () {
    $user = User::factory()->create();
    $contact = Contact::factory()->for($user)->create();
    Sanctum::actingAs($user);

    $this->deleteJson("/api/v1/contacts/{$contact->id}")
        ->assertOk();

    $this->assertSoftDeleted($contact);
    $this->getJson("/api/v1/contacts/{$contact->id}")
        ->assertNotFound();
});
