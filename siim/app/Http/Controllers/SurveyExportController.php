<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\SurveyFilterFactory;
use Illuminate\Http\Request;
use SIIM\Application\Citizen\Queries\SurveyResultsQuery;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SurveyExportController
{
    public function __invoke(
        Request $request,
        string $slug,
        SurveyResultsQuery $query,
        SurveyFilterFactory $filters,
    ): StreamedResponse {
        $options = $query->filterOptions($slug);
        $definition = $query->exportDefinition($slug);
        abort_if($options === null || $definition === null, 404);
        $filter = $filters->fromRequest($request, $options, withPage: false);

        return response()->streamDownload(function () use ($query, $slug, $filter, $definition): void {
            $stream = fopen('php://output', 'wb');
            if ($stream === false) {
                return;
            }
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, array_map($this->safeCell(...), $definition->headers));
            foreach ($query->exportRows($slug, $filter) as $row) {
                fputcsv($stream, array_map($this->safeCell(...), $row->cells));
            }
            fclose($stream);
        }, "encuesta-{$definition->slug}.csv", [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function safeCell(string $value): string
    {
        return preg_match('/^[\x00-\x20]*[=+\-@]/u', $value) === 1 ? "'{$value}" : $value;
    }
}
