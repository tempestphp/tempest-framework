<?php

declare(strict_types=1);

namespace Tempest\Container\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Container\AutowireDiscovery;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Container\Initializer;
use Tempest\Container\InitializerDiscovery;
use Tempest\Container\Lifetime;
use Tempest\Container\Singleton;
use Tempest\Container\Tests\Fixtures\ContainerObjectE;
use Tempest\Container\Tests\Fixtures\ProcessLifetimeSingleton;
use Tempest\Container\Tests\Fixtures\RequestLifetimeClassInitializer;
use Tempest\Container\Tests\Fixtures\RequestLifetimeDependency;
use Tempest\Container\Tests\Fixtures\RequestLifetimeDynamicInitializer;
use Tempest\Container\Tests\Fixtures\RequestLifetimeInitializer;
use Tempest\Container\Tests\Fixtures\RequestLifetimeInterface;
use Tempest\Container\Tests\Fixtures\RequestLifetimeSingleton;
use Tempest\Container\Tests\Fixtures\SingletonClass;
use Tempest\Container\Tests\Fixtures\SingletonInitializer;
use Tempest\Discovery\DiscoveryItems;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Reflection\ClassReflector;

/** @internal */
final class ContainerLifetimeTest extends TestCase
{
    #[Test]
    public function class_singletons_are_shared_until_reset(): void
    {
        $container = new GenericContainer();
        $process = $container->get(ProcessLifetimeSingleton::class);

        for ($request = 0; $request < 3; $request++) {
            $instance = $container->get(RequestLifetimeSingleton::class);
            $tagged = $container->get(RequestLifetimeSingleton::class, 'tag');
            $this->assertSame($instance, $container->get(RequestLifetimeSingleton::class));
            $this->assertSame($tagged, $container->get(RequestLifetimeSingleton::class, 'tag'));
            $this->assertNotSame($instance, $tagged);

            $container->reset();

            $this->assertNotSame($instance, $container->get(RequestLifetimeSingleton::class));
            $this->assertNotSame($tagged, $container->get(RequestLifetimeSingleton::class, 'tag'));
            $this->assertSame($process, $container->get(ProcessLifetimeSingleton::class));
        }
    }

    #[Test]
    public function lifetimes_are_tracked_when_resolving_nested_dependencies(): void
    {
        $container = new GenericContainer();
        $instance = $container->get(RequestLifetimeDependency::class)->singleton;
        $this->assertSame($instance, $container->get(RequestLifetimeSingleton::class));

        $container->reset();

        $this->assertNotSame($instance, $container->get(RequestLifetimeDependency::class)->singleton);
    }

    #[Test]
    public function initializer_singletons_are_shared_until_reset(): void
    {
        foreach ([RequestLifetimeInitializer::class, RequestLifetimeClassInitializer::class, RequestLifetimeDynamicInitializer::class] as $initializer) {
            $container = new GenericContainer();
            $discovery = new InitializerDiscovery($container);
            $discovery->setItems(new DiscoveryItems());
            $discovery->discover(new DiscoveryLocation(__NAMESPACE__, __DIR__), new ClassReflector($initializer));
            $discovery->apply();

            for ($request = 0; $request < 3; $request++) {
                $instance = $container->get(RequestLifetimeInterface::class);
                $this->assertSame($instance, $container->get(RequestLifetimeInterface::class));

                $container->reset();

                $this->assertNotSame($instance, $container->get(RequestLifetimeInterface::class));
            }
        }
    }

    #[Test]
    public function tagged_initializer_singletons_are_cleared(): void
    {
        $container = new GenericContainer();
        $container->addInitializer(RequestLifetimeInitializer::class);

        $instance = $container->get(RequestLifetimeInterface::class, 'tag');
        $this->assertSame($instance, $container->get(RequestLifetimeInterface::class, 'tag'));

        $container->reset();

        $this->assertNotSame($instance, $container->get(RequestLifetimeInterface::class, 'tag'));
    }

    #[Test]
    public function discovered_interface_bindings_follow_the_class_lifetime(): void
    {
        $container = new GenericContainer();
        $discovery = new AutowireDiscovery($container);
        $discovery->setItems(new DiscoveryItems());
        $discovery->discover(new DiscoveryLocation(__NAMESPACE__, __DIR__), new ClassReflector(RequestLifetimeSingleton::class));
        $discovery->apply();

        for ($request = 0; $request < 3; $request++) {
            $instance = $container->get(RequestLifetimeInterface::class);
            $this->assertSame($instance, $container->get(RequestLifetimeSingleton::class));
            $this->assertSame($instance, $container->get(RequestLifetimeInterface::class));

            $container->reset();

            $next = $container->get(RequestLifetimeInterface::class);
            $this->assertNotSame($instance, $next);
            $this->assertSame($next, $container->get(RequestLifetimeSingleton::class));
        }
    }

