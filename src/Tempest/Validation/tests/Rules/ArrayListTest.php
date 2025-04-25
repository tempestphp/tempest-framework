<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\ArrayList;

/**
 * @internal
 */
#[CoversClass(ArrayList::class)]
final class ArrayListTest extends TestCase
{
    public function test_array_list(): void
    {
        $rule = new ArrayList();

        $this->assertFalse($rule->isValid(['foo' => 'bar']));
        $this->assertTrue($rule->isValid([]));
        $this->assertTrue($rule->isValid(['a', 'b', 'c']));
        $this->assertFalse($rule->isValid([0 => 'a', 1 => 'b', 3 => 'c']));
        $this->assertSame('Value must be a list', $rule->message());
    }
}
