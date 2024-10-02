<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Initializers\Invocation;

interface ConsoleMiddleware
{
    /** @param callable(Invocation $invocation): ExitCode $next */
    public function __invoke(Invocation $invocation, callable $next): ExitCode;
}
