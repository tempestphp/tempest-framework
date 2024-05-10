<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use Tempest\Console\Actions\ExecuteConsoleCommand;
use Tempest\Console\Console;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\Invocation;

final readonly class ResolveOrRescueMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private ConsoleConfig $consoleConfig,
        private Console $console,
        private ExecuteConsoleCommand $executeConsoleCommand,
    ) {
    }

    public function __invoke(Invocation $invocation, callable $next): void
    {
        $consoleCommand = $this->consoleConfig->commands[$invocation->argumentBag->getCommandName()] ?? null;

        if (! $consoleCommand) {
            $this->rescue($invocation->argumentBag->getCommandName());

            return;
        }

        $next(new Invocation(
            argumentBag: $invocation->argumentBag,
            consoleCommand: $consoleCommand,
        ));
    }

    private function rescue(string $commandName): void
    {
        $this->console->writeln("<error>Command {$commandName} not found</error>");

        $similarCommands = $this->getSimilarCommands($commandName);

        if ($similarCommands === []) {
            return;
        }

        if (count($similarCommands) === 1) {
            if ($this->console->confirm("Did you mean {$similarCommands[0]}?")) {
                $this->runIntendedCommand($similarCommands[0]);
            }
        } else {
            $intendedCommand = $this->console->ask(
                'Did you mean to run one of these?',
                options: $similarCommands,
            );

            $this->runIntendedCommand($intendedCommand);
        }
    }

    private function getSimilarCommands(string $name): array
    {
        $similarCommands = [];

        foreach ($this->consoleConfig->commands as $consoleCommand) {
            if (str_starts_with($consoleCommand->getName(), $name)) {
                $similarCommands[] = $consoleCommand->getName();

                continue;
            }

            $levenshtein = levenshtein($name, $consoleCommand->getName());

            if ($levenshtein <= 3) {
                $similarCommands[] = $consoleCommand->getName();
            }
        }

        return $similarCommands;
    }

    private function runIntendedCommand(string $commandName): void
    {
        ($this->executeConsoleCommand)($commandName);
    }
}
