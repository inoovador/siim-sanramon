<?php

declare(strict_types=1);

namespace App\Http\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

final class SurveySubmissionRateLimitException extends HttpException
{
    public const MESSAGE = 'Has realizado demasiados intentos. Espera un minuto e inténtalo nuevamente.';

    public function __construct()
    {
        parent::__construct(429, self::MESSAGE, headers: ['Retry-After' => '60']);
    }
}
