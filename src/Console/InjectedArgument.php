<?php

declare(strict_types=1);

namespace Tempest\Console;

readonly class InjectedArgument extends Argument
{
    /**
     * @throws ExitException
     */
    public function handle(ConsoleCommand $command): void
    {

    }

    public function shouldInject(): bool
    {
        return ! ! $this->value;
    }
}
