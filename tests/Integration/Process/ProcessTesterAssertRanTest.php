<?php

namespace Tests\Tempest\Integration\Process;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\ExpectationFailedException;
use Tempest\Process\PendingProcess;
use Tempest\Process\ProcessExecutor;
use Tempest\Process\ProcessResult;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class ProcessTesterAssertRanTest extends FrameworkIntegrationTestCase
{
    private ProcessExecutor $executor {
        get => $this->container->get(ProcessExecutor::class);
    }

    #[TestWith(['*'])]
    #[TestWith(['echo *'])]
    #[TestWith(['echo "hello"'])]
    public function test_expectation_succeeds_when_command_is_ran(string $pattern): void
    {
        $this->process->registerProcessResult('echo *', "hello\n");
        $this->executor->run('echo "hello"');
        $this->process->assertCommandRan($pattern);
    }

    public function test_expectation_succeeds_when_command_is_ran_and_callback_returns_true(): void
    {
        $this->process->registerProcessResult('echo *', "hello\n");
        $this->executor->run('echo "hello"');
        $this->process->assertCommandRan('echo *', function (ProcessResult $result) {
            return $result->output === "hello\n";
        });
    }

    public function test_expectation_fails_when_specified_command_is_not_ran(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Expected process with command "not-ran" to be executed, but it was not.');

        $this->process->registerProcessResult('echo *', "hello\n");
        $this->executor->run('echo "hello"');
        $this->process->assertCommandRan('not-ran');
    }

    public function test_expectation_fails_when_command_is_ran_and_callback_returns_false(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Callback for command "echo "hello"" returned false.');

        $this->process->registerProcessResult('echo *', "hello\n");
        $this->executor->run('echo "hello"');
        $this->process->assertCommandRan('echo *', function (ProcessResult $result) {
            return $result->output !== "hello\n";
        });
    }

    public function test_expectation_succeeds_when_callback_returns_nothing(): void
    {
        $this->process->registerProcessResult('echo *', "hello\n");
        $this->executor->run('echo "hello"');
        $this->process->assertCommandRan('echo *', function (): void {});
    }

    public function test_expectation_succeeds_when_callback_returns_true(): void
    {
        $this->process->registerProcessResult('echo *', "hello\n");
        $this->executor->run('echo "hello"');

        $this->process->assertRan(function (PendingProcess $process): bool {
            return $process->command === 'echo "hello"';
        });
    }

    public function test_returning_false_from_callback_fails_expectation(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Callback for command "echo "hello"" returned false.');

        $this->process->registerProcessResult('echo *', "hello\n");
        $this->executor->run('echo "hello"');

        $this->process->assertRan(function (PendingProcess $_process): bool {
            return false;
        });
    }

    public function test_returning_true_from_callback_skips_other_iterations(): void
    {
        $this->process->registerProcessResult('echo *', "hello\n");
        $this->executor->run('echo "hello"');
        $this->executor->run('echo "world"');

        $this->process->assertRan(function (PendingProcess $process): bool {
            if ($process->command === 'echo "hello"') {
                return true;
            }

            throw new ExpectationFailedException('This should not be reached.');
        });
    }

    public function test_never_returning_fails_expectation(): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Could not find a matching command for the provided callback.');

        $this->process->registerProcessResult('echo *', "hello\n");
        $this->executor->run('echo "hello"');

        $this->process->assertRan(function (PendingProcess $_process): void {
            // This callback never returns.
        });
    }
}
