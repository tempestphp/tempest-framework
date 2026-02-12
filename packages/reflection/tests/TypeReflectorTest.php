<?php

namespace Tempest\Reflection\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\Tests\Fixtures\AnnulledInvoice;
use Tempest\Reflection\Tests\Fixtures\NullableClass;
use Tempest\Reflection\Tests\Fixtures\TestClassA;
use Tempest\Reflection\TypeReflector;

final class TypeReflectorTest extends TestCase
{
    public function test_is_enum(): void
    {
        $this->assertTrue(
            new ClassReflector(TestClassA::class)
                ->getMethod('method')
                ->getParameter('enum')
                ->getType()
                ->isEnum(),
        );

        $this->assertTrue(
            new ClassReflector(TestClassA::class)
                ->getMethod('method')
                ->getParameter('backedEnum')
                ->getType()
                ->isEnum(),
        );

        $this->assertTrue(
            new ClassReflector(TestClassA::class)
                ->getMethod('method')
                ->getParameter('backedEnum')
                ->getType()
                ->isBackedEnum(),
        );

        $this->assertTrue(
            new ClassReflector(TestClassA::class)
                ->getMethod('method')
                ->getParameter('backedEnum')
                ->getType()
                ->isEnum(),
        );

        $this->assertTrue(
            new ClassReflector(TestClassA::class)
                ->getMethod('method')
                ->getParameter('backedEnum')
                ->getType()
                ->isUnitEnum(),
        );

        $this->assertTrue(
            new ClassReflector(TestClassA::class)
                ->getMethod('method')
                ->getParameter('enum')
                ->getType()
                ->isUnitEnum(),
        );

        $this->assertFalse(
            new ClassReflector(TestClassA::class)
                ->getMethod('method')
                ->getParameter('enum')
                ->getType()
                ->isBackedEnum(),
        );

        $this->assertFalse(
            new ClassReflector(TestClassA::class)
                ->getMethod('method')
                ->getParameter('other')
                ->getType()
                ->isBackedEnum(),
        );

        $this->assertFalse(
            new ClassReflector(TestClassA::class)
                ->getMethod('method')
                ->getParameter('other')
                ->getType()
                ->isEnum(),
        );

        $this->assertFalse(
            new ClassReflector(TestClassA::class)
                ->getMethod('method')
                ->getParameter('other')
                ->getType()
                ->isUnitEnum(),
        );
    }

    public function test_is_nullable(): void
    {
        $this->assertTrue(new TypeReflector('?string')->isNullable());
        $this->assertTrue(new TypeReflector('string|null')->isNullable());
        $this->assertTrue(new TypeReflector('null')->isNullable());
        $this->assertFalse(new TypeReflector('string')->isNullable());
    }

    public function test_class_name_containing_null_is_not_nullable(): void
    {
        $this->assertFalse(new TypeReflector(AnnulledInvoice::class)->isNullable());
        $this->assertFalse(new TypeReflector(NullableClass::class)->isNullable());
    }
}
