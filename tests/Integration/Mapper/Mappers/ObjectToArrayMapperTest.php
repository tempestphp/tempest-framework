<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Mappers;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tempest\DateTime\DateTime;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\MapperConfig;
use Tempest\Support\Json\Exception\JsonCouldNotBeEncoded;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\NestedObjectWithDate;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectA;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithJsonSerialize;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithNestedObjectAndDate;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithNullableProperties;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithScalarValues;
use Tests\Tempest\Integration\Mapper\Fixtures\ParentObject;

use function Tempest\Mapper\map;

/**
 *
 * @internal
 */
final class ObjectToArrayMapperTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function object_to_array(): void
    {
        $array = map(new ObjectA('a', 'b'))->toArray();

        $this->assertSame(['a' => 'a', 'b' => 'b'], $array);
    }

    #[Test]
    public function custom_to_array(): void
    {
        $array = map(new ObjectWithJsonSerialize('a', 'b'))->toArray();

        $this->assertSame(['c' => 'a', 'd' => 'b'], $array);
    }

    #[Test]
    public function object_with_nullable_properties_to_array(): void
    {
        $object = new ObjectWithNullableProperties(a: 'a', b: 3.1416, c: null);
        $array = map($object)->toArray();

        $this->assertSame(
            [
                'a' => 'a',
                'b' => 3.1416,
                'c' => null,
            ],
            $array,
        );
    }

    #[Test]
    public function object_with_scalar_values_to_array(): void
    {
        $array = map(new ObjectWithScalarValues(
            active: true,
            score: 1.5,
            count: 3,
        ))->toArray();

        $this->assertSame(
            [
                'active' => true,
                'score' => 1.5,
                'count' => 3,
            ],
            $array,
        );
    }

    #[Test]
    public function object_with_single_nested_object_to_array(): void
    {
        $date = DateTime::parse('2026-08-19T12:34:56+00:00');

        $array = map(new ObjectWithNestedObjectAndDate(
            createdAt: $date,
            child: new NestedObjectWithDate($date),
            children: [new NestedObjectWithDate($date)],
        ))->toArray();

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
            $array,
        );
    }

    #[Test]
    #[RequiresPhpExtension('pcntl')]
    public function cyclic_nested_objects_fail_instead_of_hanging(): void
    {
        $parent = map([
            'name' => 'parent',
            'child' => ['name' => 'child'],
        ])->to(ParentObject::class);
        $this->assertInstanceOf(ParentObject::class, $parent);

        pcntl_async_signals(true);
        pcntl_signal(SIGALRM, static function (): never {
            throw new RuntimeException('Serialization did not terminate');
        });
        pcntl_alarm(2);

        try {
            $this->expectException(JsonCouldNotBeEncoded::class);

            map($parent)->toJson();
        } finally {
            pcntl_alarm(0);
            pcntl_signal(SIGALRM, SIG_DFL);
        }
    }

    #[Test]
    public function nested_objects_do_not_resolve_unused_mappers(): void
    {
        map(new ObjectA('a', 'b'))->toArray();

        $this->container
            ->get(MapperConfig::class)
            ->addMapper(MapperResolutionProbe::class);

        MapperResolutionProbe::$constructions = 0;

        map(new ObjectWithMapperResolutionChildren([
            new MapperResolutionChild('a'),
            new MapperResolutionChild('b'),
            new MapperResolutionChild('c'),
        ]))->toArray();

        $this->assertSame(0, MapperResolutionProbe::$constructions);
    }
}

final readonly class ObjectWithMapperResolutionChildren
{
    public function __construct(
        /** @var \Tests\Tempest\Integration\Mapper\Mappers\MapperResolutionChild[] */
        public array $children,
    ) {}
}

final readonly class MapperResolutionChild
{
    public function __construct(
        public string $name,
    ) {}
}

final class MapperResolutionProbe implements Mapper
{
    public static int $constructions = 0;

    public function __construct()
    {
        self::$constructions++;
    }

    public function canMap(mixed $from, mixed $to): bool
    {
        return false;
    }

    public function map(mixed $from, mixed $to): mixed
    {
        return $from;
    }
}
