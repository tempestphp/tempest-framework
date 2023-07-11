<?php

namespace Tests\Tempest\Container;

use Tempest\Container\GenericContainer;
use PHPUnit\Framework\TestCase;
use Tempest\Container\InitializedBy;
use Tempest\Interfaces\Container;
use Tempest\Interfaces\Initializer;

class ContainerTest extends TestCase
{
    /** @test */
    public function get_with_autowire()
    {
        $container = new GenericContainer();

        $b = $container->get(ContainerObjectB::class);

        $this->assertInstanceOf(ContainerObjectB::class, $b);
        $this->assertInstanceOf(ContainerObjectA::class, $b->a);
    }

    /** @test */
    public function get_with_definition()
    {
        $container = new GenericContainer();

        $container->register(
            ContainerObjectC::class,
            fn() => new ContainerObjectC(prop: 'test'),
        );

        $c = $container->get(ContainerObjectC::class);

        $this->assertEquals('test', $c->prop);
    }

    /** @test */
    public function get_with_initializer()
    {
        $container = new GenericContainer();

        $d = $container->get(ContainerObjectD::class);

        $this->assertEquals('test', $d->prop);
    }

    /** @test */
    public function test_singleton()
    {
        $container = new GenericContainer();

        $container->singleton(SingletonClass::class, fn () => new SingletonClass());

        $instance = $container->get(SingletonClass::class);

        $this->assertEquals(1, $instance::$count);

        $instance = $container->get(SingletonClass::class);

        $this->assertEquals(1, $instance::$count);
    }
}

class ContainerObjectA
{
}

class ContainerObjectB
{
    public function __construct(public ContainerObjectA $a) {}
}

class ContainerObjectC
{
    public function __construct(public string $prop) {}
}

#[InitializedBy(ContainerObjectDInitializer::class)]
class ContainerObjectD
{
    public function __construct(public string $prop) {}
}

class ContainerObjectDInitializer implements Initializer
{
    public function initialize(string $className, Container $container): ContainerObjectD
    {
        return new ContainerObjectD(prop: 'test');
    }
}

class SingletonClass
{
    public static $count = 0;

    public function __construct()
    {
        self::$count += 1;
    }
}