<?php

namespace Tests\Tempest\Unit\Container\Exceptions;

use PHPUnit\Framework\TestCase;
use Tempest\Container\Exceptions\CircularDependencyException;
use Tempest\Container\GenericContainer;
use Tests\Tempest\Unit\Container\Fixtures\CircularA;

class CircularDependencyExceptionTest extends TestCase
{
    /** @test */
    public function circular_dependency_test()
    {
        $this->expectException(CircularDependencyException::class);

        try {
            $container = new GenericContainer();

            $container->get(CircularA::class);
        } catch (CircularDependencyException $exception) {
            $this->assertStringContainsString("Cannot autowire Tests\\Tempest\\Unit\\Container\\Fixtures\\CircularA because it is a circular dependency", $exception->getMessage());

            $this->assertStringContainsString("┌─► CircularA::__construct()", $exception->getMessage());
            $this->assertStringContainsString("│   CircularB::__construct()", $exception->getMessage());
            $this->assertStringContainsString("│   CircularC::__construct()", $exception->getMessage());
            $this->assertStringContainsString("└─► CircularA::__construct()", $exception->getMessage());

            throw $exception;
        }
    }
}
