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
            // if the initializer requires it.
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

        $constructor = $reflectionClass->getConstructor();

        return $constructor
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
        $dependencies = [];

        // Build the class by iterating through its
        // dependencies and resolving them.
        foreach ($method->getParameters() as $parameter) {
            // TODO: Check with Brent on this one. Behavior
            // is slight different than the built-in type
            // approach since we are checking with the
            // provided value is of the right type.
            // We COULD early return here if a
            // providedValue is set.
            $dependencies[] = $this->autowireDependency(
                parameter: $parameter,
                providedValue: $parameters[$parameter->getName()] ?? null
            );
        }

        return $dependencies;
    }

    private function autowireDependency(ReflectionParameter $parameter, mixed $providedValue = null): mixed
    {
        $parameterType = $parameter->getType();

        // If the parameter is a built-in type, immediately skip reflection
        // stuff and attempt to give it a default or null value.
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
                // We were unable to resolve the dependency for the last union
                // type, so we are moving on to the next one.
            }
        }

        // If the dependency has a default value, we do our best to prevent
        // an error by using that.
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        // At this point, there is nothing else we can do; we don't know
        // how to autowire this dependency.
        throw new LogicException(
            sprintf('Unable to autowire dependency [%s].', $parameter->getName())
        );
    }

    private function autowireObjectDependency(ReflectionNamedType $type, mixed $providedValue): mixed
    {
        // If the provided value is of the right type,
        // don't waste time autowiring, return it!
        if (is_a($providedValue, $type->getName())) {
            return $providedValue;
        }

        // If we can successfully retrieve an instance
        // of the necessary dependency, return it.
        if ($instance = $this->get($type->getName())) {
            return $instance;
        }

        // At this point, there is nothing else we can do; we don't know
        // how to autowire this dependency.
        throw new LogicException(
            sprintf('Unable to autowire dependency [%s].', $type->getName())
        );
    }

    private function autowireBuiltinDependency(ReflectionParameter $parameter, mixed $providedValue): mixed
    {
        // Due to type coercion, the provided value may (or may not) work.
        // Here we give up trying to do type work for people. If they
        // didn't provide the right type, that's on them.
        if ($providedValue !== null) {
            return $providedValue;
        }

        // If the dependency has a default value, we might as well
        // use that at this point.
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        // If the dependency's type is an array or variadic variable, we'll
        // try to prevent an error by returning an empty array.
        if (
            $parameter->getType()?->getName() === 'array' ||
            $parameter->isVariadic()
        ) {
            return [];
        }

        // If the dependency's type allows null or is optional, we'll
        // try to prevent an error by returning null.
        if ($parameter->allowsNull() || $parameter->isOptional()) {
            return null;
        }

        // At this point, there is nothing else we can do; we don't know
        // how to autowire this dependency.
        throw new LogicException(
            sprintf('Unable to autowire built-in dependency [%s].', $parameter->getName())
        );
    }
}
