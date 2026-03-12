<?php

namespace Tempest\Process\Testing;

use Closure;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Tempest\Container\Container;
use Tempest\Container\Singleton;
use Tempest\Process\GenericProcessExecutor;
use Tempest\Process\PendingProcess;
use Tempest\Process\ProcessExecutor;
use Tempest\Process\ProcessResult;
use Tempest\Support\Arr;

#[Singleton]
final class ProcessTester
{
    private(set) ?TestingProcessExecutor $executor = null;

    private bool $allowRunningActualProcesses = false;

    public function __construct(
        private readonly Container $container,
    ) {}

    /**
     * Records process executions for assertions.
     */
    public function recordProcessExecutions(): void
    {
        $this->executor ??= new TestingProcessExecutor(
            executor: new GenericProcessExecutor(),
            mocks: [],
            allowRunningActualProcesses: $this->allowRunningActualProcesses,
        );

        $this->container->singleton(ProcessExecutor::class, $this->executor);
    }

    /**
     * Sets up the specified command or pattern to return the specified result. The command accepts `*` as a placeholder.
     */
    public function mockProcessResult(string $command = '*', string|ProcessResult|InvokedProcessDescription $result = ''): self
    {
        $this->recordProcessExecutions();

        $this->executor->mocks[$command] = $result;

        return $this;
    }

    /**
     * Sets up the specified commands or patterns to return the specified results.
     *
     * @param array<string,string|ProcessResult|InvokedProcessDescription> $results
     */
    public function mockProcessResults(array $results): self
    {
        $this->recordProcessExecutions();

        foreach ($results as $command => $result) {
            $this->executor->mocks[$command] = $result;
        }

        return $this;
    }

    /**
     * Allows processes to be executed when they don't have a registered result.
     */
    public function allowRunningActualProcesses(): void
    {
        $this->allowRunningActualProcesses = true;

        if ($this->executor) {
            $this->executor->allowRunningActualProcesses = true;
        } else {
            $this->recordProcessExecutions();
        }
    }

    /**
     * Prevents processes from actually running when they don't have a registered result.
     */
    public function preventRunningActualProcesses(): void
    {
        $this->allowRunningActualProcesses = false;

        if ($this->executor) {
            $this->executor->allowRunningActualProcesses = false;
        } else {
            $this->recordProcessExecutions();
        }
    }

    /**
     * Completely disables process execution. This forces developers to register process expectations, while disabling actually running processes in tests. To allow running processes, call `allowRunningActualProcesses()`.
     */
    public function disableProcessExecution(): void
    {
        $this->container->singleton(ProcessExecutor::class, new RestrictedProcessExecutor());
    }

    /**
     * Stops the process and dumps the recorded process executions.
     */
    public function debugExecutedProcesses(): never
    {
        $this->ensureTestingSetUp();

        throw new \RuntimeException(var_export($this->executor->executions, true));
    }

    /**
     * Describes how an asynchronous process is expected to behave.
     */
    public function describe(): InvokedProcessDescription
    {
        return new InvokedProcessDescription();
    }

    /**
     * Asserts that the given command has been ran. Alternatively, a callback may be passed.
     *
     * @param null|Closure(): mixed|Closure(ProcessResult): mixed|Closure(ProcessResult, PendingProcess): mixed $callback
     */
    public function assertCommandRan(string $command, ?\Closure $callback = null): self
    {
        $this->ensureTestingSetUp();

        $executions = $this->findExecutionsByPattern($command);

        Assert::assertNotEmpty(
            actual: $executions,
            message: sprintf('Expected process with command "%s" to be executed, but it was not.', $command),
        );

        if ($callback instanceof Closure) {
            foreach ($executions as [$process, $result]) {
                $assertion = $callback($result, $process);

                if ($assertion === true) {
                    Assert::assertTrue(true);

                    return $this;
                }

                if ($assertion === false) {
                    throw new ExpectationFailedException(sprintf('Callback for command "%s" returned false.', $process->command));
                }
            }
        }

        return $this;
    }

