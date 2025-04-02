<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use Tempest\Console\Console;
use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\ConsoleMiddlewareCallable;
use Tempest\Console\Exceptions\ConsoleException;
use Tempest\Console\ExitCode;
use Tempest\Console\Initializers\Invocation;
use Tempest\Core\Priority;

#[Priority(Priority::FRAMEWORK - 9)]
final readonly class ConsoleExceptionMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private Console $console,
    ) {}

    public function __invoke(Invocation $invocation, ConsoleMiddlewareCallable $next): ExitCode|int
    {
        try {
            return $next($invocation);
        } catch (ConsoleException $consoleException) {
            $consoleException->render($this->console);
        }

        return ExitCode::ERROR;
    }
}
