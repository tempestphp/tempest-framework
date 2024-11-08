<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\Input\ConsoleArgumentDefinition;
use function Tempest\Support\str;

final readonly class RenderConsoleCommand
{
    public function __construct(private Console $console)
    {
    }

    public function __invoke(ConsoleCommand $consoleCommand): void
    {
        $parts = ["<em><strong>{$consoleCommand->getName()}</strong></em>"];

        foreach ($consoleCommand->getArgumentDefinitions() as $argument) {
            $parts[] = $this->renderArgument($argument);
        }

        if ($consoleCommand->description !== null && $consoleCommand->description !== '') {
            $parts[] = "- {$consoleCommand->description}";
        }

        $this->console->writeln(' ' . implode(' ', $parts));
    }

    private function renderArgument(ConsoleArgumentDefinition $argument): string
    {
        $name = str($argument->name)
            ->prepend('<em>')
            ->append('</em>');

        $asString = match($argument->type) {
            'bool' => "<em>--</em>{$name}",
            default => $name,
        };

        if (! $argument->hasDefault) {
            return "<{$asString}>";
        }

        return match (true) {
            $argument->default === true => "[{$asString}=true]",
            $argument->default === false => "[{$asString}=false]",
            is_null($argument->default) => "[{$asString}=null]",
            is_array($argument->default) => "[{$asString}=array]",
            default => "[{$asString}={$argument->default}]"
        };
    }
}
