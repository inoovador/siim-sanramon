<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Topic;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    public function run(): void
    {
        $topics = [
            ['slug' => 'obras_publicas', 'label' => 'Obras públicas'],
            ['slug' => 'seguridad', 'label' => 'Seguridad'],
            ['slug' => 'salud', 'label' => 'Salud'],
            ['slug' => 'educacion', 'label' => 'Educación'],
            ['slug' => 'transporte', 'label' => 'Transporte'],
            ['slug' => 'medio_ambiente', 'label' => 'Medio ambiente'],
            ['slug' => 'limpieza', 'label' => 'Limpieza'],
            ['slug' => 'tributos', 'label' => 'Tributos'],
        ];

        foreach ($topics as $topic) {
            Topic::query()->updateOrCreate(
                ['slug' => $topic['slug']],
                ['label' => $topic['label'], 'description' => null, 'is_active' => true],
            );
        }
    }
}
