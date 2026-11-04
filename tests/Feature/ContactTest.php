<?php

use App\Models\Contact;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function contactData(array $cambios = []): array
{
    return array_merge([
        'name' => 'Luis Gómez',
        'number_phone' => '3104445566',
        'email' => 'luis@correo.com',
        'residence' => 'Calle 20 # 30-40',
    ], $cambios);
}

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otro = User::factory()->create();
});

describe('sin iniciar sesión', function () {
    it('devuelve al login en cada endpoint', function (string $method, string $uri) {
        $contact = Contact::factory()->for($this->user)->create();

        $this->json($method, str_replace('{id}', $contact->id, $uri))
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
        Contact::factory()->for($this->user)->create(['name' => 'Zoe']);
        Contact::factory()->for($this->user)->create(['name' => 'Ana']);
        Contact::factory(3)->for($this->otro)->create();
        Sanctum::actingAs($this->user);

        $this->getJson('/api/v1/contacts')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Ana')
            ->assertJsonPath('data.1.name', 'Zoe')
            ->assertJsonStructure(['data', 'links', 'meta']);
    });

    it('no lista los contactos eliminados', function () {
        Contact::factory(2)->for($this->user)->create();
        Contact::factory()->for($this->user)->create()->delete();
        Sanctum::actingAs($this->user);

        $this->getJson('/api/v1/contacts')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    });

    it('pagina de 15 en 15', function () {
        Contact::factory(20)->for($this->user)->create();
        Sanctum::actingAs($this->user);

        $this->getJson('/api/v1/contacts?page=2')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.total', 20);
    });
});

describe('agregar', function () {
    it('agrega un contacto a la agenda del usuario', function () {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/contacts', contactData())
            ->assertCreated()
            ->assertJsonPath('data.name', 'Luis Gómez')
            ->assertJsonPath('data.email', 'luis@correo.com');

        expect($this->user->contacts()->count())->toBe(1);
    });

    it('ignora un user_id enviado en la petición', function () {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/contacts', contactData(['user_id' => $this->otro->id]))
            ->assertCreated();

        expect($this->user->contacts()->count())->toBe(1)
            ->and($this->otro->contacts()->count())->toBe(0);
    });

    it('valida el nombre y los formatos', function (string $campo, mixed $valor) {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/contacts', contactData([$campo => $valor]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$campo]);
    })->with([
        'nombre vacío' => ['name', ''],
        'correo inválido' => ['email', 'luis'],
        'teléfono con letras' => ['number_phone', '310-abc'],
    ]);

    it('pide un teléfono o un correo', function () {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/contacts', ['name' => 'Sin datos'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.number_phone.0', 'Debes indicar un teléfono o un correo.');
    });

    it('no permite repetir el teléfono de otro contacto propio', function () {
        Contact::factory()->for($this->user)->create(['number_phone' => '3104445566']);
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/contacts', contactData())
            ->assertUnprocessable()
            ->assertJsonPath('errors.number_phone.0', 'Ya tienes un contacto con este teléfono.');
    });

    it('no permite repetir el correo de otro contacto propio', function () {
        Contact::factory()->for($this->user)->create(['email' => 'luis@correo.com']);
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/contacts', contactData())
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'Ya tienes un contacto con este correo.');
    });

    it('permite repetir el nombre y la dirección', function () {
        Contact::factory()->for($this->user)->create([
            'name' => 'Luis Gómez',
            'residence' => 'Calle 20 # 30-40',
        ]);
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/contacts', contactData())->assertCreated();
    });

    it('permite el mismo teléfono y correo en la agenda de otro usuario', function () {
        Contact::factory()->for($this->otro)->create([
            'number_phone' => '3104445566',
            'email' => 'luis@correo.com',
        ]);
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/contacts', contactData())->assertCreated();
    });

    it('permite reutilizar el teléfono y correo de un contacto eliminado', function () {
        Contact::factory()->for($this->user)->create([
            'number_phone' => '3104445566',
            'email' => 'luis@correo.com',
        ])->delete();
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/contacts', contactData())->assertCreated();
    });
});

