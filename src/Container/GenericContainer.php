<?php

namespace Tempest\Container;

use Exception;
use ReflectionClass;
use ReflectionParameter;
use Tempest\Interfaces\Container;
use Tempest\Interfaces\Resolver;
use Throwable;

final class GenericContainer implements Container
{
    private array $definitions = [];

    private array $singletons = [];

    /** @var Resolver[] */
    private array $resolvers = [];

    public function register(string $className, callable $definition): self
    {
        $this->definitions[$className] = $definition;

        return $this;
    }

    public function singleton(string $className, callable $definition): self
    {
        $this->definitions[$className] = function () use ($definition, $className) {
            $instance = $definition($this);

            $this->singletons[$className] = $instance;

            return $instance;
        };

        return $this;
    }

    public function config(object $config): self
    {
        $this->singleton($config::class, fn() => $config);

        return $this;
    }

    public function get(string $className): object
    {
        if ($instance = $this->singletons[$className] ?? null) {
            return $instance;
        }

        $log = new ContainerLog();

        try {
            return $this->resolve($className, $log);
        } catch (Throwable $throwable) {
            throw new Exception(
                message: "Could not resolve {$log}",
                previous: $throwable,
            );
        }
    }

    public function call(object $object, string $methodName, ...$params): mixed
    {
        $reflectionMethod = (new ReflectionClass($object))->getMethod($methodName);

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $className = $parameter->getName();

            if (array_key_exists($className, $params)) {
                continue;
            }

            $params[$className] = $this->get($parameter->getType()->getName());
        }

        return $object->{$methodName}(...$params);
    }

    public function addResolver(Resolver $resolver): Container
    {
        $this->resolvers[] = $resolver;

        return $this;
    }

    private function resolve(string $className, ContainerLog $log): object
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->canResolve($className)) {
                $log
                    ->add($resolver::class)
                    ->add($className);

                return $resolver->resolve($className, $this);
            }
        }

        $definition = $this->definitions[$className] ?? null;

        if ($definition) {
            return $definition($this);
        }

        $log->add($className);

        return $this->autowire($className, $log);
    }

    private function autowire(string $className, ContainerLog $log): object
    {
        $reflection = new ReflectionClass($className);

        if ($initializedBy = ($reflection->getAttributes(InitializedBy::class)[0] ?? null)) {
            $initializerClassName = $initializedBy->newInstance()->className;

            $initializer = $this->get($initializerClassName);

            if (! is_callable($initializer)) {
                throw new Exception("Initializers must be callable, {$initializerClassName} is not.");
            }

            return $initializer($this);
        }

        $parameters = array_map(
            fn(ReflectionParameter $parameter) => $this->resolve($parameter->getType()->getName(), $log),
            $reflection->getConstructor()?->getParameters() ?? [],
        );

        return new $className(...$parameters);
    }
}