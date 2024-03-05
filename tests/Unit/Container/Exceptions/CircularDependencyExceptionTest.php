<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Exceptions;

use PHPUnit\Framework\TestCase;
use Tempest\Container\Exceptions\CircularDependencyException;
use Tempest\Container\GenericContainer;
use Tests\Tempest\Unit\Container\Fixtures\CircularA;
use Tests\Tempest\Unit\Container\Fixtures\CircularZ;

/**
 * @internal
 * @small
 */
class CircularDependencyExceptionTest extends TestCase
{
    public function test_circular_dependency_test()
    {
        $this->expectException(CircularDependencyException::class);

        try {
            $container = new GenericContainer();

            $container->get(CircularA::class);
        } catch (CircularDependencyException $exception) {
            $this->assertStringContainsString("Cannot autowire Tests\\Tempest\\Unit\\Container\\Fixtures\\CircularA because it has a circular dependency on Tests\\Tempest\\Unit\\Container\\Fixtures\\CircularA", $exception->getMessage());

            $this->assertStringContainsString("┌─► CircularA::__construct(ContainerObjectA \$other, CircularB \$b)", $exception->getMessage());
            $this->assertStringContainsString("│   CircularB::__construct(CircularC \$c)", $exception->getMessage());
            $this->assertStringContainsString("│   CircularC::__construct(ContainerObjectA \$other, CircularA \$a)", $exception->getMessage());
            $this->assertStringContainsString("└───────────────────────────────────────────────────▒▒▒▒▒▒▒▒▒", $exception->getMessage());
            $this->assertStringContainsString("CircularDependencyExceptionTest.php:", $exception->getMessage());

            throw $exception;
        }
    }

    public function test_circular_dependency_as_a_child_test()
    {
        $this->expectException(CircularDependencyException::class);

        try {
            $container = new GenericContainer();

            $container->get(CircularZ::class);
        } catch (CircularDependencyException $exception) {
            $this->assertStringContainsString("Cannot autowire Tests\\Tempest\\Unit\\Container\\Fixtures\\CircularZ because it has a circular dependency on Tests\\Tempest\\Unit\\Container\\Fixtures\\CircularA", $exception->getMessage());
            $this->assertStringContainsString("    CircularZ::__construct(CircularA \$a)", $exception->getMessage());
            $this->assertStringContainsString("┌─► CircularA::__construct(ContainerObjectA \$other, CircularB \$b)", $exception->getMessage());
            $this->assertStringContainsString("│   CircularB::__construct(CircularC \$c)", $exception->getMessage());
            $this->assertStringContainsString("│   CircularC::__construct(ContainerObjectA \$other, CircularA \$a)", $exception->getMessage());
            $this->assertStringContainsString("└───────────────────────────────────────────────────▒▒▒▒▒▒▒▒▒", $exception->getMessage());

            throw $exception;
        }
    }
}
