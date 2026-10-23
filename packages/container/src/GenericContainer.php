<?php

declare(strict_types=1);

namespace Tempest\Container;

use ArrayIterator;
use ArrayObject;
use Closure;
use Psr\Container\ContainerInterface;
use ReflectionFunction;
use Tempest\Container\Exceptions\CircularDependencyEncountered;
use Tempest\Container\Exceptions\DecoratorDidNotImplementInterface;
use Tempest\Container\Exceptions\DependencyCouldNotBeAutowired;
use Tempest\Container\Exceptions\DependencyCouldNotBeInstantiated;
use Tempest\Container\Exceptions\InvokedCallableWasInvalid;
use Tempest\Container\Exceptions\TaggedDependencyCouldNotBeResolved;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\FunctionReflector;
use Tempest\Reflection\MethodReflector;
use Tempest\Reflection\ParameterReflector;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use Throwable;
use UnitEnum;

use const ARRAY_FILTER_USE_BOTH;

final class GenericContainer implements Container
{
    use HasInstance;

    /**
     * Which initializer can initialize a given class and tag, or `false` when there is none. Unlike resolved
     * instances, this is kept when the container is reset, since it only depends on the registered initializers.
     *
     * @var ArrayObject<string, class-string<Initializer|DynamicInitializer>|false>
     */
    private ArrayObject $initializerLookups;

    /** @var ArrayObject<class-string, Initializer|DynamicInitializer> */
    private ArrayObject $resolvedInitializers;

    /*
     * Reflection data only depends on class definitions, which can't change at runtime,
     * so it is cached once and shared between all containers.
     */

    /** @var array<string, ClassReflector> */
    private static array $classReflectors = [];

    /** @var array<string, MethodReflector|null> */
    private static array $constructors = [];

    /** @var array<string, list<array{ParameterReflector, string|UnitEnum|null, bool}>> */
    private static array $parameters = [];

    /** @var array<string, list<array{PropertyReflector, Inject, bool}>> */
    private static array $injectedProperties = [];

    /** @var array<string, bool> */
    private static array $autowiredSingletons = [];

    /** @var array<string, Singleton|null> */
    private static array $initializerSingletons = [];

    public function __construct(
        /** @var ArrayIterator<array-key, mixed> $definitions */
        private(set) ArrayIterator $definitions = new ArrayIterator(),

        /** @var ArrayIterator<array-key, mixed> $singletonDefinitions */
        private(set) ArrayIterator $singletonDefinitions = new ArrayIterator(),

        /** @var ArrayIterator<array-key, object> $resolvedSingletons */
        private(set) ArrayIterator $resolvedSingletons = new ArrayIterator(),

        /** @var ArrayIterator<array-key, class-string> $initializers */
        private(set) ArrayIterator $initializers = new ArrayIterator(),

        /** @var ArrayIterator<array-key, class-string> $dynamicInitializers */
        private(set) ArrayIterator $dynamicInitializers = new ArrayIterator(),

        /** @var ArrayIterator<array-key, class-string[]> $decorators */
        private(set) ArrayIterator $decorators = new ArrayIterator(),

        /** @var ArrayIterator<array-key, class-string<\Tempest\Container\Resettable>> $resettables */
        private(set) ArrayIterator $resettables = new ArrayIterator(),

        private(set) ?DependencyChain $chain = null,
    ) {
        // These are objects, so that they are shared with clones of this container.
        $this->initializerLookups = new ArrayObject();
        $this->resolvedInitializers = new ArrayObject();

        $this->singleton(Container::class, $this);
        $this->singleton(ContainerInterface::class, $this);
        $this->singleton(GenericContainer::class, $this);
    }

    public function setDefinitions(array $definitions): self
    {
        $this->definitions = new ArrayIterator($definitions);

        return $this;
    }

    public function setSingletons(array $singletons): self
    {
        $this->singletonDefinitions = new ArrayIterator($singletons);

        return $this;
    }

