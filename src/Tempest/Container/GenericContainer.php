<?php

declare(strict_types=1);

namespace Tempest\Container;

use ArrayIterator;
use ReflectionClass;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use Tempest\Container\Exceptions\CannotAutowireException;
use Tempest\Container\Exceptions\CannotInstantiateDependencyException;
use Tempest\Container\Exceptions\CannotResolveTaggedDependency;
use Tempest\Support\Reflection\Attributes;
use Throwable;

final class GenericContainer implements Container
{
    use HasInstance;

    public function __construct(
        /** @var ArrayIterator<array-key, mixed> $definitions */
        private ArrayIterator $definitions = new ArrayIterator(),

        /** @var ArrayIterator<array-key, mixed> $singletons */
        private ArrayIterator $singletons = new ArrayIterator(),

        /** @var ArrayIterator<array-key, class-string> $initializers */
        private ArrayIterator $initializers = new ArrayIterator(),

        /** @var ArrayIterator<array-key, class-string> $dynamicInitializers */
        private ArrayIterator $dynamicInitializers = new ArrayIterator(),
        private ?DependencyChain $chain = null,
    ) {
    }

    public function setInitializers(array $initializers): self
    {
        $this->initializers = new ArrayIterator($initializers);

        return $this;
    }

    public function setDynamicInitializers(array $dynamicInitializers): self
    {
        $this->dynamicInitializers = new ArrayIterator($dynamicInitializers);

        return $this;
    }

    public function getInitializers(): array
    {
        return $this->initializers->getArrayCopy();
    }

    public function getDynamicInitializers(): array
    {
        return $this->dynamicInitializers->getArrayCopy();
    }

    public function register(string $className, callable $definition): self
    {
        $this->definitions[$className] = $definition;

        return $this;
    }

    public function singleton(string $className, object|callable $definition, ?string $tag = null): self
    {
        $className = $this->resolveTaggedName($className, $tag);

        $this->singletons[$className] = $definition;

        return $this;
    }

    public function config(object $config): self
    {
        $this->singleton($config::class, $config);

        return $this;
    }

    public function get(string $className, ?string $tag = null, mixed ...$params): object
    {
        $this->resolveChain();

        $dependency = $this->resolve(
            className: $className,
            tag: $tag,
            params: $params,
        );

        $this->stopChain();

        return $dependency;
    }

    public function call(string|object $object, string $methodName, ...$params): mixed
    {
        $this->resolveChain();

        $object = is_string($object) ? $this->get($object) : $object;

        $reflectionMethod = (new ReflectionClass($object))->getMethod($methodName);

        $parameters = $this->autowireDependencies($reflectionMethod, $params);

        $this->stopChain();

        return $reflectionMethod->invokeArgs($object, $parameters);
    }

    public function addInitializer(ReflectionClass|string $initializerClass): Container
    {
        $initializerClass = $initializerClass instanceof ReflectionClass
            ? $initializerClass
            : new ReflectionClass($initializerClass);

        // First, we check whether this is a DynamicInitializer,
        // which don't have a one-to-one mapping
        if ($initializerClass->implementsInterface(DynamicInitializer::class)) {
            $this->dynamicInitializers[] = $initializerClass->getName();

            return $this;
        }

        $initializeMethod = $initializerClass->getMethod('initialize');

        // We resolve the optional Tag attribute from this initializer class
        $singleton = Attributes::find(Singleton::class)->in($initializerClass)->first()
            ?? Attributes::find(Singleton::class)->in($initializeMethod)->first();

        // For normal Initializers, we'll use the return type
        // to determine which dependency they resolve
        $returnTypes = $initializeMethod->getReturnType();

        $returnTypes = match ($returnTypes::class) {
            ReflectionNamedType::class => [$returnTypes],
            ReflectionUnionType::class, ReflectionIntersectionType::class => $returnTypes->getTypes(),
        };

        /** @var ReflectionNamedType[] $returnTypes */
        foreach ($returnTypes as $returnType) {
            $this->initializers[$this->resolveTaggedName($returnType->getName(), $singleton?->tag)] = $initializerClass->getName();
        }

        return $this;
    }

    private function resolve(string $className, ?string $tag = null, mixed ...$params): object
    {
        $dependencyName = $this->resolveTaggedName($className, $tag);

        // Check if the class has been registered as a singleton.
        if ($instance = $this->singletons[$dependencyName] ?? null) {
            if (is_callable($instance)) {
                $instance = $instance($this);
                $this->singletons[$className] = $instance;
            }

            $this->resolveChain()->add(new ReflectionClass($className));

            return $instance;
        }

        // Check if a callable has been registered to resolve this class.
        if ($definition = $this->definitions[$dependencyName] ?? null) {
            $this->resolveChain()->add(new ReflectionFunction($definition));

            return $definition($this);
        }

        // Next we check if any of our default initializers can initialize this class.
        if ($initializer = $this->initializerFor($className, $tag)) {
            $this->resolveChain()->add(new ReflectionClass($initializer));

            $object = match (true) {
                $initializer instanceof Initializer => $initializer->initialize($this->clone()),
                $initializer instanceof DynamicInitializer => $initializer->initialize($className, $this->clone()),
            };

            // Check whether the initializer's result should be registered as a singleton
            $singleton = Attributes::find(Singleton::class)->in($initializer::class)->first()
                ?? Attributes::find(Singleton::class)->in((new ReflectionClass($initializer))->getMethod('initialize'))->first();

            if ($singleton !== null) {
                $this->singleton($className, $object, $tag);
            }

            return $object;
        }

        // If we're requesting a tagged dependency and haven't resolved it at this point, something's wrong
        if ($tag) {
            throw new CannotResolveTaggedDependency($this->chain, new Dependency($className), $tag);
        }

        // Finally, autowire the class.
        return $this->autowire($className, ...$params);
    }

