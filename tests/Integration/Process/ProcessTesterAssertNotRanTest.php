<?php

namespace Tests\Tempest\Integration\Process;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\ExpectationFailedException;
use Tempest\Process\PendingProcess;
use Tempest\Process\ProcessExecutor;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class ProcessTesterAssertNotRanTest extends FrameworkIntegrationTestCase
{
    private ProcessExecutor $executor {
        get => $this->container->get(ProcessExecutor::class);
    }

    #[Test]
    public function succeeds_when_command_is_not_ran(): void
    {
        $this->process->recordProcessExecutions();
        $this->process->assertCommandDidNotRun('echo "hello"');
    }

    #[Test]
    public function succeeds_with_callback_when_no_command_ran(): void
    {
        $this->process->recordProcessExecutions();
        $this->process->assertCommandDidNotRun(function (): void {});
    }

    #[Test]
    public function succeeds_with_callback_when_other_commands_ran(): void
    {
        $this->process->mockProcessResult('echo *', 'hello');
        $this->executor->run('echo "hello"');

        $this->process->assertCommandDidNotRun(
            // this returns false, so expectation succeeds
            fn (PendingProcess $process) => $process->command === 'echo "world"',
        );
    }

    #[Test]
    public function fails_with_callback_when_returning_false(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Callback for command "echo "hello"" returned true.');

        $this->process->mockProcessResult('echo *', 'hello');
        $this->executor->run('echo "hello"');

        $this->process->assertCommandDidNotRun(
            // this returns true, so expectation fails
            fn (PendingProcess $process) => $process->command === 'echo "hello"',
        );
    }
}
