<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Mappers;

use Tempest\Drift\FrameworkIntegrationTestCase;
use Tempest\Mapper\Tests\Integration\Fixtures\ObjectA;
use Tempest\Mapper\Tests\Integration\Fixtures\ObjectWithJsonSerialize;
use Tempest\Mapper\Tests\Integration\Fixtures\ObjectWithNullableProperties;

use function Tempest\map;

/**
 *
 * @internal
 */
final class ObjectToArrayMapperTestCase extends FrameworkIntegrationTestCase
{
    public function test_object_to_array(): void
    {
        $array = map(new ObjectA('a', 'b'))->toArray();

        $this->assertSame(['a' => 'a', 'b' => 'b'], $array);
    }

    public function test_custom_to_array(): void
    {
        $array = map(new ObjectWithJsonSerialize('a', 'b'))->toArray();

        $this->assertSame(['c' => 'a', 'd' => 'b'], $array);
    }

    public function test_object_with_nullable_properties_to_array(): void
    {
        $object = new ObjectWithNullableProperties(a: 'a', b: 3.1416, c: null);
        $array = map($object)->toArray();

        $this->assertSame(
            [
                'a' => 'a',
                'b' => '3.1416',
                'c' => null,
            ],
            $array,
        );
    }
}
