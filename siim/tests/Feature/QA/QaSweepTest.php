<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->create(['email_verified_at' => now()]);
    $this->admin->assignRole('admin');
    $this->analyst = User::factory()->create(['email_verified_at' => now()]);
    $this->analyst->assignRole('analyst');
});

test('admin can hit all panel routes', function () {
    $routes = [
        '/panel', '/panel/comentarios', '/panel/temas', '/panel/fuentes',
        '/panel/chat-rag', '/panel/reportes', '/panel/auditoria',
        '/panel/usuarios', '/panel/configuracion',
    ];

    $this->actingAs($this->admin);

    foreach ($routes as $url) {
        $r = $this->get($url);
        expect($r->status())->toBe(200, "Failed at {$url}: HTTP {$r->status()}");
    }
});

test('analyst cannot hit admin routes', function () {
    $this->actingAs($this->analyst);

    foreach (['/panel/usuarios', '/panel/configuracion'] as $url) {
        $r = $this->get($url);
        expect($r->status())->toBe(403, "Expected 403 at {$url}, got {$r->status()}");
    }
});

test('guest is redirected from panel', function () {
    $this->get('/panel')->assertRedirect('/login');
});

test('dashboard contains expected markers', function () {
    $this->actingAs($this->admin);
    $html = $this->get('/panel')->getContent();
    expect($html)->toContain('Dashboard');
});

test('comentarios module contains expected markers', function () {
    $this->actingAs($this->admin);
    $html = $this->get('/panel/comentarios')->getContent();
    expect($html)->toContain('Comentarios');
});

test('temas module contains vocabulario marker', function () {
    $this->actingAs($this->admin);
    $html = $this->get('/panel/temas')->getContent();
    expect($html)->toContain('Vocabulario');
});

test('fuentes module contains source markers', function () {
    $this->actingAs($this->admin);
    $html = $this->get('/panel/fuentes')->getContent();
    expect($html)->toContain('Meta Graph');
});

test('configuracion module contains llm marker', function () {
    $this->actingAs($this->admin);
    $html = $this->get('/panel/configuracion')->getContent();
    expect($html)->toContain('Proveedor LLM');
});

test('usuarios module contains author names', function () {
    $this->actingAs($this->admin);
    $html = $this->get('/panel/usuarios')->getContent();
    expect($html)->toContain('Kimberly Medina');
});

test('auditoria module contains eventos marker', function () {
    $this->actingAs($this->admin);
    $html = $this->get('/panel/auditoria')->getContent();
    expect($html)->toContain('Eventos hoy');
});

test('chat-rag module contains expected markers', function () {
    $this->actingAs($this->admin);
    $html = $this->get('/panel/chat-rag')->getContent();
    expect($html)->toContain('Pregunta en lenguaje natural');
});

test('reportes module contains executive report marker', function () {
    $this->actingAs($this->admin);
    $html = $this->get('/panel/reportes')->getContent();
    expect($html)->toContain('Reporte ejecutivo');
});
