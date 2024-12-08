<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use Tempest\Console\Actions\ExecuteConsoleCommand;
use Tempest\Console\Actions\ResolveConsoleCommand;
use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\ConsoleMiddlewareCallable;
use Tempest\Console\ExitCode;
use Tempest\Console\Initializers\Invocation;
use Tempest\Support\ArrayHelper;
use Throwable;

final readonly class ResolveOrRescueMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private ConsoleConfig $consoleConfig,
        private Console $console,
        private ExecuteConsoleCommand $executeConsoleCommand,
        private ResolveConsoleCommand $resolveConsoleCommand,
    ) {
    }

    public function __invoke(Invocation $invocation, ConsoleMiddlewareCallable $next): ExitCode|int
    {
        try {
            $consoleCommand = ($this->resolveConsoleCommand)($invocation->argumentBag->getCommandName());
        } catch (Throwable) {
            return $this->rescue($invocation->argumentBag->getCommandName());
        }

        return $next(new Invocation(
            argumentBag: $invocation->argumentBag,
            consoleCommand: $consoleCommand,
        ));
    }

    private function rescue(string $commandName): ExitCode|int
    {
        $this->console->writeln();
        $this->console->writeln('<style="bg-dark-red fg-white"> Error </style>');
        $this->console->writeln("<style=\"fg-red\">Command <em>{$commandName}</em> not found.</style>");

        $similarCommands = $this->getSimilarCommands($commandName);

        if ($similarCommands === []) {
            return ExitCode::ERROR;
        }

        if (count($similarCommands) === 1) {
            if ($this->console->confirm("Did you mean <em>{$similarCommands[0]}</em>?")) {
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

        /** @var ConsoleCommand $consoleCommand */
        foreach ($this->consoleConfig->commands as $consoleCommand) {
            if (in_array($consoleCommand->getName(), $similarCommands, strict: true)) {
                continue;
            }

            if (str_contains($name, ':')) {
                $wantedParts = ArrayHelper::explode($name, separator: ':');
                $currentParts = ArrayHelper::explode($consoleCommand->getName(), separator: ':');

                if ($wantedParts->count() === $currentParts->count() && $wantedParts->every(fn (string $part, int $index) => str_starts_with($currentParts[$index], $part))) {
                    $similarCommands[] = $consoleCommand->getName();

                    continue;
                }
            }

            if (str_starts_with($consoleCommand->getName(), $name)) {
                $similarCommands[] = $consoleCommand->getName();

                continue;
            }

            $levenshtein = levenshtein($name, $consoleCommand->getName());

            if ($levenshtein <= 2) {
                $similarCommands[] = $consoleCommand->getName();
            }
        }

        return $similarCommands;
    }

    private function runIntendedCommand(string $commandName): ExitCode|int
    {
        return ($this->executeConsoleCommand)($commandName);
    }
}
