<?php

use App\Models\Contact;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('crea un contacto valido', function () {
    $usuario = User::factory()->create();
    Sanctum::actingAs($usuario);

    $response = $this->postJson('/api/v1/contacts', [
        'name' => 'Jaider Gil',
        'number_phone' => '3135327884',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Jaider Gil')
        ->assertJsonPath('data.number_phone', '3135327884');

    $this->assertDatabaseHas('contacts', [
        'name' => 'Jaider Gil',
        'number_phone' => '3135327884',
        'user_id' => $usuario->id,
        'email' => null,
    ]);
});

test('devuelve 403 para un contacto ajeno', function () {
    $owner = User::factory()->create();
    $visitor = User::factory()->create();
    $contacto = Contact::factory()->for($owner)->create([
        'name' => 'Contacto privado',
    ]);
    Sanctum::actingAs($visitor);

    $this->getJson("/api/v1/contacts/{$contacto->id}")
        ->assertForbidden()
        ->assertJsonPath('message', 'No tienes permiso para acceder a este contacto.');
});
