<?php

declare(strict_types=1);

namespace App\Console;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;

final readonly class Package
{
    #[ConsoleCommand]
    public function list(): void
    {
    }

    #[ConsoleCommand]
    public function info(
        #[ConsoleArgument(
            description: 'The name of the package',
            help: 'Extended help text for this argument',
            aliases: ['n'],
        )]
        string $name
    ): void {
    }
}
