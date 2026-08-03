<?php

declare(strict_types=1);

namespace SIIM\Domain\Citizen;

enum QuestionType: string
{
    case Scale1To5 = 'scale_1_5';
    case SingleChoice = 'single_choice';
    case MultiChoice = 'multi_choice';
    case OpenText = 'open_text';
    case Nps = 'nps';
}
