<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\Contracts;

use Closure;

interface CitizenTransaction
{
    /** @param Closure(): mixed $callback */
    public function run(Closure $callback): mixed;
}
