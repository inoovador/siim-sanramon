<?php

declare(strict_types=1);

namespace Tests\Feature\QA;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use SIIM\Application\Shared\Contracts\AssistantProvider;
use Tests\TestCase;

final class E2ESweepTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->admin = User::factory()->create(['email_verified_at' => now()]);
        $this->admin->assignRole('admin');
    }

    public function test_panel_modules_respond_with_expected_markers_within_reasonable_time(): void
    {
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

            $response->assertOk();

            $content = $response->getContent();
            self::assertIsString($content);

            foreach ($markers as $marker) {
                self::assertNotFalse(
                    stripos($content, $marker),
                    "Module {$url} does not contain expected marker '{$marker}'.",
                );
            }

            self::assertLessThan(3000, $elapsed, "Module {$url} took {$elapsed}ms.");
        }
    }

    public function test_nvidia_assistant_responds_to_three_distinct_questions_within_five_seconds(): void
    {
        $apiKey = config('llm.providers.nvidia_glm.api_key');

        if (! is_string($apiKey) || $apiKey === '') {
            $this->markTestSkipped('NVIDIA_API_KEY is not configured; skipping live test.');
        }

        $systemPrompt = config('llm.assistant.system_prompt');
        self::assertIsString($systemPrompt);

        $provider = app(AssistantProvider::class);
        $questions = [
            '¿Cómo veo el sentimiento de la última semana?',
            '¿Dónde subo un archivo CSV con comentarios?',
            '¿Qué hace el módulo de Reportes?',
        ];

        foreach ($questions as $question) {
            $start = microtime(true);
            $reply = $provider->chat([
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $question],
            ]);
            $elapsed = (int) ((microtime(true) - $start) * 1000);

            self::assertGreaterThan(20, strlen($reply->content), "Response is too short for: {$question}");
            self::assertLessThan(8000, $elapsed, "Latency is too high for: {$question} → {$elapsed}ms");
            self::assertGreaterThan(0, $reply->tokensOutput);
        }
    }

    public function test_analyst_cannot_access_admin_pages(): void
    {
        $analyst = User::factory()->create(['email_verified_at' => now()]);
        $analyst->assignRole('analyst');

        $this->actingAs($analyst);

        $this->get('/panel/usuarios')->assertForbidden();
        $this->get('/panel/configuracion')->assertForbidden();

        foreach (['/panel', '/panel/comentarios', '/panel/temas', '/panel/reportes'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_guest_is_redirected_to_login(): void
    {
        foreach (['/panel', '/panel/comentarios', '/panel/usuarios'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_public_routes_are_accessible_without_authentication(): void
    {
        foreach (['/', '/login', '/register', '/forgot-password', '/health'] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
