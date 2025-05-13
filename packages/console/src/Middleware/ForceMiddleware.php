<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use Tempest\Console\Console;
use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\ConsoleMiddlewareCallable;
use Tempest\Console\ExitCode;
use Tempest\Console\GenericConsole;
use Tempest\Console\GlobalFlags;
use Tempest\Console\Initializers\Invocation;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final readonly class ForceMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private Console $console,
    ) {}

    public function __invoke(Invocation $invocation, ConsoleMiddlewareCallable $next): ExitCode|int
    {
        if ($invocation->argumentBag->get(GlobalFlags::FORCE_SHORTHAND->value) || $invocation->argumentBag->get(GlobalFlags::FORCE->value)) {
            if ($this->console instanceof GenericConsole) {
                $this->console->setForced();
            }
        }

        return $next($invocation);
    }
}
