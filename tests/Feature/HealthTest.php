<?php

it('responde el estado del servicio', function () {
    $this->getJson('/api/v1/health')
        ->assertOk()
        ->assertJson([
            'status' => 'ok',
            'app' => 'Contac_API',
        ]);
});

it('responde 404 en JSON si la ruta no existe', function () {
    $this->get('/api/v1/no-existe')
        ->assertNotFound()
        ->assertHeader('Content-Type', 'application/json');
});