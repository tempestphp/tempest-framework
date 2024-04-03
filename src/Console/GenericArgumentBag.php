<?php

declare(strict_types=1);

namespace Tempest\Console;

final class GenericArgumentBag implements ArgumentBag
{
    /** @var ConsoleInputArgument[] */
    protected array $arguments = [];

    /** @var string[] */
    protected array $path = [];

    /**
     * @param array<string|int, mixed> $arguments
     */
    public function __construct(array $arguments)
    {
        $this->path = array_filter([
            $arguments[0] ?? null,
            $arguments[1] ?? null,
        ]);

        unset($arguments[0], $arguments[1]);

        foreach ($arguments as $name => $argument) {
            if (str_starts_with($argument, '--')) {
                $parts = explode('=', str_replace('--', '', $argument));

                $key = $parts[0];

                $this->set($key, $parts[1] ?? true);

                continue;
            }

            $this->set($name, $argument);
        }
    }

    /**
     * @return ConsoleInputArgument[]
     */
    public function all(): array
    {
        return $this->arguments;
    }

    public function get(string $name): ?ConsoleInputArgument
    {
        return $this->arguments[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->arguments);
    }

    public function set(string $name, mixed $value): ConsoleInputArgument
    {
        return $this->arguments[$name] = new ConsoleInputArgument($name, $value, $value);
    }

    public function resolveArguments(ConsoleCommand $command): ConsoleCommandInput
    {
        $availableArguments = [];

        foreach ($command->getAvailableArguments()->arguments as $argument) {
            $availableArguments[] = $argument->withValue(
                $this->get($argument->name)?->value,
            );
        }

        return new ConsoleCommandInput($availableArguments);
    }

    public function getCommandName(): string
    {
        return $this->path[1] ?? '';
    }
}
