<?php

namespace Tempest\Reflection\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\Tests\Fixtures\ClassWithIterableProperty;
use Tempest\Reflection\Tests\Fixtures\ClassWithUnionOfStringAndArray;

final class PropertyReflectorTest extends TestCase
{
    public function test_get_iterable_type(): void
    {
        $reflector = new ClassReflector(ClassWithIterableProperty::class)->getProperty('items');

        $iterableType = $reflector->getIterableType();

        $this->assertEquals('string', $iterableType->getName());
    }

    public function test_get_union_of_string_and_array(): void
    {
        $reflector = new ClassReflector(ClassWithUnionOfStringAndArray::class)->getProperty('items');

        $unionType = $reflector->getDocType();

        $this->assertTrue($unionType->isUnion());
        $this->assertCount(3, $unionType->split());
        $this->assertTrue($unionType->accepts('a'));
        $this->assertTrue($unionType->accepts(['a', 'b', 'c']));
        $this->assertTrue($unionType->accepts([]));
        $this->assertTrue($unionType->accepts(null));

        $this->assertFalse($unionType->accepts(123));
        $this->assertFalse($unionType->accepts([123]));
        $this->assertFalse($unionType->accepts([null]));
    }
}
