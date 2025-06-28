<?php

namespace Tempest\Process\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Process\GenericProcessExecutor;
use Tempest\Process\InvokedProcessPool;
use Tempest\Process\PendingProcess;
use Tempest\Process\Pool;

final class PoolTest extends TestCase
{
    public function test_pool(): void
    {
        $executor = new GenericProcessExecutor();
        $pool = $executor->pool([
            'echo hello',
            'echo world',
        ]);

        $this->assertInstanceOf(Pool::class, $pool);
        $this->assertCount(2, $pool->processes());

        // quick immutability check
        $pool->processes()->add(new PendingProcess('echo foo'));
        $this->assertCount(2, $pool->processes());

        $invoked = $pool->start();

        $this->assertInstanceOf(InvokedProcessPool::class, $invoked);
        $this->assertSame(2, $invoked->count());

        $results = $invoked->wait();

        $this->assertCount(2, $results);
        $this->assertStringEqualsStringIgnoringLineEndings("hello\n", $results[0]->output);
        $this->assertStringEqualsStringIgnoringLineEndings("world\n", $results[1]->output);
    }

    public function test_concurrently(): void
    {
        $executor = new GenericProcessExecutor();
        $results = $executor->concurrently([
            'echo hello',
            'echo world',
        ]);

        $this->assertCount(2, $results);
        $this->assertStringEqualsStringIgnoringLineEndings("hello\n", $results[0]->output);
        $this->assertStringEqualsStringIgnoringLineEndings("world\n", $results[1]->output);
    }

    public function test_concurrently_deconstruct(): void
    {
        $executor = new GenericProcessExecutor();
        [$hello, $world] = $executor->concurrently([
            'echo hello',
            'echo world',
        ]);

        $this->assertStringEqualsStringIgnoringLineEndings("hello\n", $hello->output);
        $this->assertStringEqualsStringIgnoringLineEndings("world\n", $world->output);
    }
}
