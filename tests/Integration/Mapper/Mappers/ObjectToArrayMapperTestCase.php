<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Mappers;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectA;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithJsonSerialize;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithNullableProperties;

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
