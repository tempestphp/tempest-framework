<?php

declare(strict_types=1);

namespace Tempest\Container;

use LogicException;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use function Tempest\attribute;
use Tempest\Container\Exceptions\ContainerException;
use Tempest\Container\Exceptions\InvalidInitializerException;
use Throwable;

final class GenericContainer implements Container
{
    use HasInstance;

    private array $definitions = [];

    private array $singletons = [];

    /** @var CanInitialize[] */
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

        try {
            return $this->resolve($className, $log);
        } catch (Throwable $throwable) {
            throw new ContainerException($log, $throwable);
        }
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

    private function resolve(string $className, ContainerLog $log): object
    {
        $log->add($className);

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

            $log->add($initializer::class);

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
            return $this->autowireBuiltinDependency($parameter);
        }

        // If there are multiple possible types, loop through them
        // until we hit a match.
        if ($parameterType instanceof ReflectionUnionType) {
            foreach ($parameterType->getTypes() as $type) {
                try {
                    if ($instance = $this->resolve($type->getName(), $log)) {
                        return $instance;
                    }
                } catch (Throwable) {
                }
            }
        }

        // If this is a single type, attempt to resolve it.
        try {
            return $this->resolve($parameter->getType()->getName(), $log);
        } catch (Throwable) {
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new LogicException(
            sprintf(
                'Unable to autowire dependency $%s in %s::__construct().',
                $parameter->getName(),
                $parameter->getDeclaringClass()->getName(),
            )
        );
    }

    private function autowireBuiltinDependency(ReflectionParameter $parameter): mixed
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if (
            $parameter->getType()?->getName() === 'array' ||
            $parameter->isVariadic()
        ) {
            return [];
        }

        if ($parameter->allowsNull() || $parameter->isOptional()) {
            return null;
        }

        throw new LogicException(
            sprintf('Unable to autowire built-in dependency [%s].', $parameter->getName())
        );
    }
}
