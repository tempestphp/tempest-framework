<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Mapper\Exceptions\DataCouldNotBeMapped;
use Tempest\Mapper\Exceptions\MapperWasMissing;
use Tempest\Mapper\Mappers\ArrayToJsonMapper;
use Tempest\Mapper\Mappers\ArrayToObjectMapper;
use Tempest\Mapper\Mappers\ObjectToArrayMapper;
use Tempest\Mapper\ObjectFactory;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectA;

use function Tempest\Mapper\make;
use function Tempest\Mapper\map;

/**
 * @internal
 */
final class ObjectFactoryTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function single_object(): void
    {
        $factory = $this->container->get(ObjectFactory::class);

        $object = $factory->map(
            ['a' => 'a', 'b' => 'b'],
            ObjectA::class,
        );

        $this->assertEquals('a', $object->a);
        $this->assertEquals('b', $object->b);
    }

    #[Test]
    public function collection(): void
    {
        $factory = $this->container->get(ObjectFactory::class);

        $objects = $factory->collection()->map(
            [['a' => 'a', 'b' => 'b'], ['a' => 'aa', 'b' => 'bb']],
            ObjectA::class,
        );

        $this->assertCount(2, $objects);

        $this->assertEquals('a', $objects[0]->a);
        $this->assertEquals('b', $objects[0]->b);

        $this->assertEquals('aa', $objects[1]->a);
        $this->assertEquals('bb', $objects[1]->b);
    }

    #[Test]
    public function make_function(): void
    {
        $object = make(ObjectA::class)->from(['a' => 'a', 'b' => 'b']);

        $this->assertEquals('a', $object->a);
        $this->assertEquals('b', $object->b);
    }

    #[Test]
    public function map_function(): void
    {
        $object = map(['a' => 'a', 'b' => 'b'])->to(ObjectA::class);

        $this->assertEquals('a', $object->a);
        $this->assertEquals('b', $object->b);
    }

    #[Test]
    public function cannot_map_exception(): void
    {
        $this->expectException(DataCouldNotBeMapped::class);

        map(['a' => 'a', 'b' => 'b'])->to('unknown');
    }

    #[Test]
    public function map_with(): void
    {
        $result = map(['a' => 'a', 'b' => 'b'])->with(
            fn (ArrayToObjectMapper $mapper, mixed $from) => $mapper->map($from, ObjectA::class),
            ObjectToArrayMapper::class,
            ArrayToJsonMapper::class,
        )->do();

        $this->assertSame('{"a":"a","b":"b"}', $result);
    }

    #[Test]
    public function map_do_without_with_throws(): void
    {
        $this->expectException(MapperWasMissing::class);

        map([])->do();
    }

    #[Test]
    public function map_with_to(): void
    {
        $result = map(['a' => 'a', 'b' => 'b'])->with(ArrayToObjectMapper::class)->to(ObjectA::class);

        $this->assertSame('a', $result->a);
        $this->assertSame('b', $result->b);
    }

    #[Test]
    public function map_with_collection_to(): void
    {
        $result = map([
            ['a' => 'a', 'b' => 'b'],
            ['a' => 'c', 'b' => 'd'],
        ])
            ->with(ArrayToObjectMapper::class)
            ->collection()
            ->to(ObjectA::class);

        $this->assertSame('a', $result[0]->a);
        $this->assertSame('b', $result[0]->b);
        $this->assertSame('c', $result[1]->a);
        $this->assertSame('d', $result[1]->b);
    }
}
