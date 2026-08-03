<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\Contracts;

use SIIM\Domain\Citizen\Events\CommentIngested;

interface CitizenEventPublisher
{
    public function publish(CommentIngested $event): void;
}
