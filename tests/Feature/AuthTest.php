<?php

use App\Models\User;

test('inicia sesión con correo y contraseña correctos', function () {
    $user = User::factory()->create([
        'email' => 'ana@correo.com',
        'password' => 'secreta123',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'ana@correo.com',
        'password' => 'secreta123',
        'device_name' => 'pruebas',
    ])
        ->assertOk()
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.email', 'ana@correo.com')
        ->assertJsonMissingPath('user.password');

    expect($user->tokens()->count())->toBe(1);
});

test('rechaza una contraseña incorrecta con 422', function () {
    User::factory()->create([
        'email' => 'ana@correo.com',
        'password' => 'secreta123',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'ana@correo.com',
        'password' => 'otra-clave',
        'device_name' => 'pruebas',
    ])
        ->assertUnprocessable()
        ->assertJsonPath('errors.email.0', 'Las credenciales no son correctas.');
});

test('cierra sesión y revoca el token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('pruebas')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/auth/logout')
        ->assertNoContent();

    expect($user->tokens()->count())->toBe(0);
});

test('pide iniciar sesión para usar la API protegida', function () {
    $this->getJson('/api/v1/profile')
        ->assertUnauthorized()
        ->assertJsonPath('login_url', route('auth.login'));
});
