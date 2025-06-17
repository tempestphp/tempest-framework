<?php

namespace Tests\Tempest\Integration\Container\Commands;

use Tempest\Container\Commands\ContainerShowCommand;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use UnitEnum;

final class ContainerShowCommandTest extends FrameworkIntegrationTestCase
{
    public function test_command(): void
    {
        $this->console
            ->call(ContainerShowCommand::class)
            ->assertSee('INITIALIZERS')
            ->assertSee('SINGLETONS')
            ->assertSuccess();
    }

    public function test_with_another_container(): void
    {
        $this->container->singleton(
            Container::class,
            new readonly class(clone $this->container) implements Container {
                public function __construct(
                    private Container $container,
                ) {}

                public function register(string $className, callable $definition): self
                {
                    $this->container->register($className, $definition);

                    return $this;
                }

                public function unregister(string $className, bool $tagged = false): self
                {
                    $this->container->unregister($className);

                    return $this;
                }

                public function singleton(string $className, mixed $definition, null|string|UnitEnum $tag = null): self
                {
                    $this->container->singleton($className, $definition, $tag);

                    return $this;
                }

                public function config(object $config): self
                {
                    $this->container->config($config);

                    return $this;
                }

                public function get(string $className, null|string|UnitEnum $tag = null, mixed ...$params): mixed
                {
                    return $this->container->get($className, $tag, ...$params);
                }

                public function has(string $className, null|string|UnitEnum $tag = null): bool
                {
                    return $this->container->has($className, $tag);
                }

                public function invoke(mixed $method, mixed ...$params): mixed
                {
                    return $this->container->invoke($method, ...$params);
                }

                public function addInitializer(mixed $initializerClass): self
                {
                    $this->container->addInitializer($initializerClass);

                    return $this;
                }
            },
        );

        $this->console
            ->call(ContainerShowCommand::class)
            ->assertSee('The registered container instance does not expose its bindings.')
            ->assertError();
    }
}
