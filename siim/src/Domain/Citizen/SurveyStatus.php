<?php

declare(strict_types=1);

namespace SIIM\Domain\Citizen;

enum SurveyStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Closed = 'closed';
}