    /**
     * Asserts that the a command has been ran by the given callback.
     *
     * @param Closure(PendingProcess): mixed|Closure(PendingProcess, ProcessResult): mixed $callback
     */
    public function assertRan(\Closure $callback): self
    {
        $this->ensureTestingSetUp();

        foreach ($this->executor->executions as $executions) {
            foreach ($executions as [$process, $result]) {
                $assertion = $callback($process, $result);

                if ($assertion === true) {
                    Assert::assertTrue(true);

                    return $this;
                }

                if ($assertion === false) {
                    throw new ExpectationFailedException(sprintf('Callback for command "%s" returned false.', $process->command));
                }
            }
        }

        throw new ExpectationFailedException('Could not find a matching command for the provided callback.');
    }

    /**
     * Asserts that the given command did not run. Alternatively, a callback may be passed.
     *
     * @param string|Closure(): mixed|Closure(PendingProcess): mixed|Closure(PendingProcess, ProcessResult): mixed $command
     */
    public function assertCommandDidNotRun(string|\Closure $command): self
    {
        $this->ensureTestingSetUp();

        if ($command instanceof Closure) {
            foreach ($this->executor->executions as $executions) {
                foreach ($executions as [$process, $result]) {
                    $assertion = $command($process, $result);

                    if ($assertion === true) {
                        throw new ExpectationFailedException(sprintf('Callback for command "%s" returned true.', $process->command));
                    }
                }
            }

            Assert::assertTrue(true);

            return $this;
        }

        $executions = $this->findExecutionsByPattern($command);

        Assert::assertEmpty(
            actual: $executions,
            message: sprintf('Expected process with command "%s" to not be ran, but it was.', $command),
        );

        return $this;
    }

    /**
     * Asserts that no processes have been executed.
     */
    public function assertNothingRan(): self
    {
        $this->ensureTestingSetUp();

        Assert::assertEmpty(
            actual: $this->executor->executions,
            message: 'Expected no processes to be executed, but some were.',
        );

        return $this;
    }

    /**
     * Asserts that the given command has ran the specified amount of times.
     *
     * @param string|\Closure(PendingProcess,ProcessResult):bool $command
     */
    public function assertRanTimes(string|\Closure $command, int $times): self
    {
        $this->ensureTestingSetUp();

        if ($command instanceof \Closure) {
            $count = 0;
            foreach ($this->executor->executions as $executions) {
                foreach ($executions as [$process, $result]) {
                    if ($command($process, $result) === true) {
                        $count++;
                    }
                }
            }
        } else { // @mago-expects linter:no-else-clause
            $count = count($this->findExecutionsByPattern($command));
        }

        Assert::assertSame(
            expected: $times,
            actual: $count,
            message: $command instanceof Closure
                ? sprintf('Expected command matching callback to be executed %d times, but it was executed %d times.', $times, $count)
                : sprintf('Expected command "%s" to be executed %d times, but it was executed %d times.', $command, $times, $count),
        );

        return $this;
    }

    /** @return array<array{PendingProcess,ProcessResult}> */
    private function findExecutionsByPattern(string $pattern): array
    {
        $this->ensureTestingSetUp();

        $executions = [];

        foreach ($this->executor->executions as $command => $commandExecutions) {
            if ($this->executor->commandMatchesPattern($command, $pattern)) {
                $executions[] = $commandExecutions;
            }
        }

        return Arr\flatten($executions, depth: 1);
    }

    private function ensureTestingSetUp(): void
    {
        if (is_null($this->executor)) {
            throw new ExpectationFailedException(
                'Process testing is not set up. Please call `$this->process->recordProcessExecutions()` or `$this->process->registerProcessResult()` before running assertions, or call `$this->process->allowRunningActualProcesses()` to allow actual processes to run.',
            );
        }
    }
}
