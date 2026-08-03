<?php

declare(strict_types=1);

namespace SIIM\Domain\Citizen;

final readonly class Answer
{
    /**
     * @param  int|string|list<string>|mixed  $value
     */
    public function __construct(
        public string $questionId,
        public mixed $value,
    ) {}
}
