<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use ReflectionParameter;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\ConsoleStyle;

final readonly class RenderConsoleCommand
{
    public function __construct(private ConsoleOutput $output)
    {
    }

    public function __invoke(ConsoleCommand $consoleCommand): void
    {
        $parts = [ConsoleStyle::FG_DARK_BLUE($consoleCommand->getName())];

        foreach ($consoleCommand->handler->getParameters() as $parameter) {
            $parts[] = $this->renderParameter($parameter);
        }

        if ($consoleCommand->description !== null && $consoleCommand->description !== '') {
            $parts[] = "- {$consoleCommand->description}";
        }

        $this->output->writeln(' ' . implode(' ', $parts));
    }

    private function renderParameter(ReflectionParameter $parameter): string
    {
        /** @phpstan-ignore-next-line */
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
