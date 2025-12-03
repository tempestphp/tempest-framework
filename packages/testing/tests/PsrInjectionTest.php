<?php

namespace Tempest\Testing\Tests;

use Psr\Container\ContainerInterface;
use Tempest\Container\Container;
use Tempest\Testing\Actions\RunTest;
use Tempest\Testing\After;
use Tempest\Testing\Before;
use Tempest\Testing\Test;
use Tempest\Testing\Tests\Fixtures\Dependency;

use function Tempest\reflect;
use function Tempest\Testing\test;

/**
 * Jumping through some hoops to be able to overwrite the container WHILE running tests
 */
final readonly class PsrInjectionTest
{
    public function __construct(
        private Container $container,
    ) {}

    #[Before]
    public function before(): void
    {
        $runTest = $this->container->get(RunTest::class);

        $class = reflect($runTest);

        $property = $class->getProperty('container');

        $property->setValue($runTest, new PsrContainer());
    }

    #[After]
    public function after(): void
    {
        $runTest = $this->container->get(RunTest::class);

        $class = reflect($runTest);

        $property = $class->getProperty('container');

        $property->setValue($runTest, $this->container);
    }

    #[Test]
    public function psrInjection(Dependency $dependency): void
    {
        test($dependency->name)->is('psr');
    }
}

final class PsrContainer implements ContainerInterface
{
    public function get(string $id)
    {
        return new Dependency('psr');
    }

    public function has(string $id): bool
    {
        return true;
    }
}
