<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Clock;

use DateTimeImmutable;
use SIIM\Application\Analysis\Contracts\AnalysisClock;

final class SystemAnalysisClock implements AnalysisClock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable;
    }
}
