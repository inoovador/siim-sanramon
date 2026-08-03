<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\Data;

use SIIM\Domain\Citizen\SurveyResponse;

final readonly class SurveySubmission
{
    public function __construct(public SurveyResponse $response, public ?string $contactQuestionId, public ?CommentDraft $comment) {}
}
