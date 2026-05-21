<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SIIM\Application\Identity\UseCases\DefaultRoleAssigner;
use SIIM\Infrastructure\Identity\SpatieDefaultRoleAssigner;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
