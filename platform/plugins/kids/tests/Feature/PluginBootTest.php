<?php

namespace Botble\Kids\Tests\Feature;

require_once __DIR__.'/../KidsTestCase.php';

use Botble\Kids\Tests\KidsTestCase;

class PluginBootTest extends KidsTestCase
{
    public function test_kids_api_prefix_is_registered(): void
    {
        $this->getJson('/api/v1/kids/me')->assertStatus(401);
    }
}
