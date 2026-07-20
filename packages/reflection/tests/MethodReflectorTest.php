<?php

namespace Tempest\Reflection\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\ParameterReflector;
use Tempest\Reflection\Tests\Fixtures\NoReturnType;
use Tempest\Reflection\Tests\Fixtures\TestClassA;

final class MethodReflectorTest extends TestCase
{
    #[Test]
    public function get_parameter_by_name(): void
    {
        $this->assertInstanceOf(
            ParameterReflector::class,
            new ClassReflector(TestClassA::class)
                ->getMethod('method')
                ->getParameter('enum'),
        );

        $this->assertNull(
            new ClassReflector(TestClassA::class)
                ->getMethod('method')
                ->getParameter('unknown'),
        );
    }

    #[Test]
    public function no_return_type_returns_null(): void
    {
        $this->assertNull(
            new ClassReflector(NoReturnType::class)
                ->getMethod('noReturnType')
                ->getReturnType(),
        );
    }
}
