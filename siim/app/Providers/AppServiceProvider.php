<?php

declare(strict_types=1);

namespace App\Providers;

use App\Listeners\DispatchAnalysisOnCommentIngested;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SIIM\Application\Analysis\Contracts\AnalysisClock;
use SIIM\Application\Analysis\Contracts\AnalysisRepository;
use SIIM\Application\Analysis\Contracts\FallbackSentimentAnalyzer;
use SIIM\Application\Analysis\Contracts\PrimarySentimentAnalyzer;
use SIIM\Application\Analysis\UseCases\AnalyzeCommentUseCase;
use SIIM\Application\Citizen\Contracts\CitizenEventPublisher;
use SIIM\Application\Citizen\Contracts\CitizenSubmissionRepository;
use SIIM\Application\Citizen\Contracts\CitizenTransaction;
use SIIM\Application\Citizen\Contracts\ContactEncryptor;
use SIIM\Application\Citizen\Contracts\SurveyAttemptRepository;
use SIIM\Application\Citizen\Contracts\SurveyContactAccessRepository;
use SIIM\Application\Citizen\Contracts\SurveyRepository;
use SIIM\Application\Citizen\Queries\SurveyResultsQuery;
use SIIM\Application\Citizen\UseCases\SubmitSurveyResponseUseCase;
use SIIM\Application\Identity\UseCases\DefaultRoleAssigner;
use SIIM\Application\Shared\Contracts\AssistantProvider;
use SIIM\Domain\Citizen\Events\CommentIngested;
use SIIM\Infrastructure\Clock\SystemAnalysisClock;
use SIIM\Infrastructure\Events\LaravelCitizenEventPublisher;
use SIIM\Infrastructure\Identity\SpatieDefaultRoleAssigner;
use SIIM\Infrastructure\Llm\LexiconSentimentAnalyzer;
use SIIM\Infrastructure\Llm\NvidiaGlmAssistant;
use SIIM\Infrastructure\Llm\NvidiaSentimentAnalyzer;
use SIIM\Infrastructure\Persistence\Analysis\EloquentAnalysisRepository;
use SIIM\Infrastructure\Persistence\Citizen\EloquentCitizenSubmissionRepository;
use SIIM\Infrastructure\Persistence\Citizen\EloquentCitizenTransaction;
use SIIM\Infrastructure\Persistence\Citizen\EloquentSurveyAttemptRepository;
use SIIM\Infrastructure\Persistence\Citizen\EloquentSurveyContactAccessRepository;
use SIIM\Infrastructure\Persistence\Citizen\EloquentSurveyRepository;
use SIIM\Infrastructure\Persistence\Citizen\EloquentSurveyResultsQuery;
use SIIM\Infrastructure\Security\GcmContactEncryptor;
use SIIM\Infrastructure\Security\SurveySubmissionRateLimitKey;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SurveyRepository::class, EloquentSurveyRepository::class);
        $this->app->bind(SurveyAttemptRepository::class, EloquentSurveyAttemptRepository::class);
        $this->app->bind(SurveyContactAccessRepository::class, EloquentSurveyContactAccessRepository::class);
        $this->app->bind(SurveyResultsQuery::class, EloquentSurveyResultsQuery::class);
        $this->app->bind(CitizenSubmissionRepository::class, EloquentCitizenSubmissionRepository::class);
        $this->app->bind(CitizenTransaction::class, EloquentCitizenTransaction::class);
        $this->app->bind(CitizenEventPublisher::class, LaravelCitizenEventPublisher::class);
        $this->app->bind(AnalysisRepository::class, EloquentAnalysisRepository::class);
        $this->app->bind(AnalysisClock::class, SystemAnalysisClock::class);
        $this->app->bind(PrimarySentimentAnalyzer::class, fn ($app): NvidiaSentimentAnalyzer => $this->nvidiaSentiment($app));
        $this->app->bind(FallbackSentimentAnalyzer::class, LexiconSentimentAnalyzer::class);
        $this->app->singleton(ContactEncryptor::class, fn ($app): GcmContactEncryptor => new GcmContactEncryptor((string) $app['config']->get('app.key')));
        $this->app->singleton(SurveySubmissionRateLimitKey::class, fn ($app): SurveySubmissionRateLimitKey => new SurveySubmissionRateLimitKey((string) $app['config']->get('app.key')));
        $this->app->when(SubmitSurveyResponseUseCase::class)
            ->needs('$appKey')->giveConfig('app.key');
        $this->app->when(AnalyzeCommentUseCase::class)
            ->needs('$primaryConfigured')->give(fn ($app): bool => (string) $app['config']->get('llm.providers.nvidia_glm.api_key') !== '');
        $this->app->when(AnalyzeCommentUseCase::class)
            ->needs('$dailyBudget')->giveConfig('llm.budgets.daily_usd');

        $this->app->bind(
            DefaultRoleAssigner::class,
            SpatieDefaultRoleAssigner::class,
        );

        $this->app->singleton(AssistantProvider::class, function ($app) {
            $cfg = $app['config']->get('llm.providers.nvidia_glm');

            return new NvidiaGlmAssistant(
                apiKey: (string) ($cfg['api_key'] ?? ''),
                baseUrl: (string) ($cfg['base_url'] ?? 'https://integrate.api.nvidia.com/v1'),
                model: (string) ($cfg['model'] ?? 'z-ai/glm-5.2'),
                timeout: (int) ($cfg['timeout'] ?? 30),
                maxTokens: (int) ($cfg['max_tokens'] ?? 4096),
                verifySsl: (bool) ($cfg['verify_ssl'] ?? true),
                environment: $app->environment(),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(CommentIngested::class, DispatchAnalysisOnCommentIngested::class);
    }

    private function nvidiaSentiment(Application $app): NvidiaSentimentAnalyzer
    {
        $cfg = $app->make(ConfigRepository::class)->get('llm.providers.nvidia_glm');
        $cfg = is_array($cfg) ? $cfg : [];

        return new NvidiaSentimentAnalyzer(
            apiKey: (string) ($cfg['api_key'] ?? ''),
            baseUrl: (string) ($cfg['base_url'] ?? ''),
            model: (string) ($cfg['model'] ?? ''),
            verifySsl: (bool) ($cfg['verify_ssl'] ?? true),
            environment: $app->environment(),
        );
    }
}
