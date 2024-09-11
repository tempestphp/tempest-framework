<?php

namespace Tests\Tempest\Unit\Reflection;

use PHPUnit\Framework\TestCase;

use ReflectionClass;
use Tempest\Reflection\ClassReflector;
use Tests\Tempest\Unit\Reflection\Fixtures\TestClassA;

final class ClassReflectorTest extends TestCase
{
    public function test_getting_underlying_reflection_class()
    {
        $reflector = new ClassReflector(TestClassA::class);

        $this->assertEquals(new ReflectionClass(TestClassA::class), $reflector->getReflection());
    }

    public function test_getting_name()
    {
        $reflector = new ClassReflector(TestClassA::class);
        $reflection = new ReflectionClass(TestClassA::class);

        $this->assertSame($reflector->getName(), $reflection->getName());
    }

    public function test_getting_short_name()
    {
        $reflector = new ClassReflector(TestClassA::class);
        $reflection = new ReflectionClass(TestClassA::class);

        $this->assertSame($reflector->getShortName(), $reflection->getShortName());
    }
}