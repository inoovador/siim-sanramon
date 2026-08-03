<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\Results;

final readonly class SubmitSurveyResponseResult
{
    public function __construct(public string $responseId, public ?string $commentId) {}
}
