<?php

namespace Tempest\Console\Exceptions;

use Tempest\Console\Console;
use Tempest\Console\Input\ConsoleInputArgument;
use Tempest\Support\Arr\ImmutableArray;

final class UnknownArgumentsException extends ConsoleException
{
    /**
     * @param ImmutableArray<int, ConsoleInputArgument> $invalidArguments
     */
    public function __construct(
        private readonly ImmutableArray $invalidArguments,
    ) {}

    public function render(Console $console): void
    {
        $console->error(sprintf(
            'Unknown arguments: %s',
            $this->invalidArguments
                ->map(fn (ConsoleInputArgument $argument) => sprintf(
                    '<code>%s</code>',
                    $argument->name,
                ))
                ->implode(', '),
        ));
    }
}
