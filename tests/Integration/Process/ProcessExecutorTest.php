<?php

namespace Tests\Tempest\Integration\Process;

use PHPUnit\Framework\ExpectationFailedException;
use Tempest\Process\InvokedProcess;
use Tempest\Process\ProcessExecutor;
use Tempest\Process\ProcessResult;
use Tempest\Process\Testing\InvokedProcessDescription;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class ProcessExecutorTest extends FrameworkIntegrationTestCase
{
    private ProcessExecutor $executor {
        get => $this->container->get(ProcessExecutor::class);
    }

    public function test_run(): void
    {
        $this->process->registerProcessResult('echo *', "Hello\n");

        $result = $this->executor->run('echo "Hello"');

        $this->assertSame("Hello\n", $result->output);
        $this->process->assertCommandRan('echo "Hello"');
        $this->process->assertRanTimes('echo *', times: 1);
    }

    public function test_describe_and_assert_async_process(): void
    {
        $this->process->registerProcessResults([
            'echo *' => $this->process
                ->describe()
                ->withOutput('hello')
                ->withOutput('world')
                ->withIterations(2)
                ->withExitCode(0),
        ]);

        $process = $this->executor->start('echo "Hello"');

        $output = '';
        while ($process->running) {
            $output = $process->output;
        }

        $result = $process->wait();

        $this->assertSame("hello\nworld\n", $output);
        $this->assertSame("hello\nworld\n", $result->output);

        $this->process->assertRanTimes('echo *', times: 1);
        $this->process->assertCommandRan('echo "Hello"', function (ProcessResult $result): void {
            $this->assertSame(0, $result->exitCode);
            $this->assertSame("hello\nworld\n", $result->output);
            $this->assertEmpty($result->errorOutput);
        });
    }

    public function test_concurrently(): void
    {
        $this->process->registerProcessResults([
            'echo "hello"' => $this->process->describe()->withOutput('hello'),
            'echo "world"' => $this->process->describe()->withOutput('world'),
        ]);

        [$hello, $world] = $this->executor->concurrently([
            'echo "hello"',
            'echo "world"',
        ]);

        $this->assertSame("hello\n", $hello->output);
        $this->assertSame("world\n", $world->output);

        $this->process->assertRanTimes('echo *', times: 2);
        $this->process->assertRanTimes('echo "hello"', times: 1);
        $this->process->assertRanTimes('echo "world"', times: 1);
    }

    public function test_pool(): void
    {
        $this->process->registerProcessResults([
            'echo "hello"' => $this->process->describe()->withOutput('hello'),
            'echo "world"' => $this->process->describe()->withOutput('world'),
        ]);

        $pool = $this->executor->pool([
            'echo "hello"',
            'echo "world"',
        ]);

        $invocation = $pool->start();

        $output = '';
        while ($invocation->running->isNotEmpty()) {
            $output = $invocation
                ->all
                ->map(fn (InvokedProcess $process) => $process->output)
                ->toArray();
        }

        $this->assertSame(["hello\n", "world\n"], $output);
    }
}
