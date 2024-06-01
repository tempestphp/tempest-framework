<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;

final readonly class CombinedFlagCommand
{
    public function __construct(private Console $console)
    {
    }

    #[ConsoleCommand('flags')]
    public function __invoke(
        #[ConsoleArgument(aliases: ['-a'])]
        bool $flagA = false,
        #[ConsoleArgument(aliases: ['-b'])]
        bool $flagB = false,
    ): void {
        if ($flagA && $flagB) {
            $this->console->writeln('ok');
        }
    }

    #[ConsoleCommand('flags:short')]
    public function short(bool $a = false, bool $b = false): void
    {
        if ($a && $b) {
            $this->console->writeln('ok');
        }
    }
}
