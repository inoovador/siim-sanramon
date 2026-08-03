<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SIIM\Application\Citizen\UseCases\RevealSurveyContactUseCase;
use Throwable;

final class RevealSurveyContactController
{
    public function __invoke(
        Request $request,
        string $slug,
        string $response,
        RevealSurveyContactUseCase $reveal,
    ): JsonResponse {
        $user = $request->user();
        abort_unless($user !== null && $user->hasRole('admin'), 403);
        $userId = $user->getAuthIdentifier();
        abort_unless(is_int($userId), 403);

        try {
            $contact = $reveal->handle(
                surveySlug: $slug,
                responseId: $response,
                userId: $userId,
                accessedAt: now()->toDateTimeImmutable(),
                requestHash: $this->requestHash($request),
            );
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(
                ['message' => 'No pudimos revelar el contacto. Inténtalo nuevamente.'],
                503,
                ['Cache-Control' => 'private, no-store, max-age=0'],
            );
        }
        abort_if($contact === null, 404);

        return response()->json(['contact' => $contact])->withHeaders([
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    private function requestHash(Request $request): ?string
    {
        $configuredKey = config('app.key');
        $key = is_string($configuredKey) ? $configuredKey : '';
        if ($key === '') {
            return null;
        }

        return hash_hmac('sha256', ($request->ip() ?? 'unknown') . '|' . ($request->userAgent() ?? ''), $key);
    }
}
