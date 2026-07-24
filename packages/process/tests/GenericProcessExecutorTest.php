<?php

declare(strict_types=1);

namespace Tempest\Process\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\DateTime\Duration;
use Tempest\Process\Exceptions\ProcessHasTimedOut;
use Tempest\Process\GenericProcessExecutor;
use Tempest\Process\OutputChannel;
use Tempest\Process\PendingProcess;

/**
 * @internal
 */
final class GenericProcessExecutorTest extends TestCase
{
    #[Test]
    public function run_string(): void
    {
        $executor = new GenericProcessExecutor();
        $result = $executor->run('echo hello');

        $this->assertStringEqualsStringIgnoringLineEndings("hello\n", $result->output);
        $this->assertSame('', $result->errorOutput);
        $this->assertSame(0, $result->exitCode);
    }

    #[Test]
    public function run_pending_process(): void
    {
        $executor = new GenericProcessExecutor();
        $result = $executor->run(new PendingProcess('echo hello'));

        $this->assertStringEqualsStringIgnoringLineEndings("hello\n", $result->output);
        $this->assertSame('', $result->errorOutput);
        $this->assertSame(0, $result->exitCode);
    }

    #[Test]
    public function start(): void
    {
        $executor = new GenericProcessExecutor();
        $process = $executor->start('"' . PHP_BINARY . '" -r "usleep(500000); echo \'hello\';"');

        $this->assertIsInt($process->pid);
        $this->assertTrue($process->running);
        $this->assertSame('', $process->output);
        $this->assertSame('', $process->errorOutput);

        $result = $process->wait();

        $this->assertNull($process->pid);
        $this->assertFalse($process->running);
        $this->assertSame('hello', $process->output);
        $this->assertSame('', $process->errorOutput);

        $this->assertSame('hello', $result->output);
        $this->assertSame('', $result->errorOutput);
        $this->assertSame(0, $result->exitCode);
    }

    #[Test]
    public function wait_callback(): void
    {
        $executor = new GenericProcessExecutor();
        $process = $executor->start('"' . PHP_BINARY . '" -r "usleep(500000); echo \'hello\';"');

        $output = [];
        $process->wait(function (OutputChannel $channel, string $data) use (&$output) {
            $output[$channel->value] ??= [];
            $output[$channel->value][] = $data;
        });

        $this->assertCount(1, $output);
        $this->assertArrayHasKey(OutputChannel::OUTPUT->value, $output);
        $this->assertSame('hello', $output[OutputChannel::OUTPUT->value][0]);
    }

    #[Test]
    public function run_timeout(): void
    {
        $this->skipOnWindows();
        $this->expectException(ProcessHasTimedOut::class);

        $executor = new GenericProcessExecutor();
        $executor->run(new PendingProcess('sleep .2', timeout: Duration::milliseconds(100)));
    }

    #[Test]
    public function run_idle_timeout(): void
    {
        $this->skipOnWindows();
        $this->expectException(ProcessHasTimedOut::class);

        $executor = new GenericProcessExecutor();
        $executor->run(new PendingProcess('sleep .2', idleTimeout: Duration::milliseconds(100)));
    }

    #[Test]
    public function run_input(): void
    {
        $executor = new GenericProcessExecutor();
        $result = $executor->run(new PendingProcess('cat', input: 'hello'));

        $this->assertSame('hello', $result->output);
        $this->assertSame('', $result->errorOutput);
        $this->assertSame(0, $result->exitCode);
    }

    #[Test]
    public function run_with_error_output(): void
    {
        $this->skipOnWindows();

        $executor = new GenericProcessExecutor();
        $result = $executor->run('echo hello >&2');

        $this->assertSame('', $result->output);
        $this->assertStringEqualsStringIgnoringLineEndings("hello\n", $result->errorOutput);
        $this->assertSame(0, $result->exitCode);
    }

    #[Test]
    public function run_with_exit_code(): void
    {
        $this->skipOnWindows();

        $executor = new GenericProcessExecutor();
        $result = $executor->run('exit 42');

        $this->assertSame('', $result->output);
        $this->assertSame('', $result->errorOutput);
        $this->assertSame(42, $result->exitCode);
    }

    #[Test]
    public function run_with_env(): void
    {
        $this->skipOnWindows();

        $executor = new GenericProcessExecutor();
        $result = $executor->run(new PendingProcess('echo $TEST_ENV', environment: ['TEST_ENV' => 'hello']));

        $this->assertStringEqualsStringIgnoringLineEndings("hello\n", $result->output);
        $this->assertSame('', $result->errorOutput);
        $this->assertSame(0, $result->exitCode);
    }

    private function skipOnWindows(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('This test is not applicable on Windows.');
        }
    }
}
