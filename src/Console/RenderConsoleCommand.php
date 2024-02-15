<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionParameter;

final readonly class RenderConsoleCommand
{
    public function __invoke(ConsoleCommand $consoleCommand): string
    {
        $parts = [ConsoleStyle::FG_DARK_BLUE($consoleCommand->getName())];

        foreach ($consoleCommand->handler->getParameters() as $parameter) {
            $parts[] = $this->renderParameter($parameter);
        }

        if ($consoleCommand->getDescription()) {
            $parts[] = "- {$consoleCommand->getDescription()}";
        }

        return implode(' ', $parts);
    }

    private function renderParameter(ReflectionParameter $parameter): string
    {
        $type = $parameter->getType()?->getName();
        $optional = $parameter->isOptional();
        $defaultValue = strtolower(var_export($optional ? $parameter->getDefaultValue() : null, true));
        $name = ConsoleStyle::FG_BLUE($parameter->getName());

        $asString = match($type) {
            'bool' => ConsoleStyle::FG_BLUE("--{$name}"),
            default => $name,
        };

        return match($optional) {
            true => "[{$asString}={$defaultValue}]",
            false => "<{$asString}>",
        };
    }
}
