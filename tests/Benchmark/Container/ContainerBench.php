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

    #[BeforeMethods('setUpSingletonInstance')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchResolveSingletonInstance(): void
    {
        $this->container->get(ContainerObjectA::class);
    }

    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchRegisterSingletonInstance(): void
    {
        $container = new GenericContainer();

        $container->singleton(ContainerObjectA::class, new ContainerObjectA());
    }

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchSingletonAttribute(): void
    {
        $this->container->get(ClassWithSingletonAttribute::class);
    }

    #[BeforeMethods('setUpDefinition')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchResolveDefinition(): void
    {
        $this->container->get(ContainerObjectA::class);
    }

    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchRegisterDefinition(): void
    {
        $container = new GenericContainer();

        $container->register(ContainerObjectA::class, fn () => new ContainerObjectA());
    }

    #[BeforeMethods('setUpInitializer')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchResolveWithInitializer(): void
    {
        $this->container->get(ContainerObjectD::class);
    }

    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchRegisterInitializer(): void
    {
        $container = new GenericContainer();

        $container->addInitializer(ContainerObjectDInitializer::class);
    }

    #[BeforeMethods('setUpDynamicInitializer')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchResolveWithDynamicInitializer(): void
    {
        $this->container->get(ContainerObjectE::class);
    }

    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchRegisterDynamicInitializer(): void
    {
        $container = new GenericContainer();

        $container->addInitializer(ContainerObjectEInitializer::class);
    }

    #[BeforeMethods('setUpClosureSingleton')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchResolveClosureSingleton(): void
    {
        $this->container->get(ContainerObjectA::class);
    }

    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchRegisterClosureSingleton(): void
    {
        $container = new GenericContainer();

        $container->singleton(ContainerObjectA::class, fn () => new ContainerObjectA());
    }

    #[BeforeMethods('setUp')]
    #[Iterations(5)]
    #[Revs(1000)]
    #[Warmup(10)]
    public function benchInvokeClosure(): void
    {
        $this->container->invoke(fn (ContainerObjectA $a) => $a);
    }

    public function setUpSingletonInstance(): void
    {
        $this->setUp();
        $this->container->singleton(ContainerObjectA::class, new ContainerObjectA());
    }

    public function setUpDefinition(): void
    {
        $this->setUp();
        $this->container->register(ContainerObjectA::class, fn () => new ContainerObjectA());
    }

    public function setUpInitializer(): void
    {
        $this->setUp();
        $this->container->addInitializer(ContainerObjectDInitializer::class);
    }

    public function setUpDynamicInitializer(): void
    {
        $this->setUp();
        $this->container->addInitializer(ContainerObjectEInitializer::class);
    }

    public function setUpClosureSingleton(): void
    {
        $this->setUp();
        $this->container->singleton(ContainerObjectA::class, fn () => new ContainerObjectA());
    }
}
