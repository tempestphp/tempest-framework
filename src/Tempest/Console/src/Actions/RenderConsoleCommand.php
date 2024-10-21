<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Reflection\ParameterReflector;

final readonly class RenderConsoleCommand
{
    public function __construct(private Console $console)
    {
    }

    public function __invoke(ConsoleCommand $consoleCommand): void
    {
        $parts = ["<em><strong>{$consoleCommand->getName()}</strong></em>"];

        foreach ($consoleCommand->handler->getParameters() as $parameter) {
            $parts[] = $this->renderParameter($parameter);
        }

        if ($consoleCommand->description !== null && $consoleCommand->description !== '') {
            $parts[] = "- {$consoleCommand->description}";
        }

        $this->console->writeln(' ' . implode(' ', $parts));
    }

    private function renderParameter(ParameterReflector $parameter): string
    {
        /** @phpstan-ignore-next-line */
        $type = $parameter->getType()?->getName();
        $optional = $parameter->isOptional();
        $defaultValue = strtolower(var_export($optional ? $parameter->getDefaultValue() : null, true));
        $name = "<em>{$parameter->getName()}</em>";

        $asString = match($type) {
            'bool' => "<em>--</em>{$name}",
            default => $name,
        };

        return match($optional) {
            true => "[{$asString}={$defaultValue}]",
            false => "<{$asString}>",
        };
    }
}
