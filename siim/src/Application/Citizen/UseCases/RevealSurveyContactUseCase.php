<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\UseCases;

use DateTimeImmutable;
use SIIM\Application\Citizen\Contracts\ContactEncryptor;
use SIIM\Application\Citizen\Contracts\SurveyContactAccessRepository;
use SIIM\Application\Citizen\Queries\SurveyResultsQuery;

final readonly class RevealSurveyContactUseCase
{
    public function __construct(
        private SurveyResultsQuery $query,
        private SurveyContactAccessRepository $accesses,
        private ContactEncryptor $encryptor,
    ) {}

    public function handle(
        string $surveySlug,
        string $responseId,
        int $userId,
        DateTimeImmutable $accessedAt,
        ?string $requestHash,
    ): ?string {
        $contact = $this->query->encryptedContact($surveySlug, $responseId);
        if ($contact === null) {
            return null;
        }

        $this->accesses->record(
            $contact->surveyId,
            $contact->responseId,
            $userId,
            $accessedAt,
            $requestHash,
        );

        return $this->encryptor->decrypt($contact->ciphertext);
    }
}
