<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Support;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Support\Fixtures\TestObject;
use function Tempest\Support\arr;

/**
 * @internal
 */
final class ArrayTest extends FrameworkIntegrationTestCase
{
    public function test_map_to(): void
    {
        $array = arr([['name' => 'test']])->mapTo(TestObject::class);

        $this->assertInstanceOf(TestObject::class, $array[0]);
    }

    #[DataProvider('provide_sort_cases')]
    public function test_sort(array $input, array $expected): void
    {
        $array = arr($input)->sort()->toArray();
        $this->assertEquals($expected, $array);
    }

    public static function provide_sort_cases(): iterable
    {
        return [
            'Regular case' => [[3, 1, 4, 1, 5, 9], [1, 1, 3, 4, 5, 9]],
            'Empty array' => [[], []],
            'Single element' => [[1], [1]],
            'All elements the same' => [[2, 2, 2], [2, 2, 2]],
            'Reverse order' => [[5, 4, 3, 2, 1], [1, 2, 3, 4, 5]],
            'Negative numbers' => [[-1, -3, -2, 0], [-3, -2, -1, 0]],
            'Mixed positive and negative' => [[3, -1, 4, 1, -5, 9], [-5, -1, 1, 3, 4, 9]],
            'Strings' => [['apple', 'orange', 'banana'], ['apple', 'banana', 'orange']],
            'Large array' => [range(1000, 1, -1), range(1, 1000)],
        ];
    }
}
