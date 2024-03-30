<?php

declare(strict_types=1);

namespace Tempest\Console;

final class GenericArgumentBag implements ArgumentBag
{
    protected array $arguments = [];
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

        foreach ($arguments as $i => $argument) {
            if (str_starts_with($argument, '--')) {
                $parts = explode('=', str_replace('--', '', $argument));

                $key = $parts[0];

                $this->set($key, $parts[1] ?? true);
                continue;
            }

            $this->set((string) $i, $argument);
        }
    }

    public function get(string ...$names): ?Argument
    {
        foreach ($names as $name) {
            if ($this->has($name)) {
                return $this->arguments[$name];
            }
        }

        return null;
    }

    public function has(string $name): bool
    {
        return isset($this->arguments[$name]);
    }

    public function all(): array
    {
        return $this->arguments;
    }

    public function set(string $name, mixed $value): Argument
    {
        return $this->arguments[$name] = new Argument($name, $value);
    }

    public function hasAny(string ...$names): bool
    {
        foreach ($names as $name) {
            if ($this->has($name)) {
                return true;
            }
        }

        return false;
    }

    public function getCommandName(): ?string
    {
        return $this->path[1] ?? null;
    }

    /**
     * @param ConsoleCommand $command
     *
     * @return CommandArguments
     */
    public function resolveParameters(ConsoleCommand $command): CommandArguments
    {
        $values = [];

        $args = $command->getAvailableArguments();
        $injected = [];

        foreach (array_values($args->arguments) as $key => $argument) {
            $values[] = $argument->withValue(
                $this->get($argument->name, ...$argument->aliases) ?: $this->get((string) ($key + 2)) ?? $argument->parameter?->getDefaultValue(),
            );
        }

        foreach ($args->injectedArguments as $argument) {
            $injected[] = $argument->withValue(
                $this->get($argument->name, ...$argument->aliases),
            );
        }

        return new CommandArguments($values, $injected);
    }
}
