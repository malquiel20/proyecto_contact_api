<?php

use App\Models\Contact;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('crea un contacto valido', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = $this->postJson('/api/v1/contacts', [
        'name' => 'Jaider Gil',
        'phone' => '3135327884',
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Jaider Gil')
        ->assertJsonPath('data.phone', '3135327884');

    $this->assertDatabaseHas('contacts', [
        'name' => 'Jaider Gil',
        'phone' => '3135327884',
        'user_id' => $user->id,
        'email' => null,
    ]);
});

test('devuelve 404 para un contacto ajeno', function () {
    $owner = User::factory()->create();
    $visitor = User::factory()->create();
    $contact = Contact::factory()->for($owner)->create([
        'name' => 'Contacto privado',
    ]);
    Sanctum::actingAs($visitor);

    $this->getJson("/api/v1/contacts/{$contact->id}")
        ->assertNotFound();
});