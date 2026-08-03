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

    public function test_production_compose_isolates_database_and_worker_on_an_egress_enabled_backend(): void
    {
        $compose = $this->fileContents('docker-compose.prod.yml');
        $application = $this->serviceBlock($compose, 'siim-prod');
        $database = $this->serviceBlock($compose, 'siim-db');
        $worker = $this->serviceBlock($compose, 'siim-worker');

        self::assertStringContainsString("    networks:\n      - coolify\n      - siim-backend", $application);
        self::assertStringContainsString("    networks:\n      - siim-backend", $database);
        self::assertStringNotContainsString('      - coolify', $database);
        self::assertStringContainsString("    networks:\n      - siim-backend", $worker);
        self::assertStringNotContainsString('      - coolify', $worker);
        self::assertStringContainsString(
            "  siim-backend:\n    name: siim-backend\n    driver: bridge\n    internal: false",
            $compose,
        );
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

    public function test_production_worker_disables_the_inherited_web_healthcheck(): void
    {
        $compose = $this->fileContents('docker-compose.prod.yml');
        preg_match('/^  siim-worker:\R(?<body>[\s\S]*?)(?=^[^\s]|\z)/m', $compose, $matches);

        $workerBlock = $matches['body'] ?? null;
        self::assertIsString($workerBlock);
        self::assertStringContainsString(
            '    healthcheck: { disable: true }',
            $workerBlock,
        );
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
        self::assertStringContainsString('COPY --chown=root:root siim/composer.json siim/composer.lock ./', $dockerfile);
        self::assertStringContainsString('COPY --chown=root:root siim ./', $dockerfile);
        self::assertStringContainsString(
            'COPY --from=frontend --chown=root:root /app/public/build ./public/build',
            $dockerfile,
        );
        self::assertStringNotContainsString('COPY --chown=33:33', $dockerfile);
        self::assertStringNotContainsString('COPY --from=frontend --chown=33:33', $dockerfile);
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

    public function test_production_image_keeps_immutable_code_root_owned_and_runtime_paths_writable(): void
    {
        $dockerfile = $this->fileContents('siim/Dockerfile.production');

        self::assertStringContainsString('chown -R root:root /var/www/html', $dockerfile);
        self::assertStringContainsString('chmod -R a=rX /var/www/html', $dockerfile);
        self::assertStringContainsString('chmod 0755 /var/www/html', $dockerfile);

        $immutablePermissions = strpos($dockerfile, 'chmod -R a=rX /var/www/html');
        $runtimeOwnership = strpos($dockerfile, 'chown -R 33:33 storage bootstrap/cache');
        self::assertIsInt($immutablePermissions);
        self::assertIsInt($runtimeOwnership);
        self::assertLessThan($runtimeOwnership, $immutablePermissions);
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
        foreach ([
            'APP_KEY',
            'DB_PASSWORD',
            'MARIADB_ROOT_PASSWORD',
            'NVIDIA_API_KEY',
            'SIIM_ADMIN_PASSWORD',
            'SIIM_ANALYST_PASSWORD',
        ] as $sensitiveKey) {
            self::assertArrayHasKey($sensitiveKey, $environmentValues);
            self::assertSame('', $environmentValues[$sensitiveKey]);
        }

        self::assertArrayNotHasKey('MARIADB_PASSWORD', $environmentValues);
        self::assertStringNotContainsString('${MARIADB_PASSWORD}', $this->fileContents('docker-compose.prod.yml'));
        self::assertStringContainsString('!.env.production.example', $this->fileContents('.gitignore'));
    }

    public function test_production_bootstrap_credentials_are_transient_and_have_no_tracked_default(): void
    {
        $compose = $this->fileContents('docker-compose.prod.yml');
        $seeder = $this->fileContents('siim/database/seeders/DatabaseSeeder.php');

        self::assertStringNotContainsString('SIIM_ADMIN_PASSWORD', $compose);
        self::assertStringNotContainsString('SIIM_ANALYST_PASSWORD', $compose);
        self::assertStringNotContainsString('siim' . '2026', $seeder);
        self::assertStringNotContainsString('firstOrCreate(', $seeder);
        self::assertStringContainsString('Hash::check(', $seeder);
        self::assertStringContainsString('SIIM_ADMIN_PASSWORD', $seeder);
        self::assertStringContainsString('SIIM_ANALYST_PASSWORD', $seeder);
    }

    public function test_production_bootstrap_config_reads_credentials_from_environment_without_defaults(): void
    {
        $configurationPath = dirname(base_path()) . '/siim/config/siim.php';

        self::assertFileExists($configurationPath);
        $configuration = $this->fileContents('siim/config/siim.php');
        self::assertStringContainsString("env('SIIM_ADMIN_PASSWORD')", $configuration);
        self::assertStringContainsString("env('SIIM_ANALYST_PASSWORD')", $configuration);
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

    private function serviceBlock(string $compose, string $service): string
    {
        $servicePattern = preg_quote($service, '/');
        preg_match(
            "/^  {$servicePattern}:\\R(?<body>[\\s\\S]*?)(?=^  [a-zA-Z0-9_-]+:\\R|^[^\\s]|\\z)/m",
            $compose,
            $matches,
        );

        $serviceBlock = $matches['body'] ?? null;
        self::assertIsString($serviceBlock);

        return $serviceBlock;
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
