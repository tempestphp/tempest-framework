<?php

namespace Tempest\Process;

use Symfony\Component\Process\Exception\ProcessTimedOutException as SymfonyTimeoutException;
use Symfony\Component\Process\Process as SymfonyProcess;
use Tempest\DateTime\Duration;
use Tempest\Process\Exceptions\ProcessExecutionHasTimedOut;

/**
 * Represents a process that has been invoked and is currently running or has completed.
 */
final class InvokedSystemProcess implements InvokedProcess
{
    /**
     * Gets the process identifier.
     */
    public ?int $pid {
        get => $this->process->getPid();
    }

    /**
     * Whether the process is running.
     */
    public bool $running {
        get => $this->process->isRunning();
    }

    /**
     * Gets the output of the process.
     */
    public string $output {
        get => $this->process->getOutput();
    }

    /**
     * Gets the error output of the process.
     */
    public string $errorOutput {
        get => $this->process->getErrorOutput();
    }

    public function __construct(
        private readonly SymfonyProcess $process,
    ) {}

    public function signal(int $signal): self
    {
        $this->process->signal($signal);

        return $this;
    }

    public function stop(float|int|Duration $timeout = 10, ?int $signal = null): self
    {
        if ($timeout instanceof Duration) {
            $timeout = $timeout->getTotalSeconds();
        }

        $this->process->stop((float) $timeout, $signal);

        return $this;
    }

    public function wait(?callable $output = null): ProcessResult
    {
        try {
            $callback = $output
                ? fn (string $type, mixed $data) => $output(OutputChannel::fromSymfonyOutputType($type), $data)
                : null;

            $this->process->wait($callback);

            return ProcessResult::fromSymfonyProcess($this->process);
        } catch (SymfonyTimeoutException $exception) {
            throw new ProcessExecutionHasTimedOut(
                result: ProcessResult::fromSymfonyProcess($this->process),
                original: $exception,
            );
        }
    }
}
