<?php

namespace Tempest\Process;

use Symfony\Component\Process\Process as SymfonyProcess;

/**
 * Represents the result of a terminated process.
 */
final readonly class ProcessResult
{
    public function __construct(
        public int $exitCode,
        public string $output,
        public string $errorOutput,
    ) {}

    /**
     * Determines whether the process was successful.
     */
    public function successful(): bool
    {
        return $this->exitCode === 0;
    }

    /**
     * Determines whether the process has failed.
     */
    public function failed(): bool
    {
        return ! $this->successful();
    }

    public static function fromSymfonyProcess(SymfonyProcess $process): self
    {
        return new self(
            exitCode: $process->getExitCode(),
            output: $process->getOutput(),
            errorOutput: $process->getErrorOutput(),
        );
    }
}
