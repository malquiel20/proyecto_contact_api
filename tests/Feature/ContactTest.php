<?php

use App\Models\Contact;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function datosContacto(array $cambios = []): array
{
    return array_merge([
        'name' => 'Luis Gómez',
        'number_phone' => '3104445566',
        'email' => 'luis@correo.com',
        'residence' => 'Calle 20 # 30-40',
    ], $cambios);
}

beforeEach(function () {
    $this->usuario = User::factory()->create();
    $this->otro = User::factory()->create();
});

describe('sin iniciar sesión', function () {
    it('devuelve al login en cada endpoint', function (string $metodo, string $ruta) {
        $contacto = Contact::factory()->for($this->usuario)->create();

        $this->json($metodo, str_replace('{id}', $contacto->id, $ruta))
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Debes iniciar sesión para acceder a este recurso.')
            ->assertJsonPath('login_url', route('auth.login'));
    })->with([
        'listar' => ['GET', '/api/v1/contacts'],
        'agregar' => ['POST', '/api/v1/contacts'],
        'ver' => ['GET', '/api/v1/contacts/{id}'],
        'actualizar' => ['PUT', '/api/v1/contacts/{id}'],
        'eliminar' => ['DELETE', '/api/v1/contacts/{id}'],
    ]);
});

describe('listar', function () {
    it('lista solo los contactos propios, ordenados por nombre', function () {
        Contact::factory()->for($this->usuario)->create(['name' => 'Zoe']);
        Contact::factory()->for($this->usuario)->create(['name' => 'Ana']);
        Contact::factory(3)->for($this->otro)->create();
        Sanctum::actingAs($this->usuario);

        $this->getJson('/api/v1/contacts')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Ana')
            ->assertJsonPath('data.1.name', 'Zoe')
            ->assertJsonStructure(['data', 'links', 'meta']);
    });

    it('no lista los contactos eliminados', function () {
        Contact::factory(2)->for($this->usuario)->create();
        Contact::factory()->for($this->usuario)->create()->delete();
        Sanctum::actingAs($this->usuario);

        $this->getJson('/api/v1/contacts')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    });

    it('pagina de 15 en 15', function () {
        Contact::factory(20)->for($this->usuario)->create();
        Sanctum::actingAs($this->usuario);

        $this->getJson('/api/v1/contacts?page=2')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 20);
    });
});

