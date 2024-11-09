<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Console;

use function Tempest\command;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tests\Tempest\Integration\CommandBus\Fixtures\MyAsyncCommand;
use Tests\Tempest\Integration\CommandBus\Fixtures\MyFailingAsyncCommand;

final readonly class DispatchAsyncCommand
{
    use HasConsole;

    #[ConsoleCommand(name: 'command:dispatch')]
    public function __invoke(int $times = 10, bool $failing = false): void
    {
        foreach (range(1, $times) as $i) {
            command(
                $failing
                ? new MyFailingAsyncCommand("{$i}")
                : new MyAsyncCommand("{$i}"),
            );
        }

        $this->info('Dispatched commands');
    }
}
