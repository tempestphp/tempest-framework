<?php

declare(strict_types=1);

namespace Tempest\Console;

use InvalidArgumentException;

final class NullArgumentBag implements ArgumentBag
{

    public function all(): array
    {
        return [];
    }

    public function get(string $name): ?Argument
    {
        return null;
    }

    public function has(string $name): bool
    {
        return false;
    }

    public function set(string $name, mixed $value): Argument
    {
        throw new InvalidArgumentException("Cannot set arguments on NullArgumentBag");
    }

    public function hasAny(string ...$names): bool
    {
        return false;
    }

    public function getCommandName(): ?string
    {
        return null;
    }

    public function resolveParameters(ConsoleCommand $command): CommandArguments
    {
        return new CommandArguments();
    }
}
