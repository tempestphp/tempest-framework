<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use Tempest\Console\Actions\ExecuteConsoleCommand;
use Tempest\Console\Console;
use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\Exceptions\InvalidCommandException;
use Tempest\Console\ExitCode;
use Tempest\Console\Initializers\Invocation;
use Tempest\Console\Input\ConsoleInputArgument;
use Tempest\Validation\Rules\NotEmpty;

final readonly class InvalidCommandMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private Console $console,
        private ExecuteConsoleCommand $executeConsoleCommand,
    ) {
    }

    public function __invoke(Invocation $invocation, callable $next): ExitCode
    {
        try {
            return $next($invocation);
        } catch (InvalidCommandException $invalidCommandException) {
            return $this->retry($invocation, $invalidCommandException);
        }
    }

    private function retry(Invocation $invocation, InvalidCommandException $exception): ExitCode
    {
        $this->console->writeln("<em>Provide missing input:</em>");

        foreach ($exception->invalidArguments as $argument) {
            $value = $this->console->ask($argument->name, validation: [new NotEmpty()]);

            $invocation->argumentBag->add(new ConsoleInputArgument(
                name: $argument->name,
                position: $argument->position,
                value: $value
            ));
        }

        return ($this->executeConsoleCommand)($invocation->consoleCommand->getName());
    }
}
