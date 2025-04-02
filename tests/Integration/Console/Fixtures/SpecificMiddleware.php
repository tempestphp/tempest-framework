<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\ConsoleMiddlewareCallable;
use Tempest\Console\ExitCode;
use Tempest\Console\Initializers\Invocation;
use Tempest\Discovery\DoNotDiscover;

#[DoNotDiscover]
final readonly class SpecificMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private Console $console,
    ) {}

    public function __invoke(Invocation $invocation, ConsoleMiddlewareCallable $next): ExitCode|int
    {
        $this->console->writeln('from middleware');

        return $next($invocation);
    }
}
