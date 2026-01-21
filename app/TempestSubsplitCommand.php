<?php

namespace App;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final class TempestSubsplitCommand
{
    use HasConsole;

    #[ConsoleCommand]
    public function __invoke(): void
    {
        $this->info('Todo');
    }
}