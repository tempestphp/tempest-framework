<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;

final readonly class StringEnumCommand
{
    public function __construct(private Console $console)
    {
    }

    #[ConsoleCommand('string-enum-command')]
    public function __invoke(TestStringEnum $enum): void
    {
        $this->console->writeln($enum->value);
    }
}
