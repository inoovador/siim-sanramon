<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\Data;

use DateTimeImmutable;

final readonly class CommentDraft
{
    public function __construct(public string $id, public string $text, public string $source, public DateTimeImmutable $capturedAt) {}
}
