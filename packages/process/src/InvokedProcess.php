<?php

namespace Tempest\Process;

use Tempest\DateTime\Duration;

interface InvokedProcess
{
    /**
     * Gets the process identifier.
     */
    public ?int $pid {
        get;
    }

    /**
     * Whether the process is running.
     */
    public bool $running {
        get;
    }

    /**
     * Gets the output of the process.
     */
    public string $output {
        get;
    }

    /**
     * Gets the error output of the process.
     */
    public string $errorOutput {
        get;
    }

    /**
     * Sends a signal to the process.
     */
    public function signal(int $signal): self;

    /**
     * Stops the process if it is currently running.
     *
     * @param float|int|Duration $timeout The maximum time to wait for the process to stop.
     * @param int|null $signal A POSIX signal to send to the process.
     */
    public function stop(float|int|Duration $timeout = 10, ?int $signal = null): self;

    /**
     * Waits for the process to finish.
     *
     * @param null|callable(OutputChannel, string): void $output The callback receives the type of output (out or err) and some bytes from the output in real-time while writing the standard input to the process. It allows to have feedback from the independent process during execution.
     */
    public function wait(?callable $output = null): ProcessResult;
}