    #[Test]
    public function registered_factories_follow_their_lifetime(): void
    {
        $container = new GenericContainer();
        $container->singleton(SingletonClass::class, fn () => new SingletonClass());
        $container->singleton(RequestLifetimeInterface::class, fn () => new RequestLifetimeSingleton(), 'request');

        $process = $container->get(SingletonClass::class);

        for ($request = 0; $request < 3; $request++) {
            $instance = $container->get(RequestLifetimeInterface::class, 'request');
            $this->assertSame($instance, $container->get(RequestLifetimeInterface::class, 'request'));

            $container->reset();

            $this->assertNotSame($instance, $container->get(RequestLifetimeInterface::class, 'request'));
            $this->assertSame($process, $container->get(SingletonClass::class));
        }
    }

    #[Test]
    public function process_initializer_singletons_survive_reset(): void
    {
        $container = new GenericContainer();
        $container->addInitializer(SingletonInitializer::class);

        $instance = $container->get(ContainerObjectE::class);

        $container->reset();

        $this->assertSame($instance, $container->get(ContainerObjectE::class));
    }

    #[Test]
    public function initialized_classes_can_declare_their_own_lifetime(): void
    {
        $initializer = new class implements Initializer {
            public function initialize(Container $container): RequestLifetimeSingleton
            {
                return new RequestLifetimeSingleton();
            }
        };
        $container = new GenericContainer();
        $container->addInitializer($initializer::class);

        $instance = $container->get(RequestLifetimeSingleton::class);
        $this->assertSame($instance, $container->get(RequestLifetimeSingleton::class));

        $container->reset();

        $this->assertNotSame($instance, $container->get(RequestLifetimeSingleton::class));
    }

    #[Test]
    public function registered_request_instances_are_removed(): void
    {
        $container = new GenericContainer();
        $container->singleton(RequestLifetimeSingleton::class, $instance = new RequestLifetimeSingleton());
        $this->assertSame($instance, $container->get(RequestLifetimeSingleton::class));

        $container->reset();

        $this->assertFalse($container->has(RequestLifetimeSingleton::class));
        $this->assertNotSame($instance, $container->get(RequestLifetimeSingleton::class));
    }

    #[Test]
    public function initializer_lifetime_is_shared_by_all_bindings_of_the_instance(): void
    {
        $initializer = new class implements Initializer {
            #[Singleton(lifetime: Lifetime::REQUEST)]
            public function initialize(Container $container): SingletonClass
            {
                return new SingletonClass();
            }
        };
        $container = new GenericContainer();
        $container->addInitializer($initializer::class);
        $container->singleton(SingletonClass::class, fn (Container $container) => $container->get(SingletonClass::class), 'alias');

        for ($request = 0; $request < 3; $request++) {
            // Parameter resolution uses an internal container clone.
            $instance = $container->invoke(fn (SingletonClass $instance) => $instance);
            $container->singleton(SingletonClass::class, $instance, 'instance');
            $this->assertSame($instance, $container->get(SingletonClass::class, 'alias'));
            $this->assertSame($instance, $container->get(SingletonClass::class, 'instance'));

            $container->reset();

            $this->assertFalse($container->has(SingletonClass::class, 'instance'));
            $next = $container->get(SingletonClass::class, 'alias');
            $this->assertNotSame($instance, $next);
            $this->assertSame($next, $container->get(SingletonClass::class));
        }
    }

    #[Test]
    public function initializer_lifetime_takes_precedence_over_the_class_lifetime(): void
    {
        $initializer = new class implements Initializer {
            #[Singleton(lifetime: Lifetime::PROCESS)]
            public function initialize(Container $container): RequestLifetimeSingleton
            {
                return new RequestLifetimeSingleton();
            }
        };
        $container = new GenericContainer();
        $container->addInitializer($initializer::class);

        $instance = $container->get(RequestLifetimeSingleton::class);
        $container->singleton(RequestLifetimeInterface::class, $instance);

        $container->reset();

        $this->assertSame($instance, $container->get(RequestLifetimeSingleton::class));
        $this->assertSame($instance, $container->get(RequestLifetimeInterface::class));
    }

    #[Test]
    public function replacing_a_request_singleton_updates_its_lifetime(): void
    {
        $container = new GenericContainer();
        $container->singleton(SingletonClass::class, new RequestLifetimeSingleton());
        $container->get(SingletonClass::class);
        $container->singleton(SingletonClass::class, $process = new SingletonClass());

        $container->reset();

        $this->assertSame($process, $container->get(SingletonClass::class));
    }
}
