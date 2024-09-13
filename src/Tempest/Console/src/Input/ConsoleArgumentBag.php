<?php

declare(strict_types=1);

namespace Tempest\Console\Input;

final class ConsoleArgumentBag
{
    /** @var ConsoleInputArgument[] */
    private array $arguments = [];

    /** @var string[] */
    private array $path = [];

    /**
     * @param array<string|int, mixed> $arguments
     */
    public function __construct(array $arguments)
    {
        $cli = $arguments[0] ?? null;
        unset($arguments[0]);

        $commandName = $arguments[1] ?? null;

        if (
            $commandName !== null
            && ! str_starts_with($commandName, '--')
            && ! str_starts_with($commandName, '-')
        ) {
            unset($arguments[1]);
        } else {
            $commandName = null;
        }

        $this->path = [$cli, $commandName];

        foreach ($arguments as $argument) {
            if (str_starts_with($argument, '-') && ! str_starts_with($argument, '--')) {
                $flags = str_split($argument);
                unset($flags[0]);

                foreach ($flags as $flag) {
                    $arguments[] = "-{$flag}";
                }
            }
        }

        foreach (array_values($arguments) as $position => $argument) {
            $this->add(
                ConsoleInputArgument::fromString($argument, $position),
            );
        }
    }

    /**
     * @return ConsoleInputArgument[]
     */
    public function all(): array
    {
        return $this->arguments;
    }

    public function last(): ?ConsoleInputArgument
    {
        return $this->arguments[array_key_last($this->arguments)] ?? null;
    }

    public function has(string ...$names): bool
    {
        foreach ($this->arguments as $argument) {
            foreach ($names as $name) {
                if ($argument->matches($name)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function get(string $name): ?ConsoleInputArgument
    {
        foreach ($this->arguments as $argument) {
            if ($argument->matches($name)) {
                return $argument;
            }
        }

        return null;
    }

    public function findFor(ConsoleArgumentDefinition $argumentDefinition): ?ConsoleInputArgument
    {
        foreach ($this->arguments as $argument) {
            if ($argumentDefinition->matchesArgument($argument)) {
                return $argument;
            }
        }

        if ($argumentDefinition->hasDefault) {
            return new ConsoleInputArgument(
                name: $argumentDefinition->name,
                position: $argumentDefinition->position,
                value: $argumentDefinition->default,
            );
        }

        return null;
    }

    public function findArrayFor(ConsoleArgumentDefinition $argumentDefinition): ?ConsoleInputArgument
    {
        $values = [];

        foreach ($this->arguments as $argument) {
            if ($argumentDefinition->matchesArgument($argument)) {
                $values[] = $argument->value;
            }
        }

        return new ConsoleInputArgument(
            name: $argumentDefinition->name,
            position: $argumentDefinition->position,
            value: $values,
        );
    }

    public function add(ConsoleInputArgument $argument): self
    {
        $this->arguments[] = $argument;

        return $this;
    }

    public function getCommandName(): string
    {
        return $this->path[1] ?? '';
    }

    public function setCommandName(string $commandName): self
    {
        $this->path[1] = $commandName;

        return $this;
    }
}
