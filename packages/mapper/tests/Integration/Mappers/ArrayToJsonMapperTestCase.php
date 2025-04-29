<?php

declare(strict_types=1);

namespace Tempest\Mapper\Tests\Integration\Mappers;

use Tempest\Drift\FrameworkIntegrationTestCase;

use function Tempest\map;

/**
 * @internal
 */
final class ArrayToJsonMapperTestCase extends FrameworkIntegrationTestCase
{
    public function test_mapper(): void
    {
        $json = map(['a'])->toJson();

        $this->assertSame('["a"]', $json);
    }
}
