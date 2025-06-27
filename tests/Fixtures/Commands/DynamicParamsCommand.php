<?php

namespace Tests\Tempest\Fixtures\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Input\ConsoleArgumentBag;

final class DynamicParamsCommand
{
    use HasConsole;

    public function __construct(
        private readonly ConsoleArgumentBag $consoleArgumentBag,
    ) {}

    #[ConsoleCommand(allowDynamicArguments: true)]
    public function __invoke(): void
    {
        $dynamic = $this->consoleArgumentBag->get('dynamic');

        $this->console->info($dynamic ? 'yes' : 'no');
    }
}