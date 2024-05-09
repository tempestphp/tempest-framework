<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Actions\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\Invocation;
use Tempest\Console\Middleware\ConsoleMiddleware;

final readonly class SpecificMiddleware implements ConsoleMiddleware
{
    public function __construct(private Console $console)
    {
    }

    public function __invoke(Invocation $invocation, callable $next): void
    {
        $this->console->writeln('from middleware');

        $next($invocation);
    }
}
