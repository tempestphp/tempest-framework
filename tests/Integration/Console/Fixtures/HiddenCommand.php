<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;

final readonly class HiddenCommand
{
    public function __construct(private Console $console)
    {
    }

    #[ConsoleCommand(name:"hidden", hidden: true)]
    public function __invoke(): void
    {
        $this->console->info('boo!');
    }
}
