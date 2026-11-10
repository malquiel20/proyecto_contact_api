<?php

use App\Models\Contact;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\UniqueConstraintViolationException;

it('relaciona al usuario con sus contactos', function () {
    $usuario = User::factory()->create();
    Contact::factory(3)->for($usuario)->create();
    Contact::factory(2)->create();

    expect($usuario->contactos)->toHaveCount(3)
        ->and($usuario->contactos->first()->user->is($usuario))->toBeTrue();
});

it('borra contactos de forma lógica', function () {
    $contacto = Contact::factory()->create();

    $contacto->delete();

    expect(Contact::count())->toBe(0)
        ->and(Contact::withTrashed()->count())->toBe(1)
        ->and($contacto->fresh()->deleted_at)->not->toBeNull();
});

it('borra de forma lógica a los usuarios', function () {
    $usuario = User::factory()->create();

    $usuario->delete();

    expect(User::count())->toBe(0)
        ->and(User::withTrashed()->count())->toBe(1);
});

it('no repite el correo de un usuario', function () {
    User::factory()->create(['email' => 'ana@contact.test']);
    User::factory()->create(['email' => 'ana@contact.test']);
})->throws(UniqueConstraintViolationException::class);

it('no repite el teléfono de un usuario', function () {
    User::factory()->create(['phone_number' => '3001234567']);
    User::factory()->create(['phone_number' => '3001234567']);
})->throws(UniqueConstraintViolationException::class);

it('elimina los contactos si se elimina definitivamente el usuario', function () {
    $usuario = User::factory()->create();
    Contact::factory(2)->for($usuario)->create();

    $usuario->forceDelete();

    expect(Contact::withTrashed()->count())->toBe(0);
});

it('no guarda la contraseña en texto plano', function () {
    $usuario = User::factory()->create(['password' => 'secreta123']);

    expect($usuario->password)->not->toBe('secreta123')
        ->and(Hash::check('secreta123', $usuario->password))->toBeTrue();
});

it('siembra el administrador y los contactos de ejemplo', function () {
    $this->seed(DatabaseSeeder::class);

    $administrador = User::firstWhere('email', 'admin@contact.test');

    expect($administrador)->not->toBeNull()
        ->and($administrador->contactos()->count())->toBe(10)
        ->and(User::count())->toBe(4)
        ->and(Contact::count())->toBe(25);
});
