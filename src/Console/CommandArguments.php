<?php

declare(strict_types=1);

namespace Tempest\Console;

final readonly class CommandArguments
{
    /**
     * @param array<string|int, Argument> $arguments
     * @param array<string|int, InjectedArgument> $injectedArguments
     */
    public function __construct(
        public array $arguments = [],
        public array $injectedArguments = []
    ) {
    }

    /**
     * @return Argument[]|InjectedArgument[]
     */
    public function all(): array
    {
        return $this->arguments + $this->injectedArguments;
    }
}