    public function setInitializers(array $initializers): self
    {
        $this->initializers = new ArrayIterator($initializers);
        $this->initializerLookups->exchangeArray([]);

        return $this;
    }

    public function setDynamicInitializers(array $dynamicInitializers): self
    {
        $this->dynamicInitializers = new ArrayIterator($dynamicInitializers);
        $this->initializerLookups->exchangeArray([]);

        return $this;
    }

    public function setDecorators(array $decorators): self
    {
        $this->decorators = new ArrayIterator($decorators);

        return $this;
    }

    public function getDefinitions(): array
    {
        return $this->definitions->getArrayCopy();
    }

    /**
     * Returns all registered singletons. If `$interface` is specified, returns only singletons for that interface.
     */
    public function getSingletons(?string $interface = null): array
    {
        $singletons = $this->singletonDefinitions->getArrayCopy();

        if (is_null($interface)) {
            return $singletons;
        }

        return array_filter(
            array: $singletons,
            callback: static fn (mixed $_, string $key) => str_starts_with($key, "{$interface}#") || $key === $interface,
            mode: ARRAY_FILTER_USE_BOTH,
        );
    }

    public function getInitializers(): array
    {
        return $this->initializers->getArrayCopy();
    }

    public function getDynamicInitializers(): array
    {
        return $this->dynamicInitializers->getArrayCopy();
    }

    public function getDecorators(): array
    {
        return $this->decorators->getArrayCopy();
    }

    public function register(string $className, callable $definition): self
    {
        $this->definitions[$className] = $definition;

        return $this;
    }

    public function unregister(string $className, bool $tagged = false): self
    {
        unset($this->definitions[$className]);
        unset($this->singletonDefinitions[$className]);
        unset($this->resolvedSingletons[$className]);

        if ($tagged) {
            foreach ($this->singletonDefinitions as $key => $definition) {
                if (! str_starts_with($key, "{$className}#")) {
                    continue;
                }

                unset($this->singletonDefinitions[$key]);
            }

            foreach ($this->resolvedSingletons as $key => $definition) {
                if (! str_starts_with($key, "{$className}#")) {
                    continue;
                }

                unset($this->resolvedSingletons[$key]);
            }
        }

        return $this;
    }

    public function has(string $className, string|UnitEnum|null $tag = null): bool
    {
        return isset($this->definitions[$className]) || isset($this->singletonDefinitions[$this->resolveTaggedName($className, $tag)]);
    }

    public function singleton(string $className, mixed $definition, string|UnitEnum|null $tag = null): self
    {
        if ($definition instanceof HasTag) {
            $tag = $definition->tag;
        }

        $dependencyName = $this->resolveTaggedName($className, $tag);

        $this->singletonDefinitions[$dependencyName] = $definition;
        unset($this->resolvedSingletons[$dependencyName]);

        return $this;
    }

    public function config(object $config): self
    {
        $this->singleton($config::class, $config);

        foreach (new ClassReflector($config)->getInterfaces() as $interface) {
            $this->singleton($interface->getName(), $config);
        }

        return $this;
    }

    /**
     * @template TClassName of object
     * @param class-string<TClassName> $className
     * @return TClassName
     * @throws CircularDependencyEncountered
     * @throws TaggedDependencyCouldNotBeResolved
     */
    public function get(string $className, string|UnitEnum|null $tag = null, mixed ...$params): object
    {
        $previousChain = $this->chain;

        $this->resolveChain();

        try {
            return $this->resolve($className, $tag, ...$params);
        } finally {
            $this->stopChain($previousChain);
        }
    }

