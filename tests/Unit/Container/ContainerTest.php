<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container;

use PHPUnit\Framework\TestCase;
use Tempest\Container\Exceptions\CircularDependencyException;
use Tempest\Container\GenericContainer;
use Tests\Tempest\Unit\Container\Fixtures\BuiltinArrayClass;
use Tests\Tempest\Unit\Container\Fixtures\BuiltinTypesWithDefaultsClass;
use Tests\Tempest\Unit\Container\Fixtures\CallContainerObjectE;
use Tests\Tempest\Unit\Container\Fixtures\CircularWithInitializerA;
use Tests\Tempest\Unit\Container\Fixtures\CircularWithInitializerBInitializer;
use Tests\Tempest\Unit\Container\Fixtures\ContainerObjectA;
use Tests\Tempest\Unit\Container\Fixtures\ContainerObjectB;
use Tests\Tempest\Unit\Container\Fixtures\ContainerObjectC;
use Tests\Tempest\Unit\Container\Fixtures\ContainerObjectD;
use Tests\Tempest\Unit\Container\Fixtures\ContainerObjectDInitializer;
use Tests\Tempest\Unit\Container\Fixtures\ContainerObjectE;
use Tests\Tempest\Unit\Container\Fixtures\ContainerObjectEInitializer;
use Tests\Tempest\Unit\Container\Fixtures\IntersectionInitializer;
use Tests\Tempest\Unit\Container\Fixtures\OptionalTypesClass;
use Tests\Tempest\Unit\Container\Fixtures\SingletonClass;
use Tests\Tempest\Unit\Container\Fixtures\SingletonInitializer;
use Tests\Tempest\Unit\Container\Fixtures\UnionImplementation;
use Tests\Tempest\Unit\Container\Fixtures\UnionInitializer;
use Tests\Tempest\Unit\Container\Fixtures\UnionInterfaceA;
use Tests\Tempest\Unit\Container\Fixtures\UnionInterfaceB;
use Tests\Tempest\Unit\Container\Fixtures\UnionTypesClass;

/**
 * @internal
 * @small
 */
class ContainerTest extends TestCase
{
    public function test_get_with_autowire()
    {
        $container = new GenericContainer();

        $b = $container->get(ContainerObjectB::class);

        $this->assertInstanceOf(ContainerObjectB::class, $b);
        $this->assertInstanceOf(ContainerObjectA::class, $b->a);
    }

    public function test_get_with_definition()
    {
        $container = new GenericContainer();

        $container->register(
            ContainerObjectC::class,
            fn () => new ContainerObjectC(prop: 'test'),
        );

        $c = $container->get(ContainerObjectC::class);

        $this->assertEquals('test', $c->prop);
    }

    public function test_get_with_initializer()
    {
        $container = new GenericContainer(initializers: [
            ContainerObjectD::class => ContainerObjectDInitializer::class,
        ]);

        $d = $container->get(ContainerObjectD::class);

        $this->assertEquals('test', $d->prop);
    }

    public function test_singleton()
    {
        $container = new GenericContainer();

        $container->singleton(SingletonClass::class, fn () => new SingletonClass());

        $instance = $container->get(SingletonClass::class);

        $this->assertEquals(1, $instance::$count);

        $instance = $container->get(SingletonClass::class);

        $this->assertEquals(1, $instance::$count);
    }

    public function test_initialize_with_can_initializer()
    {
        $container = new GenericContainer();

        $container->addInitializer(ContainerObjectEInitializer::class);

        $object = $container->get(ContainerObjectE::class);

        $this->assertInstanceOf(ContainerObjectE::class, $object);
    }

    public function test_call_tries_to_transform_unmatched_values()
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

    public function test_arrays_are_automatically_created()
    {
        $container = new GenericContainer();

        /**
         * @var BuiltinArrayClass $class
         */
        $class = $container->get(BuiltinArrayClass::class);

        $this->assertIsArray($class->anArray);
        $this->assertEmpty($class->anArray);
    }

    public function test_builtin_defaults_are_used()
    {
        $container = new GenericContainer();

        /**
         * @var BuiltinTypesWithDefaultsClass $class
         */
        $class = $container->get(BuiltinTypesWithDefaultsClass::class);

        $this->assertSame('This is a default value', $class->aString);
    }

    public function test_optional_types_resolve_to_null()
    {
        $container = new GenericContainer();

        /**
         * @var OptionalTypesClass $class
         */
        $class = $container->get(OptionalTypesClass::class);

        $this->assertNull($class->aString);
    }

    public function test_union_types_iterate_to_resolution()
    {
        $container = new GenericContainer();

        /** @var UnionTypesClass $class */
        $class = $container->get(UnionTypesClass::class);

        $this->assertInstanceOf(UnionTypesClass::class, $class);
        $this->assertInstanceOf(ContainerObjectA::class, $class->input);
    }

    public function test_singleton_initializers()
    {
        $container = new GenericContainer();
        $container->addInitializer(SingletonInitializer::class);

        $a = $container->get(ContainerObjectE::class);
        $b = $container->get(ContainerObjectE::class);
        $this->assertSame(spl_object_id($a), spl_object_id($b));
    }

    public function test_union_initializers()
    {
        $container = new GenericContainer();
        $container->addInitializer(UnionInitializer::class);

        $a = $container->get(UnionInterfaceA::class);
        $b = $container->get(UnionInterfaceB::class);

        $this->assertInstanceOf(UnionImplementation::class, $a);
        $this->assertInstanceOf(UnionImplementation::class, $b);
    }

    public function test_intersection_initializers()
    {
        $container = new GenericContainer();
        $container->addInitializer(IntersectionInitializer::class);

        $a = $container->get(UnionInterfaceA::class);
        $b = $container->get(UnionInterfaceB::class);

        $this->assertInstanceOf(UnionImplementation::class, $a);
        $this->assertInstanceOf(UnionImplementation::class, $b);
    }

    public function test_circular_with_initializer_log(): void
    {
        $container = new GenericContainer();
        $container->addInitializer(CircularWithInitializerBInitializer::class);

        try {
            $container->get(CircularWithInitializerA::class);
        } catch (CircularDependencyException $e) {
            $this->assertStringContainsString('CircularWithInitializerA', $e->getMessage());
            $this->assertStringContainsString('CircularWithInitializerB', $e->getMessage());
            $this->assertStringContainsString('CircularWithInitializerBInitializer', $e->getMessage());
            $this->assertStringContainsString('CircularWithInitializerC', $e->getMessage());
            $this->assertStringContainsString(__FILE__, $e->getMessage());
        }
    }
}
