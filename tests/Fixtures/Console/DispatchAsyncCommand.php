<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Console;

use function Tempest\command;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tests\Tempest\Integration\CommandBus\Fixtures\MyAsyncCommand;

final readonly class DispatchAsyncCommand
{
    use HasConsole;

    #[ConsoleCommand(name: 'command:dispatch')]
    public function __invoke(): void
    {
        foreach (range(1, 10) as $i) {
            command(new MyAsyncCommand("{$i}"));
        }

        $this->info('Dispatched commands');
    }
}
