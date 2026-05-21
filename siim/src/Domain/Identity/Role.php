<?php

declare(strict_types=1);

namespace SIIM\Domain\Identity;

enum Role: string
{
    case Admin = 'admin';
    case Analyst = 'analyst';
    case Citizen = 'citizen';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Analyst => 'Analista',
            self::Citizen => 'Ciudadano',
        };
    }
}