    public function invoke(ClassReflector|MethodReflector|FunctionReflector|callable|string $method, mixed ...$params): mixed
    {
        if ($method instanceof ClassReflector) {
            $method = $method->getMethod('__invoke');
        }

        if ($method instanceof MethodReflector) {
            return $this->invokeMethod($method, ...$params);
        }

        if ($method instanceof FunctionReflector) {
            return $this->invokeFunction($method, ...$params);
        }

        if ($method instanceof Closure) {
            return $this->invokeClosure($method, ...$params);
        }

        if (is_array($method)) {
            return $this->invokeClosure($method(...), ...$params);
        }

        if (method_exists($method, '__invoke')) {
            return $this->invokeClosure(
                $this->get($method)->__invoke(...),
                ...$params,
            );
        }

        throw new InvokedCallableWasInvalid(new Dependency($method));
    }

    private function invokeClosure(Closure $closure, mixed ...$params): mixed
    {
        $previousChain = $this->chain;

        $this->resolveChain();

        try {
            $parameters = $this->autowireDependencies(
                method: $reflector = new FunctionReflector($closure),
                parameters: $params,
            );
        } finally {
            $this->stopChain($previousChain);
        }

        return $reflector->invokeArgs($parameters);
    }

    private function invokeMethod(MethodReflector $method, mixed ...$params): mixed
    {
        $previousChain = $this->chain;

        $this->resolveChain();

        try {
            $object = $this->get($method->getDeclaringClass()->getName());

            $parameters = $this->autowireDependencies($method, $params);
        } finally {
            $this->stopChain($previousChain);
        }

        return $method->invokeArgs($object, $parameters);
    }

    private function invokeFunction(FunctionReflector|Closure $callback, mixed ...$params): mixed
    {
        $previousChain = $this->chain;

        $this->resolveChain();

        $reflector = match (true) {
            $callback instanceof FunctionReflector => $callback,
            default => new ReflectionFunction($callback),
        };

        try {
            $parameters = $this->autowireDependencies($reflector, $params);
        } finally {
            $this->stopChain($previousChain);
        }

        return $reflector->invokeArgs($parameters);
    }

    public function addInitializer(ClassReflector|string $initializerClass): Container
    {
        if (! $initializerClass instanceof ClassReflector) {
            $initializerClass = new ClassReflector($initializerClass);
        }

        // First, we check whether this is a DynamicInitializer,
        // which don't have a one-to-one mapping
        $this->initializerLookups->exchangeArray([]);

        if ($initializerClass->getType()->matches(DynamicInitializer::class)) {
            $this->dynamicInitializers[] = $initializerClass->getName();

            return $this;
        }

        $initializeMethod = $initializerClass->getMethod('initialize');

        // We resolve the optional Tag attribute from this initializer class
        $singleton = $initializeMethod->getAttribute(Singleton::class);

        // For normal Initializers, we'll use the return type
        // to determine which dependency they resolve
        $returnType = $initializeMethod->getReturnType();

        foreach ($returnType->split() as $type) {
            $this->initializers[$this->resolveTaggedName($type->getName(), $singleton?->tag)] = $initializerClass->getName();
        }

        return $this;
    }

    public function removeInitializer(ClassReflector|string $initializerClass): Container
    {
        if (! $initializerClass instanceof ClassReflector) {
            $initializerClass = new ClassReflector($initializerClass);
        }

        if ($initializerClass->getType()->matches(DynamicInitializer::class)) {
            $index = array_find_key(
                array: $this->dynamicInitializers->getArrayCopy(),
                callback: static fn (string $className) => $className === $initializerClass->getName(),
            );

            unset($this->dynamicInitializers[$index]);
            unset($this->resolvedInitializers[$initializerClass->getName()]);
            $this->initializerLookups->exchangeArray([]);

            return $this;
        }

        unset($this->initializers[$initializerClass->getName()]);
        unset($this->resolvedInitializers[$initializerClass->getName()]);
        $this->initializerLookups->exchangeArray([]);

        return $this;
    }

    public function addDecorator(ClassReflector|string $decoratorClass, ClassReflector|string $decoratedClass): Container
    {
        $decoratorClass = is_string($decoratorClass) ? $decoratorClass : $decoratorClass->getName();
        $decoratedClass = is_string($decoratedClass) ? $decoratedClass : $decoratedClass->getName();

        $this->decorators[$decoratedClass][] = $decoratorClass;

        return $this;
    }

