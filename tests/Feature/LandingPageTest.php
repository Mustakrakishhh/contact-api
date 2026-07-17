<?php

test('the landing page presents the project and contact form', function () {
    $this->withoutVite();

    $response = $this->get('/');

    $response
        ->assertOk()
        ->assertSee('Laravel backend')
        ->assertSee('Давайте обсудим')
        ->assertSee('contact-form');
});

test('the documentation page presents endpoints and openapi specification', function () {
    $this->withoutVite();

    $response = $this->get('/docs');

    $response
        ->assertOk()
        ->assertSee('Документация API')
        ->assertSee('/api/contact')
        ->assertSee('/api/health')
        ->assertSee('openapi.yaml');
});
