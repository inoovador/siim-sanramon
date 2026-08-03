<?php

declare(strict_types=1);

namespace SIIM\Application\Analysis\Data;

enum AnalysisErrorCategory: string
{
    case NotConfigured = 'not_configured';
    case BudgetExhausted = 'budget_exhausted';
    case RateLimited = 'rate_limited';
    case ProviderUnavailable = 'provider_unavailable';
    case ConnectionFailure = 'connection_failure';
    case RequestRejected = 'request_rejected';
    case InvalidResponse = 'invalid_response';
    case RetriesExhausted = 'retries_exhausted';
}
