<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;

final readonly class Package
{
    #[ConsoleCommand]
    public function list(): void
    {
    }

    #[ConsoleCommand(help: 'help text')]
    public function info(
        #[ConsoleArgument(
            description: 'The name of the package',
            help: 'Extended help text for this argument
            with a new line',
            aliases: ['n'],
        )]
        string $name
    ): void {
    }
}
