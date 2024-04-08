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
            $this->add(
                ConsoleInputArgument::fromString($argument, $position)
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
}
