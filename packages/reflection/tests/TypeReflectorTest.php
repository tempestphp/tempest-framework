<?php

namespace Tempest\Reflection\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\ParameterReflector;
use Tempest\Reflection\Tests\Fixtures\TestClassA;

final class TypeReflectorTest extends TestCase
{
    public function test_is_enum(): void
    {
        $this->assertTrue(
            new ClassReflector(TestClassA::class)->getMethod('method')->getParameter('enum')->getType()->isEnum(),
        );

        $this->assertTrue(
            new ClassReflector(TestClassA::class)->getMethod('method')->getParameter('backedEnum')->getType()->isEnum(),
        );

        $this->assertTrue(
            new ClassReflector(TestClassA::class)->getMethod('method')->getParameter('backedEnum')->getType()->isBackedEnum(),
        );

        $this->assertTrue(
            new ClassReflector(TestClassA::class)->getMethod('method')->getParameter('backedEnum')->getType()->isEnum(),
        );

        $this->assertTrue(
            new ClassReflector(TestClassA::class)->getMethod('method')->getParameter('backedEnum')->getType()->isUnitEnum(),
        );

        $this->assertTrue(
            new ClassReflector(TestClassA::class)->getMethod('method')->getParameter('enum')->getType()->isUnitEnum(),
        );

        $this->assertFalse(
            new ClassReflector(TestClassA::class)->getMethod('method')->getParameter('enum')->getType()->isBackedEnum(),
        );

        $this->assertFalse(
            new ClassReflector(TestClassA::class)->getMethod('method')->getParameter('other')->getType()->isBackedEnum(),
        );

        $this->assertFalse(
            new ClassReflector(TestClassA::class)->getMethod('method')->getParameter('other')->getType()->isEnum(),
        );

        $this->assertFalse(
            new ClassReflector(TestClassA::class)->getMethod('method')->getParameter('other')->getType()->isUnitEnum(),
        );
    }
}