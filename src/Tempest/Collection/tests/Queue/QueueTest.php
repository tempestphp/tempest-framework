<?php

namespace Tempest\Collection\Tests\Queue;

use PHPUnit\Framework\TestCase;
use Tempest\Collection\Queue\Queue;

class QueueTest extends TestCase
{
    public function test_enqueuing_an_item(): void
    {
        $queue = new Queue();

        $queue->enqueue('value1');

        $this->assertEqualsCanonicalizing([
            'value1',
        ], $queue->toArray());
    }

    public function test_dequeuing_an_item(): void
    {
        $queue = new Queue([
            'value1',
            'value2',
        ]);

        $this->assertSame('value1', $queue->dequeue());
        $this->assertSame('value2', $queue->dequeue());
    }

    public function test_peeking_at_the_next_item(): void
    {
        $queue = new Queue([
            'value1',
            'value2',
        ]);

        $this->assertSame('value1', $queue->peek());
        $this->assertSame('value1', $queue->dequeue());
    }

    public function test_checking_if_queue_contains_item(): void
    {
        $queue = new Queue([
            'value1',
        ]);

        $this->assertTrue($queue->contains('value1'));
        $this->assertFalse($queue->contains('value2'));
    }

    public function test_cloning_a_queue(): void
    {
        $queue1 = new Queue(['value1']);
        $queue2 = $queue1->clone();

        $this->assertNotSame($queue1, $queue2);
        $this->assertEquals($queue1, $queue2);
    }
}