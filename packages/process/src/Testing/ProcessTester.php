<?php

namespace Tempest\Process\Testing;

use Closure;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Tempest\Container\Container;
use Tempest\Process\GenericProcessExecutor;
use Tempest\Process\PendingProcess;
use Tempest\Process\ProcessExecutor;
use Tempest\Process\ProcessResult;
use Tempest\Support\Arr;

final class ProcessTester
{
    private ?TestingProcessExecutor $executor = null;

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
            registeredProcessResult: [],
            allowRunningActualProcesses: $this->allowRunningActualProcesses,
        );

        $this->container->singleton(ProcessExecutor::class, $this->executor);
    }

    /**
     * Sets up the specified command or pattern to return the specified result.
     */
    public function registerProcessResult(string $command, string|ProcessResult $result): self
    {
        $this->recordProcessExecutions();

        $this->executor->registeredProcessResult[$command] = $result;

        return $this;
    }

    /**
     * Sets up the specified commands or patterns to return the specified results.
     *
     * @var array<string,string|ProcessResult> $results
     */
    public function registerProcessResults(array $results): self
    {
        $this->recordProcessExecutions();

        foreach ($results as $command => $result) {
            $this->executor->registeredProcessResult[$command] = $result;
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
     * Describes how an asynchronous process is expected to behave.
     */
    public function describe(): InvokedProcessDescription
    {
        return new InvokedProcessDescription();
    }

    /**
     * Asserts that the given command has been ran. Alternatively, a callback may be passed.
     *
     * @param (\Closure(ProcessResult,PendingProcess=):false|void)|string $command
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
     * @param \Closure(PendingProcess,ProcessResult=):false|void $callback
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
     * @param (\Closure(PendingProcess,ProcessResult=):false|void)|string $command
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
        } else {
            $count = count($this->findExecutionsByPattern($command));
        }

        Assert::assertSame(
            expected: $times,
            actual: $count,
            message: ($command instanceof Closure)
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
