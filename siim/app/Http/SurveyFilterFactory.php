<?php

declare(strict_types=1);

namespace App\Http;

use DateTimeImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use SIIM\Application\Citizen\ReadModels\SurveyFilterOptions;
use SIIM\Application\Citizen\ReadModels\SurveyFilters;

final class SurveyFilterFactory
{
    public function fromRequest(Request $request, SurveyFilterOptions $options, bool $withPage = true): SurveyFilters
    {
        $rules = [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'zone' => ['nullable', 'string', Rule::in($options->zones)],
            'age' => ['nullable', 'string', Rule::in($options->ageRanges)],
            'search' => ['nullable', 'string', 'max:120'],
        ];
        if ($withPage) {
            $rules['page'] = ['nullable', 'integer', 'min:1', 'max:1000000'];
        }

        /** @var array<string, mixed> $validated */
        $validated = Validator::make($request->query(), $rules, [
            'from.date_format' => 'La fecha inicial debe tener el formato AAAA-MM-DD.',
            'to.date_format' => 'La fecha final debe tener el formato AAAA-MM-DD.',
            'to.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
            'zone.in' => 'La zona seleccionada no pertenece a esta encuesta.',
            'age.in' => 'El rango de edad seleccionado no pertenece a esta encuesta.',
        ])->validate();

        return new SurveyFilters(
            from: $this->date($validated['from'] ?? null),
            to: $this->date($validated['to'] ?? null),
            zone: $this->text($validated['zone'] ?? null),
            ageRange: $this->text($validated['age'] ?? null),
            search: $this->text($validated['search'] ?? null),
            page: $withPage ? $this->integer($validated['page'] ?? 1) : 1,
        );
    }

    private function date(mixed $value): ?DateTimeImmutable
    {
        return is_string($value) && $value !== '' ? new DateTimeImmutable($value) : null;
    }

    private function text(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function integer(mixed $value): int
    {
        return is_int($value) || (is_string($value) && ctype_digit($value)) ? (int) $value : 1;
    }
}
