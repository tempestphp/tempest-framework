<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\TestCase;
use Tempest\Validation\Rules\IsArrayList;

/**
 * @internal
 */
final class IsArrayListTest extends TestCase
{
    public function test_array_list(): void
    {
        $rule = new IsArrayList();

        $this->assertFalse($rule->isValid(['foo' => 'bar']));
        $this->assertTrue($rule->isValid([]));
        $this->assertTrue($rule->isValid(['a', 'b', 'c']));
        $this->assertFalse($rule->isValid([0 => 'a', 1 => 'b', 3 => 'c']));
    }
}
