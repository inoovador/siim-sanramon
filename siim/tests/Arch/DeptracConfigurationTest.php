<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

it('classifies bounded-context domain classes only in their context layer', function (): void {
    $configuration = Yaml::parseFile(__DIR__ . '/../../deptrac.yaml');
    assert(is_array($configuration));
    assert(is_array($configuration['deptrac'] ?? null));
    assert(is_array($configuration['deptrac']['layers'] ?? null));

    $domainLayer = collect($configuration['deptrac']['layers'])
        ->firstWhere('name', 'Domain');

    expect($domainLayer)->toBeArray()
        ->and($domainLayer['collectors'][0]['type'] ?? null)->toBe('bool')
        ->and($domainLayer['collectors'][0]['must_not'][0]['value'] ?? null)
        ->toBe('SIIM\\\\Domain\\\\(Identity|Citizen|Ingestion|Analysis|Conversation|Reporting)\\\\.*');
});
