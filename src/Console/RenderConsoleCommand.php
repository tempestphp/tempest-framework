<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionParameter;
use function Tempest\get;

final readonly class RenderConsoleCommand
{
    public function __invoke(ConsoleCommand $consoleCommand, bool $fullPath = false, bool $includeDescription = true, array $errorParts = []): array
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

        // todo: refactor this class after we decide we want to pursue this approach, a helper class for creating command outputs would be nice
        $noAnsi = function (string $string) {
            return preg_replace('/\x1b\[[0-9;]*m/', '', $string);
        };

        $sum = function ($parts) use ($noAnsi) {
            $sum = 0;

            foreach ($parts as $part) {
                $sum += strlen($noAnsi($part));
            }

            return $sum;
        };

        $validations = [
            str_repeat(" ", $sum($parts)),
        ];

        foreach ($arguments->arguments as $parameter) {
            if ($reflectionParameter = $parameter->parameter) {
                $part = $this->renderParameter($reflectionParameter);
                $parts[] = $part;

                if (isset($errorParts[$parameter->name])) {
                    $validations[] = ConsoleStyle::FG_RED(
                        str_repeat("^", $sum([$part]))
                    );
                } else {
                    $validations[] = str_repeat(" ", $sum([$part]));
                }
            }
        }

        foreach ($arguments->injectedArguments as $parameter) {
            $part = $this->renderInjected($parameter);
            $parts[] = $part;

            if (isset($errorParts[$parameter->name])) {
                $validations[] = ConsoleStyle::FG_RED(
                    str_repeat("^", $sum([$part]))
                );
            } else {
                $validations[] = str_repeat(" ", $sum([$part]));
            }
        }

        if ($includeDescription && $consoleCommand->getDescription()) {
            $parts[] = "- {$consoleCommand->getDescription()}";
        }

        return [
            implode(' ', $parts),
            implode(' ', $validations),
        ];
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
