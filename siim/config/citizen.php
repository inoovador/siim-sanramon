<?php

declare(strict_types=1);

return [
    'public_survey_slug' => env('SURVEY_PUBLIC_SLUG', ''),

    'submission' => [
        'max_attempts' => (int) env('SURVEY_MAX_ATTEMPTS', 60),
        'decay_seconds' => (int) env('SURVEY_RATE_DECAY_SECONDS', 60),
        'min_completion_seconds' => (float) env('SURVEY_MIN_COMPLETION_SECONDS', 2),
        'route_throttle' => (string) env('SURVEY_ROUTE_THROTTLE', '120,1'),
    ],
];
