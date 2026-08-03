<?php

declare(strict_types=1);

namespace SIIM\Application\Analysis\Contracts;

use DateTimeImmutable;

interface AnalysisClock
{
    public function now(): DateTimeImmutable;
}
