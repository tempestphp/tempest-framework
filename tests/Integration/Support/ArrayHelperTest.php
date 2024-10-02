<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Support;

use function Tempest\Support\arr;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Support\Fixtures\TestObject;

/**
 * @internal
 */
final class ArrayHelperTest extends FrameworkIntegrationTestCase
{
    public function test_map_to(): void
    {
        $array = arr([['name' => 'test']])->mapTo(TestObject::class);

        $this->assertInstanceOf(TestObject::class, $array[0]);
    }
}
