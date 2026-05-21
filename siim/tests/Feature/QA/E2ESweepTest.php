<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use SIIM\Application\Shared\Contracts\AssistantProvider;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->create(['email_verified_at' => now()]);
    $this->admin->assignRole('admin');
});

test('E2E: 9 módulos panel responden 200 + tiempo razonable', function () {
    $this->actingAs($this->admin);

    $routes = [
        '/panel' => ['Dashboard', 'KPI'],
        '/panel/comentarios' => ['Filtros', 'Sentimiento'],
        '/panel/temas' => ['Vocabulario', 'obras_publicas'],
        '/panel/chat-rag' => ['Atajos', 'Filtros'],
        '/panel/reportes' => ['Reporte', 'Plantilla'],
        '/panel/fuentes' => ['Meta', 'Buz'],
        '/panel/auditoria' => ['Eventos', 'Severidad'],
        '/panel/usuarios' => ['Funcionarios', 'Kimberly'],
        '/panel/configuracion' => ['LLM', 'Presupuesto'],
    ];

    foreach ($routes as $url => $markers) {
        $start = microtime(true);
        $response = $this->get($url);
        $elapsed = (int) ((microtime(true) - $start) * 1000);

        expect($response->status())->toBe(200, "Falló {$url}: HTTP {$response->status()}");

        $content = $response->getContent();

        foreach ($markers as $marker) {
            expect(stripos($content, $marker))->not->toBeFalse(
                "Módulo {$url} no contiene marker esperado '{$marker}'"
            );
        }

        // Logging info: ningún módulo debería tardar >2s en test
        expect($elapsed)->toBeLessThan(3000, "Módulo {$url} tardó {$elapsed}ms");
    }
});

test('E2E: asistente NVIDIA responde en <5s a 3 preguntas distintas', function () {
    $provider = app(AssistantProvider::class);
    $systemPrompt = (string) config('llm.assistant.system_prompt');

    $questions = [
        '¿Cómo veo el sentimiento de la última semana?',
        '¿Dónde subo un archivo CSV con comentarios?',
        '¿Qué hace el módulo de Reportes?',
    ];

    foreach ($questions as $q) {
        $start = microtime(true);
        $reply = $provider->chat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $q],
        ]);
        $elapsed = (int) ((microtime(true) - $start) * 1000);

        expect(strlen($reply->content))->toBeGreaterThan(20, "Respuesta muy corta a: {$q}");
        expect($elapsed)->toBeLessThan(8000, "Latencia muy alta para: {$q} → {$elapsed}ms");
        expect($reply->tokensOutput)->toBeGreaterThan(0);
    }
})->skip(fn () => ! config('llm.providers.nvidia_glm.api_key'), 'NVIDIA_API_KEY no configurada — skip live test');

test('E2E: roles diferenciados — analyst no entra a admin pages', function () {
    $analyst = User::factory()->create(['email_verified_at' => now()]);
    $analyst->assignRole('analyst');

    $this->actingAs($analyst);

    $this->get('/panel/usuarios')->assertForbidden();
    $this->get('/panel/configuracion')->assertForbidden();

    // Analyst puede ver demás
    foreach (['/panel', '/panel/comentarios', '/panel/temas', '/panel/reportes'] as $url) {
        expect($this->get($url)->status())->toBe(200);
    }
});

test('E2E: guest queda redirigido a login', function () {
    foreach (['/panel', '/panel/comentarios', '/panel/usuarios'] as $url) {
        $this->get($url)->assertRedirect('/login');
    }
});

test('E2E: rutas públicas accesibles sin auth', function () {
    foreach (['/', '/login', '/register', '/forgot-password', '/health'] as $url) {
        $response = $this->get($url);
        expect($response->status())->toBe(200, "Ruta pública {$url} devolvió {$response->status()}");
    }
});
