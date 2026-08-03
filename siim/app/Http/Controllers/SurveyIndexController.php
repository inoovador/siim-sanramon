<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use SIIM\Application\Citizen\Queries\SurveyResultsQuery;

final class SurveyIndexController
{
    public function __invoke(SurveyResultsQuery $query): View
    {
        return view('panel.surveys.index', ['surveys' => $query->surveys()]);
    }
}
