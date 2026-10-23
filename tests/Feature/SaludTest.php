<?php

namespace Tests\Feature;

use Tests\TestCase;

class SaludTest extends TestCase
{
    public function test_el_endpoint_de_salud_responde_correctamente(): void
    {
        $this->get('/up')->assertOk();
    }
}
