<?php

declare(strict_types=1);

namespace SIIM\Application\Analysis\Data;

final readonly class AnalyzableComment
{
    public function __construct(public string $id, public string $text) {}
}
