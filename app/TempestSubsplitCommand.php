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
        // Similarly to the release command, write all subsplit logic here

        $this->info('Todo');

        // 1. Copy each package to some kind of "dist" folder
        // 2. Init their git repo if it doesn't exist yet
        // 3. Do checks on composer versions etc
        // 4. Replace dependency versions
        // 5. Commit and push to each repo
    }
}