describe('agregar', function () {
    it('agrega un contacto a la agenda del usuario', function () {
        Sanctum::actingAs($this->usuario);

        $this->postJson('/api/v1/contacts', datosContacto())
            ->assertCreated()
            ->assertJsonPath('data.name', 'Luis Gómez')
            ->assertJsonPath('data.email', 'luis@correo.com');

        expect($this->usuario->contactos()->count())->toBe(1);
    });

    it('ignora un user_id enviado en la petición', function () {
        Sanctum::actingAs($this->usuario);

        $this->postJson('/api/v1/contacts', datosContacto(['user_id' => $this->otro->id]))
            ->assertCreated();

        expect($this->usuario->contactos()->count())->toBe(1)
            ->and($this->otro->contactos()->count())->toBe(0);
    });

    it('valida el nombre y los formatos', function (string $campo, mixed $valor) {
        Sanctum::actingAs($this->usuario);

        $this->postJson('/api/v1/contacts', datosContacto([$campo => $valor]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$campo]);
    })->with([
        'nombre vacío' => ['name', ''],
        'correo inválido' => ['email', 'luis'],
        'teléfono con letras' => ['number_phone', '310-abc'],
    ]);

    it('pide un teléfono o un correo', function () {
        Sanctum::actingAs($this->usuario);

        $this->postJson('/api/v1/contacts', ['name' => 'Sin datos'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.number_phone.0', 'Debes indicar un teléfono o un correo.');
    });

    it('no permite repetir el teléfono de otro contacto propio', function () {
        Contact::factory()->for($this->usuario)->create(['number_phone' => '3104445566']);
        Sanctum::actingAs($this->usuario);

        $this->postJson('/api/v1/contacts', datosContacto())
            ->assertUnprocessable()
            ->assertJsonPath('errors.number_phone.0', 'Ya tienes un contacto con este teléfono.');
    });

    it('no permite repetir el correo de otro contacto propio', function () {
        Contact::factory()->for($this->usuario)->create(['email' => 'luis@correo.com']);
        Sanctum::actingAs($this->usuario);

        $this->postJson('/api/v1/contacts', datosContacto())
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Ya tienes un contacto con este correo.');
    });

    it('permite repetir el nombre y la dirección', function () {
        Contact::factory()->for($this->usuario)->create([
            'name' => 'Luis Gómez',
            'residence' => 'Calle 20 # 30-40',
        ]);
        Sanctum::actingAs($this->usuario);

        $this->postJson('/api/v1/contacts', datosContacto())->assertCreated();
    });

    it('permite el mismo teléfono y correo en la agenda de otro usuario', function () {
        Contact::factory()->for($this->otro)->create([
            'number_phone' => '3104445566',
            'email' => 'luis@correo.com',
        ]);
        Sanctum::actingAs($this->usuario);

        $this->postJson('/api/v1/contacts', datosContacto())->assertCreated();
    });

    it('permite reutilizar el teléfono y correo de un contacto eliminado', function () {
        Contact::factory()->for($this->usuario)->create([
            'number_phone' => '3104445566',
            'email' => 'luis@correo.com',
        ])->delete();
        Sanctum::actingAs($this->usuario);

        $this->postJson('/api/v1/contacts', datosContacto())->assertCreated();
    });
});

describe('perfil del contacto', function () {
    it('muestra la información de un contacto propio', function () {
        $contacto = Contact::factory()->for($this->usuario)->create(datosContacto());
        Sanctum::actingAs($this->usuario);

        $this->getJson("/api/v1/contacts/{$contacto->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $contacto->id)
            ->assertJsonPath('data.name', 'Luis Gómez')
            ->assertJsonPath('data.number_phone', '3104445566')
            ->assertJsonPath('data.email', 'luis@correo.com')
            ->assertJsonPath('data.residence', 'Calle 20 # 30-40');
    });

    it('no deja ver el contacto de otro usuario', function () {
        $ajeno = Contact::factory()->for($this->otro)->create();
        Sanctum::actingAs($this->usuario);

        $this->getJson("/api/v1/contacts/{$ajeno->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'No tienes permiso para acceder a este contacto.');
    });

    it('responde 404 en JSON si el contacto no existe', function () {
        Sanctum::actingAs($this->usuario);

        $this->get('/api/v1/contacts/999')
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('message', 'El recurso solicitado no existe.');
    });

    it('responde 404 si el contacto fue eliminado', function () {
        $contacto = Contact::factory()->for($this->usuario)->create();
        $contacto->delete();
        Sanctum::actingAs($this->usuario);

        $this->getJson("/api/v1/contacts/{$contacto->id}")->assertNotFound();
    });
});

describe('actualizar', function () {
    it('actualiza un contacto propio con PUT', function () {
        $contacto = Contact::factory()->for($this->usuario)->create();
        Sanctum::actingAs($this->usuario);

        $this->putJson("/api/v1/contacts/{$contacto->id}", datosContacto())
            ->assertOk()
            ->assertJsonPath('data.name', 'Luis Gómez');

        expect($contacto->fresh())
            ->number_phone->toBe('3104445566')
            ->residence->toBe('Calle 20 # 30-40');
    });

    it('actualiza solo los campos enviados con PATCH', function () {
        $contacto = Contact::factory()->for($this->usuario)->create(['name' => 'Ana']);
        Sanctum::actingAs($this->usuario);

        $this->patchJson("/api/v1/contacts/{$contacto->id}", ['residence' => 'Nueva 123'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Ana')
            ->assertJsonPath('data.residence', 'Nueva 123');
    });

    it('permite conservar su propio teléfono y correo', function () {
        $contacto = Contact::factory()->for($this->usuario)->create(datosContacto());
        Sanctum::actingAs($this->usuario);

        $this->putJson("/api/v1/contacts/{$contacto->id}", datosContacto(['name' => 'Luis G.']))
            ->assertOk();
    });

    it('no permite usar el teléfono o correo de otro contacto propio', function (string $campo) {
        Contact::factory()->for($this->usuario)->create(datosContacto());
        $contacto = Contact::factory()->for($this->usuario)->create();
        Sanctum::actingAs($this->usuario);

        $this->patchJson("/api/v1/contacts/{$contacto->id}", [$campo => datosContacto()[$campo]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$campo]);
    })->with(['number_phone', 'email']);

    it('no deja actualizar el contacto de otro usuario', function () {
        $ajeno = Contact::factory()->for($this->otro)->create(['name' => 'Original']);
        Sanctum::actingAs($this->usuario);

        $this->putJson("/api/v1/contacts/{$ajeno->id}", datosContacto())
            ->assertForbidden();

        expect($ajeno->fresh()->name)->toBe('Original');
    });

    it('responde 403 antes de validar los datos de un contacto ajeno', function () {
        $ajeno = Contact::factory()->for($this->otro)->create();
        Sanctum::actingAs($this->usuario);

        $this->putJson("/api/v1/contacts/{$ajeno->id}", ['name' => ''])
            ->assertForbidden();
    });
});

describe('eliminar', function () {
    it('elimina un contacto propio con borrado lógico', function () {
        $contacto = Contact::factory()->for($this->usuario)->create();
        Sanctum::actingAs($this->usuario);

        $this->deleteJson("/api/v1/contacts/{$contacto->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Contacto eliminado correctamente.');

        $this->assertSoftDeleted($contacto);
    });

    it('no deja eliminar el contacto de otro usuario', function () {
        $ajeno = Contact::factory()->for($this->otro)->create();
        Sanctum::actingAs($this->usuario);

        $this->deleteJson("/api/v1/contacts/{$ajeno->id}")
            ->assertForbidden();

        $this->assertNotSoftDeleted($ajeno);
    });

    it('responde 404 al eliminar un contacto ya eliminado', function () {
        $contacto = Contact::factory()->for($this->usuario)->create();
        $contacto->delete();
        Sanctum::actingAs($this->usuario);

        $this->deleteJson("/api/v1/contacts/{$contacto->id}")->assertNotFound();
    });
});
