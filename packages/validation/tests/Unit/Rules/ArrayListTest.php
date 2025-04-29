<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Unit\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\ArrayList;

/**
 * @internal
 */
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
