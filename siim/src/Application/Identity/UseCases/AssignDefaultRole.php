<?php

declare(strict_types=1);

namespace SIIM\Application\Identity\UseCases;

use SIIM\Domain\Identity\Role;

final class AssignDefaultRole
{
    public function __construct(private readonly DefaultRoleAssigner $assigner) {}

    public function handle(string $userId): void
    {
        $this->assigner->assign($userId, Role::Citizen);
    }
}
