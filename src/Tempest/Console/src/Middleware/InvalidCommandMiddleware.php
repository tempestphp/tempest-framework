<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use Tempest\Console\Actions\ExecuteConsoleCommand;
use Tempest\Console\Console;
use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\ConsoleMiddlewareCallable;
use Tempest\Console\Exceptions\InvalidCommandException;
use Tempest\Console\ExitCode;
use Tempest\Console\Initializers\Invocation;
use Tempest\Console\Input\ConsoleArgumentDefinition;
use Tempest\Console\Input\ConsoleInputArgument;
use function Tempest\Support\str;
use Tempest\Validation\Rules\Boolean;
use Tempest\Validation\Rules\NotEmpty;
use Tempest\Validation\Rules\Numeric;

final readonly class InvalidCommandMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private Console $console,
        private ExecuteConsoleCommand $executeConsoleCommand,
    ) {
    }

    public function __invoke(Invocation $invocation, ConsoleMiddlewareCallable $next): ExitCode|int
    {
        try {
            return $next($invocation);
        } catch (InvalidCommandException $invalidCommandException) {
            return $this->retry($invocation, $invalidCommandException);
        }
    }

    private function retry(Invocation $invocation, InvalidCommandException $exception): ExitCode|int
    {
        $this->console->header(
            header: $invocation->consoleCommand->getName(),
            subheader: $invocation->consoleCommand->description,
        );

        /** @var ConsoleArgumentDefinition $argument */
        foreach ($exception->invalidArguments as $argument) {
            $value = $this->console->ask(
                question: str($argument->name)->snake(' ')->upperFirst()->toString(),
                default: (string) $argument->default,
                hint: $argument->help ?? $argument->description,
                validation: [
                    match ($argument->type) {
                        'bool' => new Boolean(),
                        'int' => new Numeric(),
                        default => new NotEmpty(),
                    },
                ]
            );

            $invocation->argumentBag->add(new ConsoleInputArgument(
                name: $argument->name,
                position: $argument->position,
                value: $value
            ));
        }

        return ($this->executeConsoleCommand)($invocation->consoleCommand->getName());
    }
}
