<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Container;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Tempest\Container\GenericContainer;
use Tempest\Container\Tests\Fixtures\ClassWithSingletonAttribute;
use Tempest\Container\Tests\Fixtures\ContainerObjectA;
use Tempest\Container\Tests\Fixtures\ContainerObjectB;
use Tempest\Container\Tests\Fixtures\ContainerObjectD;
use Tempest\Container\Tests\Fixtures\ContainerObjectDInitializer;
use Tempest\Container\Tests\Fixtures\ContainerObjectE;
use Tempest\Container\Tests\Fixtures\ContainerObjectEInitializer;

final class ContainerBench
{
    private GenericContainer $container;

    public function setUp(): void
    {
        $this->container = new GenericContainer();
    }

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchAutowireSimple(): void
    {
        $this->container->get(ContainerObjectA::class);
    }

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchAutowireNested(): void
    {
        $this->container->get(ContainerObjectB::class);
    }

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchSingletonResolution(): void
    {
        $this->container->singleton(ContainerObjectA::class, new ContainerObjectA());
        $this->container->get(ContainerObjectA::class);
    }

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchSingletonAttribute(): void
    {
        $this->container->get(ClassWithSingletonAttribute::class);
    }

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchDefinitionResolution(): void
    {
        $this->container->register(ContainerObjectA::class, fn () => new ContainerObjectA());
        $this->container->get(ContainerObjectA::class);
    }

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchInitializerResolution(): void
    {
        $this->container->addInitializer(ContainerObjectDInitializer::class);
        $this->container->get(ContainerObjectD::class);
    }

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchDynamicInitializerResolution(): void
    {
        $this->container->addInitializer(ContainerObjectEInitializer::class);
        $this->container->get(ContainerObjectE::class);
    }

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchClosureSingletonResolution(): void
    {
        $this->container->singleton(ContainerObjectA::class, fn () => new ContainerObjectA());
        $this->container->get(ContainerObjectA::class);
    }

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchInvokeClosure(): void
    {
        $this->container->invoke(fn (ContainerObjectA $a) => $a);
    }
}
