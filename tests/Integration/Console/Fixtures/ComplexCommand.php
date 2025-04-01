<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;

final readonly class ComplexCommand
{
    public function __construct(
        private Console $console,
    ) {}

    #[ConsoleCommand('complex')]
    public function __invoke(string $a, string $b, string $c, bool $flag = false): void
    {
        $this->console->writeln($a . $b . $c);
        $this->console->writeln($flag ? 'true' : 'false');
    }
}
