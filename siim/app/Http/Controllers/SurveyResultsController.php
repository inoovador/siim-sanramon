<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\SurveyFilterFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use SIIM\Application\Citizen\Queries\SurveyResultsQuery;

final class SurveyResultsController
{
    public function __invoke(
        Request $request,
        string $slug,
        SurveyResultsQuery $query,
        SurveyFilterFactory $filters,
    ): View {
        $options = $query->filterOptions($slug);
        abort_if($options === null, 404);
        $filter = $filters->fromRequest($request, $options);
        $results = $query->results($slug, $filter);
        abort_if($results === null, 404);

        return view('panel.surveys.results', [
            'options' => $options,
            'filters' => $filter,
            'results' => $results,
        ]);
    }
}
