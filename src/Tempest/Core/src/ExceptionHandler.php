<?php

declare(strict_types=1);

namespace Tempest\Core;

use Throwable;

interface ExceptionHandler
{
    public function handleException(Throwable $throwable): void;

    public function handleError(int $errNo, string $errstr, string $errFile, int $errLine): void;
}
