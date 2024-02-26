<?php

declare(strict_types=1);

namespace Tempest\Container;

use LogicException;
use ReflectionClass;
use ReflectionMethod;
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

    public function call(string|object $object, string $methodName, ...$params): mixed
    {
        $object = is_string($object) ? $this->get($object) : $object;

        $reflectionMethod = (new ReflectionClass($object))->getMethod($methodName);

        $parameters = $this->autowireDependencies($reflectionMethod, $params);

        return $reflectionMethod->invokeArgs($object, $parameters);
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

            // TODO: This causes some errors because CanInitialize !== Initializer
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

        $constructor = $reflectionClass->getConstructor();

        return ($constructor === null)
            // If there isn't a constructor, don't waste time
            // trying to build it.
            ? $reflectionClass->newInstanceWithoutConstructor()

            // Otherwise, use our autowireDependencies helper to automagically
            // build up each parameter.
            : $reflectionClass->newInstanceArgs(
                $this->autowireDependencies($constructor)
            );
    }

    /**
     * @return ReflectionParameter[]
     */
    private function autowireDependencies(ReflectionMethod $method, array $parameters = []): array
    {
        // Build the class by iterating through dependencies
        // and resolving them.
        $dependencies = [];

        foreach ($method->getParameters() as $parameter) {
            $dependencies[] = $this->autowireDependency(
                $parameter,
                $parameters[$parameter->getName()] ?? null
            );
        }

        return $dependencies;
    }

    private function autowireDependency(ReflectionParameter $parameter, mixed $providedValue = null): mixed
    {
        $parameterType = $parameter->getType();

        // If the parameter is a built-in type, immediately skip
        // reflection stuff and attempt to give it a default
        // or null value.
        if ($parameterType instanceof ReflectionNamedType && $parameterType->isBuiltin()) {
            return $this->autowireBuiltinDependency($parameter, $providedValue);
        }

        // Convert the types to an array regardless, so we can handle
        // union types and single types the same.
        $types = ($parameterType instanceof ReflectionUnionType)
            ? $parameterType->getTypes()
            : [$parameterType];

        // Loop through each type until we hit a match.
        foreach ($types as $type) {
            try {
                return $this->autowireObjectDependency($type, $providedValue);
            } catch (Throwable) {
            }
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new LogicException(
            sprintf('Unable to autowire dependency [%s].', $parameter->getName())
        );
    }

    private function autowireObjectDependency(ReflectionNamedType $type, mixed $providedValue): mixed
    {
        if (is_a($providedValue, $type->getName())) {
            return $providedValue;
        }

        if ($instance = $this->get($type->getName())) {
            return $instance;
        }

        throw new LogicException(
            sprintf('Unable to autowire dependency [%s].', $type->getName())
        );
    }

    private function autowireBuiltinDependency(ReflectionParameter $parameter, mixed $providedValue): mixed
    {
        // At this point, give up trying to do type work for people
        // if they didn't provide the right type, that's on them.
        if (! is_null($providedValue)) {
            return $providedValue;
        }

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
