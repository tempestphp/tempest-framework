<?php

declare(strict_types=1);

namespace Tempest\Console;

final class GenericConsole implements Console
{
    public function __construct(
        private readonly ConsoleInput $input,
        private readonly ConsoleOutput $output,
    ) {
    }

    public function delimiter(string $delimiter): ConsoleOutput
    {
        return $this->output->delimiter($delimiter);
    }

    public function readln(): string
    {
        return $this->input->readln();
    }

    public function ask(string $question, ?array $options = null, ?string $default = null): string
    {
        return $this->input->ask($question, $options, $default);
    }

    public function confirm(string $question, bool $default = false): bool
    {
        return $this->input->confirm($question, $default);
    }

    public function write(string $line, ConsoleOutputType $type = ConsoleOutputType::DEFAULT): ConsoleOutput
    {
        return $this->output->write($line, $type);
    }

    public function writeln(string $line = '', ConsoleOutputType $type = ConsoleOutputType::DEFAULT): ConsoleOutput
    {
        return $this->output->writeln($line, $type);
    }

    public function info(string $line): ConsoleOutput
    {
        return $this->output->info($line);
    }

    public function error(string $line): ConsoleOutput
    {
        return $this->output->error($line);
    }

    public function success(string $line): ConsoleOutput
    {
        return $this->output->success($line);
    }

    public function when(mixed $expression, callable $callback): ConsoleOutput
    {
        return $this->output->when($expression, $callback);
    }
}
