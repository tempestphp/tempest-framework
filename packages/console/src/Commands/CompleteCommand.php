<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Actions\CompleteConsoleCommandArguments;
use Tempest\Console\Actions\CompleteConsoleCommandNames;
use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\HasConsole;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Container;

final readonly class CompleteCommand
{
    use HasConsole;

    public function __construct(
        private Console $console,
        private ConsoleConfig $consoleConfig,
        private Container $container,
    ) {}

    #[ConsoleCommand(
        name: '_complete',
        description: 'Provide autocompletion',
        hidden: true,
    )]
    public function __invoke(
        array $input,
        int $current,
    ): void {
        $commandName = $input[1] ?? '';

        $command = $this->consoleConfig->commands[$commandName] ?? null;

        $argumentBag = new ConsoleArgumentBag($input);

        if (! $command) {
            $completions = $this->container->get(CompleteConsoleCommandNames::class)->complete($argumentBag, $current);

            $this->writeln(implode(PHP_EOL, $completions));

            return;
        }

        $complete = match (true) {
            is_string($command->complete) && class_exists($command->complete) => $this->container->get($command->complete),
            default => $this->container->get(CompleteConsoleCommandArguments::class),
        };

        $this->writeln(implode(PHP_EOL, $complete->complete(
            $command,
            $argumentBag,
            $current,
        )));
    }
}
