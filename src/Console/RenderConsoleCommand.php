<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionParameter;
use Tempest\Console\Styling\ConsoleOutputBuilder;

final readonly class RenderConsoleCommand
{
    public function __construct(protected ArgumentBag $bag)
    {

    }

    public function __invoke(
        ConsoleCommand $consoleCommand,
        bool $fullPath = false,
        bool $includeDescription = true,
        /** @var array<string, array> */
        array $errorParts = [],
    ): string {
        $commandName = match ($fullPath) {
            true => join(' ', [
                'php',
                $this->bag->getFullCommand(),
            ]),
            default => $consoleCommand->getName(),
        };

        $arguments = $consoleCommand->getAvailableArguments();

        $line = ConsoleOutputBuilder::new(' ')
            ->formatted($commandName)
            ->when(! ! $consoleCommand->getAliases(), function (ConsoleOutputBuilder $builder) use ($consoleCommand) {
                $builder->muted('(' . implode(', ', $consoleCommand->getAliases()) . ')');
            })
            ->when($consoleCommand->isDangerous(), function (ConsoleOutputBuilder $builder) {
                $builder->error(' !!! ');
            })
            ->when(! ! $arguments->arguments, function (ConsoleOutputBuilder $builder) use ($arguments) {
                foreach ($arguments->arguments as $parameter) {
                    if ($reflectionParameter = $parameter->parameter) {
                        $builder->formatted(
                            $this->renderParameter($reflectionParameter)
                        );
                    }
                }
            })
            ->when(! ! $arguments->injectedArguments, function (ConsoleOutputBuilder $builder) use ($arguments) {
                foreach ($arguments->injectedArguments as $parameter) {
                    $builder->formatted(
                        $this->renderInjected($parameter)
                    );
                }
            })
            ->when(
                $includeDescription && $consoleCommand->getDescription(),
                fn (ConsoleOutputBuilder $b) => $b->muted(" - " . $consoleCommand->getDescription() . " "),
            )
            ->toString();

        if ($errorParts) {
            $validationLine = $this->buildValidationLine($line, $errorParts);

            if (! $validationLine) {
                return $line;
            }

            return $line . PHP_EOL . $validationLine;
        }

        return $line;
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

    private function buildValidationLine(string $line, array $errorParts): string
    {
        // Remove ANSI escape codes from the original line
        $noAnsi = preg_replace('/\x1b\[[0-9;]*m/', '', $line);

        // Initialize the validation line with spaces
        $validationLine = str_repeat(" ", strlen($noAnsi));

        foreach ($errorParts as $key => $error) {
            // Match the error key in the original line
            preg_match("/\[(--)?$key(=)?.*]/U", $noAnsi, $matches, PREG_OFFSET_CAPTURE);

            foreach ($matches as $match) {
                // Calculate the start position and length of the error part
                $start = $match[1];
                $length = strlen($match[0]);

                // Generate the error indicator (^) for the matched part
                $errorIndicator = str_repeat("^", $length);

                $prefix = substr($validationLine, 0, $start);
                $suffix = substr($validationLine, $start + strlen($errorIndicator));

                $validationLine = $prefix . $errorIndicator . $suffix;
            }
        }

        // If there's no error, return the original line
        if (trim($validationLine) === "") {
            return $line;
        }

        // Otherwise, return the validation line
        return preg_replace('/(\^+)/', ConsoleStyle::FG_RED('$1'), $validationLine);
    }
}
