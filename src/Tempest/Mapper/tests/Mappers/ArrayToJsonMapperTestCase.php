<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Mappers;

use function Tempest\map;
use Tempest\Mapper\MapTo;
use Tests\Tempest\Integration\IntegrationTestCase;

/**
 * @internal
 * @small
 */
final class ArrayToJsonMapperTestCase extends IntegrationTestCase
{
    public function test_mapper(): void
    {
        $json = map(['a'])->to(MapTo::JSON);

        $this->assertSame('["a"]', $json);
    }
}
