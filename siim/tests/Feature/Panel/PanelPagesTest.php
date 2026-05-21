<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('analyst can access general pages', function () {
    $user = User::factory()->create();
    $user->assignRole('analyst');
    $user->markEmailAsVerified();

    $this->actingAs($user);

    foreach (['/panel', '/panel/comentarios', '/panel/temas', '/panel/fuentes', '/panel/chat-rag', '/panel/reportes', '/panel/auditoria'] as $url) {
        $this->get($url)->assertOk();
    }
});

test('analyst cannot access admin pages', function () {
    $user = User::factory()->create();
    $user->assignRole('analyst');
    $user->markEmailAsVerified();

    $this->actingAs($user);

    $this->get('/panel/usuarios')->assertForbidden();
    $this->get('/panel/configuracion')->assertForbidden();
});

test('admin can access admin pages', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');
    $user->markEmailAsVerified();

    $this->actingAs($user);

    $this->get('/panel/usuarios')->assertOk();
    $this->get('/panel/configuracion')->assertOk();
});
