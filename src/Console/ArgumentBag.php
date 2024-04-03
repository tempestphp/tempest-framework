<?php

declare(strict_types=1);

namespace Tempest\Console;

interface ArgumentBag
{
    /**
     * @return ConsoleInputArgument[]
     */
    public function all(): array;

    public function get(string $name): ?ConsoleInputArgument;

    public function has(string $name): bool;

    public function set(string $name, mixed $value): ConsoleInputArgument;

    public function resolveArguments(ConsoleCommand $command): ConsoleCommandInput;

    public function getCommandName(): string;
}
