<?php

namespace Tempest\Process\Testing;

use Tempest\Process\GenericProcessExecutor;
use Tempest\Process\InvokedProcess;
use Tempest\Process\InvokedSystemProcess;
use Tempest\Process\PendingProcess;
use Tempest\Process\Pool;
use Tempest\Process\ProcessExecutor;
use Tempest\Process\ProcessPoolResults;
use Tempest\Process\ProcessResult;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Regex;

final class TestingProcessExecutor implements ProcessExecutor
{
    /** @var array<string,array<array{PendingProcess,ProcessResult}>> */
    private(set) array $executions = [];

    /**
     * @param array<string, string|ProcessResult|InvokedProcessDescription> $mocks
     */
    public function __construct(
        private readonly GenericProcessExecutor $executor,
        public array $mocks = [],
        public bool $allowRunningActualProcesses = false,
    ) {}

    public function run(string|PendingProcess $command): ProcessResult
    {
        if ($result = $this->findMockedProcess($command)) {
            return $this->recordExecution($command, $result);
        }

        if (! $this->allowRunningActualProcesses) {
            throw ProcessExecutionWasForbidden::forPendingProcess($command);
        }

        return $this->recordExecution($command, $this->start($command)->wait());
    }

    public function start(string|PendingProcess $command): InvokedProcess
    {
        if ($processResult = $this->findInvokedProcessDescription($command)) {
            $this->recordExecution($command, $process = new InvokedTestingProcess($processResult));
        } else {
            if (! $this->allowRunningActualProcesses) {
                throw ProcessExecutionWasForbidden::forPendingProcess($command);
            }

            $this->recordExecution($command, $process = $this->executor->start($command));
        }

        return $process;
    }

    public function pool(iterable $pool): Pool
    {
        return new Pool(
            pendingProcesses: new ImmutableArray($pool)->map($this->createPendingProcess(...)),
            processExecutor: $this,
        );
    }

    public function concurrently(iterable $pool): ProcessPoolResults
    {
        return $this->pool($pool)->start()->wait();
    }

    private function findMockedProcess(string|PendingProcess $command): ?ProcessResult
    {
        $process = $this->createPendingProcess($command);

        foreach ($this->mocks as $command => $result) {
            if (! Regex\matches($process->command, $this->buildRegExpFromString($command))) {
                continue;
            }

            if ($result instanceof ProcessResult) {
                return $result;
            }

            return new ProcessResult(
                exitCode: 0,
                output: $result,
                errorOutput: '',
            );
        }

        return null;
    }

    private function findInvokedProcessDescription(string|PendingProcess $command): ?InvokedProcessDescription
    {
        $process = $this->createPendingProcess($command);

        foreach ($this->mocks as $command => $result) {
            if (! $this->commandMatchesPattern($process->command, $command)) {
                continue;
            }

            if ($result instanceof InvokedProcessDescription) {
                return $result;
            }

            return new InvokedProcessDescription();
        }

        return null;
    }

    private function recordExecution(string|PendingProcess $command, InvokedProcess|ProcessResult $result): ProcessResult
    {
        $process = $this->createPendingProcess($command);
        $result = match (true) {
            $result instanceof ProcessResult => $result,
            $result instanceof InvokedTestingProcess => $result->getProcessResult(),
            $result instanceof InvokedSystemProcess => $result->wait(), // TODO: fix
            default => throw new \RuntimeException('Unexpected result type.'),
        };

        $this->executions[$process->command] ??= [];
        $this->executions[$process->command][] = [$process, $result];

        return $result;
    }

    /**
     * Checks if the specified command matches the specified pattern.
     */
    public function commandMatchesPattern(string $command, string $pattern): bool
    {
        return Regex\matches($command, $this->buildRegExpFromString($pattern));
    }

    /**
     * Builds a regular expression from a string containing a `*`.
     */
    private function buildRegExpFromString(string $string): string
    {
        return sprintf('/%s/', str_replace('\\*', '.*', preg_quote($string, delimiter: '/')));
    }

    private function createPendingProcess(array|string|PendingProcess $processOrCommand): PendingProcess
    {
        if ($processOrCommand instanceof PendingProcess) {
            return $processOrCommand;
        }

        return new PendingProcess(command: $processOrCommand);
    }
}
