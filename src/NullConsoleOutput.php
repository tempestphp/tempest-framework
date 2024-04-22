<?php

declare(strict_types=1);

namespace Tempest\Console;

final class NullConsoleOutput implements ConsoleOutput
{
    public string $delimiter = PHP_EOL;

    public function delimiter(string $delimiter): ConsoleOutput
    {
        $clone = clone $this;

        $this->delimiter = $delimiter;

        return $clone;
    }

    public function write(string $contents): ConsoleOutput
    {
        return $this;
    }

    public function writeln(string $line = ''): ConsoleOutput
    {
        return $this;
    }

    public function info(string $line): ConsoleOutput
    {
        return $this;
    }

    public function error(string $line): ConsoleOutput
    {
        return $this;
    }

    public function success(string $line): ConsoleOutput
    {
        return $this;
    }

    public function when(mixed $expression, callable $callback): ConsoleOutput
    {
        return $this;
    }
}
