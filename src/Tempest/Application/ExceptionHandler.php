<?php

declare(strict_types=1);

namespace Tempest\Application;

use Throwable;

interface ExceptionHandler
{
    public function handle(Throwable $throwable): void;
}
