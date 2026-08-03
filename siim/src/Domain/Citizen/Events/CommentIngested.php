<?php

declare(strict_types=1);

namespace SIIM\Domain\Citizen\Events;

final readonly class CommentIngested
{
    public function __construct(public string $commentId) {}
}
