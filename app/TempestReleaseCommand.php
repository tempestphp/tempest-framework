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
        $this->info('Todo');
    }
}