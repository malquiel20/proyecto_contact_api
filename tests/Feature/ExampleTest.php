<?php

it('responde correctamente en la página de inicio', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
