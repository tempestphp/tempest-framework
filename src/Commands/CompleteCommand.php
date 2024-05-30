<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use ReflectionMethod;
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
    ) {
    }

    #[ConsoleCommand(
        name: '_complete',
        description: 'Provide autocompletion',
        hidden: true,
    )]
    public function __invoke(
        array $input,
        int $current,
    ): void {
        $commandName = $input[1] ?? null;

        $command = $this->consoleConfig->commands[$commandName] ?? null;
        $argumentBag = new ConsoleArgumentBag($input);

        if (! $command) {
            $this->container->get(CompleteConsoleCommandNames::class)($argumentBag, $current);

            return;
        }

        $complete = match(true) {
            is_array($command->complete) => new ReflectionMethod(...$command->complete),
            is_string($command->complete) && class_exists($command->complete) => new ReflectionMethod($command->complete, '__invoke'),
            default => new ReflectionMethod(CompleteConsoleCommandArguments::class, '__invoke'),
        };

        $complete->invoke(
            $this->container->get($complete->getDeclaringClass()->getName()),
            $command,
            $argumentBag,
            $current,
        );
    }
}
