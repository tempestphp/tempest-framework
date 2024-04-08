<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console\Fixtures;

use Tempest\Console\ConsoleCommand;

final class ListFrameworks
{
    #[ConsoleCommand(
        name: 'frameworks:list',
        description: 'List all available frameworks.',
        aliases: ['f:l'],
    )]
    public function handle(
        bool $sortByBest = false,
    ) {
    }
}
