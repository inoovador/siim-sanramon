<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = $this->bootstrapPassword(
            'siim.bootstrap.admin_password',
            'SIIM_ADMIN_PASSWORD',
        );
        $analystPassword = $this->bootstrapPassword(
            'siim.bootstrap.analyst_password',
            'SIIM_ANALYST_PASSWORD',
        );

        $this->call(RolesAndPermissionsSeeder::class);

        $this->syncBootstrapUser(
            email: 'admin@siim.local',
            name: 'Admin SIIM',
            password: $adminPassword,
            role: 'admin',
        );

        $this->syncBootstrapUser(
            email: 'analyst@siim.local',
            name: 'Carlos Aguirre M.',
            password: $analystPassword,
            role: 'analyst',
        );
    }

    private function bootstrapPassword(string $configKey, string $environmentKey): string
    {
        $password = config($configKey);

        if (is_string($password) && trim($password) !== '') {
            if (app()->environment('production') && strlen($password) < 24) {
                throw new RuntimeException("{$environmentKey} must contain at least 24 characters.");
            }

            return $password;
        }

        if (app()->environment('production')) {
            throw new RuntimeException("{$environmentKey} is required for production database seeding.");
        }

        return Str::password(32);
    }

    private function syncBootstrapUser(string $email, string $name, string $password, string $role): void
    {
        $user = User::query()->firstOrNew(['email' => $email]);
        $user->name = $name;
        $user->setAttribute('email_verified_at', $user->email_verified_at ?? now());

        if (! $user->exists || ! Hash::check($password, (string) $user->password)) {
            $user->password = Hash::make($password);
        }

        $user->save();
        $user->syncRoles([$role]);
    }
}
