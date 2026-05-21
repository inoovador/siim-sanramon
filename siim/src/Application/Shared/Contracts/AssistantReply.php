<?php

declare(strict_types=1);

namespace SIIM\Application\Shared\Contracts;

final class AssistantReply
{
    public function __construct(
        public readonly string $content,
        public readonly int $tokensInput,
        public readonly int $tokensOutput,
        public readonly string $model,
    ) {
    }
}
