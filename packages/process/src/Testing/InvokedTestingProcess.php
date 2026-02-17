<?php

namespace Tempest\Process\Testing;

use Tempest\DateTime\Duration;
use Tempest\Process\InvokedProcess;
use Tempest\Process\OutputChannel;
use Tempest\Process\ProcessResult;

final class InvokedTestingProcess implements InvokedProcess
{
    public ?int $pid {
        get {
            $this->invokeOutputHandlerWithNextLineOfOutput();

            return $this->description->pid;
        }
    }

    public bool $running {
        get {
            $this->invokeOutputHandlerWithNextLineOfOutput();

            if ($this->remainingRunIterations === 0) {
                // @mago-expect lint:no-empty-loop
                while ($this->invokeOutputHandlerWithNextLineOfOutput()) {
                }

                return false;
            }

            $this->remainingRunIterations -= 1;

            return true;
        }
    }

    public string $output {
        get {
            $this->latestOutput();

            $output = [];

            for ($i = 0; $i < $this->nextOutputIndex; $i++) {
                if ($this->description->output[$i]['type'] === OutputChannel::OUTPUT) {
                    $output[] = $this->description->output[$i]['buffer'];
                }
            }

            return rtrim(implode('', $output), "\n") . "\n";
        }
    }

    public string $errorOutput {
        get {
            $this->latestErrorOutput();

            $output = [];

            for ($i = 0; $i < $this->nextErrorOutputIndex; $i++) {
                if ($this->description->output[$i]['type'] === OutputChannel::ERROR) {
                    $output[] = $this->description->output[$i]['buffer'];
                }
            }

            return rtrim(implode('', $output), "\n") . "\n";
        }
    }

    /**
     * The general output handler callback.
     *
     * @var null|\Closure(OutputChannel, string): void
     */
    private ?\Closure $outputHandler = null;

    /**
     * The number of times the process should indicate that it is "running".
     */
    private int $remainingRunIterations {
        get {
            if (! isset($this->remainingRunIterations)) {
                $this->remainingRunIterations = $this->description->runIterations;
            }

            return $this->remainingRunIterations;
        }
    }

    /**
     * The current output's index.
     */
    private int $nextOutputIndex = 0;

    /**
     * The current error output's index.
     */
    private int $nextErrorOutputIndex = 0;

    public function __construct(
        private readonly InvokedProcessDescription $description,
    ) {}

    public function signal(int $signal): self
    {
        $this->invokeOutputHandlerWithNextLineOfOutput();

        return $this;
    }

    public function stop(float|int|Duration $timeout = 10, ?int $signal = null): self
    {
        $this->remainingRunIterations = 0;

        return $this;
    }

    public function wait(?callable $output = null): ProcessResult
    {
        if ($output !== null) {
            $this->outputHandler = $output instanceof \Closure
                ? $output
                : \Closure::fromCallable($output);
        }

        if (! $this->outputHandler) {
            $this->remainingRunIterations = 0;

            return $this->getProcessResult();
        }

        // @mago-expect lint:no-empty-loop
        while ($this->invokeOutputHandlerWithNextLineOfOutput()) {
        }

        $this->remainingRunIterations = 0;

        return $this->getProcessResult();
    }

    /**
     * Gets the latest standard output for the process.
     */
    private function latestOutput(): string
    {
        $outputCount = count($this->description->output);

        for ($i = $this->nextOutputIndex; $i < $outputCount; $i++) {
            if ($this->description->output[$i]['type'] === OutputChannel::OUTPUT) {
                $output = $this->description->output[$i]['buffer'];
                $this->nextOutputIndex = $i + 1;

                break;
            }

            $this->nextOutputIndex = $i + 1;
        }

        return $output ?? '';
    }

    /**
     * Gets the latest error output for the process.
     */
    public function latestErrorOutput(): string
    {
        $outputCount = count($this->description->output);

        for ($i = $this->nextErrorOutputIndex; $i < $outputCount; $i++) {
            if ($this->description->output[$i]['type'] === OutputChannel::ERROR) {
                $output = $this->description->output[$i]['buffer'];
                $this->nextErrorOutputIndex = $i + 1;

                break;
            }

            $this->nextErrorOutputIndex = $i + 1;
        }

        return $output ?? '';
    }

    /**
     * Invokes the asynchronous output handler with the next single line of output if necessary.
     */
    private function invokeOutputHandlerWithNextLineOfOutput(): bool
    {
        if (! $this->outputHandler) {
            return false;
        }

        [$outputCount, $outputStartingPoint] = [
            count($this->description->output),
            min($this->nextOutputIndex, $this->nextErrorOutputIndex),
        ];

        for ($i = $outputStartingPoint; $i < $outputCount; $i++) {
            $currentOutput = $this->description->output[$i];

            if ($currentOutput['type'] === OutputChannel::OUTPUT && $i >= $this->nextOutputIndex) {
                call_user_func($this->outputHandler, OutputChannel::OUTPUT, $currentOutput['buffer']);

                $this->nextOutputIndex = $i + 1;

                return true;
            }

            if ($currentOutput['type'] === OutputChannel::ERROR && $i >= $this->nextErrorOutputIndex) {
                call_user_func($this->outputHandler, OutputChannel::ERROR, $currentOutput['buffer']);

                $this->nextErrorOutputIndex = $i + 1;

                return true;
            }
        }

        return false;
    }

    public function getProcessResult(): ProcessResult
    {
        return new ProcessResult(
            exitCode: $this->description->exitCode,
            output: $this->description->resolveOutput(error: false),
            errorOutput: $this->description->resolveOutput(error: true),
        );
    }
}
