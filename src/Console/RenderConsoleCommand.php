<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionParameter;
use function Tempest\get;

final readonly class RenderConsoleCommand
{
    public function __invoke(ConsoleCommand $consoleCommand, bool $fullPath = false, bool $includeDescription = true): string
    {
        $commandName = $consoleCommand->getName();

        if ($fullPath) {
            $bag = get(ArgumentBag::class);

            $commandName = join(' ', [
                'php',
                $bag->getFullCommand(),
            ]);
        }

        $parts = [ConsoleStyle::FG_DARK_BLUE($commandName)];

        if ($consoleCommand->getAliases()) {
            $parts[] = ConsoleStyle::FG_LIGHT_GRAY('(' . implode(', ', $consoleCommand->getAliases()) . ')');
        }

        if ($consoleCommand->isDangerous()) {
            $parts[] = ConsoleStyle::BG_RED(' !!! ');
        }

        $arguments = $consoleCommand->getAvailableArguments();

        foreach ($arguments->arguments as $parameter) {
            if ($reflectionParameter = $parameter->parameter) {
                $parts[] = $this->renderParameter($reflectionParameter);
            }
        }

        foreach ($arguments->injectedArguments as $parameter) {
            $parts[] = $this->renderInjected($parameter);
        }

        if ($includeDescription && $consoleCommand->getDescription()) {
            $parts[] = "- {$consoleCommand->getDescription()}";
        }

        return implode(' ', $parts);
    }

    private function renderParameter(ReflectionParameter $parameter): string
    {
        $optional = $parameter->isOptional();

        return $this->renderArgument(
            type: $parameter->getType()?->getName(),
            name: $parameter->getName(),
            optional: $optional,
            defaultValue: strtolower(var_export($optional ? $parameter->getDefaultValue() : null, true)),
        );
    }

    private function renderArgument(?string $type, ?string $name, bool $optional, string $defaultValue)
    {
        $name = ConsoleStyle::FG_BLUE($name ?? '');

        $asString = match($type) {
            'bool' => ConsoleStyle::FG_BLUE("--{$name}"),
            default => $name,
        };

        return match($optional) {
            true => "[{$asString}={$defaultValue}]",
            false => "<{$asString}>",
        };
    }

    private function renderInjected(InjectedArgument $parameter)
    {
        return self::renderArgument(
            type: 'bool', // todo: we should actually pull the type from the argument, not assume bool.
            name: $parameter->name,
            optional: true,
            defaultValue: 'false',
        );
    }
}
