<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Mappers;

use PHPUnit\Framework\Attributes\Test;
use Tempest\DateTime\DateTime;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\NestedObjectWithDate;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectA;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithNestedObjectAndDate;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithScalarValues;

use function Tempest\Mapper\map;

/**
 * @internal
 */
final class ObjectToJsonMapperTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function object_to_json(): void
    {
        $json = map(new ObjectA('a', 'b'))->toJson();

        $this->assertSame('{"a":"a","b":"b"}', $json);
    }

    #[Test]
    public function object_with_scalar_values_to_json(): void
    {
        $json = map(new ObjectWithScalarValues(
            active: true,
            score: 1.5,
            count: 3,
        ))->toJson();

        $this->assertSame(
            [
                'active' => true,
                'score' => 1.5,
                'count' => 3,
            ],
            json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR),
        );
    }

    #[Test]
    public function object_with_single_nested_object_to_json(): void
    {
        $date = DateTime::parse('2026-08-19T12:34:56+00:00');

        $json = map(new ObjectWithNestedObjectAndDate(
            createdAt: $date,
            child: new NestedObjectWithDate($date),
            children: [new NestedObjectWithDate($date)],
        ))->toJson();

        $this->assertSame(
            [
                'createdAt' => '2026-08-19T12:34:56.000Z',
                'child' => [
                    'createdAt' => '2026-08-19T12:34:56.000Z',
                ],
                'children' => [
                    ['createdAt' => '2026-08-19T12:34:56.000Z'],
                ],
            ],
            json_decode($json, associative: true, flags: JSON_THROW_ON_ERROR),
        );
    }
}
