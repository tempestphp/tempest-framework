<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Container;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Container\AutowireDiscovery;
use Tempest\Container\GenericContainer;
use Tempest\Container\InitializerDiscovery;
use Tempest\Container\Tests\Fixtures\RequestLifetimeClassInitializer;
use Tempest\Container\Tests\Fixtures\RequestLifetimeDynamicInitializer;
use Tempest\Container\Tests\Fixtures\RequestLifetimeInitializer;
use Tempest\Container\Tests\Fixtures\RequestLifetimeInterface;
use Tempest\Container\Tests\Fixtures\RequestLifetimeSingleton;
use Tempest\Discovery\DiscoveryItems;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Reflection\ClassReflector;

/**
 * These tests rely on `tempest/discovery`, which is not a dependency of `tempest/container`.
 *
 * @internal
 */
final class ContainerLifetimeDiscoveryTest extends TestCase
{
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
}
