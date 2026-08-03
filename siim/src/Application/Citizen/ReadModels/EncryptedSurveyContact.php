<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\ReadModels;

final readonly class EncryptedSurveyContact
{
    public function __construct(
        public string $surveyId,
        public string $responseId,
        public string $ciphertext,
    ) {}
}
