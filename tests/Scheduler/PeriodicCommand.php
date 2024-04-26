<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Scheduler;

use DateInterval;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\Scheduler\CronDefinition;

class PeriodicCommand
{
    public function __construct(
        private readonly ConsoleOutput $output
    ) {

    }

    #[ConsoleCommand(
        name: 'periodic:dummy',
        cron: new CronDefinition(
            interval: new DateInterval('PT1M'),
            runInBackground: true,
        )
    )]
    public function dummy()
    {
        $this->output->writeln("Dummy run, RUN!");
    }

    #[ConsoleCommand(
        name: 'periodic:every-five',
        description: 'Run every five minutes.',
        cron: new CronDefinition(
            interval: new DateInterval('PT5M'),
            runInBackground: true,
        )
    )]
    public function everyFive()
    {
        $this->output->writeln("Ran every five minutes.");

        sleep(3);

        $this->output->writeln("Ran every five minutes.");
    }
}
