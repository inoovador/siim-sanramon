<?php

declare(strict_types=1);

namespace Tests\Feature\Deployment;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

final class ProductionBootstrapSecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array{admin: ?string, analyst: ?string}  $credentials
     */
    #[DataProvider('missingCredentialCases')]
    public function test_production_seeding_fails_closed_when_a_bootstrap_credential_is_absent(
        array $credentials,
        string $missingEnvironmentKey,
    ): void {
        $this->app['env'] = 'production';
        config([
            'siim.bootstrap.admin_password' => $credentials['admin'],
            'siim.bootstrap.analyst_password' => $credentials['analyst'],
        ]);

        try {
            $this->app->make(DatabaseSeeder::class)->run();
            self::fail('Production seeding accepted a missing bootstrap credential.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString($missingEnvironmentKey, $exception->getMessage());
        }

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('roles', 0);
    }

    public function test_production_seeding_rotates_existing_users_and_preserves_matching_hashes(): void
    {
        $this->app['env'] = 'production';
        $adminPassword = 'admin-rotation-test-fixture-2026';
        $analystPassword = 'analyst-rotation-test-fixture-2026';
        $stalePassword = 'stale-rotation-test-fixture-2026';
        config([
            'siim.bootstrap.admin_password' => $adminPassword,
            'siim.bootstrap.analyst_password' => $analystPassword,
        ]);

        User::factory()->create([
            'email' => 'admin@siim.local',
            'password' => Hash::make($stalePassword),
        ]);
        User::factory()->create([
            'email' => 'analyst@siim.local',
            'password' => Hash::make($stalePassword),
        ]);

        $seeder = $this->app->make(DatabaseSeeder::class);
        $seeder->run();

        $admin = User::query()->where('email', 'admin@siim.local')->firstOrFail();
        $analyst = User::query()->where('email', 'analyst@siim.local')->firstOrFail();
        self::assertTrue(Hash::check($adminPassword, $admin->password));
        self::assertTrue(Hash::check($analystPassword, $analyst->password));
        self::assertFalse(Hash::check($stalePassword, $admin->password));
        self::assertFalse(Hash::check($stalePassword, $analyst->password));
        self::assertTrue($admin->hasRole('admin'));
        self::assertTrue($analyst->hasRole('analyst'));

        $adminHash = $admin->password;
        $analystHash = $analyst->password;
        $seeder->run();

        self::assertSame($adminHash, $admin->fresh()?->password);
        self::assertSame($analystHash, $analyst->fresh()?->password);
    }

    /**
     * @param  array{admin: string, analyst: string}  $credentials
     */
    #[DataProvider('weakCredentialCases')]
    public function test_production_seeding_rejects_bootstrap_credentials_shorter_than_24_characters(
        array $credentials,
        string $weakEnvironmentKey,
    ): void {
        $this->app['env'] = 'production';
        config([
            'siim.bootstrap.admin_password' => $credentials['admin'],
            'siim.bootstrap.analyst_password' => $credentials['analyst'],
        ]);

        try {
            $this->app->make(DatabaseSeeder::class)->run();
            self::fail('Production seeding accepted a weak bootstrap credential.');
        } catch (RuntimeException $exception) {
            self::assertStringContainsString($weakEnvironmentKey, $exception->getMessage());
            self::assertStringContainsString('at least 24 characters', $exception->getMessage());
        }

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('roles', 0);
    }

    /**
     * @return iterable<string, array{array{admin: ?string, analyst: ?string}, string}>
     */
    public static function missingCredentialCases(): iterable
    {
        yield 'admin missing' => [
            ['admin' => null, 'analyst' => 'analyst-present-test-fixture-2026'],
            'SIIM_ADMIN_PASSWORD',
        ];

        yield 'analyst missing' => [
            ['admin' => 'admin-present-test-fixture-2026', 'analyst' => ''],
            'SIIM_ANALYST_PASSWORD',
        ];
    }

    /**
     * @return iterable<string, array{array{admin: string, analyst: string}, string}>
     */
    public static function weakCredentialCases(): iterable
    {
        yield 'admin too short' => [
            ['admin' => 'short-admin-fixture', 'analyst' => 'analyst-long-enough-test-fixture'],
            'SIIM_ADMIN_PASSWORD',
        ];

        yield 'analyst too short' => [
            ['admin' => 'admin-long-enough-test-fixture', 'analyst' => 'short-analyst-fixture'],
            'SIIM_ANALYST_PASSWORD',
        ];
    }
}
