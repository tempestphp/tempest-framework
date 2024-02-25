<?php

declare(strict_types=1);

namespace Tempest\Exceptions;

use Tempest\Container\InitializedBy;
use Throwable;

#[InitializedBy(ExceptionHandlerInitializer::class)]
interface ExceptionHandler
{
    public function handle(Throwable $throwable): void;
}
