<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Exceptions\UnresolvedArgumentsException;

final class ConsoleArgumentBag
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

        foreach (array_values($arguments) as $position => $argument) {
            if (str_starts_with($argument, '--')) {
                $parts = explode('=', str_replace('--', '', $argument));

                $key = $parts[0];

                $this->set($key, $parts[1] ?? true);

                continue;
            }

            $this->set($position, $argument);
        }
    }

    /**
     * @return ConsoleInputArgument[]
     */
    public function all(): array
    {
        return $this->arguments;
    }

    public function get(string|int $name): ?ConsoleInputArgument
    {
        return $this->arguments[$name] ?? null;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->arguments);
    }

    public function set(string|int $name, mixed $value): ConsoleInputArgument
    {
        return $this->arguments[$name] = new ConsoleInputArgument(
            $name,
            $value,
            $value,
            position: count($this->arguments),
        );
    }

    public function resolveInput(ConsoleCommand $command): ConsoleCommandInput
    {
        $availableArguments = [];
        $unresolvedArguments = $this->arguments;

        foreach ($command->getAvailableArguments() as $argument) {
            $availableArguments[] = $this->resolveArgument($argument);

            foreach ([...$argument->getAllNames(), (string) $argument->position] as $name) {
                unset($unresolvedArguments[$name]);
            }
        }

        if (count($unresolvedArguments) > 0) {
            throw UnresolvedArgumentsException::fromArguments($unresolvedArguments);
        }

        return new ConsoleCommandInput($availableArguments);
    }

    public function getCommandName(): string
    {
        return $this->path[1] ?? '';
    }

    private function resolveArgument(ConsoleInputArgument $argument): ConsoleInputArgument
    {
        foreach ([...$argument->getAllNames(), (string) $argument->position] as $name) {
            $argumentValue = $this->get($name);

            if ($argumentValue === null) {
                continue;
            }

            return $argument->withValue(
                $argumentValue->getValue()
            );
        }

        return $argument->withValue($argument->default);
    }
}
