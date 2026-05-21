<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Identity;

use App\Models\User;
use SIIM\Application\Identity\UseCases\DefaultRoleAssigner;
use SIIM\Domain\Identity\Role;

final class SpatieDefaultRoleAssigner implements DefaultRoleAssigner
{
    public function assign(string $userId, Role $role): void
    {
        $user = User::query()->findOrFail($userId);
        $user->assignRole($role->value);
    }
}
