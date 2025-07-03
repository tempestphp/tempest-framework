<?php

namespace Tests\Tempest\Integration\Process;

use PHPUnit\Framework\ExpectationFailedException;
use Tempest\Process\PendingProcess;
use Tempest\Process\ProcessExecutor;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class ProcessTesterAssertNotRanTest extends FrameworkIntegrationTestCase
{
    private ProcessExecutor $executor {
        get => $this->container->get(ProcessExecutor::class);
    }

    public function test_succeeds_when_command_is_not_ran(): void
    {
        $this->process->recordProcessExecutions();
        $this->process->assertCommandDidNotRun('echo "hello"');
    }

    public function test_succeeds_with_callback_when_no_command_ran(): void
    {
        $this->process->recordProcessExecutions();
        $this->process->assertCommandDidNotRun(function (): void {});
    }

    public function test_succeeds_with_callback_when_other_commands_ran(): void
    {
        $this->process->mockProcess('echo *', 'hello');
        $this->executor->run('echo "hello"');

        $this->process->assertCommandDidNotRun(function (PendingProcess $process) {
            // this returns false, so expectation succeeds
            return $process->command === 'echo "world"';
        });
    }

    public function test_fails_with_callback_when_returning_false(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Callback for command "echo "hello"" returned true.');

        $this->process->mockProcess('echo *', 'hello');
        $this->executor->run('echo "hello"');

        $this->process->assertCommandDidNotRun(function (PendingProcess $process) {
            // this returns true, so expectation fails
            return $process->command === 'echo "hello"';
        });
    }
}
