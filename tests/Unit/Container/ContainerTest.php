<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container;

use PHPUnit\Framework\TestCase;
use Tempest\Container\GenericContainer;
use Tests\Tempest\Unit\Container\Fixtures\BuiltinArrayClass;
use Tests\Tempest\Unit\Container\Fixtures\BuiltinTypesWithDefaultsClass;
use Tests\Tempest\Unit\Container\Fixtures\CallContainerObjectE;
use Tests\Tempest\Unit\Container\Fixtures\ContainerObjectA;
use Tests\Tempest\Unit\Container\Fixtures\ContainerObjectB;
use Tests\Tempest\Unit\Container\Fixtures\ContainerObjectC;
use Tests\Tempest\Unit\Container\Fixtures\ContainerObjectD;
use Tests\Tempest\Unit\Container\Fixtures\ContainerObjectDInitializer;
use Tests\Tempest\Unit\Container\Fixtures\ContainerObjectE;
use Tests\Tempest\Unit\Container\Fixtures\ContainerObjectEInitializer;
use Tests\Tempest\Unit\Container\Fixtures\OptionalTypesClass;
use Tests\Tempest\Unit\Container\Fixtures\SingletonClass;
use Tests\Tempest\Unit\Container\Fixtures\UnionTypesClass;

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
        $container = new GenericContainer(initializers: [
            ContainerObjectD::class => ContainerObjectDInitializer::class,
        ]);

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

        $container->addInitializer(ContainerObjectEInitializer::class);

        $object = $container->get(ContainerObjectE::class);

        $this->assertInstanceOf(ContainerObjectE::class, $object);
    }

    /** @test */
    public function call_tries_to_transform_unmatched_values()
    {
        $container = new GenericContainer();
        $container->addInitializer(ContainerObjectEInitializer::class);
        $classToCall = new CallContainerObjectE();

        $return = $container->call($classToCall, 'method', input: '1');
        $this->assertInstanceOf(ContainerObjectE::class, $return);
        $this->assertSame('default', $return->id);

        $return = $container->call($classToCall, 'method', input: new ContainerObjectE('other'));
        $this->assertInstanceOf(ContainerObjectE::class, $return);
        $this->assertSame('other', $return->id);
    }

    /**
     * @test
     */
    public function arrays_are_automatically_created()
    {
        $container = new GenericContainer();

        /**
         * @var BuiltinArrayClass $class
         */
        $class = $container->get(BuiltinArrayClass::class);

        $this->assertIsArray($class->anArray);
        $this->assertEmpty($class->anArray);
    }

    /**
     * @test
     */
    public function builtin_defaults_are_used()
    {
        $container = new GenericContainer();

        /**
         * @var BuiltinTypesWithDefaultsClass $class
         */
        $class = $container->get(BuiltinTypesWithDefaultsClass::class);

        $this->assertSame('This is a default value', $class->aString);
    }

    /**
     * @test
     */
    public function optional_types_resolve_to_null()
    {
        $container = new GenericContainer();

        /**
         * @var OptionalTypesClass $class
         */
        $class = $container->get(OptionalTypesClass::class);

        $this->assertNull($class->aString);
    }

    /**
     * @test
     */
    public function union_types_iterate_to_resolution()
    {
        $container = new GenericContainer();

        /** @var UnionTypesClass $class */
        $class = $container->get(UnionTypesClass::class);

        $this->assertInstanceOf(UnionTypesClass::class, $class);
        $this->assertInstanceOf(ContainerObjectA::class, $class->input);
    }
}
