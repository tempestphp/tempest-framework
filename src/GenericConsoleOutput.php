<?php

declare(strict_types=1);

namespace Tempest\Console;

final class GenericConsoleOutput implements ConsoleOutput
{
    public string $delimiter = PHP_EOL;

    public function delimiter(string $delimiter): ConsoleOutput
    {
        $clone = clone $this;

        $clone->delimiter = $delimiter;

        return $clone;
    }

    public function write(string $line, ConsoleOutputType $type = ConsoleOutputType::DEFAULT): ConsoleOutput
    {
        $this->writeToStdOut($type->style($line));

        return $this;
    }

    public function writeln(string $line = '', ConsoleOutputType $type = ConsoleOutputType::DEFAULT): ConsoleOutput
    {
        $this->writeToStdOut($type->style($line) . $this->delimiter);

        return $this;
    }

    public function info(string $line): ConsoleOutput
    {
        $this->writeln($line, ConsoleOutputType::INFO);

        return $this;
    }

    public function error(string $line): ConsoleOutput
    {
        $this->writeln($line, ConsoleOutputType::ERROR);

        return $this;
    }

    public function success(string $line): ConsoleOutput
    {
        $this->writeln($line, ConsoleOutputType::SUCCESS);

        return $this;
    }

    public function when(mixed $expression, callable $callback): ConsoleOutput
    {
        if ($expression) {
            $callback($this);
        }

        return $this;
    }

    private function writeToStdOut(string $content): void
    {
        $stdout = fopen('php://stdout', 'w');

        fwrite(
            $stdout,
            ConsoleStyle::RESET($content),
        );

        fclose($stdout);
    }
}
