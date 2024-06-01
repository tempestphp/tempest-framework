<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Fixtures;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;

final class ListFrameworks
{
    public function __construct(private Console $console)
    {
    }

    #[ConsoleCommand(
        name: 'frameworks:list',
        description: 'List all available frameworks.',
        aliases: ['f:l'],
    )]
    public function handle(
        bool $sortByBest = false,
    ) {
        $this->console->write('list');
    }
}
