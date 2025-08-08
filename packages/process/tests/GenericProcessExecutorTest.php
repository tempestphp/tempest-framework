<?php

declare(strict_types=1);

namespace Tempest\Process\Tests;

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
    public function test_run_string(): void
    {
        $executor = new GenericProcessExecutor();
        $result = $executor->run('echo hello');

        $this->assertStringEqualsStringIgnoringLineEndings("hello\n", $result->output);
        $this->assertSame('', $result->errorOutput);
        $this->assertSame(0, $result->exitCode);
    }

    public function test_run(): void
    {
        $executor = new GenericProcessExecutor();
        $result = $executor->run(new PendingProcess('echo hello'));

        $this->assertStringEqualsStringIgnoringLineEndings("hello\n", $result->output);
        $this->assertSame('', $result->errorOutput);
        $this->assertSame(0, $result->exitCode);
    }

    public function test_start(): void
    {
        $executor = new GenericProcessExecutor();
        $process = $executor->start('echo hello');

        $this->assertIsInt($process->pid);
        $this->assertTrue($process->running);
        $this->assertSame('', $process->output);
        $this->assertSame('', $process->errorOutput);

        $result = $process->wait();

        $this->assertNull($process->pid);
        $this->assertFalse($process->running);
        $this->assertStringEqualsStringIgnoringLineEndings("hello\n", $process->output);
        $this->assertSame('', $process->errorOutput);

        $this->assertStringEqualsStringIgnoringLineEndings("hello\n", $result->output);
        $this->assertSame('', $result->errorOutput);
        $this->assertSame(0, $result->exitCode);
    }

    public function test_wait_callback(): void
    {
        $executor = new GenericProcessExecutor();
        $process = $executor->start('echo hello');

        $output = [];
        $process->wait(function (OutputChannel $channel, string $data) use (&$output) {
            $output[$channel->value] ??= [];
            $output[$channel->value][] = $data;
        });

        $this->assertCount(1, $output);
        $this->assertArrayHasKey(OutputChannel::OUTPUT->value, $output);
        $this->assertStringEqualsStringIgnoringLineEndings("hello\n", $output[OutputChannel::OUTPUT->value][0]);
    }

    public function test_run_timeout(): void
    {
        $this->skipOnWindows();
        $this->expectException(ProcessHasTimedOut::class);

        $executor = new GenericProcessExecutor();
        $executor->run(new PendingProcess('sleep .2', timeout: Duration::milliseconds(100)));
    }

    public function test_run_idle_timeout(): void
    {
        $this->skipOnWindows();
        $this->expectException(ProcessHasTimedOut::class);

        $executor = new GenericProcessExecutor();
        $executor->run(new PendingProcess('sleep .2', idleTimeout: Duration::milliseconds(100)));
    }

    public function test_run_input(): void
    {
        $executor = new GenericProcessExecutor();
        $result = $executor->run(new PendingProcess('cat', input: 'hello'));

        $this->assertSame('hello', $result->output);
        $this->assertSame('', $result->errorOutput);
        $this->assertSame(0, $result->exitCode);
    }

    public function test_run_with_error_output(): void
    {
        $this->skipOnWindows();

        $executor = new GenericProcessExecutor();
        $result = $executor->run('echo hello >&2');

        $this->assertSame('', $result->output);
        $this->assertStringEqualsStringIgnoringLineEndings("hello\n", $result->errorOutput);
        $this->assertSame(0, $result->exitCode);
    }

    public function test_run_with_exit_code(): void
    {
        $this->skipOnWindows();

        $executor = new GenericProcessExecutor();
        $result = $executor->run('exit 42');

        $this->assertSame('', $result->output);
        $this->assertSame('', $result->errorOutput);
        $this->assertSame(42, $result->exitCode);
    }

    public function test_run_with_env(): void
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
