<?php

use App\Models\User;

function loginData(array $cambios = []): array
{
    return array_merge([
        'email' => 'ana@contact.test',
        'password' => 'secreta123',
        'device_name' => 'pruebas',
    ], $cambios);
}

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'ana@contact.test',
        'password' => 'secreta123',
    ]);
});

it('entrega un token con credenciales correctas', function () {
    $this->postJson('/api/v1/auth/login', loginData())
        ->assertOk()
        ->assertJsonStructure(['token', 'token_type', 'user' => ['id', 'name', 'email']])
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.email', 'ana@contact.test')
        ->assertJsonMissingPath('user.password');

    expect($this->user->tokens()->count())->toBe(1);
});

it('rechaza una contraseña incorrecta con 422', function () {
    $this->postJson('/api/v1/auth/login', loginData(['password' => 'otra']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('usa el mismo mensaje si el correo no existe', function () {
    $this->postJson('/api/v1/auth/login', loginData(['email' => 'nadie@contact.test']))
        ->assertUnprocessable()
        ->assertJsonPath('errors.email.0', 'Las credenciales no son correctas.');
});

it('valida los campos obligatorios', function (string $campo) {
    $this->postJson('/api/v1/auth/login', loginData([$campo => '']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors([$campo]);
})->with(['email', 'password', 'device_name']);

it('no deja entrar a un usuario eliminado', function () {
    $this->user->delete();

    $this->postJson('/api/v1/auth/login', loginData())
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

it('cierra sesión y revoca el token', function () {
    $token = $this->user->createToken('pruebas')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/auth/logout')
        ->assertNoContent();

    expect($this->user->tokens()->count())->toBe(0);
});

it('pide iniciar sesión para cerrar sesión', function () {
    $this->postJson('/api/v1/auth/logout')
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Debes iniciar sesión para acceder a este recurso.')
        ->assertJsonPath('login_url', route('auth.login'));
});

it('responde 401 en JSON aunque falte la cabecera Accept', function () {
    $this->post('/api/v1/auth/logout')
        ->assertUnauthorized()
        ->assertHeader('Content-Type', 'application/json');
});

it('limita los intentos de login a 5 por minuto', function () {
    foreach (range(1, 5) as $intento) {
        $this->postJson('/api/v1/auth/login', loginData(['password' => 'otra']))
            ->assertUnprocessable();
    }

    $this->postJson('/api/v1/auth/login', loginData(['password' => 'otra']))
        ->assertTooManyRequests();
});
