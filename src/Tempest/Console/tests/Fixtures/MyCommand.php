<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;

final readonly class MyCommand
{
    public function __construct(private Console $console)
    {
    }

    #[ConsoleCommand('do')]
    public function __invoke()
    {
        $result = $this->console->ask(
            question: 'Pick several:',
            options: ['a', 'b', 'c'],
            multiple: true,
        );
    }
}
