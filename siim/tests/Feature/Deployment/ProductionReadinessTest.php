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
        self::assertStringContainsString('mkdir -p', $dockerfile);
        foreach ([
            'storage/app/private',
            'storage/app/public',
            'storage/framework/cache/data',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            'storage/logs',
            'bootstrap/cache',
        ] as $writableDirectory) {
            self::assertStringContainsString($writableDirectory, $dockerfile);
        }
        self::assertStringNotContainsString('EXPOSE', $dockerfile);
    }

    public function test_production_environment_example_contains_no_literal_secret_values(): void
    {
        $environment = $this->fileContents('.env.production.example');
        $environmentValues = $this->environmentValues($environment);

        self::assertStringContainsString('APP_ENV=production', $environment);
        self::assertStringContainsString('APP_DEBUG=false', $environment);
        self::assertStringContainsString('APP_URL=https://siim-sanramon.duckdns.org', $environment);
        self::assertStringContainsString('ASSET_URL=https://siim-sanramon.duckdns.org', $environment);
        self::assertStringContainsString('DB_CONNECTION=mariadb', $environment);
        self::assertStringContainsString('DB_HOST=siim-db', $environment);
        self::assertStringContainsString('CACHE_STORE=database', $environment);
        self::assertStringContainsString('SESSION_DRIVER=database', $environment);
        self::assertStringContainsString('QUEUE_CONNECTION=database', $environment);
        foreach (['APP_KEY', 'DB_PASSWORD', 'MARIADB_ROOT_PASSWORD', 'NVIDIA_API_KEY'] as $sensitiveKey) {
            self::assertArrayHasKey($sensitiveKey, $environmentValues);
            self::assertSame('', $environmentValues[$sensitiveKey]);
        }

        self::assertArrayNotHasKey('MARIADB_PASSWORD', $environmentValues);
        self::assertStringNotContainsString('${MARIADB_PASSWORD}', $this->fileContents('docker-compose.prod.yml'));
        self::assertStringContainsString('!.env.production.example', $this->fileContents('.gitignore'));
    }

    public function test_docker_build_context_excludes_secrets_and_local_dependencies(): void
    {
        $rules = array_filter(array_map('trim', explode("\n", $this->fileContents('.dockerignore'))));

        foreach ([
            '.env',
            '.env.*',
            'siim/.env',
            'siim/.env.*',
            'siim/vendor',
            'siim/node_modules',
            'siim/storage',
            'siim/bootstrap/cache',
        ] as $requiredRule) {
            self::assertContains($requiredRule, $rules);
        }
    }

    public function test_e2e_sweep_preserves_database_isolation_and_eight_second_latency_contract(): void
    {
        $e2eSweep = $this->fileContents('siim/tests/Feature/QA/E2ESweepTest.php');

        self::assertStringContainsString('use Illuminate\\Foundation\\Testing\\RefreshDatabase;', $e2eSweep);
        self::assertStringContainsString('use RefreshDatabase;', $e2eSweep);
        self::assertStringContainsString('within_eight_seconds', $e2eSweep);
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

    /**
     * @return array<string, string>
     */
    private function environmentValues(string $environment): array
    {
        $values = [];

        foreach (preg_split('/\R/', $environment) ?: [] as $line) {
            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $values[$key] = $value;
        }

        return $values;
    }
}
