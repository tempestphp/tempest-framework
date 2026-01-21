<?php

namespace App;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final class TempestReleaseCommand
{
    use HasConsole;

    #[ConsoleCommand]
    public function __invoke(): void
    {
        // Move all logic of the `bin/release` script into here

        $this->info('Todo');
    }
}