<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use Tempest\Console\Console;
use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\Exceptions\ConsoleException;
use Tempest\Console\ExitCode;
use Tempest\Console\Initializers\Invocation;

final readonly class ConsoleExceptionMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private Console $console
    ) {
    }

    public function __invoke(Invocation $invocation, callable $next): ExitCode
    {
        try {
            return $next($invocation);
        } catch (ConsoleException $consoleException) {
            $consoleException->render($this->console);
        }

        return ExitCode::ERROR;
    }
}
