<?php

declare(strict_types=1);

arch('No dd(), dump(), var_dump() en código de producción')
    ->expect(['dd', 'dump', 'var_dump', 'print_r'])
    ->not->toBeUsed();

arch('No die() ni exit()')
    ->expect(['die', 'exit'])
    ->not->toBeUsed();
