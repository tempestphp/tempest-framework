<?php

namespace Tempest\Process\Testing;

use Tempest\Process\OutputChannel;

use function Tempest\Support\arr;

final class InvokedProcessDescription
{
    /**
     * The process identifier.
     */
    public ?int $pid = 1000;

    /**
     * The process output, in order it should be received.
     *
     * @var array<int, array{type: OutputChannel, buffer: string}>
     */
    public array $output = [];

    /**
     * The process' exit code.
     */
    public int $exitCode = 0;

    /**
     * The number of times the process should indicate that it is running.
     */
    public int $runIterations = 1;

    /**
     * Defines the identifier that should be assigned to the process.
     */
    public function pid(int $pid): self
    {
        $this->pid = $pid;

        return $this;
    }

    /**
     * Describes a line of standard output in the order it should be received.
     *
     * @param string|array<int, string> $output
     */
    public function output(string|array $output): self
    {
        if (is_string($output)) {
            $output = [$output];
        }

        foreach ($output as $item) {
            $this->output[] = [
                'type' => OutputChannel::OUTPUT,
                'buffer' => rtrim($item, "\n") . "\n",
            ];
        }

        return $this;
    }

    /**
     * Describes a line of error output in the order it should be received.
     *
     * @param string|array<int, string> $errorOutput
     */
    public function errorOutput(string|array $errorOutput): self
    {
        if (is_string($errorOutput)) {
            $errorOutput = [$errorOutput];
        }

        foreach ($errorOutput as $item) {
            $this->output[] = [
                'type' => OutputChannel::ERROR,
                'buffer' => rtrim($item, "\n") . "\n",
            ];
        }

        return $this;
    }

    /**
     * Defines the exit code of the process.
     */
    public function exitCode(int $exitCode): self
    {
        $this->exitCode = $exitCode;

        return $this;
    }

    /**
     * Specify how many times the "isRunning" method should return "true".
     */
    public function iterations(int $iterations): self
    {
        $this->runIterations = $iterations;

        return $this;
    }

    /**
     * Returns the output of the process, filtered by the type of output.
     */
    public function resolveOutput(bool $error = false): string
    {
        $expectedType = $error ? OutputChannel::ERROR : OutputChannel::OUTPUT;

        return arr($this->output)
            ->filter(static fn (array $output) => $output['type'] === $expectedType)
            ->map(static fn (array $output) => rtrim($output['buffer'], "\n"))
            ->implode("\n")
            ->when(fn ($s) => $s->isNotEmpty(), fn ($s) => $s->finish("\n"))
            ->toString();
    }
}
