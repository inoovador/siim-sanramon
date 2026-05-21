<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $admin = User::firstOrCreate(
            ['email' => 'admin@siim.local'],
            [
                'name' => 'Admin SIIM',
                'password' => bcrypt('siim2026'),
                'email_verified_at' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        $analyst = User::firstOrCreate(
            ['email' => 'analyst@siim.local'],
            [
                'name' => 'Carlos Aguirre M.',
                'password' => bcrypt('siim2026'),
                'email_verified_at' => now(),
            ]
        );
        $analyst->syncRoles(['analyst']);
    }
}
