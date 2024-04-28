<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Scheduler;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleOutput;

class PeriodicCommand
{
    public function __construct(
        private readonly ConsoleOutput $output
    ) {

    }

    #[ConsoleCommand(
        name: 'periodic:dummy',
    )]
    public function dummy()
    {
        $this->output->writeln("Dummy run, RUN!");
    }

    #[ConsoleCommand(
        name: 'periodic:every-five',
        description: 'Run every five minutes.',
    )]
    public function everyFive()
    {
        $this->output->writeln("Ran every five minutes.");

        sleep(3);

        $this->output->writeln("Ran every five minutes.");
    }
}
