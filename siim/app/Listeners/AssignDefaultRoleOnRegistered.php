<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Authenticatable;
use SIIM\Application\Identity\UseCases\AssignDefaultRole;

final class AssignDefaultRoleOnRegistered
{
    public function __construct(private readonly AssignDefaultRole $useCase) {}

    public function handle(Registered $event): void
    {
        /** @var Authenticatable $user */
        $user = $event->user;
        $this->useCase->handle((string) $user->getAuthIdentifier());
    }
}
