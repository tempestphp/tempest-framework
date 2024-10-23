<?php

declare(strict_types=1);

namespace Tempest {

    use Tempest\Container\GenericContainer;
    use Tempest\Reflection\FunctionReflector;
    use Tempest\Reflection\MethodReflector;

    /**
     * Retrieves an instance of the specified `$className` from the container.
     *
     * @template TClassName of object
     * @param class-string<TClassName> $className
     * @return TClassName
     */
    function get(string $className, ?string $tag = null, mixed ...$params): object
    {
        $container = GenericContainer::instance();

        return $container->get($className, $tag, ...$params);
    }

    /**
     * Invokes the given method, function, callable or invokable class from the container. If no named parameters are specified, they will be resolved from the container.
     *
     * #### Examples
     * ```php
     * \Tempest\invoke(function (MyService $service) {
     *   $service->execute();
     * });
     * ```
     * ```php
     * \Tempest\invoke(MyService::class, key: $apiKey);
     * ```
     */
    function invoke(MethodReflector|FunctionReflector|callable|string $callable, mixed ...$params): mixed
    {
        $container = GenericContainer::instance();

        return $container->invoke($callable, ...$params);
    }
}
