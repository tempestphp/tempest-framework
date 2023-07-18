<?php

declare(strict_types=1);

namespace Tests\Tempest\Container;

use PHPUnit\Framework\TestCase;
use Tempest\Container\GenericContainer;
use Tempest\Container\InitializedBy;
use Tempest\Interfaces\CanInitialize;
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
            fn () => new ContainerObjectC(prop: 'test'),
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

    /** @test */
    public function initialize_with_can_initializer()
    {
        $container = new GenericContainer();

        $container->addInitializer(new ContainerObjectEInitializer());

        $object = $container->get(ContainerObjectE::class);

        $this->assertInstanceOf(ContainerObjectE::class, $object);
    }

    /** @test */
    public function call_tries_to_transform_unmatched_values()
    {
        $container = new GenericContainer();
        $container->addInitializer(new ContainerObjectEInitializer());
        $classToCall = new CallContainerObjectE();

        $return = $container->call($classToCall, 'method', input: '1');
        $this->assertInstanceOf(ContainerObjectE::class, $return);
        $this->assertSame('default', $return->id);

        $return = $container->call($classToCall, 'method', input: new ContainerObjectE('other'));
        $this->assertInstanceOf(ContainerObjectE::class, $return);
        $this->assertSame('other', $return->id);
    }
}

class ContainerObjectA
{
}

class ContainerObjectB
{
    public function __construct(public ContainerObjectA $a)
    {
    }
}

class ContainerObjectC
{
    public function __construct(public string $prop)
    {
    }
}

#[InitializedBy(ContainerObjectDInitializer::class)]
class ContainerObjectD
{
    public function __construct(public string $prop)
    {
    }
}
class ContainerObjectDInitializer implements Initializer
{
    public function initialize(string $className, Container $container): ContainerObjectD
    {
        return new ContainerObjectD(prop: 'test');
    }
}

class ContainerObjectE
{
    public function __construct(public string $id = 'default')
    {
    }
}

class ContainerObjectEInitializer implements CanInitialize
{
    public function initialize(string $className, Container $container): ContainerObjectE
    {
        return new ContainerObjectE();
    }

    public function canInitialize(string $className): bool
    {
        return $className === ContainerObjectE::class;
    }
}

class SingletonClass
{
    public static int $count = 0;

    public function __construct()
    {
        self::$count += 1;
    }
}

class CallContainerObjectE
{
    public function method(ContainerObjectE $input): ContainerObjectE
    {
        return $input;
    }
}
