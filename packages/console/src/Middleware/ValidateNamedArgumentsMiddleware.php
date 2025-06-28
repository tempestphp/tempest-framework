<?php

namespace Tempest\Console\Middleware;

use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\ConsoleMiddlewareCallable;
use Tempest\Console\Exceptions\UnknownArgumentsException;
use Tempest\Console\ExitCode;
use Tempest\Console\GlobalFlags;
use Tempest\Console\Initializers\Invocation;
use Tempest\Console\Input\ConsoleArgumentDefinition;
use Tempest\Console\Input\ConsoleInputArgument;
use Tempest\Core\Priority;

use function Tempest\Support\arr;

#[Priority(Priority::FRAMEWORK - 6)]
final class ValidateNamedArgumentsMiddleware implements ConsoleMiddleware
{
    public function __invoke(Invocation $invocation, ConsoleMiddlewareCallable $next): ExitCode|int
    {
        if ($invocation->consoleCommand->allowDynamicArguments) {
            return $next($invocation);
        }

        $allowedParameterNames = arr($invocation->consoleCommand->getArgumentDefinitions())
            ->flatMap(function (ConsoleArgumentDefinition $definition) {
                return [$definition->name, ...$definition->aliases];
            })
            ->map(function (string $name) {
                return ltrim($name, '-');
            });

        $invalidInput = arr($invocation->argumentBag->arguments)
            ->filter(fn (ConsoleInputArgument $argument) => $argument->name !== null)
            ->filter(fn (ConsoleInputArgument $argument) => ! $allowedParameterNames->hasValue(ltrim($argument->name, '-')))
            ->filter(fn (ConsoleInputArgument $argument) => ! in_array($argument->name, GlobalFlags::values(), strict: true));

        if ($invalidInput->isNotEmpty()) {
            throw new UnknownArgumentsException(
                consoleCommand: $invocation->consoleCommand,
                invalidArguments: $invalidInput,
            );
        }

        return $next($invocation);
    }
}
