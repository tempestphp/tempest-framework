<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use Tempest\Console\Console;
use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\ExitCode;
use Tempest\Console\GenericConsole;
use Tempest\Console\Initializers\Invocation;

final readonly class ForceMiddleware implements ConsoleMiddleware
{
    public function __construct(private Console $console)
    {
    }

    public function __invoke(Invocation $invocation, callable $next): ExitCode
    {
        if ($invocation->argumentBag->get('-f') || $invocation->argumentBag->get('force')) {
            if ($this->console instanceof GenericConsole) {
                $this->console->setForced();
            }
        }

        return $next($invocation);
    }
}
