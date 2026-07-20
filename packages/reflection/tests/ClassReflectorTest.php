<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\Tests\Fixtures\ChildWithRecursiveAttribute;
use Tempest\Reflection\Tests\Fixtures\ClassWithInterfaceWithRecursiveAttribute;
use Tempest\Reflection\Tests\Fixtures\RecursiveAttribute;
use Tempest\Reflection\Tests\Fixtures\TestClassA;
use Tempest\Reflection\Tests\Fixtures\TestClassB;

/**
 * @internal
 */
final class ClassReflectorTest extends TestCase
{
    #[Test]
    public function getting_underlying_reflection_class(): void
    {
        $reflector = new ClassReflector(TestClassA::class);

        $this->assertEquals(new ReflectionClass(TestClassA::class), $reflector->getReflection());
    }

    #[Test]
    public function getting_name(): void
    {
        $reflector = new ClassReflector(TestClassA::class);
        $reflection = new ReflectionClass(TestClassA::class);

        $this->assertSame($reflector->getName(), $reflection->getName());
    }

    #[Test]
    public function getting_short_name(): void
    {
        $reflector = new ClassReflector(TestClassA::class);
        $reflection = new ReflectionClass(TestClassA::class);

        $this->assertSame($reflector->getShortName(), $reflection->getShortName());
    }

    #[Test]
    public function nullable_property_type(): void
    {
        $reflector = new ClassReflector(TestClassB::class);
        $this->assertTrue($reflector->getProperty('name')->isNullable());
    }

    #[Test]
    public function recursive_attribute_from_interface(): void
    {
        $reflector = new ClassReflector(ClassWithInterfaceWithRecursiveAttribute::class);
        $this->assertNull($reflector->getAttribute(RecursiveAttribute::class));
        $this->assertNotNull($reflector->getAttribute(RecursiveAttribute::class, recursive: true));
    }

    #[Test]
    public function recursive_attribute_from_parent(): void
    {
        $reflector = new ClassReflector(ChildWithRecursiveAttribute::class);
        $this->assertNull($reflector->getAttribute(RecursiveAttribute::class));
        $this->assertNotNull($reflector->getAttribute(RecursiveAttribute::class, recursive: true));
    }

    #[Test]
    public function serialize(): void
    {
        $reflector = new ClassReflector(TestClassA::class);

        $serialized = serialize($reflector);
        $unserialized = unserialize($serialized);

        $this->assertEquals($reflector, $unserialized);
    }
}
