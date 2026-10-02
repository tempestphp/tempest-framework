<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Exceptions;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Container\Exceptions\CircularDependencyEncountered;
use Tempest\Container\GenericContainer;
use Tempest\Container\Tests\Fixtures\CircularA;
use Tempest\Container\Tests\Fixtures\CircularZ;
use Tempest\Container\Tests\Fixtures\NestedResolutionA;
use Tempest\Container\Tests\Fixtures\NestedResolutionAInitializer;
use Tempest\Container\Tests\Fixtures\NestedResolutionBInitializer;
use Tempest\Container\Tests\Fixtures\ResolvedTwiceParent;
use Tempest\Container\Tests\Fixtures\ResolvedTwiceParentInitializer;

/**
 * @internal
 */
final class CircularDependencyExceptionTest extends TestCase
{
    #[Test]
    public function circular_dependency_test(): void
    {
        $this->expectException(CircularDependencyEncountered::class);

        try {
            $container = new GenericContainer();

            $container->get(CircularA::class);
        } catch (CircularDependencyEncountered $circularDependencyException) {
            $this->assertStringContainsString(
                'Cannot autowire ' . CircularA::class . '::__construct because it has a circular dependency on ' . CircularA::class . '::__construct',
                $circularDependencyException->getMessage(),
            );

            $expected = <<<'TXT'
            	┌─► CircularA::__construct(ContainerObjectA $other, CircularB $b)
            	│   CircularB::__construct(CircularC $c)
            	│   CircularC::__construct(ContainerObjectA $other, CircularA $a)
            	└───────────────────────────────────────────────────▒▒▒▒▒▒▒▒▒▒▒▒
            TXT;

            $this->assertStringContainsStringIgnoringLineEndings($expected, $circularDependencyException->getMessage());

            $this->assertStringContainsString('CircularDependencyExceptionTest.php:', $circularDependencyException->getMessage());

            throw $circularDependencyException;
        }
    }

    #[Test]
    public function circular_dependency_as_a_child_test(): void
    {
        $this->expectException(CircularDependencyEncountered::class);

        try {
            $container = new GenericContainer();

            $container->get(CircularZ::class);
        } catch (CircularDependencyEncountered $circularDependencyException) {
            $this->assertStringContainsString(
                'Cannot autowire ' . CircularZ::class . '::__construct because it has a circular dependency on ' . CircularA::class . '::__construct:',
                $circularDependencyException->getMessage(),
            );

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

    #[Test]
    public function circular_dependency_after_a_nested_resolution_test(): void
    {
        $this->expectException(CircularDependencyEncountered::class);

        $container = new GenericContainer();
        $container->addInitializer(NestedResolutionAInitializer::class);
        $container->addInitializer(NestedResolutionBInitializer::class);

        $container->get(NestedResolutionA::class);
    }

    #[Test]
    public function the_same_dependency_resolved_twice_in_one_chain_is_not_circular_test(): void
    {
        $container = new GenericContainer();
        $container->addInitializer(ResolvedTwiceParentInitializer::class);

        $parent = $container->get(ResolvedTwiceParent::class);

        $this->assertInstanceOf(ResolvedTwiceParent::class, $parent);
    }
}
