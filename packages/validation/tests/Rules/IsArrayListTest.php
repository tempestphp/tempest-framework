<?php

declare(strict_types=1);

namespace Tempest\Validation\Tests\Rules;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Support\Arr\ImmutableArray;
use Tempest\Support\Arr\MutableArray;
use Tempest\Validation\Rules\IsArrayList;

/**
 * @internal
 */
final class IsArrayListTest extends TestCase
{
    #[DataProvider('provide_array_list_cases')]
    #[Test]
    public function array_list(array $value, bool $expected): void
    {
        $rule = new IsArrayList();

        $this->assertEquals($expected, $rule->isValid($value));
        $this->assertEquals($expected, $rule->isValid(new ImmutableArray($value)));
        $this->assertEquals($expected, $rule->isValid(new MutableArray($value)));
    }

    public static function provide_array_list_cases(): iterable
    {
        return [
            'Should return false for associate array' => [['foo' => 'bar'], false],
            'Should return true for empty array' => [[], true],
            'Should return true for true array list' => [['a', 'b', 'c'], true],
            'Should return false for list with skipped indexed key' => [[0 => 'a', 1 => 'b', 3 => 'c'], false],
        ];
    }
}
