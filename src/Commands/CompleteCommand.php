<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use ReflectionMethod;
use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\HasConsole;
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

        $definition = $this->consoleConfig->commands[$commandName] ?? null;

        if (! $definition) {
            $this->error("Command {$commandName} not found");

            return;
        }

        if (! $definition->complete) {
            $this->error("No completion configured for command {$commandName}");

            return;
        }

        $complete = match(true) {
            is_array($definition->complete) => new ReflectionMethod(...$definition->complete),
            is_string($definition->complete) && class_exists($definition->complete) => new ReflectionMethod($definition->complete, '__invoke'),
            default => null,
        };

        $complete?->invoke(
            $this->container->get($complete->getDeclaringClass()->getName()),
        );
    }
}
