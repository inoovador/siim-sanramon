<?php

declare(strict_types=1);

namespace SIIM\Application\Analysis\Data;

final readonly class TokenUsage
{
    public function __construct(public int $inputTokens = 0, public int $outputTokens = 0) {}
}
