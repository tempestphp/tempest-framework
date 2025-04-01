<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Mappers;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectA;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithJsonSerialize;

use function Tempest\map;

/**
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
}
