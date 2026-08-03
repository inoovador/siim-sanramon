<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Persistence\Citizen;

use Closure;
use Illuminate\Support\Facades\DB;
use SIIM\Application\Citizen\Contracts\CitizenTransaction;
use Throwable;

final class EloquentCitizenTransaction implements CitizenTransaction
{
    public function run(Closure $callback): mixed
    {
        DB::beginTransaction();
        try {
            $result = $callback();
            DB::commit();

            return $result;
        } catch (Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
