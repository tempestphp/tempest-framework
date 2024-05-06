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
            $this->assertStringContainsString("Cannot autowire Tests\\Tempest\\Unit\\Container\\Fixtures\\CircularA::__construct because it has a circular dependency on Tests\\Tempest\\Unit\\Container\\Fixtures\\CircularA::__construct", $exception->getMessage());

            $expected = <<<'TXT'
	┌─► CircularA::__construct(ContainerObjectA $other, CircularB $b)
	│   CircularB::__construct(CircularC $c)
	│   CircularC::__construct(ContainerObjectA $other, CircularA $a)
	└───▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒
TXT;

            $this->assertStringContainsString($expected, $exception->getMessage());

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
            $this->assertStringContainsString('Cannot autowire Tests\Tempest\Unit\Container\Fixtures\CircularZ::__construct because it has a circular dependency on Tests\Tempest\Unit\Container\Fixtures\CircularA::__construct:', $exception->getMessage());

            $expected = <<<'TXT'
	    CircularZ::__construct(CircularA $a)
	┌─► CircularA::__construct(ContainerObjectA $other, CircularB $b)
	│   CircularB::__construct(CircularC $c)
	│   CircularC::__construct(ContainerObjectA $other, CircularA $a)
	└───▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒▒
TXT;

            $this->assertStringContainsString($expected, $exception->getMessage());

            throw $exception;
        }
    }
}
