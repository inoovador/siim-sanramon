<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use SIIM\Application\Identity\UseCases\AssignDefaultRole;

final class AssignDefaultRoleOnRegistered
{
    public function __construct(private readonly AssignDefaultRole $useCase) {}

    public function handle(Registered $event): void
    {
        $this->useCase->handle((string) $event->user->getKey());
    }
}
