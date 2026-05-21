<?php

declare(strict_types=1);

arch('Domain layer no depende de Laravel')
    ->expect('SIIM\Domain')
    ->not->toUse('Illuminate')
    ->not->toUse('Carbon\Carbon')
    ->not->toUse('Eloquent');

arch('Domain layer es final por default')
    ->expect('SIIM\Domain')
    ->classes()
    ->toBeFinal()
    ->ignoring('SIIM\Domain\Shared');

arch('Domain layer es strict_types')
    ->expect('SIIM\Domain')
    ->toUseStrictTypes();
