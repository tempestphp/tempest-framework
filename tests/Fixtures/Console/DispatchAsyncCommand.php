<?php

namespace Tests\Tempest\Fixtures\Console;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tests\Tempest\Integration\CommandBus\Fixtures\MyAsyncCommand;
use function Tempest\command;

final readonly class DispatchAsyncCommand
{
    use HasConsole;

    #[ConsoleCommand(name: 'command:dispatch')]
    public function __invoke(): void
    {
        foreach (range(1, 10) as $i) {
            command(new MyAsyncCommand($i));
        }

        $this->info('Dispatched commands');
    }
}