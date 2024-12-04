<?php

declare(strict_types=1);

namespace Tempest\Console\Stubs;

use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\Initializers\Invocation;
use Tempest\Console\ConsoleMiddlewareCallable;
use Tempest\Console\ExitCode;
use Tempest\Console\HasConsole;

final class ConsoleMiddlewareStub implements ConsoleMiddleware {
    use HasConsole;
    
    public function __invoke(Invocation $invocation, ConsoleMiddlewareCallable $next): ExitCode|int {
        return $next($invocation);
    }
}
