<?php

declare(strict_types=1);

namespace Tempest\Container;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use function Tempest\attribute;
use Tempest\Container\Exceptions\CannotAutowireException;
use Tempest\Container\Exceptions\InvalidInitializerException;
use Throwable;

final class GenericContainer implements Container
{
    use HasInstance;

    private array $definitions = [];

    private array $singletons = [];

    /** @var (Initializer&CanInitialize)[] */
    private array $initializers = [];

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
        $this->singleton($config::class, fn () => $config);

        return $this;
    }

    public function get(string $className): object
    {
        if ($instance = $this->singletons[$className] ?? null) {
            return $instance;
        }

        $log = new ContainerLog();

        return $this->resolve($className, $log);
    }

    public function call(object $object, string $methodName, ...$params): mixed
    {
        $reflectionMethod = (new ReflectionClass($object))->getMethod($methodName);

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $className = $parameter->getName();

            if (! array_key_exists($className, $params)) {
                $params[$className] = $this->get($parameter->getType()->getName());

                continue;
            }

            $originalValue = $params[$className];

            // If the original value can be passed to this parameter without type errors,
            // we'll simply use it. However, if the value can't be passed,
            // we'll try to resolve the required dependency from the container.
            if (
                is_a($originalValue, $parameter->getType()->getName())
                || $parameter->getType()->getName() === gettype($originalValue)
            ) {
                continue;
            }

            $params[$className] = $this->get($parameter->getType()->getName());
        }

        return $object->{$methodName}(...$params);
    }

    public function addInitializer(CanInitialize $initializer): Container
    {
        $this->initializers = [$initializer, ...$this->initializers];

        return $this;
    }

    private function resolve(
        string $className,
        ContainerLog $log,
        ?ContainerLogItem $logItem = null,
    ): object {
        $log->add($logItem ?? new ContainerLogItem($className));

        // First we check if a callable has been registered
        // to resolve this class.
        if ($definition = $this->definitions[$className] ?? null) {
            return $definition($this);
        }

        // Next we check if any of our default initializers
        // can initialize this class.
        if ($initializer = $this->initializerFor($className)) {
            // Provide the classname of the object we're trying to result
            // if the initializer requires it
            if ($initializer instanceof RequiresClassName) {
                $initializer->setClassName($className);
            }

            $log->add(new ContainerLogItem($initializer::class));

            return $initializer->initialize($this);
        }

        // Finally, autowire the class.
        return $this->autowire($className, $log);
    }

    private function initializerFor(string $className): ?Initializer
    {
        // Loop through the registered initializers to see if
        // we have something to handle this class.
        foreach ($this->initializers as $initializer) {
            if (! $initializer->canInitialize($className)) {
                continue;
            }

            return $initializer;
        }

        // Next, check if the class specifically defines an initializer.
        $reflectionClass = new ReflectionClass($className);

        $initializedBy = attribute(InitializedBy::class)
            ->in($reflectionClass)
            ->first();

        if ($initializedBy) {
            $initializerClassName = $initializedBy->className;

            $initializer = $this->get($initializerClassName);

            if (! $initializer instanceof Initializer) {
                throw new InvalidInitializerException($initializerClassName);
            }

            return $initializer;
        }

        return null;
    }

    private function autowire(string $className, ContainerLog $log): object
    {
        $reflectionClass = new ReflectionClass($className);

        // If there isn't a constructor, don't waste time
        // trying to build it.
        if ($reflectionClass->getConstructor() === null) {
            return $reflectionClass->newInstanceWithoutConstructor();
        }

        // Build the class by iterating through dependencies
        // and resolving them.
        $dependencies = [];

        foreach ($reflectionClass->getConstructor()->getParameters() as $parameter) {
            $dependencies[] = $this->autowireDependency($parameter, $log);
        }

        return $reflectionClass->newInstanceArgs($dependencies);
    }

    private function autowireDependency(ReflectionParameter $parameter, ContainerLog $log): mixed
    {
        $parameterType = $parameter->getType();

        // If the parameter is a built-in type, immediately skip
        // reflection stuff and attempt to give it a default
        // or null value.
        if ($parameterType instanceof ReflectionNamedType && $parameterType->isBuiltin()) {
            return $this->autowireBuiltinDependency($parameter, $log);
        }

        // If there are multiple possible types, loop through them
        // until we hit a match.
        if ($parameterType instanceof ReflectionUnionType) {
            return $this->autowireUnionDependency(
                parameterType: $parameterType,
                parameter: $parameter,
                log: $log,
            );
        }

        // If this is a single type, attempt to resolve it.
        return $this->autowireTypedDependency($parameter, $log);
    }

    private function autowireBuiltinDependency(
        ReflectionParameter $parameter,
        ContainerLog $log,
    ): mixed {
        // Check if a default value is set
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        // If the dependency's type is an array or variadic variable,
        // we'll return an empty array
        if (
            $parameter->getType()?->getName() === 'array' ||
            $parameter->isVariadic()
        ) {
            return [];
        }

        // If the dependency's type allows null or is optional,
        // we'll return null
        if ($parameter->allowsNull() || $parameter->isOptional()) {
            return null;
        }

        // Nothing else we can do, we don't know how to autowire this dependency
        throw new CannotAutowireException($log, $parameter);
    }

    private function autowireUnionDependency(
        ReflectionUnionType $parameterType,
        ReflectionParameter $parameter,
        ContainerLog $log,
    ): ?object {
        // Loop over each type, and try to resolve a value for it.
        // We'll use the first value that works
        foreach ($parameterType->getTypes() as $type) {
            // TODO: we should deal with error handling here,
            // However, if we simply catch all exceptions here,
            // Our container is gone
            $instance = $this->resolve(
                $type->getName(),
                $log,
                new ContainerLogItem(
                    id: $type->getName(),
                    parameter: $parameter,
                ),
            );

            if ($instance) {
                return $instance;
            }
        }

        // Nothing else we can do, we don't know how to autowire this dependency
        throw new CannotAutowireException($log, $parameter);
    }

    public function autowireTypedDependency(
        ReflectionParameter $parameter,
        ContainerLog $log
    ): ?object {
        // Try to resolve the dependency
        try {
            return $this->resolve(
                className: $parameter->getType()->getName(),
                log: $log,
                logItem: new ContainerLogItem(
                    id: $parameter->getType()->getName(),
                    parameter: $parameter,
                ),
            );
        } catch (Throwable $e) {
            // Check if there's a default value we can use
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            // Otherwise, we give up
            throw $e;
        }
    }
}
