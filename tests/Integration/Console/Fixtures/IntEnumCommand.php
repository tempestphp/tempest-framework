<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;

final readonly class IntEnumCommand
{
    public function __construct(
        private Console $console,
    ) {}

    #[ConsoleCommand('int-enum-from-one-command')]
    public function __invoke(TestIntEnumFromOne $enum): void
    {
        $this->console->writeln($enum->name);
    }
}
