<?php

declare(strict_types=1);

namespace Tempest\Console;

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
                [$key, $value] = $this->parseNamedArgument($argument);

                $this->add(
                    new ConsoleInputArgument(
                        name: $key,
                        value: $value,
                        position: $position,
                    )
                );

                continue;
            }

            $this->add(
                new ConsoleInputArgument(
                    name: null,
                    value: $argument,
                    position: $position,
                )
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

    private function add(ConsoleInputArgument $argument): self
    {
        $this->arguments[] = $argument;

        return $this;
    }

    public function getCommandName(): string
    {
        return $this->path[1] ?? '';
    }

    /**
     * @param string $argument
     *
     * @return array{0: string, 1: mixed}
     */
    private function parseNamedArgument(string $argument): array
    {
        $parts = explode('=', str_replace('--', '', $argument));

        $key = $parts[0];

        $value = $parts[1] ?? true;

        $value = match ($value) {
            'true' => true,
            'false' => false,
            default => $value,
        };

        return [$key, $value];
    }
}