    /**
     * @template TClassName of object
     * @param class-string<TClassName> $className
     * @return TClassName
     */
    private function resolve(string $className, string|UnitEnum|null $tag = null, mixed ...$params): object
    {
        $instance = $this->resolveDependency($className, $tag, ...$params);

        if ($this->decorators[$className] ?? null) {
            return $this->resolveDecorator($className, $instance, $tag, ...$params);
        }

        return $instance;
    }

    private function resolveDependency(string $className, string|UnitEnum|null $tag = null, mixed ...$params): object
    {
        $dependencyName = $this->resolveTaggedName($className, $tag);

        // Check if a resolved singleton is present. A singleton is only registered once it is fully
        // constructed, so it can never be in flight and cannot take part in a cycle.
        if ($instance = $this->resolvedSingletons[$dependencyName] ?? null) {
            return $instance;
        }

        $class = self::reflect($className);

        // Check if the class has been registered as a singleton.
        if ($singletonDefinition = $this->singletonDefinitions[$dependencyName] ?? null) {
            $this->resolveChain()->add($class);

            try {
                $instance = $singletonDefinition instanceof Closure ? $singletonDefinition($this) : $singletonDefinition;
            } finally {
                $this->resolveChain()->pop();
            }

            $this->resolvedSingletons[$dependencyName] = $instance;

            return $instance;
        }

        // Check if a callable has been registered to resolve this class.
        if ($definition = $this->definitions[$dependencyName] ?? null) {
            $this->resolveChain()->add(new FunctionReflector($definition));

            try {
                return $definition($this);
            } finally {
                $this->resolveChain()->pop();
            }
        }

        // Next we check if any of our default initializers can initialize this class.
        if (($initializer = $this->initializerForClass($class, $tag)) !== null) {
            $initializerClass = self::reflect($initializer::class);

            $this->resolveChain()->add($initializerClass);

            try {
                $object = match (true) {
                    $initializer instanceof Initializer => $initializer->initialize($this->clone()),
                    $initializer instanceof DynamicInitializer => $initializer->initialize($class, $tag, $this->clone()),
                };
            } finally {
                $this->resolveChain()->pop();
            }

            if (self::initializerSingleton($initializerClass) !== null) {
                $this->singleton($className, $object, $tag);
            }

            return $object;
        }

        // Check for dynamic tags
        if ($tag !== null) {
            /** @var \Tempest\Container\Singleton|null $singleton */
            $singleton = $class->getAttribute(Singleton::class);

            if (! $singleton || ! $singleton->dynamicTags) {
                throw new TaggedDependencyCouldNotBeResolved($this->chain, new Dependency($className), $tag);
            }

            return $this->autowire($className, $tag, ...$params);
        }

        // Finally, autowire the class.
        return $this->autowire($className, null, ...$params);
    }

    private function initializerForBuiltin(TypeReflector $target, string|UnitEnum $tag): ?Initializer
    {
        if ($initializerClass = $this->initializers[$this->resolveTaggedName($target->getName(), $tag)] ?? null) {
            return $this->resolveInitializer($initializerClass);
        }

        return null;
    }

    private function initializerForClass(ClassReflector $target, string|UnitEnum|null $tag = null): Initializer|DynamicInitializer|null
    {
        // Most classes don't have an initializer, and finding out requires asking every dynamic initializer,
        // so the outcome is remembered for every class and tag.
        $lookup = $this->resolveTaggedName($target->getName(), $tag);

        if (! isset($this->initializerLookups[$lookup])) {
            $this->initializerLookups[$lookup] = $this->findInitializerClass($target, $tag) ?? false;
        }

        $initializerClass = $this->initializerLookups[$lookup];

        if ($initializerClass === false) {
            return null;
        }

        return $this->resolveInitializer($initializerClass);
    }

