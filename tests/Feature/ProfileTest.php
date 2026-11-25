<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('muestra el perfil del usuario sin contraseña', function () {
    $usuario = User::factory()->create(['email' => 'ana@correo.com']);
    Sanctum::actingAs($usuario);

    $this->getJson('/api/v1/profile')
        ->assertOk()
        ->assertJsonPath('data.email', 'ana@correo.com')
        ->assertJsonMissingPath('data.password');
});

test('actualiza el nombre y la dirección del perfil', function () {
    $usuario = User::factory()->create();
    Sanctum::actingAs($usuario);

    $this->patchJson('/api/v1/profile', [
        'name' => 'Ana María',
        'residence' => 'Calle 10 # 20-30',
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Ana María')
        ->assertJsonPath('data.residence', 'Calle 10 # 20-30');
});

test('no permite usar el correo de otro usuario', function () {
    User::factory()->create(['email' => 'luis@correo.com']);
    $usuario = User::factory()->create();
    Sanctum::actingAs($usuario);

    $this->patchJson('/api/v1/profile', ['email' => 'luis@correo.com'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});
