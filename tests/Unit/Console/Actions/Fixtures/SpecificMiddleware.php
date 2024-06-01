<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console\Actions\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\ExitCode;
use Tempest\Console\Initializers\Invocation;

final readonly class SpecificMiddleware implements ConsoleMiddleware
{
    public function __construct(private Console $console)
    {
    }

    public function __invoke(Invocation $invocation, callable $next): ExitCode
    {
        $this->console->writeln('from middleware');

        return $next($invocation);
    }
}
