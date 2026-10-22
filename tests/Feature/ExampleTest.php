<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_el_invitado_es_redirigido_al_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
