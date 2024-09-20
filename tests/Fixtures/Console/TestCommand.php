<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Console;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;

final readonly class TestCommand
{
    public function __construct(
        private Console $console,
    ) {
    }

    #[ConsoleCommand]
    public function test(): void
    {
        $this->console->confirm('yes or no?');
    }
}
