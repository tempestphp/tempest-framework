<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Middleware\ForceMiddleware;

final readonly class ForceCommand
{
    public function __construct(private Console $console)
    {
    }

    #[ConsoleCommand(
        middleware: [ForceMiddleware::class]
    )]
    public function __invoke(): void
    {
        if (! $this->console->confirm('continue?')) {
            return;
        }

        $this->console->writeln('continued');
    }
}
