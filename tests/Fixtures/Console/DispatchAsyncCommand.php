<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Console;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tests\Tempest\Integration\CommandBus\Fixtures\MyAsyncCommand;
use Tests\Tempest\Integration\CommandBus\Fixtures\MyFailingAsyncCommand;

use function Tempest\command;

final readonly class DispatchAsyncCommand
{
    use HasConsole;

    #[ConsoleCommand(name: 'command:dispatch')]
    public function __invoke(int $times = 10, bool $fail = false): void
    {
        foreach (range(1, $times) as $i) {
            $command = $fail
                ? new MyFailingAsyncCommand("{$i}")
                : new MyAsyncCommand("{$i}");

            command(
                $command,
            );
        }

        $this->info('Dispatched commands');
    }
}
