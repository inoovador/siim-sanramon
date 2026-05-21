<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Events\Registered;

it('asigna rol citizen al registrar nuevo usuario', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create();
    event(new Registered($user));

    expect($user->fresh()->hasRole('citizen'))->toBeTrue();
});
