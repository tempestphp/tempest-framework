<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use Tempest\Console\Invocation;

interface ConsoleMiddleware
{
    public function __invoke(Invocation $invocation, callable $next): void;
}
