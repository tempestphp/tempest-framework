<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Actions\ResolveConsoleInput;
use Tempest\Console\Exceptions\InvalidCommandException;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Input\ConsoleInputArgument;

final readonly class ConsoleInputBuilder
{
    public function __construct(
        private ConsoleCommand $command,
        private ConsoleArgumentBag $argumentBag,
    ) {}

    /**
     * @return list<mixed>
     */
    public function build(): array
    {
        [$validArguments, $invalidArguments] = (new ResolveConsoleInput())(
            argumentBag: $this->argumentBag,
            argumentDefinitions: $this->command->getArgumentDefinitions(),
        );

        if ($invalidArguments !== []) {
            throw new InvalidCommandException($this->command, $invalidArguments);
        }

        return array_map(
            callback: fn (ConsoleInputArgument $argument) => $argument->value,
            array: $validArguments,
        );
    }
}
