<?php

declare(strict_types=1);

namespace Tempest\Core;

use Throwable;

interface ExceptionHandler
{
    /**
     * Handles the given exception.
     */
    public function handle(Throwable $throwable): void;
}
