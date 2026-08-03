<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use DateTimeImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use SIIM\Application\Citizen\Commands\CreateSurveyCommand;
use SIIM\Application\Citizen\Commands\CreateSurveyQuestion;
use SIIM\Application\Citizen\UseCases\CreateSurveyUseCase;

final class SurveyBuilderController
{
    public function create(CreateSurveyUseCase $create): View
    {
        return view('panel.surveys.create', ['questionTypes' => $create->supportedTypes()]);
    }

    public function store(Request $request, CreateSurveyUseCase $create): RedirectResponse
    {
        $types = array_map(static fn ($type): string => $type->value, $create->supportedTypes());
        /** @var array<string, mixed> $validated */
        $validated = Validator::make($request->all(), [
            'title' => ['required', 'string', 'min:3', 'max:200'],
            'slug' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'description' => ['nullable', 'string', 'max:2000'],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date', 'after:opens_at'],
            'questions' => ['required', 'array', 'min:1', 'max:50'],
            'questions.*.label' => ['required', 'string', 'max:300'],
            'questions.*.type' => ['required', 'string', Rule::in($types)],
            'questions.*.is_required' => ['nullable', 'boolean'],
            'questions.*.options' => ['nullable'],
            'questions.*.max_selections' => ['nullable', 'integer', 'min:1', 'max:100'],
            'questions.*.max_length' => ['nullable', 'integer', 'min:1', 'max:5000'],
            'questions.*.help_text' => ['nullable', 'string', 'max:300'],
        ], [
            'questions.required' => 'Agrega al menos una pregunta.',
            'questions.*.label.required' => 'Cada pregunta necesita un texto.',
            'questions.*.type.in' => 'Selecciona un tipo de pregunta válido.',
        ])->validate();

        $user = $request->user();
        abort_if($user === null, 403);
        $userId = $user->getAuthIdentifier();
        abort_unless(is_int($userId), 403);

        try {
            $survey = $create->handle(new CreateSurveyCommand(
                title: $this->requiredString($validated['title']),
                slug: $this->requiredString($validated['slug']),
                description: $this->nullableString($validated['description'] ?? null),
                opensAt: $this->date($validated['opens_at'] ?? null),
                closesAt: $this->date($validated['closes_at'] ?? null),
                createdBy: $userId,
                questions: $this->questions($validated['questions']),
            ));
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['survey' => 'No se pudo crear el borrador. Revisa las preguntas y fechas.']);
        }

        return redirect()->route('panel.surveys.results', ['slug' => $survey->slug])
            ->with('status', 'Borrador creado correctamente.');
    }

    /**
     * @return list<CreateSurveyQuestion>
     */
    private function questions(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $questions = [];
        foreach ($raw as $item) {
            if (! is_array($item)) {
                continue;
            }
            $options = $item['options'] ?? [];
            if (is_string($options)) {
                $options = preg_split('/\R/u', $options) ?: [];
            }
            $questions[] = new CreateSurveyQuestion(
                label: (string) ($item['label'] ?? ''),
                type: (string) ($item['type'] ?? ''),
                isRequired: filter_var($item['is_required'] ?? false, FILTER_VALIDATE_BOOL),
                options: is_array($options) ? array_values(array_map('strval', $options)) : [],
                maxSelections: $this->nullableInt($item['max_selections'] ?? null),
                maxLength: $this->nullableInt($item['max_length'] ?? null),
                helpText: $this->nullableString($item['help_text'] ?? null),
            );
        }

        return $questions;
    }

    private function nullableString(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_int($value) || (is_string($value) && ctype_digit($value)) ? (int) $value : null;
    }

    private function date(mixed $value): ?DateTimeImmutable
    {
        return is_string($value) && $value !== '' ? new DateTimeImmutable($value) : null;
    }

    private function requiredString(mixed $value): string
    {
        if (! is_string($value)) {
            throw ValidationException::withMessages(['survey' => 'El formulario contiene datos inválidos.']);
        }

        return $value;
    }
}
