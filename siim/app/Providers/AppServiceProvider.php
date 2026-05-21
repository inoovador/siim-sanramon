<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SIIM\Application\Identity\UseCases\DefaultRoleAssigner;
use SIIM\Application\Shared\Contracts\AssistantProvider;
use SIIM\Infrastructure\Identity\SpatieDefaultRoleAssigner;
use SIIM\Infrastructure\Llm\NvidiaGlmAssistant;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            DefaultRoleAssigner::class,
            SpatieDefaultRoleAssigner::class,
        );

        $this->app->singleton(AssistantProvider::class, function ($app) {
            $cfg = $app['config']->get('llm.providers.nvidia_glm');

            return new NvidiaGlmAssistant(
                apiKey: (string) ($cfg['api_key'] ?? ''),
                baseUrl: (string) ($cfg['base_url'] ?? 'https://integrate.api.nvidia.com/v1'),
                model: (string) ($cfg['model'] ?? 'z-ai/glm-5.1'),
                timeout: (int) ($cfg['timeout'] ?? 30),
                maxTokens: (int) ($cfg['max_tokens'] ?? 4096),
                verifySsl: (bool) ($cfg['verify_ssl'] ?? true),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
