<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use Tempest\Console\Actions\ExecuteConsoleCommand;
use Tempest\Console\Actions\PromptForConsoleInput;
use Tempest\Console\Console;
use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\ConsoleMiddlewareCallable;
use Tempest\Console\Exceptions\InvalidCommandException;
use Tempest\Console\ExitCode;
use Tempest\Console\Initializers\Invocation;
use Tempest\Support\Priority;

#[Priority(Priority::FRAMEWORK - 7)]
final readonly class InvalidCommandMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private Console $console,
        private ExecuteConsoleCommand $executeConsoleCommand,
        private PromptForConsoleInput $promptForConsoleInput,
    ) {}

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

        if (! $this->console->supportsPrompting()) {
            throw $exception;
        }

        foreach ($exception->invalidArguments as $argument) {
            ($this->promptForConsoleInput)(
                argumentBag: $invocation->argumentBag,
                argumentDefinition: $argument,
            );
        }

        return ($this->executeConsoleCommand)($invocation->consoleCommand->getName());
    }
}