    /**
     * @return class-string<Initializer|DynamicInitializer>|null
     */
    private function findInitializerClass(ClassReflector $target, string|UnitEnum|null $tag = null): ?string
    {
        // Initializers themselves can't be initialized,
        // otherwise you'd end up with infinite loops
        if ($target->getType()->matches(Initializer::class) || $target->getType()->matches(DynamicInitializer::class)) {
            return null;
        }

        if ($initializerClass = $this->initializers[$this->resolveTaggedName($target->getName(), $tag)] ?? null) {
            return $initializerClass;
        }

        // Check if dynamic tags are allowed
        if ($tag && ($initializerClass = $this->initializers[$target->getName()] ?? null)) {
            $class = new ClassReflector($initializerClass);

            $singleton = $class->getAttribute(Singleton::class) ?? $class->getMethod('initialize')->getAttribute(Singleton::class);

            if ($singleton?->dynamicTags) {
                return $class->getName();
            }
        }

        // Loop through the registered initializers to see if
        // we have something to handle this class.
        foreach ($this->dynamicInitializers as $initializerClass) {
            if ($this->resolveInitializer($initializerClass)->canInitialize(class: $target, tag: $tag)) {
                return $initializerClass;
            }
        }

        return null;
    }

    /**
     * @param class-string<Initializer|DynamicInitializer> $initializerClass
     */
    private function resolveInitializer(string $initializerClass): Initializer|DynamicInitializer
    {
        return $this->resolvedInitializers[$initializerClass] ??= $this->resolve($initializerClass);
    }

    private function autowire(string $className, string|UnitEnum|null $tag, mixed ...$params): object
    {
        $classReflector = self::reflect($className);

        $constructor = self::constructor($classReflector);

        if (! $classReflector->isInstantiable()) {
            throw new DependencyCouldNotBeInstantiated($classReflector, $this->chain);
        }

        $instance = $constructor instanceof MethodReflector
            // trying to build it.
            // If there isn't a constructor, don't waste time
            ? $classReflector->newInstanceArgs(
                $this->autowireDependencies($constructor, $params),
            )
            // build up each parameter.
            // Otherwise, use our autowireDependencies helper to automagically
            : $classReflector->newInstanceWithoutConstructor();

        if (self::isAutowiredSingleton($classReflector)) {
            $this->singleton($className, $instance, $tag);
        }

        foreach (self::injectedProperties($classReflector) as [$property, $inject, $lazy]) {
            if ($property->isInitialized($instance)) {
                continue;
            }

            if ($lazy) {
                $property->set($instance, $property
                    ->getType()
                    ->asClass()
                    ->getReflection()
                    ->newLazyProxy(
                        fn () => $this->get($property->getType()->getName(), $inject->tag),
                    ));
            } else {
                $property->set($instance, $this->get($property->getType()->getName(), $inject->tag));
            }
        }

        return $instance;
    }

    /**
     * @return ParameterReflector[]
     */
    private function autowireDependencies(MethodReflector|FunctionReflector $method, array $parameters = []): array
    {
        $this->resolveChain()->add($method);

        $dependencies = [];

        // Build the class by iterating through its
        // dependencies and resolving them.
        try {
            foreach (self::parameters($method) as [$parameter, $tag, $lazy]) {
                $dependencies[] = $this->clone()->autowireDependency(
                    parameter: $parameter,
                    tag: $tag,
                    lazy: $lazy,
                    providedValue: $parameters[$parameter->getName()] ?? null,
                );
            }
        } finally {
            $this->resolveChain()->pop();
        }

        return $dependencies;
    }

