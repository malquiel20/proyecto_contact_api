<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'Ana Pérez',
        'email' => 'ana@contact.test',
        'phone_number' => '3001112233',
        'residence' => 'Calle 1 # 2-3',
    ]);
});

it('pide iniciar sesión para ver el perfil', function () {
    $this->getJson('/api/v1/profile')
        ->assertUnauthorized()
        ->assertJsonPath('login_url', route('auth.login'));
});

it('muestra el perfil del usuario autenticado', function () {
    Sanctum::actingAs($this->user);

    $this->getJson('/api/v1/profile')
        ->assertOk()
        ->assertJsonPath('data.email', 'ana@contact.test')
        ->assertJsonPath('data.phone_number', '3001112233')
        ->assertJsonPath('data.residence', 'Calle 1 # 2-3')
        ->assertJsonMissingPath('data.password');
});

it('actualiza el perfil completo con PUT', function () {
    Sanctum::actingAs($this->user);

    $this->putJson('/api/v1/profile', [
        'url_avatar' => 'https://example.com/ana.png',
        'name' => 'Ana María Pérez',
        'phone_number' => '3009998877',
        'email' => 'anamaria@contact.test',
        'residence' => 'Carrera 9 # 8-7',
    ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Ana María Pérez')
        ->assertJsonPath('data.email', 'anamaria@contact.test');

    expect($this->user->fresh())
        ->phone_number->toBe('3009998877')
        ->residence->toBe('Carrera 9 # 8-7')
        ->url_avatar->toBe('https://example.com/ana.png');
});

it('actualiza solo los campos enviados con PATCH', function () {
    Sanctum::actingAs($this->user);

    $this->patchJson('/api/v1/profile', ['residence' => 'Nueva dirección 123'])
        ->assertOk()
        ->assertJsonPath('data.residence', 'Nueva dirección 123')
        ->assertJsonPath('data.email', 'ana@contact.test');
});

it('permite conservar su propio correo y teléfono', function () {
    Sanctum::actingAs($this->user);

    $this->putJson('/api/v1/profile', [
        'name' => 'Ana',
        'email' => 'ana@contact.test',
        'phone_number' => '3001112233',
    ])->assertOk();
});

it('no permite usar el correo de otro usuario', function () {
    User::factory()->create(['email' => 'luis@contact.test']);
    Sanctum::actingAs($this->user);

    $this->patchJson('/api/v1/profile', ['email' => 'luis@contact.test'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.email.0', 'El correo ya está en uso por otro usuario.');
});

it('no permite usar el teléfono de otro usuario', function () {
    User::factory()->create(['phone_number' => '3205556677']);
    Sanctum::actingAs($this->user);

    $this->patchJson('/api/v1/profile', ['phone_number' => '3205556677'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.phone_number.0', 'El teléfono ya está en uso por otro usuario.');
});

it('permite repetir el nombre y la dirección de otro usuario', function () {
    User::factory()->create(['name' => 'Luis Gómez', 'residence' => 'Calle 50 # 10-10']);
    Sanctum::actingAs($this->user);

    $this->patchJson('/api/v1/profile', ['name' => 'Luis Gómez', 'residence' => 'Calle 50 # 10-10'])
        ->assertOk();
});

it('valida el formato de los campos', function (string $campo, mixed $valor) {
    Sanctum::actingAs($this->user);

    $this->patchJson('/api/v1/profile', [$campo => $valor])
        ->assertUnprocessable()
        ->assertJsonValidationErrors([$campo]);
})->with([
    'nombre vacío' => ['name', ''],
    'correo inválido' => ['email', 'ana'],
    'teléfono con letras' => ['phone_number', '300-abc'],
    'avatar sin URL' => ['url_avatar', 'no-es-url'],
]);

it('cambia la contraseña con confirmación', function () {
    Sanctum::actingAs($this->user);

    $this->patchJson('/api/v1/profile', [
        'password' => 'nueva-clave-123',
        'password_confirmation' => 'nueva-clave-123',
    ])->assertOk();

    expect(Hash::check('nueva-clave-123', $this->user->fresh()->password))->toBeTrue();
});

it('rechaza una contraseña sin confirmar', function () {
    Sanctum::actingAs($this->user);

    $this->patchJson('/api/v1/profile', ['password' => 'nueva-clave-123'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['password']);
});
