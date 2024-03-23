<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Collection\Generic;

use PHPUnit\Framework\TestCase;
use Tempest\Collection\Generic\GenericStack;

/**
 * @internal
 * @small
 */
class GenericStackTest extends TestCase
{
    public function test_pushing_an_item(): void
    {
        $stack = new GenericStack();

        $stack->push('value1');

        $this->assertEqualsCanonicalizing(['value1'], $stack->toArray());
    }

    public function test_popping_an_item(): void
    {
        $stack = new GenericStack([
            'value1',
            'value2',
        ]);

        $this->assertSame('value2', $stack->pop());
        $this->assertSame('value1', $stack->pop());
    }

    public function test_peeking_at_the_next_item(): void
    {
        $stack = new GenericStack([
            'value1',
            'value2',
        ]);

        $this->assertSame('value2', $stack->peek());
        $this->assertSame('value2', $stack->pop());
    }

    public function test_checking_if_stack_contains_item(): void
    {
        $stack = new GenericStack([
            'value1',
        ]);

        $this->assertTrue($stack->contains('value1'));
        $this->assertFalse($stack->contains('value2'));
    }

    public function test_cloning_a_stack(): void
    {
        $stack1 = new GenericStack(['value1']);
        $stack2 = $stack1->clone();

        $this->assertNotSame($stack1, $stack2);
        $this->assertEquals($stack1, $stack2);
    }
}
