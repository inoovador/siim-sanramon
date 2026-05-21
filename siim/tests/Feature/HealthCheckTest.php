<?php

declare(strict_types=1);

use function Pest\Laravel\getJson;

it('responde /health con status ok y nombre SIIM', function () {
    $response = getJson('/health');

    $response->assertOk()
        ->assertJsonStructure(['status', 'app', 'version', 'timestamp'])
        ->assertJson([
            'status'  => 'ok',
            'app'     => 'SIIM',
            'version' => '0.1.0-F0',
        ]);
});
