<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Interface\Console;
use Tempest\Interface\ConsoleInput;
use Tempest\Interface\ConsoleOutput;

readonly class GenericConsole implements Console
{
    public function __construct(
        private ConsoleInput $input,
        private ConsoleOutput $output,
    ) {
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

    public function writeln(string $line, ConsoleStyle ...$styles): void
    {
        $this->output->writeln($line, ...$styles);
    }

    public function info(string $line): void
    {
        $this->output->info($line);
    }

    public function error(string $line): void
    {
        $this->output->error($line);
    }

    public function success(string $line): void
    {
        $this->output->success($line);
    }
}
