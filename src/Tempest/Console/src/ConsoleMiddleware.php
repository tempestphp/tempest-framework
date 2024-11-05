<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Initializers\Invocation;

interface ConsoleMiddleware
{
    public function __invoke(Invocation $invocation, ConsoleMiddlewareCallable $next): ExitCode;
}
