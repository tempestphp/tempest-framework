<?php

declare(strict_types=1);

namespace Tempest\Console;

interface ArgumentBag
{
    /**
     * @return Argument[]
     */
    public function all(): array;

    public function get(string $name): ?Argument;

    public function has(string $name): bool;

    public function set(string $name, mixed $value): Argument;

    public function hasAny(string ...$names): bool;

    public function getCommandName(): ?string;

    /**
     * @param ConsoleCommand $command
     *
     * @return CommandArguments
     */
    public function resolveParameters(ConsoleCommand $command): CommandArguments;

}
