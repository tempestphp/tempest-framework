<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Exceptions;

use PHPUnit\Framework\TestCase;
use Tempest\Container\Exceptions\CircularDependencyException;
use Tempest\Container\GenericContainer;
use Tempest\Container\Tests\Fixtures\CircularA;
use Tempest\Container\Tests\Fixtures\CircularZ;

/**
 * @internal
 * @small
 */
class CircularDependencyExceptionTest extends TestCase
{
    public function test_circular_dependency_test(): void
    {
        $this->expectException(CircularDependencyException::class);

        try {
            $container = new GenericContainer();

            $container->get(CircularA::class);
        } catch (CircularDependencyException $circularDependencyException) {
            $this->assertStringContainsString('Cannot autowire ' . CircularA::class . '::__construct because it has a circular dependency on ' . CircularA::class . '::__construct', $circularDependencyException->getMessage());

            $expected = <<<'TXT'
	┌─► CircularA::__construct(ContainerObjectA $other, CircularB $b)
	│   CircularB::__construct(CircularC $c)
	│   CircularC::__construct(ContainerObjectA $other, CircularA $a)
	└───────────────────────────────────────────────────▒▒▒▒▒▒▒▒▒▒▒▒
TXT;

            $this->assertStringContainsStringIgnoringLineEndings($expected, $circularDependencyException->getMessage());

            $this->assertStringContainsString("CircularDependencyExceptionTest.php:", $circularDependencyException->getMessage());

            throw $circularDependencyException;
        }
    }

    public function test_circular_dependency_as_a_child_test(): void
    {
        $this->expectException(CircularDependencyException::class);

        try {
            $container = new GenericContainer();

            $container->get(CircularZ::class);
        } catch (CircularDependencyException $circularDependencyException) {
            $this->assertStringContainsString('Cannot autowire ' . CircularZ::class . '::__construct because it has a circular dependency on ' . CircularA::class . '::__construct:', $circularDependencyException->getMessage());

            $expected = <<<'TXT'
	    CircularZ::__construct(CircularA $a)
	┌─► CircularA::__construct(ContainerObjectA $other, CircularB $b)
	│   CircularB::__construct(CircularC $c)
	│   CircularC::__construct(ContainerObjectA $other, CircularA $a)
	└───────────────────────────────────────────────────▒▒▒▒▒▒▒▒▒▒▒▒
TXT;

            $this->assertStringContainsStringIgnoringLineEndings($expected, $circularDependencyException->getMessage());

            throw $circularDependencyException;
        }
    }
}
