<?php

declare(strict_types=1);

namespace SIIM\Domain\Identity;

use InvalidArgumentException;

final class UserId
{
    private function __construct(public readonly string $value) {}

    public static function fromString(string $value): self
    {
        if (! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value)
            && ! ctype_digit($value)) {
            throw new InvalidArgumentException("Invalid UserId format: {$value}");
        }

        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
