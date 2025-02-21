<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Mappers;

use DateTime;
use DateTimeImmutable;
use Tempest\Mapper\MapTo;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectA;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithBuiltInCasters;
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

    public function test_serializers(): void
    {
        $data = [
            'dateTimeObject' => new DateTimeImmutable('2024-01-01 10:10:10'),
            'dateTimeImmutable' => new DateTimeImmutable('2024-01-01 10:10:10'),
            'dateTime' => new DateTime('2024-01-01 10:10:10'),
            'dateTimeWithFormat' => new DateTime('2024-01-01 10:10:10'),
            'bool' => 'yes',
            'float' => '0.1',
            'int' => '1',
        ];

        $object = map($data)->to(ObjectWithBuiltInCasters::class);

        $array = map($object)->toArray();

        $this->assertSame([
            'dateTimeObject' => '2024-01-01T10:10:10+00:00',
            'dateTimeImmutable' => '2024-01-01T10:10:10+00:00',
            'dateTime' => '2024-01-01T10:10:10+00:00',
            'dateTimeWithFormat' => '01/01/2024 10:10:10',
            'bool' => true,
            'float' => 0.1,
            'int' => 1,
        ], $array);
    }
}
