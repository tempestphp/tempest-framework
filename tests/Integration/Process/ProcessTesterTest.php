<?php

namespace Tests\Tempest\Integration\Process;

use PHPUnit\Framework\ExpectationFailedException;
use Tempest\Process\PendingProcess;
use Tempest\Process\ProcessExecutor;
use Tempest\Process\ProcessResult;
use Tempest\Process\Testing\ProcessExecutionWasForbidden;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class ProcessTesterTest extends FrameworkIntegrationTestCase
{
    private ProcessExecutor $executor {
        get => $this->container->get(ProcessExecutor::class);
    }

    public function test_prevents_execution_by_default(): void
    {
        $this->expectException(ProcessExecutionWasForbidden::class);
        $this->expectExceptionMessage('Process `echo "Hello"` is being executed without a registered process result.');

        $this->process->preventRunningActualProcesses();
        $this->executor->run('echo "Hello"');
    }

    public function test_that_recording_must_be_enabled_to_perform_assertions(): void
    {
        try {
            $this->process->assertCommandRan('echo "hello"');
        } catch (ExpectationFailedException $expectationFailedException) {
            $this->assertStringContainsString('Process testing is not set up', $expectationFailedException->getMessage());
        }

        try {
            $this->process->assertCommandDidNotRun('echo "hello"');
        } catch (ExpectationFailedException $expectationFailedException) {
            $this->assertStringContainsString('Process testing is not set up', $expectationFailedException->getMessage());
        }
    }

    public function test_registering_result_allows_assertions(): void
    {
        $this->process->mockProcessResult('echo *', 'Hello');
        $this->process->assertCommandDidNotRun('echo *');
    }

    public function test_allowing_actual_executions_allows_assertions(): void
    {
        $this->process->allowRunningActualProcesses();
        $this->process->assertCommandDidNotRun('echo *');
    }

    public function test_preventing_actual_executions_allows_assertions(): void
    {
        $this->process->preventRunningActualProcesses();
        $this->process->assertCommandDidNotRun('echo *');
    }

    public function test_recording_executions_allows_assertions(): void
    {
        $this->process->recordProcessExecutions();
        $this->process->assertCommandDidNotRun('echo *');
    }

    public function test_assert_nothing_ran(): void
    {
        $this->process->recordProcessExecutions();
        $this->process->assertNothingRan();
    }

    public function test_assert_nothing_ran_failure(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Expected no processes to be executed, but some were.');

        $this->process->mockProcessResult('echo *', 'hello');
        $this->executor->run('echo "hello"');
        $this->process->assertNothingRan();
    }

    public function test_assert_ran_times_with_string(): void
    {
        $this->process->mockProcessResult('echo *', 'hello');
        $this->executor->run('echo "hello"');
        $this->executor->run('echo "hello"');

        $this->process->assertRanTimes('echo *', times: 2);
    }

    public function test_assert_ran_times_with_callback(): void
    {
        $this->process->mockProcessResult('echo *', 'hello');
        $this->executor->run('echo "hello"');
        $this->executor->run('echo "hello"');

        $this->process->assertRanTimes(fn (PendingProcess $process) => $process->command === 'echo "hello"', times: 2);
    }

    public function test_assert_ran_times_with_string_failure(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Expected command "echo *" to be executed 1 times, but it was executed 2 times.');

        $this->process->mockProcessResult('echo *', 'hello');
        $this->executor->run('echo "hello"');
        $this->executor->run('echo "hello"');

        $this->process->assertRanTimes('echo *', times: 1);
    }

    public function test_assert_ran_times_with_callback_failure(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Expected command matching callback to be executed 1 times, but it was executed 2 times.');

        $this->process->mockProcessResult('echo *', 'hello');
        $this->executor->run('echo "hello"');
        $this->executor->run('echo "hello"');

        $this->process->assertRanTimes(fn (PendingProcess $process) => $process->command === 'echo "hello"', times: 1);
    }

    public function test_assert_ran_times_with_unrelated_callback(): void
    {
        $this->process->mockProcessResult('echo *', 'hello');
        $this->executor->run('echo "hello"');
        $this->executor->run('echo "hello"');

        $this->process->assertRanTimes('echo *', times: 2);
        $this->process->assertRanTimes(fn (PendingProcess $process) => $process->command === 'echo "world"', times: 0);
    }

    public function test_register_multiple_process_results(): void
    {
        $this->process->mockProcessResults([
            'echo "hello"' => 'Hello',
            'echo "world"' => new ProcessResult(exitCode: 0, output: 'World', errorOutput: ''),
        ]);

        $this->executor->run('echo "hello"');
        $this->executor->run('echo "world"');

        $this->process->assertCommandRan('echo "hello"');
        $this->process->assertCommandRan('echo "world"');
    }
}
