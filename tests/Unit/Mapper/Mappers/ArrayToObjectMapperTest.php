<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Mapper\Mappers;

use InvalidArgumentException;
use function Tempest\map;
use Tempest\Mapper\Exceptions\MissingValuesException;
use Tests\Tempest\IntegrationTest;
use Tests\Tempest\Unit\Mapper\Fixtures\ObjectA;
use Tests\Tempest\Unit\Mapper\Fixtures\ObjectWithBuiltInCasters;
use Tests\Tempest\Unit\Mapper\Fixtures\ObjectWithDefaultValues;
use Tests\Tempest\Unit\Mapper\Fixtures\ObjectWithDoubleStringCaster;
use Tests\Tempest\Unit\Mapper\Fixtures\ObjectWithMyObject;
use Tests\Tempest\Unit\Mapper\Fixtures\ParentObject;
use Tests\Tempest\Unit\Mapper\Fixtures\ParentWithChildrenObject;

/**
 * @internal
 * @small
 */
class ArrayToObjectMapperTest extends IntegrationTest
{
    public function test_missing_values(): void
    {
        $this->expectException(MissingValuesException::class);

        map([])->to(ObjectA::class);
    }

    public function test_map_to_existing_object(): void
    {
        $object = map(['a' => 'a', 'b' => 'b'])->to(new ObjectA());

        $this->assertSame('a', $object->a);
        $this->assertSame('b', $object->b);
    }

    public function test_caster_on_property(): void
    {
        $object = map(['prop' => 'a'])->to(ObjectWithDoubleStringCaster::class);

        $this->assertSame('aa', $object->prop);
    }

    public function test_caster_on_target_object(): void
    {
        $object = map(['obj' => 'name'])->to(ObjectWithMyObject::class);

        $this->assertSame('name', $object->obj->name);
    }

    public function test_default_values(): void
    {
        $object = map([])->to(ObjectWithDefaultValues::class);

        $this->assertSame('a', $object->a);
        $this->assertSame(null, $object->b);
    }

    public function test_built_in_casters(): void
    {
        $object = map([
            'dateTimeImmutable' => '2024-01-01 10:10:10',
            'dateTime' => '2024-01-01 10:10:10',
            'dateTimeWithFormat' => '01/12/2024 10:10:10',
            'bool' => 'yes',
            'float' => '0.1',
            'int' => '1',
        ])->to(ObjectWithBuiltInCasters::class);

        $this->assertSame('2024-01-01 10:10:10', $object->dateTimeImmutable->format('Y-m-d H:i:s'));
        $this->assertSame('2024-01-01 10:10:10', $object->dateTime->format('Y-m-d H:i:s'));
        $this->assertSame('2024-12-01 10:10:10', $object->dateTimeWithFormat->format('Y-m-d H:i:s'));
        $this->assertSame(true, $object->bool);
        $this->assertSame(0.1, $object->float);
        $this->assertSame(1, $object->int);
    }

    public function test_wrongly_formatted_date(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must be a valid date in the format Y-m-d H:i:s');

        map([
            'dateTimeImmutable' => '01/12/2024',
        ])->to(ObjectWithBuiltInCasters::class);
    }

    public function test_parent_child(): void
    {
        $parent = map(
            [
            'name' => 'a',
            'child' => ['name' => 'b']],
        )->to(ParentObject::class);

        $this->assertSame('a', $parent->name);
        $this->assertSame('b', $parent->child->name);
        $this->assertSame('a', $parent->child->parent->name);
        $this->assertSame('a', $parent->child->parentCollection[0]->name);
    }

    public function test_parent_children(): void
    {
        $parent = map(
            [
            'name' => 'a',
            'children' => [['name' => 'b'], ['name' => 'c']]],
        )->to(ParentWithChildrenObject::class);

        $this->assertSame('a', $parent->name);

        $this->assertSame('b', $parent->children[0]->name);
        $this->assertSame('a', $parent->children[0]->parent->name);
        $this->assertSame('a', $parent->children[0]->parentCollection[0]->name);

        $this->assertSame('c', $parent->children[1]->name);
        $this->assertSame('a', $parent->children[1]->parent->name);
        $this->assertSame('a', $parent->children[1]->parentCollection[0]->name);
    }
}
