<?php

declare(strict_types=1);

$contexts = ['Identity', 'Citizen', 'Ingestion', 'Analysis', 'Conversation', 'Reporting'];

foreach ($contexts as $context) {
    arch("Domain {$context} no depende de otros contextos Domain")
        ->expect("SIIM\\Domain\\{$context}")
        ->not->toUse(array_map(
            fn (string $other): string => "SIIM\\Domain\\{$other}",
            array_diff($contexts, [$context])
        ));
}

arch('Application no depende de Infrastructure')
    ->expect('SIIM\Application')
    ->not->toUse('SIIM\Infrastructure');

arch('Application no toca Eloquent ni HTTP')
    ->expect('SIIM\Application')
    ->not->toUse('Illuminate\Database\Eloquent')
    ->not->toUse('Illuminate\Http');
