<?php

use App\Models\Contact;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('lista solo los contactos del usuario autenticado', function () {
    $usuario = User::factory()->create();
    Contact::factory(2)->for($usuario)->create();
    Contact::factory()->create();
    Sanctum::actingAs($usuario);

    $this->getJson('/api/v1/contacts')
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

test('muestra el perfil de un contacto propio', function () {
    $usuario = User::factory()->create();
    $contacto = Contact::factory()->for($usuario)->create(['name' => 'Luis Gómez']);
    Sanctum::actingAs($usuario);

    $this->getJson("/api/v1/contacts/{$contacto->id}")
        ->assertOk()
        ->assertJsonPath('data.name', 'Luis Gómez');
});

test('actualiza un contacto propio', function () {
    $usuario = User::factory()->create();
    $contacto = Contact::factory()->for($usuario)->create();
    Sanctum::actingAs($usuario);

    $this->putJson("/api/v1/contacts/{$contacto->id}", [
        'name' => 'Nombre nuevo',
        'number_phone' => '3001112233',
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Nombre nuevo');
});

test('no permite actualizar un contacto de otro usuario', function () {
    $owner = User::factory()->create();
    $contacto = Contact::factory()->for($owner)->create(['name' => 'Original']);
    Sanctum::actingAs(User::factory()->create());

    $this->putJson("/api/v1/contacts/{$contacto->id}", ['name' => 'Cambiado'])
        ->assertForbidden();

    expect($contacto->fresh()->name)->toBe('Original');
});

test('no permite repetir el teléfono en la agenda del mismo usuario', function () {
    $usuario = User::factory()->create();
    Contact::factory()->for($usuario)->create(['number_phone' => '3104445566']);
    Sanctum::actingAs($usuario);

    $this->postJson('/api/v1/contacts', [
        'name' => 'Otro contacto',
        'number_phone' => '3104445566',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['number_phone']);
});

test('elimina un contacto propio con borrado lógico', function () {
    $usuario = User::factory()->create();
    $contacto = Contact::factory()->for($usuario)->create();
    Sanctum::actingAs($usuario);

    $this->deleteJson("/api/v1/contacts/{$contacto->id}")
        ->assertOk();

    $this->assertSoftDeleted($contacto);
    $this->getJson("/api/v1/contacts/{$contacto->id}")
        ->assertNotFound();
});
