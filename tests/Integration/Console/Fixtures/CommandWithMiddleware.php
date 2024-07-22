<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;

final readonly class CommandWithMiddleware
{
    public function __construct(private Console $console)
    {
    }

    #[ConsoleCommand(
        name: 'with:middleware',
        middleware: [SpecificMiddleware::class]
    )]
    public function __invoke(): void
    {
        $this->console->writeln('from command');
    }
}
