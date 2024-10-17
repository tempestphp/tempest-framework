<?php

declare(strict_types=1);

namespace Tempest\Reflection\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\Tests\Fixtures\TestClassA;
use Tempest\Reflection\Tests\Fixtures\TestClassB;

/**
 * @internal
 */
final class ClassReflectorTest extends TestCase
{
    public function test_getting_underlying_reflection_class(): void
    {
        $reflector = new ClassReflector(TestClassA::class);

        $this->assertEquals(new ReflectionClass(TestClassA::class), $reflector->getReflection());
    }

    public function test_getting_name(): void
    {
        $reflector = new ClassReflector(TestClassA::class);
        $reflection = new ReflectionClass(TestClassA::class);

        $this->assertSame($reflector->getName(), $reflection->getName());
    }

    public function test_getting_short_name(): void
    {
        $reflector = new ClassReflector(TestClassA::class);
        $reflection = new ReflectionClass(TestClassA::class);

        $this->assertSame($reflector->getShortName(), $reflection->getShortName());
    }

    public function test_nullable_property_type(): void
    {
        $reflector = new ClassReflector(TestClassB::class);
        $this->assertTrue($reflector->getProperty('name')->isNullable());
    }
}
