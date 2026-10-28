<?php

it('responde el estado del servicio', function () {
    $this->getJson('/api/v1/health')
        ->assertOk()
        ->assertJson([
            'status' => 'ok',
            'app' => 'Contact API',
        ]);
});

it('responde 404 en JSON si la ruta no existe', function () {
    $this->get('/api/v1/no-existe')
        ->assertNotFound()
        ->assertHeader('Content-Type', 'application/json')
        ->assertJsonPath('message', 'El recurso solicitado no existe.');
});

it('responde 405 en JSON si el método no está permitido', function () {
    $this->post('/api/v1/health')
        ->assertMethodNotAllowed()
        ->assertHeader('Content-Type', 'application/json');
});
