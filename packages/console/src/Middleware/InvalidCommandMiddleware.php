<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use BackedEnum;
use Tempest\Console\Actions\ExecuteConsoleCommand;
use Tempest\Console\Console;
use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\ConsoleMiddlewareCallable;
use Tempest\Console\Exceptions\InvalidCommandException;
use Tempest\Console\ExitCode;
use Tempest\Console\Initializers\Invocation;
use Tempest\Console\Input\ConsoleInputArgument;
use Tempest\Core\Priority;
use Tempest\Validation\Rules\IsBoolean;
use Tempest\Validation\Rules\IsEnum;
use Tempest\Validation\Rules\IsNotEmptyString;
use Tempest\Validation\Rules\IsNumeric;

use function Tempest\Support\str;

#[Priority(Priority::FRAMEWORK - 7)]
final readonly class InvalidCommandMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private Console $console,
        private ExecuteConsoleCommand $executeConsoleCommand,
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
            $isEnum = is_a($argument->type, BackedEnum::class, allow_string: true);
            $name = str($argument->name)->snake(' ')->upperFirst()->toString();

            if ($argument->type === 'bool') {
                $value = $this->console->confirm(
                    question: $name,
                    default: $argument->default ?? false,
                );
            } else {
                $value = $this->console->ask(
                    question: $name,
                    options: match (true) {
                        $isEnum => $argument->type::cases(),
                        default => null,
                    },
                    default: $argument->default,
                    hint: $argument->help ?: $argument->description,
                    validation: array_filter([
                        $isEnum
                            ? new IsEnum($argument->type)
                            : new IsNotEmptyString(),
                        match ($argument->type) {
                            'bool' => new IsBoolean(),
                            'int' => new IsNumeric(),
                            default => null,
                        },
                    ]),
                );
            }

            $invocation->argumentBag->add(new ConsoleInputArgument(
                name: $argument->name,
                position: $argument->position,
                value: $value,
            ));
        }

        return ($this->executeConsoleCommand)($invocation->consoleCommand->getName());
    }
}
