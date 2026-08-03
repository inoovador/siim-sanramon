<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Security;

final readonly class SurveySubmissionRateLimitKey
{
    public function __construct(private string $appKey) {}

    public function forIp(string $ipAddress): string
    {
        return 'survey-submit:' . hash_hmac('sha256', $ipAddress, $this->appKey);
    }
}