    private function autowireDependency(ParameterReflector $parameter, string|UnitEnum|null $tag, bool $lazy, mixed $providedValue = null): mixed
    {
        $parameterType = $parameter->getType();

        // If the parameter is a built-in type, skip reflection and attempt to provide the value by
        // tagged initializer, a default value or null value.
        if ($parameterType->isBuiltin()) {
            return $this->autowireBuiltinDependency($parameter, $tag, $providedValue);
        }

        // Loop through each type until we hit a match.
        foreach ($parameterType->split() as $type) {
            try {
                return $this->autowireObjectDependency(
                    type: $type,
                    tag: $tag,
                    providedValue: $providedValue,
                    lazy: $lazy,
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
        if ($parameter->hasDefaultValue()) {
            return $parameter->getDefaultValue();
        }

        // At this point, there is nothing else we can do; we don't know
        // how to autowire this dependency.
        throw $lastThrowable ?? new DependencyCouldNotBeAutowired($this->chain, new Dependency($parameter));
    }

    private function autowireObjectDependency(TypeReflector $type, string|UnitEnum|null $tag, mixed $providedValue, bool $lazy): mixed
    {
        // If the provided value is of the right type,
        // don't waste time autowiring, return it!
        if ($type->accepts($providedValue)) {
            return $providedValue;
        }

        if ($lazy) {
            return $type
                ->asClass()
                ->getReflection()
                ->newLazyProxy(fn () => $this->resolve(className: $type->getName(), tag: $tag));
        }

        // If we can successfully retrieve an instance
        // of the necessary dependency, return it.
        return $this->resolve(className: $type->getName(), tag: $tag);
    }

    private function autowireBuiltinDependency(ParameterReflector $parameter, string|UnitEnum|null $tag, mixed $providedValue): mixed
    {
        $typeName = $parameter->getType()->getName();

        if ($tag !== null && ($initializer = $this->initializerForBuiltin($parameter->getType(), $tag))) {
            $object = $initializer->initialize($this->clone());

            if (self::initializerSingleton(self::reflect($initializer::class)) !== null) {
                $this->singleton($typeName, $object, $tag);
            }

            return $object;
        }

        // Due to type coercion, the provided value may (or may not) work.
        // Here we give up trying to do type work for people. If they
        // didn't provide the right type, that's on them.
        if ($providedValue !== null) {
            return $providedValue;
        }

        // If the dependency has a default value, we might as well
        // use that at this point.
        if ($parameter->hasDefaultValue()) {
            return $parameter->getDefaultValue();
        }

        // If the dependency's type is an array or variadic variable, we'll
        // try to prevent an error by returning an empty array.
        if ($parameter->isVariadic() || $parameter->isIterable()) {
            return [];
        }

        // If the dependency's type allows null or is optional, we'll
        // try to prevent an error by returning null.
        if (! $parameter->isRequired()) {
            return null;
        }

        // At this point, there is nothing else we can do; we don't know
        // how to autowire this dependency.
        throw new DependencyCouldNotBeAutowired($this->chain, new Dependency($parameter));
    }

    private function clone(): self
    {
        return clone $this;
    }

    private function resolveChain(): DependencyChain
    {
        if (! $this->chain instanceof DependencyChain) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, limit: 2);

            $this->chain = new DependencyChain($trace[1]['file'] . ':' . $trace[1]['line']);
        }

        return $this->chain;
    }

    /**
     * Restores the parent resolution chain, or resets it to `null` at the top level.
     */
    private function stopChain(?DependencyChain $previous = null): void
    {
        $this->chain = $previous;
    }

    public function __clone(): void
    {
        $this->chain = $this->chain?->clone();
    }

    private function resolveTaggedName(string $className, string|UnitEnum|null $tag): string
    {
        if ($tag instanceof UnitEnum) {
            $tag = $tag->name;
        }

        return $tag
            ? "{$className}#{$tag}"
            : $className;
    }

    private function resolveDecorator(string $className, mixed $instance, string|UnitEnum|null $tag = null, mixed ...$params): object
    {
        foreach ($this->decorators[$className] ?? [] as $decoratorClass) {
            $constructor = self::constructor(self::reflect($decoratorClass));
            $parameters = $constructor?->getParameters();

            // we look for parameter holding decorated instance
            foreach ($parameters ?? [] as $parameter) {
                if ($parameter->getType()->matches($className) === false) {
                    continue;
                }

                // we bind the decorated instance to the parameter, so container won't try to resolve it (it would end up as circular dependency)
                $params[$parameter->getName()] = $instance;

                break;
            }

            $decorator = $this->resolveDependency($decoratorClass, $tag, ...$params);

            if (! $decorator instanceof $className) {
                throw new DecoratorDidNotImplementInterface($className, $decoratorClass);
            }

            $instance = $decorator;
        }

        return $instance;
    }

    private static function reflect(string $className): ClassReflector
    {
        return self::$classReflectors[$className] ??= new ClassReflector($className);
    }

    private static function constructor(ClassReflector $class): ?MethodReflector
    {
        $className = $class->getName();

        if (! array_key_exists($className, self::$constructors)) {
            self::$constructors[$className] = $class->getConstructor();
        }

        return self::$constructors[$className];
    }

    /**
     * @return list<array{ParameterReflector, string|UnitEnum|null, bool}> Each parameter along with its tag and whether it should be lazy.
     */
    private static function parameters(MethodReflector|FunctionReflector $method): array
    {
        $resolve = static fn (): array => array_map(
            static fn (ParameterReflector $parameter): array => [
                $parameter,
                $parameter->getAttribute(Tag::class)?->name,
                $parameter->hasAttribute(Proxy::class),
            ],
            iterator_to_array($method->getParameters(), preserve_keys: false),
        );

        // Functions and closures can't be identified by name, so only the parameters of methods are cached.
        if (! $method instanceof MethodReflector) {
            return $resolve();
        }

        return self::$parameters[$method->getReflection()->class . '::' . $method->getName()] ??= $resolve();
    }

    /**
     * @return list<array{PropertyReflector, Inject, bool}> Each injected property along with its attribute and whether it should be lazy.
     */
    private static function injectedProperties(ClassReflector $class): array
    {
        return self::$injectedProperties[$class->getName()] ??= array_values(array_filter(array_map(
            static function (PropertyReflector $property): ?array {
                $inject = $property->getAttribute(Inject::class);

                return $inject === null ? null : [$property, $inject, $property->hasAttribute(Proxy::class)];
            },
            $class->getProperties(),
        )));
    }

    private static function isAutowiredSingleton(ClassReflector $class): bool
    {
        return self::$autowiredSingletons[$class->getName()] ??=
            ! $class->getType()->matches(Initializer::class) && ! $class->getType()->matches(DynamicInitializer::class) && $class->hasAttribute(Singleton::class);
    }

    private static function initializerSingleton(ClassReflector $initializerClass): ?Singleton
    {
        $className = $initializerClass->getName();

        if (! array_key_exists($className, self::$initializerSingletons)) {
            self::$initializerSingletons[$className] = $initializerClass->getAttribute(Singleton::class) ?? $initializerClass
                ->getMethod('initialize')
                ->getAttribute(Singleton::class);
        }

        return self::$initializerSingletons[$className];
    }

    public function addResettable(string|ClassReflector $resettableClass): Container
    {
        if ($resettableClass instanceof ClassReflector) {
            $resettableClass = $resettableClass->getName();
        }

        $this->resettables[$resettableClass] = $resettableClass;

        return $this;
    }

    public function reset(): self
    {
        $this->resolvedSingletons = new ArrayIterator();
        $this->resolvedInitializers->exchangeArray([]);

        foreach ($this->resettables as $resettableClass) {
            /** @var Resettable $resettable */
            $resettable = $this->get($resettableClass);

            $resettable->reset();
        }

        return $this;
    }

    public function getResettables(): array
    {
        return $this->resettables->getArrayCopy();
    }

    public function setResettables(array $resettables): self
    {
        $this->resettables = new ArrayIterator($resettables);

        return $this;
    }
}
