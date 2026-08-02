<?php

declare(strict_types=1);

namespace Tests\Feature\Deployment;

use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

final class ProductionReadinessTest extends TestCase
{
    public function test_production_compose_isolates_the_siim_services_on_coolify(): void
    {
        $compose = $this->fileContents('docker-compose.prod.yml');

        self::assertStringContainsString('container_name: siim-prod', $compose);
        self::assertStringContainsString('container_name: siim-db', $compose);
        self::assertStringContainsString('container_name: siim-worker', $compose);
        self::assertStringContainsString('image: mariadb:11.4', $compose);
        self::assertStringContainsString('external: true', $compose);
        self::assertStringContainsString('name: coolify', $compose);
        self::assertDoesNotMatchRegularExpression('/siim-db:[\\s\\S]*?ports:/', $compose);
        self::assertStringNotContainsString('MARIADB_ROOT_PASSWORD: secret', $compose);
    }

    public function test_production_compose_routes_only_the_application_through_traefik(): void
    {
        $compose = $this->fileContents('docker-compose.prod.yml');

        self::assertStringContainsString('Host(`siim-sanramon.duckdns.org`)', $compose);
        self::assertStringContainsString('traefik.http.routers.siim-http.entrypoints=http', $compose);
        self::assertStringContainsString('traefik.http.routers.siim-https.entrypoints=https', $compose);
        self::assertStringContainsString('traefik.http.routers.siim-https.tls.certresolver=letsencrypt', $compose);
        self::assertStringContainsString('traefik.http.services.siim.loadbalancer.server.port=8080', $compose);
        self::assertStringContainsString('traefik.docker.network=coolify', $compose);
        self::assertStringContainsString(
            'php artisan queue:work --queue=analysis,default --tries=3 --timeout=90 --sleep=3 --max-time=3600',
            $compose,
        );
        self::assertSame(2, substr_count($compose, 'image: siim:production'));
    }

    public function test_production_image_builds_assets_with_the_supported_php_runtime(): void
    {
        $dockerfile = $this->fileContents('siim/Dockerfile.production');

        self::assertStringContainsString('FROM serversideup/php:8.3-fpm-nginx', $dockerfile);
        self::assertStringContainsString('composer install --no-dev', $dockerfile);
        self::assertStringContainsString('npm ci', $dockerfile);
        self::assertStringContainsString('npm run build', $dockerfile);
        self::assertStringContainsString('33:33', $dockerfile);
        self::assertStringNotContainsString('EXPOSE', $dockerfile);
    }

    public function test_production_environment_example_contains_no_literal_secret_values(): void
    {
        $environment = $this->fileContents('.env.production.example');

        self::assertStringContainsString('APP_ENV=production', $environment);
        self::assertStringContainsString('APP_DEBUG=false', $environment);
        self::assertStringContainsString('APP_URL=https://siim-sanramon.duckdns.org', $environment);
        self::assertStringContainsString('ASSET_URL=https://siim-sanramon.duckdns.org', $environment);
        self::assertStringContainsString('DB_CONNECTION=mariadb', $environment);
        self::assertStringContainsString('DB_HOST=siim-db', $environment);
        self::assertStringContainsString('CACHE_STORE=database', $environment);
        self::assertStringContainsString('SESSION_DRIVER=database', $environment);
        self::assertStringContainsString('QUEUE_CONNECTION=database', $environment);
        self::assertMatchesRegularExpression('/^(?:APP_KEY|DB_PASSWORD|MARIADB_ROOT_PASSWORD|NVIDIA_API_KEY)=$/m', $environment);
        self::assertStringContainsString('!.env.production.example', $this->fileContents('.gitignore'));
    }

    public function test_forwarded_https_is_trusted_behind_the_proxy(): void
    {
        Route::get('/proxy-scheme-check', fn () => response()->json([
            'secure' => request()->isSecure(),
            'scheme' => request()->getScheme(),
        ]));

        $this->getJson('/proxy-scheme-check', ['X-Forwarded-Proto' => 'https'])
            ->assertOk()
            ->assertJson([
                'secure' => true,
                'scheme' => 'https',
            ]);
    }

    public function test_deptrac_keeps_bounded_context_dependencies_isolated(): void
    {
        $ruleset = strstr($this->fileContents('siim/deptrac.yaml'), '  ruleset:');
        self::assertIsString($ruleset);

        $contexts = [
            'Context_Identity',
            'Context_Citizen',
            'Context_Ingestion',
            'Context_Analysis',
            'Context_Conversation',
            'Context_Reporting',
        ];

        foreach ($contexts as $context) {
            preg_match("/^    {$context}:\\R((?:      - .+\\R)*)/m", $ruleset, $matches);
            self::assertCount(2, $matches, "Missing ruleset entry for {$context}.");

            $contextRules = $matches[1] ?? '';
            self::assertIsString($contextRules);

            preg_match_all('/^      - (.+)$/m', $contextRules, $targetMatches);
            $targets = $targetMatches[1];

            self::assertContains('Domain', $targets);
            self::assertContains('Application', $targets);
            self::assertSame([$context], array_values(array_intersect($contexts, $targets)));
        }

        preg_match('/^    SharedKernel:\\R((?:      - .+\\R)*)/m', $ruleset, $sharedMatches);
        self::assertCount(2, $sharedMatches, 'SharedKernel must enforce shared-code isolation.');

        $sharedRules = $sharedMatches[1] ?? '';
        self::assertIsString($sharedRules);

        preg_match_all('/^      - (.+)$/m', $sharedRules, $sharedTargetMatches);
        self::assertSame([], array_values(array_intersect($contexts, $sharedTargetMatches[1])));
    }

    private function fileContents(string $relativePath): string
    {
        $contents = file_get_contents(dirname(base_path()) . '/' . $relativePath);

        if ($contents === false) {
            throw new RuntimeException("Unable to read {$relativePath}.");
        }

        return $contents;
    }
}