describe('perfil del contacto', function () {
    it('muestra la información de un contacto propio', function () {
        $contact = Contact::factory()->for($this->user)->create(contactData());
        Sanctum::actingAs($this->user);

        $this->getJson("/api/v1/contacts/{$contact->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $contact->id)
            ->assertJsonPath('data.name', 'Luis Gómez')
            ->assertJsonPath('data.number_phone', '3104445566')
            ->assertJsonPath('data.email', 'luis@correo.com')
            ->assertJsonPath('data.residence', 'Calle 20 # 30-40');
    });

    it('no deja ver el contacto de otro usuario', function () {
        $ajeno = Contact::factory()->for($this->otro)->create();
        Sanctum::actingAs($this->user);

        $this->getJson("/api/v1/contacts/{$ajeno->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'No tienes permiso para acceder a este contacto.');
    });

    it('responde 404 en JSON si el contacto no existe', function () {
        Sanctum::actingAs($this->user);

        $this->get('/api/v1/contacts/999')
            ->assertNotFound()
            ->assertHeader('Content-Type', 'application/json')
            ->assertJsonPath('message', 'El recurso solicitado no existe.');
    });

    it('responde 404 si el contacto fue eliminado', function () {
        $contact = Contact::factory()->for($this->user)->create();
        $contact->delete();
        Sanctum::actingAs($this->user);

        $this->getJson("/api/v1/contacts/{$contact->id}")->assertNotFound();
    });
});

describe('actualizar', function () {
    it('actualiza un contacto propio con PUT', function () {
        $contact = Contact::factory()->for($this->user)->create();
        Sanctum::actingAs($this->user);

        $this->putJson("/api/v1/contacts/{$contact->id}", contactData())
            ->assertOk()
            ->assertJsonPath('data.name', 'Luis Gómez');

        expect($contact->fresh())
            ->number_phone->toBe('3104445566')
            ->residence->toBe('Calle 20 # 30-40');
    });

    it('actualiza solo los campos enviados con PATCH', function () {
        $contact = Contact::factory()->for($this->user)->create(['name' => 'Ana']);
        Sanctum::actingAs($this->user);

        $this->patchJson("/api/v1/contacts/{$contact->id}", ['residence' => 'Nueva 123'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Ana')
            ->assertJsonPath('data.residence', 'Nueva 123');
    });

    it('permite conservar su propio teléfono y correo', function () {
        $contact = Contact::factory()->for($this->user)->create(contactData());
        Sanctum::actingAs($this->user);

        $this->putJson("/api/v1/contacts/{$contact->id}", contactData(['name' => 'Luis G.']))
            ->assertOk();
    });

    it('no permite usar el teléfono o correo de otro contacto propio', function (string $campo) {
        Contact::factory()->for($this->user)->create(contactData());
        $contact = Contact::factory()->for($this->user)->create();
        Sanctum::actingAs($this->user);

        $this->patchJson("/api/v1/contacts/{$contact->id}", [$campo => contactData()[$campo]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$campo]);
    })->with(['number_phone', 'email']);

    it('no deja actualizar el contacto de otro usuario', function () {
        $ajeno = Contact::factory()->for($this->otro)->create(['name' => 'Original']);
        Sanctum::actingAs($this->user);

        $this->putJson("/api/v1/contacts/{$ajeno->id}", contactData())
            ->assertForbidden();

        expect($ajeno->fresh()->name)->toBe('Original');
    });

    it('responde 403 antes de validar los datos de un contacto ajeno', function () {
        $ajeno = Contact::factory()->for($this->otro)->create();
        Sanctum::actingAs($this->user);

        $this->putJson("/api/v1/contacts/{$ajeno->id}", ['name' => ''])
            ->assertForbidden();
    });
});

describe('eliminar', function () {
    it('elimina un contacto propio con borrado lógico', function () {
        $contact = Contact::factory()->for($this->user)->create();
        Sanctum::actingAs($this->user);

        $this->deleteJson("/api/v1/contacts/{$contact->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Contacto eliminado correctamente.');

        $this->assertSoftDeleted($contact);
    });

    it('no deja eliminar el contacto de otro usuario', function () {
        $ajeno = Contact::factory()->for($this->otro)->create();
        Sanctum::actingAs($this->user);

        $this->deleteJson("/api/v1/contacts/{$ajeno->id}")
            ->assertForbidden();

        $this->assertNotSoftDeleted($ajeno);
    });

    it('responde 404 al eliminar un contacto ya eliminado', function () {
        $contact = Contact::factory()->for($this->user)->create();
        $contact->delete();
        Sanctum::actingAs($this->user);

        $this->deleteJson("/api/v1/contacts/{$contact->id}")->assertNotFound();
    });
});
