<?php

namespace Tempest\Container;

use Exception;
use ReflectionClass;
use ReflectionParameter;
use Throwable;

final class Container implements \Tempest\Interfaces\Container
{
    private array $definitions = [];

    private array $singletons = [];

    public function register(string $className, callable $definition): self
    {
        $this->definitions[$className] = $definition;

        return $this;
    }

    public function singleton(string $className, callable $definition): self
    {
        $this->definitions[$className] = function () use ($definition, $className) {
            $instance = $definition();

            $this->singletons[$className] = $instance;

            return $instance;
        };

        return $this;
    }

    public function config(object $config): self
    {
        $this->singleton($config::class, fn () => $config);

        return $this;
    }

    /**
     * @template TClassName
     * @param class-string<TClassName> $className
     * @return TClassName
     */
    public function get(string $className): object
    {
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

    private function resolve(string $className, ContainerLog $log): object
    {
        if ($instance = $this->singletons[$className] ?? null) {
            $log->add($className);

            return $instance;
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