    private function initializerFor(string $className, ?string $tag = null): null|Initializer|DynamicInitializer
    {
        // Initializers themselves can't be initialized,
        // otherwise you'd end up with infinite loops
        if (is_a($className, Initializer::class, true) || is_a($className, DynamicInitializer::class, true)) {
            return null;
        }

        if ($initializerClass = $this->initializers[$this->resolveTaggedName($className, $tag)] ?? null) {
            return $this->resolve($initializerClass);
        }

        // Loop through the registered initializers to see if
        // we have something to handle this class.
        foreach ($this->dynamicInitializers as $initializerClass) {
            $initializer = $this->resolve($initializerClass);

            if (! $initializer->canInitialize($className)) {
                continue;
            }

            return $initializer;
        }

        return null;
    }

    private function autowire(string $className, mixed ...$params): object
    {
        $reflectionClass = new ReflectionClass($className);

        $constructor = $reflectionClass->getConstructor();

        if (! $reflectionClass->isInstantiable()) {
            throw new CannotInstantiateDependencyException($reflectionClass, $this->chain);
        }

        return $constructor === null
            // If there isn't a constructor, don't waste time
            // trying to build it.
            ? $reflectionClass->newInstanceWithoutConstructor()

            // Otherwise, use our autowireDependencies helper to automagically
            // build up each parameter.
            : $reflectionClass->newInstanceArgs(
                $this->autowireDependencies($constructor, $params),
            );
    }

    /**
     * @return ReflectionParameter[]
     */
    private function autowireDependencies(ReflectionMethod $method, array $parameters = []): array
    {
        $this->resolveChain()->add($method);

        $dependencies = [];

        // Build the class by iterating through its
        // dependencies and resolving them.
        foreach ($method->getParameters() as $parameter) {
            $tag = Attributes::find(Tag::class)->in($parameter)->first();

            $dependencies[] = $this->clone()->autowireDependency(
                parameter: $parameter,
                tag: $tag?->name,
                providedValue: $parameters[$parameter->getName()] ?? null,
            );
        }

        return $dependencies;
    }

    private function autowireDependency(ReflectionParameter $parameter, ?string $tag, mixed $providedValue = null): mixed
    {
        $parameterType = $parameter->getType();

        // If the parameter is a built-in type, immediately skip reflection
        // stuff and attempt to give it a default or null value.
        if ($parameterType instanceof ReflectionNamedType && $parameterType->isBuiltin()) {
            return $this->autowireBuiltinDependency($parameter, $providedValue);
        }

        // Convert the types to an array regardless, so we can handle
        // union types and single types the same.
        $types = match ($parameterType::class) {
            ReflectionNamedType::class => [$parameterType],
            ReflectionUnionType::class, ReflectionIntersectionType::class => $parameterType->getTypes(),
        };

        // Loop through each type until we hit a match.
        foreach ($types as $type) {
            try {
                return $this->autowireObjectDependency(
                    /** @phpstan-ignore-next-line  */
                    type: $type,
                    tag: $tag,
                    providedValue: $providedValue
                );
            } catch (Throwable $throwable) {
                // We were unable to resolve the dependency for the last union
                // type, so we are moving on to the next one. We hang onto
                // the exception in case it is a circular reference.
                $lastThrowable = $throwable;
            }
        }

        // If the dependency has a default value, we do our best to prevent
        // an error by using that.
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        // At this point, there is nothing else we can do; we don't know
        // how to autowire this dependency.
        throw $lastThrowable ?? new CannotAutowireException($this->chain, new Dependency($parameter));
    }

    private function autowireObjectDependency(ReflectionNamedType $type, ?string $tag, mixed $providedValue): mixed
    {
        // If the provided value is of the right type,
        // don't waste time autowiring, return it!
        if (is_a($providedValue, $type->getName())) {
            return $providedValue;
        }

        // If we can successfully retrieve an instance
        // of the necessary dependency, return it.
        if ($instance = $this->resolve(className: $type->getName(), tag: $tag)) {
            return $instance;
        }

        // At this point, there is nothing else we can do; we don't know
        // how to autowire this dependency.
        throw new CannotAutowireException($this->chain, new Dependency($type));
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
        throw new CannotAutowireException($this->chain, new Dependency($parameter));
    }

    private function clone(): self
    {
        return clone $this;
    }

    private function resolveChain(): DependencyChain
    {
        if ($this->chain === null) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

            $this->chain = new DependencyChain($trace[1]['file'] . ':' . $trace[1]['line']);
        }

        return $this->chain;
    }

    private function stopChain(): void
    {
        $this->chain = null;
    }

    public function __clone(): void
    {
        $this->chain = $this->chain?->clone();
    }

    private function resolveTaggedName(string $className, ?string $tag): string
    {
        return $tag
            ? "{$className}#{$tag}"
            : $className;
    }
}
