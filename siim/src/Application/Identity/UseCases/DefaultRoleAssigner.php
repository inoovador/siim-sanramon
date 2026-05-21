<?php

declare(strict_types=1);

namespace SIIM\Application\Identity\UseCases;

use SIIM\Domain\Identity\Role;

interface DefaultRoleAssigner
{
    public function assign(string $userId, Role $role): void;
}
