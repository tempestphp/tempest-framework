<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use Tempest\Console\Actions\ExecuteConsoleCommand;
use Tempest\Console\Console;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\ExitCode;
use Tempest\Console\Initializers\Invocation;

final readonly class ResolveOrRescueMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private ConsoleConfig $consoleConfig,
        private Console $console,
        private ExecuteConsoleCommand $executeConsoleCommand,
    ) {
    }

    public function __invoke(Invocation $invocation, callable $next): ExitCode
    {
        $consoleCommand = $this->consoleConfig->commands[$invocation->argumentBag->getCommandName()] ?? null;

        if (! $consoleCommand) {
            return $this->rescue($invocation->argumentBag->getCommandName());
        }

        return $next(new Invocation(
            argumentBag: $invocation->argumentBag,
            consoleCommand: $consoleCommand,
        ));
    }

    private function rescue(string $commandName): ExitCode
    {
        $this->console->writeln("<error>Command {$commandName} not found</error>");

        $similarCommands = $this->getSimilarCommands($commandName);

        if ($similarCommands === []) {
            return ExitCode::ERROR;
        }

        if (count($similarCommands) === 1) {
            if ($this->console->confirm("Did you mean {$similarCommands[0]}?")) {
                return $this->runIntendedCommand($similarCommands[0]);
            }

            return ExitCode::CANCELLED;
        }

        $intendedCommand = $this->console->ask(
            'Did you mean to run one of these?',
            options: $similarCommands,
        );

        return $this->runIntendedCommand($intendedCommand);
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

    private function runIntendedCommand(string $commandName): ExitCode
    {
        return ($this->executeConsoleCommand)($commandName);
    }
}
