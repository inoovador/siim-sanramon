<?php

declare(strict_types=1);

namespace SIIM\Application\Analysis\Exceptions;

use RuntimeException;
use SIIM\Application\Analysis\Data\AnalysisErrorCategory;

class SentimentAnalysisException extends RuntimeException
{
    public function __construct(public readonly AnalysisErrorCategory $category, string $message)
    {
        parent::__construct($message);
    }
}
