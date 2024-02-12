<?php

namespace Tempest\Console\Commands;

use Tempest\Console\ConsoleStyle;
use Tempest\Interface\ConsoleCommand;
use Tempest\Interface\ConsoleInput;
use Tempest\Interface\ConsoleOutput;

final readonly class Install implements ConsoleCommand
{
    public function __construct(
        private ConsoleInput $input,
        private ConsoleOutput $output,
    ) {}

    public function tempest(): void
    {
        $cwd = getcwd();

        if (! $this->input->confirm(
            question: sprintf(
                "Installing Tempest in %s, continue?",
                ConsoleStyle::BG_BLUE(str_replace('/', '-', $cwd)),
            ),
        )) {
            return;
        }
        
        dd('hi');
    }
}